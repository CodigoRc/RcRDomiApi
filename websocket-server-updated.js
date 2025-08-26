const express = require('express');
const https = require('https');
const fs = require('fs');
const { Server } = require('socket.io');

const app = express();

// Configuración SSL
const sslOptions = {
  key: fs.readFileSync('/etc/letsencrypt/live/rx.netdomi.com/privkey.pem'),
  cert: fs.readFileSync('/etc/letsencrypt/live/rx.netdomi.com/fullchain.pem')
};

const server = https.createServer(sslOptions, app);
const io = new Server(server, {
  cors: {
    origin: '*', // Mantener permisivo como estaba funcionando
    methods: ['GET', 'POST']
  }
});

// Permitir recibir JSON en peticiones HTTP
app.use(express.json());

// Configuración CORS simple para Express (sin duplicar headers)
app.use((req, res, next) => {
  // Solo setear si no existe ya (evitar duplicación)
  if (!res.getHeader('Access-Control-Allow-Origin')) {
    res.setHeader('Access-Control-Allow-Origin', '*');
  }
  next();
});

// Middleware de logging
app.use((req, res, next) => {
  console.log(`${new Date().toISOString()} - ${req.method} ${req.url}`);
  console.log('Headers:', req.headers);
  console.log('Body:', req.body);
  next();
});

// Endpoint HTTP para recibir actualizaciones desde Laravel
app.post('/api/ping', (req, res) => {
  const { total, service_id, data, manual_increment, current_count, type } = req.body;
  
  console.log('📡 Datos recibidos de Laravel:', {
    total,
    current_count,
    service_id,
    manual_increment,
    type,
    data,
    timestamp: new Date().toISOString()
  });

  // Preparar datos en el formato que espera el frontend
  const finalCount = current_count || total || 0; // Prioridad: current_count > total > 0
  const updateData = {
    service_id: parseInt(service_id),
    current_count: parseInt(finalCount),
    timestamp: new Date().toISOString()
  };

  // Si es incremento manual, agregar información adicional
  if (manual_increment) {
    updateData.manual_increment = parseInt(manual_increment);
    updateData.type = 'manual';
    console.log(`🔧 INCREMENTO MANUAL: Estación ${service_id} +${manual_increment} = ${total}`);
  } else {
    updateData.type = 'automatic';
  }

  // Emitir a todos los clientes conectados
  io.emit('updateCounter', updateData);
  
  console.log('✅ Datos enviados a clientes:', updateData);
  console.log(`📊 Clientes conectados: ${io.engine.clientsCount}`);

  res.json({ 
    status: 'ok', 
    clients_notified: io.engine.clientsCount,
    data_sent: updateData
  });
});

// Endpoint de salud para verificar que funciona
app.get('/health', (req, res) => {
  res.json({
    status: 'ok',
    timestamp: new Date().toISOString(),
    clients_connected: io.engine.clientsCount,
    uptime: process.uptime()
  });
});

// Evento de conexión de clientes WebSocket
io.on('connection', (socket) => {
  console.log(`🔌 Cliente conectado: ${socket.id} (Total: ${io.engine.clientsCount})`);

  // Enviar confirmación de conexión
  socket.emit('connected', {
    message: 'Conectado al servidor de estadísticas en tiempo real',
    server_time: new Date().toISOString(),
    socket_id: socket.id
  });

  // Puedes escuchar eventos personalizados si lo deseas
  socket.on('pingCounter', (data) => {
    console.log('🏓 Ping recibido desde cliente:', data);
    
    // Reenviar a todos los clientes
    const updateData = {
      service_id: data.service_id,
      current_count: data.total,
      type: 'ping',
      timestamp: new Date().toISOString()
    };
    
    io.emit('updateCounter', updateData);
  });

  // Suscripción a estación específica (para funcionalidad futura)
  socket.on('subscribeToStation', (data) => {
    const { stationId } = data;
    socket.join(`station_${stationId}`);
    console.log(`📻 Cliente ${socket.id} suscrito a estación ${stationId}`);
    
    socket.emit('subscribed', {
      stationId,
      message: `Suscrito a estación ${stationId}`
    });
  });

  socket.on('unsubscribeFromStation', (data) => {
    const { stationId } = data;
    socket.leave(`station_${stationId}`);
    console.log(`📻 Cliente ${socket.id} desuscrito de estación ${stationId}`);
  });

  socket.on('disconnect', () => {
    console.log(`❌ Cliente desconectado: ${socket.id} (Restantes: ${io.engine.clientsCount - 1})`);
  });

  socket.on('error', (error) => {
    console.error('❌ Error en socket:', error);
  });
});

// Iniciar el servidor en el puerto 3001
server.listen(3001, () => {
  console.log('🚀 Servidor Socket.IO corriendo en puerto 3001');
  console.log('🔐 SSL habilitado');
  console.log('📡 Esperando conexiones...');
});

// Manejo de errores del servidor
server.on('error', (error) => {
  console.error('❌ Error del servidor:', error);
});

process.on('SIGTERM', () => {
  console.log('🛑 Cerrando servidor...');
  server.close(() => {
    console.log('✅ Servidor cerrado correctamente');
    process.exit(0);
  });
});