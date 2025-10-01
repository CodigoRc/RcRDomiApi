# 📖 WHMCS Integration - Quick Reference

Referencia rápida de endpoints y comandos.

---

## 🔗 Endpoints API

### Base URL
```
http://tu-api.com/api/whmcs
```

---

## 👥 CLIENTES

### Enviar a WHMCS
```bash
POST /whmcs/clients/push/{client_id}
Body: { "force_create": false }
```

### Actualizar en WHMCS
```bash
POST /whmcs/clients/update/{client_id}
```

### Traer de WHMCS
```bash
POST /whmcs/clients/pull/{whmcs_id}
Body: { "laravel_id": 123 }
```

### Listar WHMCS
```bash
POST /whmcs/clients/list
Body: { "offset": 0, "limit": 25, "search": "john" }
```

### Ver Cliente WHMCS
```bash
POST /whmcs/clients/get/{whmcs_id}
```

### Verificar Sync
```bash
POST /whmcs/clients/check/{client_id}
```

### Eliminar de WHMCS
```bash
POST /whmcs/clients/delete/{whmcs_id}
Body: { "confirm": true }
```

---

## 🔗 VINCULACIÓN MANUAL (Read-Only)

**Estos endpoints permiten vincular clientes de Laravel con WHMCS sin sincronizar datos.**  
Ideal para ver información de WHMCS sin modificar nada.

### Vincular Cliente a WHMCS
```bash
POST /clients/{client_id}/link-whmcs
Body: { "whmcs_id": 123 }

# Ejemplo
curl -X POST http://rdomint.com/api/clients/45/link-whmcs \
  -H "Content-Type: application/json" \
  -d '{"whmcs_id": 123}'
```

**Respuesta:**
```json
{
  "success": true,
  "message": "Client linked to WHMCS successfully",
  "sync_map": {
    "id": 1,
    "entity_type": "client",
    "laravel_id": 45,
    "whmcs_id": 123,
    "sync_status": "synced",
    "linked_at": "2025-10-01 10:30:00"
  },
  "whmcs_client": {
    "id": 123,
    "name": "John Doe",
    "email": "john@example.com"
  }
}
```

### Ver Info WHMCS del Cliente
```bash
GET /clients/{client_id}/whmcs-info

# Ejemplo
curl -X GET http://rdomint.com/api/clients/45/whmcs-info
```

**Respuesta:**
```json
{
  "success": true,
  "linked": true,
  "whmcs_id": 123,
  "laravel_id": 45,
  "linked_at": "2025-10-01 10:30:00",
  "whmcs_data": {
    "client": {
      "id": 123,
      "firstname": "John",
      "lastname": "Doe",
      "email": "john@example.com",
      "status": "Active",
      "credit": "150.00"
    },
    "stats": {
      "productsnumactive": 3,
      "numdueinvoices": 2,
      "numtickets": 1
    }
  }
}
```

### Desvincular Cliente de WHMCS
```bash
DELETE /clients/{client_id}/unlink-whmcs

# Ejemplo
curl -X DELETE http://rdomint.com/api/clients/45/unlink-whmcs
```

**Respuesta:**
```json
{
  "success": true,
  "message": "Client unlinked from WHMCS successfully",
  "note": "No data was deleted from either system"
}
```

**⚠️ Importante:**
- ✅ **No sincroniza datos**: Solo guarda la relación
- ✅ **No modifica WHMCS**: Los datos permanecen intactos
- ✅ **Read-only**: Solo lectura de información
- ✅ **Seguro**: Desvincular no borra nada

---

## ⚙️ GESTIÓN SYNC

### Test Conexión
```bash
GET /whmcs/sync/test
```

### Ver Logs
```bash
POST /whmcs/sync/logs
Body: {
  "entity_type": "client",
  "status": "success",
  "days": 7,
  "limit": 50
}
```

### Ver Mapeo
```bash
POST /whmcs/sync/map
Body: {
  "entity_type": "client",
  "sync_status": "synced"
}
```

### Ver Estadísticas
```bash
GET /whmcs/sync/stats
```

### Desvincular
```bash
POST /whmcs/sync/unlink
Body: {
  "entity_type": "client",
  "laravel_id": 123
}
```

### Ver Config
```bash
GET /whmcs/sync/config
```

### Limpiar Cache
```bash
POST /whmcs/sync/clear-cache
```

---

## 🎨 Angular: Ejemplos de Código

### Service Method
```typescript
pushToWhmcs(clientId: number) {
  return this.http.post(`${this.apiUrl}/whmcs/clients/push/${clientId}`, {});
}
```

### Component Method
```typescript
syncClient(clientId: number) {
  this.whmcsService.pushToWhmcs(clientId).subscribe(
    res => console.log('Synced!', res.whmcs_id),
    err => console.error('Error:', err)
  );
}
```

### Template
```html
<button (click)="syncClient(client.id)">
  Enviar a WHMCS
</button>

<span *ngIf="client.whmcs_synced" class="badge">
  Sincronizado
</span>
```

