# 📤 INSTRUCCIONES DE DEPLOY VÍA FTP - RcRDomiApi

## 🎯 OBJETIVO
Subir solo los archivos necesarios para el sistema de estadísticas en tiempo real sin afectar la funcionalidad existente del API Laravel.

## 📁 ARCHIVOS A SUBIR

### 1. **Controlador Principal**
```
📂 app/Http/Controllers/
└── RdomiServiceStatsController.php
```

### 2. **Modelo de Estadísticas**
```
📂 app/Models/
└── ServiceStats.php
```

### 3. **Rutas del API**
```
📂 routes/
└── api.php
```

### 4. **Base de Datos**
```
📂 (archivo suelto)
└── database_stats_tables.sql
```

## 🚀 PASOS DE DEPLOY

### **PASO 1: RESPALDO (OBLIGATORIO)**
Antes de subir cualquier archivo, crear respaldos:

```bash
# En el servidor, crear respaldos
cp app/Http/Controllers/RdomiServiceStatsController.php app/Http/Controllers/RdomiServiceStatsController.php.backup
cp routes/api.php routes/api.php.backup
cp app/Models/ServiceStats.php app/Models/ServiceStats.php.backup
```

### **PASO 2: SUBIR ARCHIVOS VÍA FTP**

**Orden de subida:**
1. `app/Http/Controllers/RdomiServiceStatsController.php`
2. `app/Models/ServiceStats.php`
3. `routes/api.php`
4. `database_stats_tables.sql`

**Configuración FTP recomendada:**
- **Modo**: Binario
- **Permisos**: 644 para archivos, 755 para directorios
- **Backup**: Habilitado

### **PASO 3: EJECUTAR SQL**
Conectar a MySQL/MariaDB y ejecutar:

```sql
-- Conectar a la base de datos
USE tu_base_de_datos;

-- Ejecutar el script
source database_stats_tables.sql;

-- Verificar que las tablas se crearon
SHOW TABLES LIKE 'service_stats%';
```

### **PASO 4: LIMPIAR CACHE**
En el servidor, ejecutar:

```bash
php artisan config:clear
php artisan route:clear
php artisan cache:clear
php artisan view:clear
```

## ✅ VERIFICACIÓN POST-DEPLOY

### **1. Verificar Rutas**
```bash
# Listar rutas de estadísticas
php artisan route:list | grep rdomi
```

### **2. Probar Endpoints**
```bash
# Test endpoint básico
curl -X POST https://tu-dominio.com/api/rdomi/sts/service/ping \
  -H "Content-Type: application/json" \
  -d '{"service_id": 1}'

# Test endpoint avanzado
curl -X POST https://tu-dominio.com/api/rdomi/sts/service/ping-advanced \
  -H "Content-Type: application/json" \
  -d '{"service_id": 1}'
```

### **3. Verificar Base de Datos**
```sql
-- Verificar tablas creadas
SHOW TABLES LIKE 'service_stats%';

-- Verificar estructura
DESCRIBE service_stats_hourly;
DESCRIBE service_stats_daily;
DESCRIBE service_stats_realtime;
```

### **4. Verificar Logs**
```bash
# Revisar logs de Laravel
tail -f storage/logs/laravel.log

# Verificar que no hay errores
grep -i error storage/logs/laravel.log
```

## 🆘 ROLLBACK RÁPIDO

Si algo sale mal, restaurar inmediatamente:

```bash
# Restaurar archivos
cp app/Http/Controllers/RdomiServiceStatsController.php.backup app/Http/Controllers/RdomiServiceStatsController.php
cp routes/api.php.backup routes/api.php
cp app/Models/ServiceStats.php.backup app/Models/ServiceStats.php

# Limpiar cache
php artisan config:clear
php artisan route:clear
php artisan cache:clear
```

## 📊 ENDPOINTS NUEVOS

### **Estadísticas Avanzadas**
- `POST /api/rdomi/sts/service/ping-advanced` - Sistema anti-abuse
- `GET /api/rdomi/sts/analytics/hourly/{id}` - Estadísticas por hora
- `GET /api/rdomi/sts/analytics/daily/{id}` - Estadísticas diarias
- `GET /api/rdomi/sts/analytics/dashboard/{id}` - Dashboard completo

### **Estadísticas Originales (MANTENIDAS)**
- `POST /api/rdomi/sts/service/ping` - Incrementar contador
- `POST /api/rdomi/sts/service/status` - Obtener estadísticas
- `POST /api/rdomi/sts/service/health-check` - Múltiples estadísticas

## 🔌 INTEGRACIÓN WEBSOCKET

El sistema se conecta automáticamente a:
- **URL**: `https://rx.netdomi.com:3001/api/ping`
- **Método**: POST
- **Datos**: `service_id` y estadísticas enriquecidas

## ⚠️ CONSIDERACIONES IMPORTANTES

1. **No afecta funcionalidad existente** - Las rutas originales se mantienen
2. **Sistema de respaldo incluido** - Archivos `.backup` creados automáticamente
3. **Cache limpiado** - Laravel reconoce los cambios inmediatamente
4. **Base de datos segura** - Solo agrega tablas nuevas, no modifica existentes

## 📞 SOPORTE

Si encuentras problemas:
1. Revisar logs: `storage/logs/laravel.log`
2. Verificar permisos de archivos
3. Comprobar conectividad con WebSocket
4. Ejecutar rollback si es necesario

---
**🎵 RDOMI BROADCASTING - FTP Deploy Package v1.0** 