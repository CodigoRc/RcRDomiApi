# 📦 PAQUETE DE DEPLOY FTP - RcRDomiApi

## 🎯 OBJETIVO
Este paquete contiene SOLO los archivos necesarios para actualizar el sistema de estadísticas en tiempo real en el servidor existente, sin afectar la funcionalidad actual.

## 📁 CONTENIDO DEL PAQUETE

### 🔧 Archivos Principales
- `app/Http/Controllers/RdomiServiceStatsController.php` - Controlador principal de estadísticas
- `app/Models/ServiceStats.php` - Modelo de estadísticas
- `routes/api.php` - Rutas del API (solo las nuevas de estadísticas)
- `database_stats_tables.sql` - Script SQL para crear tablas de estadísticas
- `realtime-counter.html` - Página de monitoreo en tiempo real

### 📊 Base de Datos
- `database_stats_tables.sql` - Estructura completa de tablas de estadísticas

### 🔍 Archivos de Verificación
- `deploy_checklist.md` - Lista de verificación para el deploy
- `rollback_instructions.md` - Instrucciones de rollback si algo sale mal

## 🚀 INSTRUCCIONES DE DEPLOY

### 1. RESPALDO (OBLIGATORIO)
```bash
# Crear respaldo de archivos actuales
cp app/Http/Controllers/RdomiServiceStatsController.php app/Http/Controllers/RdomiServiceStatsController.php.backup
cp routes/api.php routes/api.php.backup
```

### 2. SUBIR ARCHIVOS VÍA FTP
Subir los archivos en este orden:
1. `app/Http/Controllers/RdomiServiceStatsController.php`
2. `app/Models/ServiceStats.php`
3. `routes/api.php`
4. `database_stats_tables.sql` (ejecutar en MySQL)
5. `realtime-counter.html` (copiar a carpeta public/)

### 3. EJECUTAR SQL
```sql
-- Ejecutar en MySQL/MariaDB
source database_stats_tables.sql;
```

### 4. LIMPIAR CACHE
```bash
php artisan config:clear
php artisan route:clear
php artisan cache:clear
```

## ⚠️ IMPORTANTE
- Este deploy NO afecta la funcionalidad existente
- Solo agrega nuevas rutas y funcionalidades
- Las rutas originales se mantienen intactas
- Sistema de respaldo incluido

## 🔄 ROLLBACK
Si algo sale mal, usar los archivos `.backup` para revertir cambios.

---
**🎵 RDOMI BROADCASTING - Deploy Package v1.0** 