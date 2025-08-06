# 🎵 RcRDomiApi

API Laravel para el sistema de estadísticas en tiempo real de RDOMI Broadcasting.

## 📋 Descripción

Este proyecto contiene el API Laravel que maneja las estadísticas de servicios (radio y TV) con integración WebSocket para actualizaciones en tiempo real.

## 🚀 Características

- **Estadísticas en Tiempo Real**: Sistema de tracking de visualizaciones
- **Anti-Abuse**: Detección de bots y comportamiento sospechoso
- **WebSocket Integration**: Comunicación en tiempo real con clientes
- **Rate Limiting**: Protección contra spam
- **Analytics Avanzados**: Estadísticas por hora, día y en tiempo real

## 🛠️ Tecnologías

- **Laravel 10+**
- **PHP 8.2+**
- **MySQL/MariaDB**
- **Socket.IO** (servidor separado)
- **Redis** (cache)

## 📁 Estructura del Proyecto

```
RcDomintApi/
├── app/
│   ├── Http/Controllers/
│   │   └── RdomiServiceStatsController.php  # Controlador principal
│   ├── Models/
│   │   └── ServiceStats.php                 # Modelo de estadísticas
│   └── Events/
│       └── StatsUpdated.php                 # Eventos WebSocket
├── routes/
│   └── api.php                              # Rutas del API
├── database/
│   └── migrations/                          # Migraciones de BD
└── deploy_package/                          # Archivos de deploy
```

## 🔧 Instalación

### Requisitos
- PHP 8.2+
- Composer
- MySQL/MariaDB
- Redis (opcional)

### Pasos

1. **Clonar el repositorio**
```bash
git clone https://github.com/CodigoRc/RcRDomiApi.git
cd RcRDomiApi
```

2. **Instalar dependencias**
```bash
composer install
```

3. **Configurar variables de entorno**
```bash
cp .env.example .env
# Editar .env con tus configuraciones
```

4. **Generar clave de aplicación**
```bash
php artisan key:generate
```

5. **Ejecutar migraciones**
```bash
php artisan migrate
```

6. **Optimizar Laravel**
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## 📡 Endpoints del API

### Estadísticas Básicas
- `POST /api/rdomi/sts/service/ping` - Incrementar contador
- `POST /api/rdomi/sts/service/status` - Obtener estadísticas
- `POST /api/rdomi/sts/service/health-check` - Múltiples estadísticas

### Estadísticas Avanzadas
- `POST /api/rdomi/sts/service/ping-advanced` - Sistema anti-abuse
- `GET /api/rdomi/sts/analytics/hourly/{id}` - Estadísticas por hora
- `GET /api/rdomi/sts/analytics/daily/{id}` - Estadísticas diarias
- `GET /api/rdomi/sts/analytics/dashboard/{id}` - Dashboard completo

## 🔌 Integración WebSocket

El API se integra con un servidor WebSocket separado en `rx.netdomi.com:3001` para enviar actualizaciones en tiempo real.

### Configuración WebSocket
```php
// En RdomiServiceStatsController.php
\Illuminate\Support\Facades\Http::post('https://rx.netdomi.com:3001/api/ping', [
    'service_id' => $serviceId,
    'data' => $enrichedResponse
]);
```

## 📊 Base de Datos

### Tablas Principales
- `service_stats` - Estadísticas básicas
- `service_stats_hourly` - Estadísticas por hora
- `service_stats_daily` - Estadísticas diarias
- `service_stats_realtime` - Cache en tiempo real
- `service_stats_events` - Log de eventos

## 🛡️ Seguridad

- **Rate Limiting**: 5 minutos por IP + servicio
- **Anti-Bot**: Detección de patrones sospechosos
- **CORS**: Configurado para dominios específicos
- **SSL**: Certificados Let's Encrypt

## 🚀 Deploy

### Servidor WebSocket
El servidor WebSocket está configurado en `rx.netdomi.com` con:
- Nginx como proxy
- Node.js + Socket.IO
- SSL automático
- PM2 para gestión de procesos

### API Laravel
El API Laravel puede desplegarse en cualquier servidor compatible con Laravel.

## 📝 Documentación

Para más detalles sobre la configuración del servidor WebSocket, ver:
- `deploy_package/DEPLOY_INSTRUCTIONS_OPTIMIZED.md`
- `deploy_package/database_stats_tables.sql`

## 🤝 Contribución

1. Fork el proyecto
2. Crea una rama para tu feature (`git checkout -b feature/AmazingFeature`)
3. Commit tus cambios (`git commit -m 'Add some AmazingFeature'`)
4. Push a la rama (`git push origin feature/AmazingFeature`)
5. Abre un Pull Request

## 📄 Licencia

Este proyecto está bajo la Licencia MIT. Ver el archivo `LICENSE` para más detalles.

## 📞 Contacto

- **Website**: https://rdomi.com
- **Email**: info@rdomi.com
- **GitHub**: https://github.com/CodigoRc

---

**🎵 RDOMI BROADCASTING - Powered by rdomi.com** 