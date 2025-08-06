# ✅ CHECKLIST DE DEPLOY - RcRDomiApi

## 📋 PRE-DEPLOY

- [ ] **Respaldar archivos actuales**
  - [ ] `app/Http/Controllers/RdomiServiceStatsController.php.backup`
  - [ ] `routes/api.php.backup`
  - [ ] Base de datos actual

- [ ] **Verificar espacio en disco**
  - [ ] Al menos 50MB libres
  - [ ] Permisos de escritura en directorios

- [ ] **Verificar conectividad**
  - [ ] FTP funcionando
  - [ ] Acceso a MySQL
  - [ ] Servidor WebSocket (rx.netdomi.com:3001) accesible

## 🚀 DURANTE EL DEPLOY

- [ ] **Subir archivos vía FTP**
  - [ ] `app/Http/Controllers/RdomiServiceStatsController.php`
  - [ ] `app/Models/ServiceStats.php`
  - [ ] `routes/api.php`
  - [ ] `database_stats_tables.sql`

- [ ] **Ejecutar SQL**
  - [ ] Conectar a MySQL/MariaDB
  - [ ] Ejecutar `source database_stats_tables.sql;`
  - [ ] Verificar que las tablas se crearon correctamente

- [ ] **Limpiar cache**
  - [ ] `php artisan config:clear`
  - [ ] `php artisan route:clear`
  - [ ] `php artisan cache:clear`

## ✅ POST-DEPLOY

- [ ] **Verificar rutas**
  - [ ] `POST /api/rdomi/sts/service/ping` - Funciona
  - [ ] `POST /api/rdomi/sts/service/ping-advanced` - Funciona
  - [ ] `GET /api/rdomi/sts/analytics/hourly/{id}` - Funciona
  - [ ] `GET /api/rdomi/sts/analytics/daily/{id}` - Funciona

- [ ] **Verificar base de datos**
  - [ ] Tabla `service_stats` existe
  - [ ] Tabla `service_stats_hourly` existe
  - [ ] Tabla `service_stats_daily` existe
  - [ ] Tabla `service_stats_realtime` existe
  - [ ] Tabla `service_stats_events` existe

- [ ] **Verificar integración WebSocket**
  - [ ] API puede conectar a `https://rx.netdomi.com:3001/api/ping`
  - [ ] Respuesta exitosa del servidor WebSocket

- [ ] **Verificar funcionalidad existente**
  - [ ] Rutas originales siguen funcionando
  - [ ] No hay errores en logs
  - [ ] Performance normal

## 🆘 EN CASO DE PROBLEMAS

- [ ] **Rollback inmediato**
  - [ ] Restaurar archivos `.backup`
  - [ ] Revertir cambios en base de datos
  - [ ] Limpiar cache nuevamente

- [ ] **Verificar logs**
  - [ ] `storage/logs/laravel.log`
  - [ ] Logs del servidor web
  - [ ] Logs de MySQL

## 📞 CONTACTO DE EMERGENCIA

- **Desarrollador**: [Tu contacto]
- **Servidor**: [Info del servidor]
- **Base de datos**: [Info de BD]

---
**✅ Checklist completado el: ___________**
**👤 Responsable: ___________** 