---

## 🛠️ Comandos Laravel

### Probar en Tinker
```bash
php artisan tinker

# Test conexión
>>> $api = app(\App\Services\WHMCS\WHMCSApiService::class);
>>> $api->testConnection();

# Push cliente
>>> $service = app(\App\Services\WHMCS\WHMCSClientService::class);
>>> $client = \App\Models\RcControlClient::find(1);
>>> $service->pushToWHMCS($client);

# Ver sync map
>>> \App\Models\WhmcsSyncMap::all();

# Ver logs
>>> \App\Models\WhmcsSyncLog::latest()->take(10)->get();
```

### Ver Logs
```bash
tail -f storage/logs/laravel.log | grep WHMCS
```

### Limpiar Cache
```bash
php artisan config:clear
php artisan cache:clear
```

---

## 🔍 Consultas SQL Útiles

### Ver mapeo actual
```sql
SELECT * FROM whmcs_sync_map 
WHERE entity_type = 'client' 
AND sync_status = 'synced';
```

### Ver logs recientes
```sql
SELECT * FROM whmcs_sync_logs 
WHERE entity_type = 'client' 
ORDER BY created_at DESC 
LIMIT 20;
```

### Ver errores
```sql
SELECT * FROM whmcs_sync_logs 
WHERE status = 'error' 
ORDER BY created_at DESC;
```

### Estadísticas
```sql
SELECT 
  entity_type, 
  sync_status, 
  COUNT(*) as count 
FROM whmcs_sync_map 
GROUP BY entity_type, sync_status;
```

---

## ⚡ Variables .env

```env
WHMCS_API_URL=https://tu-dominio.com/whmcs
WHMCS_API_IDENTIFIER=xxxxx
WHMCS_API_SECRET=yyyyy
WHMCS_ENABLED=true
WHMCS_CACHE_ENABLED=true
WHMCS_LOG_ENABLED=true
```

---

## 🎯 Flujo Típico

1. **Usuario en Angular** → Click "Enviar a WHMCS"
2. **Angular** → `POST /whmcs/clients/push/123`
3. **Laravel** → Valida cliente, mapea campos
4. **WHMCS API** → Crea cliente, retorna ID
5. **Laravel** → Guarda mapeo en `whmcs_sync_map`
6. **Laravel** → Registra en `whmcs_sync_logs`
7. **Angular** → Muestra "✅ Sincronizado"

---

## 🔒 Seguridad

- ✅ Credenciales en .env (nunca en código)
- ✅ IP whitelisted en WHMCS
- ✅ Validación de datos antes de enviar
- ✅ Logs sanitizados (passwords redacted)
- ✅ Confirmación para operaciones destructivas

---

## 📊 Respuestas Comunes

### Éxito
```json
{
  "success": true,
  "message": "Operation successful",
  "whmcs_id": 456
}
```

### Error
```json
{
  "success": false,
  "error": "Client not found",
  "whmcs_result": "error"
}
```

### Sincronizado
```json
{
  "synced": true,
  "whmcs_id": 456,
  "sync_status": "synced"
}
```

### No Sincronizado
```json
{
  "synced": false,
  "message": "Client is not synced with WHMCS"
}
```

---

## 🚨 Errores Comunes

| Error | Causa | Solución |
|-------|-------|----------|
| Invalid API Credentials | Credenciales mal configuradas | Verifica .env |
| IP Not Whitelisted | IP no permitida | Agrega IP en WHMCS |
| Connection Timeout | No puede conectar | Verifica URL y firewall |
| Client Already Exists | Email duplicado | Busca y vincula manualmente |

---

## 📱 Estados de Sincronización

| Estado | Significado |
|--------|-------------|
| `synced` | Sincronizado correctamente |
| `pending` | Pendiente de sincronización |
| `error` | Error en la última sincronización |
| `conflict` | Conflicto entre Laravel y WHMCS |
| `unlinked` | Desvinculado |

---

## 🎨 Estados Visuales (Angular)

```typescript
getStatusBadgeClass(status: string): string {
  return {
    'synced': 'badge-success',
    'pending': 'badge-warning',
    'error': 'badge-danger',
    'conflict': 'badge-warning',
    'unlinked': 'badge-secondary'
  }[status] || 'badge-secondary';
}
```

```html
<span [class]="'badge ' + getStatusBadgeClass(syncStatus)">
  {{ syncStatus }}
</span>
```

---

## 🔄 Operaciones

| Operación | Dirección | Descripción |
|-----------|-----------|-------------|
| `push` | Laravel → WHMCS | Crear en WHMCS |
| `pull` | WHMCS → Laravel | Importar de WHMCS |
| `update_whmcs` | Laravel → WHMCS | Actualizar WHMCS |
| `update_laravel` | WHMCS → Laravel | Actualizar Laravel |
| `delete` | - | Desvincular |

---

**Última actualización:** Octubre 2025

