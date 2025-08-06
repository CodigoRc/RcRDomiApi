# 🔄 INSTRUCCIONES DE ROLLBACK - RcRDomiApi

## 🚨 CUÁNDO HACER ROLLBACK

- Error 500 en cualquier endpoint
- Base de datos corrupta
- Conflictos con funcionalidad existente
- Problemas de performance críticos
- Errores de WebSocket

## ⚡ ROLLBACK RÁPIDO

### 1. RESTAURAR ARCHIVOS
```bash
# Restaurar controlador
cp app/Http/Controllers/RdomiServiceStatsController.php.backup app/Http/Controllers/RdomiServiceStatsController.php

# Restaurar rutas
cp routes/api.php.backup routes/api.php
```

### 2. LIMPIAR CACHE
```bash
php artisan config:clear
php artisan route:clear
php artisan cache:clear
```

### 3. VERIFICAR FUNCIONALIDAD
```bash
# Probar endpoint original
curl -X POST https://tu-dominio.com/api/rdomi/sts/service/ping \
  -H "Content-Type: application/json" \
  -d '{"service_id": 1}'
```

## 🔧 ROLLBACK COMPLETO

### 1. RESTAURAR BASE DE DATOS
```sql
-- Eliminar tablas nuevas (si es necesario)
DROP TABLE IF EXISTS service_stats_hourly;
DROP TABLE IF EXISTS service_stats_daily;
DROP TABLE IF EXISTS service_stats_realtime;
DROP TABLE IF EXISTS service_stats_events;

-- Verificar que service_stats original sigue funcionando
SELECT * FROM service_stats WHERE type = 'view' LIMIT 5;
```

### 2. RESTAURAR ARCHIVOS
```bash
# Lista completa de archivos a restaurar
cp app/Http/Controllers/RdomiServiceStatsController.php.backup app/Http/Controllers/RdomiServiceStatsController.php
cp routes/api.php.backup routes/api.php
rm -f app/Models/ServiceStats.php  # Si no existía antes
```

### 3. VERIFICACIÓN COMPLETA
```bash
# Verificar que no hay errores
php artisan route:list | grep rdomi

# Verificar logs
tail -f storage/logs/laravel.log

# Probar endpoints originales
curl -X POST https://tu-dominio.com/api/rdomi/sts/service/ping -H "Content-Type: application/json" -d '{"service_id": 1}'
curl -X POST https://tu-dominio.com/api/rdomi/sts/service/status -H "Content-Type: application/json" -d '{"service_id": 1}'
```

## 📊 VERIFICACIÓN POST-ROLLBACK

### ✅ Endpoints que DEBEN funcionar
- `POST /api/rdomi/sts/service/ping` ✅
- `POST /api/rdomi/sts/service/status` ✅
- `POST /api/rdomi/sts/service/health-check` ✅

### ❌ Endpoints que NO deben funcionar (rollback exitoso)
- `POST /api/rdomi/sts/service/ping-advanced` ❌
- `GET /api/rdomi/sts/analytics/hourly/{id}` ❌
- `GET /api/rdomi/sts/analytics/daily/{id}` ❌

## 🆘 CONTACTO DE EMERGENCIA

**Si el rollback no funciona:**

1. **Reiniciar servicios**
```bash
sudo systemctl restart apache2  # o nginx
sudo systemctl restart mysql
```

2. **Verificar permisos**
```bash
chmod -R 755 storage/
chmod -R 755 bootstrap/cache/
```

3. **Contactar soporte**
- **Email**: [tu-email]
- **Teléfono**: [tu-telefono]
- **Horario**: [horario-disponibilidad]

## 📝 DOCUMENTAR ROLLBACK

```bash
# Crear reporte de rollback
echo "ROLLBACK EJECUTADO: $(date)" >> rollback_log.txt
echo "Motivo: [DESCRIBIR PROBLEMA]" >> rollback_log.txt
echo "Archivos restaurados: [LISTA]" >> rollback_log.txt
echo "Estado: [EXITOSO/FRACASO]" >> rollback_log.txt
```

---
**🔄 Rollback completado el: ___________**
**👤 Responsable: ___________**
**📞 Contacto: ___________** 