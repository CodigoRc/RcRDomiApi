# 🚀 INSTRUCCIONES DE DEPLOY - Sistema Avanzado de Estadísticas

## ✅ **ARCHIVOS A SUBIR:**

### **1. Controller Actualizado:**
- **Archivo**: `RdomiServiceStatsController.php`
- **Destino**: `/app/Http/Controllers/RdomiServiceStatsController.php`
- **Acción**: ⚠️ **HACER BACKUP PRIMERO**, luego reemplazar

### **2. Rutas Actualizadas:**
- **Archivo**: `api.php`
- **Destino**: `/routes/api.php`
- **Acción**: ⚠️ **HACER BACKUP PRIMERO**, luego reemplazar

### **3. Base de Datos:**
- **Archivo**: `database_stats_tables.sql`
- **Acción**: Ejecutar en phpMyAdmin

---

## 📋 **PASOS DE DEPLOY:**

### **PASO 1: Backup de Seguridad**
```bash
# Desde cPanel File Manager o FTP:
1. Ir a /app/Http/Controllers/
2. Copiar RdomiServiceStatsController.php → RdomiServiceStatsController_BACKUP.php
3. Ir a /routes/
4. Copiar api.php → api_BACKUP.php
```

### **PASO 2: Base de Datos**
1. **Ir a phpMyAdmin**: https://rcdomi.com:2083/cpsess9471839499/3rdparty/phpMyAdmin/
2. **Seleccionar base de datos**: `rcdomico_rdomiap_db`
3. **Ir a pestaña "SQL"**
4. **Copiar y pegar** todo el contenido de `database_stats_tables.sql`
5. **Ejecutar** (botón "Go" o "Continuar")

### **PASO 3: Subir Archivos**
1. **Controller**:
   - Subir `RdomiServiceStatsController.php` a `/app/Http/Controllers/`
   - Reemplazar el archivo existente
   
2. **Rutas**:
   - Subir `api.php` a `/routes/`
   - Reemplazar el archivo existente

### **PASO 4: Verificar Permisos**
```bash
# Asegurar que los archivos tienen permisos correctos:
- Controllers: 644 o 755
- Routes: 644
- Verificar que Apache/Nginx puede leer los archivos
```

---

## 🧪 **TESTING POST-DEPLOY:**

### **Test 1: Endpoint Original (Compatibilidad)**
```bash
curl -X POST -H "Content-Type: application/json" \
  -d '{"service_id": 1596}' \
  "https://rcdomi.com/api/rdomi/sts/service/ping"
```
**Resultado esperado**: `{"message": "Request processed successfully", "service_id": 1596, "code": 200}`

### **Test 2: Endpoint Avanzado (Nuevo)**
```bash
curl -X POST -H "Content-Type: application/json" \
  -d '{"service_id": 1596}' \
  "https://rcdomi.com/api/rdomi/sts/service/ping-advanced"
```
**Resultado esperado**: 
```json
{
  "message": "Request processed successfully",
  "service_id": 1596,
  "current_count": 7802615,
  "today_total": 0,
  "today_unique": 0,
  "current_listeners": 1,
  "last_update": "2024-01-15T10:30:00.000Z",
  "code": 200
}
```

### **Test 3: Analytics**
```bash
curl "https://rcdomi.com/api/rdomi/sts/analytics/hourly/1596"
```

### **Test 4: Base de Datos**
```sql
-- Ejecutar en phpMyAdmin para verificar:
SHOW TABLES LIKE 'service_stats%';
SELECT * FROM service_stats_hourly LIMIT 5;
```

---

## 🚨 **TROUBLESHOOTING:**

### **Error 500 - Internal Server Error:**
1. **Verificar logs de Apache/PHP**
2. **Verificar sintaxis PHP**: 
   ```bash
   php -l RdomiServiceStatsController.php
   ```
3. **Verificar permisos de archivos**
4. **Verificar que Laravel está funcionando**

### **Error 404 - Not Found:**
1. **Verificar que las rutas están cargadas**
2. **Verificar configuración de Apache/Nginx**
3. **Verificar .htaccess o configuración de rewrite**

### **Error de Base de Datos:**
1. **Verificar conexión a DB en .env**
2. **Verificar que las tablas se crearon**
3. **Verificar permisos de usuario de DB**

### **Error de Memory/Performance:**
1. **Aumentar `memory_limit` en php.ini**
2. **Verificar `max_execution_time`**
3. **Optimizar consultas si es necesario**

---

## 🔧 **CONFIGURACIÓN ADICIONAL:**

### **Optimización de Performance:**
```php
// En .env o config/cache.php, asegurar:
CACHE_DRIVER=redis  # o memcached si está disponible
DB_CONNECTION=mysql
```

### **Logging (Opcional):**
```php
// Para debugging, en .env:
LOG_LEVEL=debug
APP_DEBUG=true  # Solo en desarrollo
```

---

## 📊 **MONITOREO POST-DEPLOY:**

### **Verificar que funciona:**
1. **Logs de Apache**: Verificar que no hay errores 500
2. **Logs de Laravel**: `storage/logs/laravel.log`
3. **DB Growth**: Verificar que las nuevas tablas reciben datos
4. **Performance**: Monitorear tiempo de respuesta

### **Métricas a Observar:**
- **Requests/segundo** en endpoints nuevos
- **Crecimiento de tablas** `service_stats_*`
- **Detección de abuse** en `service_stats_events`
- **Cache hit ratio** si se usa Redis/Memcached

---

## ⚡ **ROLLBACK (Si algo sale mal):**

### **Rollback Rápido:**
```bash
1. Restaurar controller backup:
   RdomiServiceStatsController_BACKUP.php → RdomiServiceStatsController.php

2. Restaurar rutas backup:
   api_BACKUP.php → api.php

3. Las nuevas tablas NO afectan el sistema original, 
   se pueden dejar o eliminar después.
```

### **Rollback de DB (Si es necesario):**
```sql
-- Solo si es absolutamente necesario:
DROP TABLE IF EXISTS service_stats_hourly;
DROP TABLE IF EXISTS service_stats_daily;
DROP TABLE IF EXISTS service_stats_realtime;
DROP TABLE IF EXISTS service_stats_sessions;
DROP TABLE IF EXISTS service_stats_events;
DROP VIEW IF EXISTS v_today_stats;
DROP VIEW IF EXISTS v_hourly_top_stations;
```

---

## ✅ **CHECKLIST FINAL:**

- [ ] ✅ Backup creado (controller + rutas)
- [ ] ✅ Base de datos ejecutada sin errores
- [ ] ✅ Archivos subidos con permisos correctos
- [ ] ✅ Test de endpoint original funciona
- [ ] ✅ Test de endpoint avanzado funciona
- [ ] ✅ Analytics endpoints responden
- [ ] ✅ Logs sin errores críticos
- [ ] ✅ Performance aceptable

---

## 🎯 **SIGUIENTE FASE: WebSocket Broadcasting**

Una vez que este deploy funcione correctamente, procederemos con:
1. **Implementar WebSocket server**
2. **Broadcasting en tiempo real**
3. **Dashboard web para analytics**

---

*Preparado para deploy en: rcdomi.com*  
*Base de datos: rcdomico_rdomiap_db*  
*Fecha: 2024-01-15*