# 🎯 WHMCS Integration Guide

Integración manual bajo demanda con WHMCS para Laravel API.

## 📋 Tabla de Contenidos

1. [Características](#características)
2. [Instalación](#instalación)
3. [Configuración](#configuración)
4. [Uso de la API](#uso-de-la-api)
5. [Integración con Angular](#integración-con-angular)
6. [Arquitectura](#arquitectura)
7. [Troubleshooting](#troubleshooting)

---

## ✨ Características

- ✅ **Manual y bajo demanda** - No sincronización automática
- ✅ **Bidireccional** - Laravel ↔ WHMCS
- ✅ **Auditable** - Logs completos de todas las operaciones
- ✅ **Rastreable** - Mapeo de entidades sincronizadas
- ✅ **Seguro** - Credenciales en .env, validación completa
- ✅ **Escalable** - Arquitectura modular lista para más módulos
- ✅ **Cache inteligente** - Reduce llamadas a WHMCS
- ✅ **Error handling** - Manejo robusto de errores

---

## 🚀 Instalación

### Paso 1: Ejecutar migraciones

```bash
cd RcDomintApi
php artisan migrate
```

Esto creará las tablas:
- `whmcs_sync_map` - Mapeo de entidades
- `whmcs_sync_logs` - Historial de operaciones

### Paso 2: Configurar credenciales

Agrega estas líneas a tu archivo `.env`:

```env
# WHMCS API Configuration
WHMCS_API_URL=https://tu-dominio.com/whmcs
WHMCS_API_IDENTIFIER=tu_api_identifier
WHMCS_API_SECRET=tu_api_secret
WHMCS_ENABLED=true
WHMCS_CACHE_ENABLED=true
WHMCS_LOG_ENABLED=true
```

### Paso 3: Probar conexión

```bash
# Vía terminal (curl)
curl -X GET http://tu-laravel-api.com/api/whmcs/sync/test

# Vía Postman/Insomnia
GET /api/whmcs/sync/test
```

Respuesta esperada:
```json
{
  "success": true,
  "message": "Connection successful",
  "whmcs_version": "8.x",
  "response_time_ms": 123
}
```

---

## ⚙️ Configuración

### Configurar WHMCS API

1. En WHMCS, ve a: **Setup > Staff Management > API Credentials**
2. Clic en **Create API Credential**
3. Configura:
   - **Admin User**: Selecciona un admin
   - **IP Restriction**: Agrega la IP de tu servidor Laravel
   - **Generate API Credential**: Copia el Identifier y Secret

### Personalizar mapeo de campos

Edita `config/whmcs.php`:

```php
'field_mapping' => [
    'client' => [
        'firstname' => 'client_name',      // WHMCS => Laravel
        'lastname' => 'client_lastname',
        'email' => 'email',
        // Personaliza según tu estructura
    ],
],
```

---

## 📡 Uso de la API

### 1. Enviar Cliente a WHMCS

**Endpoint:** `POST /api/whmcs/clients/push/{client_id}`

```bash
POST /api/whmcs/clients/push/123
Content-Type: application/json

{
  "force_create": false
}
```

**Respuesta:**
```json
{
  "success": true,
  "message": "Client pushed to WHMCS successfully",
  "whmcs_id": 456,
  "sync_map_id": 789,
  "operation": "created"
}
```

---

### 2. Actualizar Cliente en WHMCS

**Endpoint:** `POST /api/whmcs/clients/update/{client_id}`

```bash
POST /api/whmcs/clients/update/123
```

**Respuesta:**
```json
{
  "success": true,
  "message": "Client updated in WHMCS successfully",
  "whmcs_id": 456
}
```

---

### 3. Traer Cliente de WHMCS

**Endpoint:** `POST /api/whmcs/clients/pull/{whmcs_id}`

```bash
POST /api/whmcs/clients/pull/456
Content-Type: application/json

{
  "laravel_id": 123  // Opcional: si ya existe en Laravel
}
```

**Respuesta:**
```json
{
  "success": true,
  "message": "Client pulled from WHMCS successfully",
  "whmcs_client": {
    "id": 456,
    "firstname": "John",
    "lastname": "Doe",
    "email": "john@example.com"
  },
  "laravel_data": {
    "client_name": "John",
    "client_lastname": "Doe",
    "email": "john@example.com"
  }
}
```

---

### 4. Listar Clientes WHMCS

**Endpoint:** `POST /api/whmcs/clients/list`

```bash
POST /api/whmcs/clients/list
Content-Type: application/json

{
  "offset": 0,
  "limit": 25,
  "search": "john"
}
```

---

### 5. Verificar Sincronización

**Endpoint:** `POST /api/whmcs/clients/check/{client_id}`

```bash
POST /api/whmcs/clients/check/123
```

**Respuesta:**
```json
{
  "synced": true,
  "whmcs_id": 456,
  "sync_status": "synced",
  "last_synced_at": "2025-10-01 10:30:00"
}
```

---

### 6. Desvincular Cliente

**Endpoint:** `POST /api/whmcs/sync/unlink`

```bash
POST /api/whmcs/sync/unlink
Content-Type: application/json

{
  "entity_type": "client",
  "laravel_id": 123
}
```

---

### 7. Ver Logs de Sincronización

**Endpoint:** `POST /api/whmcs/sync/logs`

```bash
POST /api/whmcs/sync/logs
Content-Type: application/json

{
  "entity_type": "client",
  "status": "success",
  "days": 7,
  "limit": 50,
  "offset": 0
}
```

---

### 8. Ver Estadísticas

**Endpoint:** `GET /api/whmcs/sync/stats`

```bash
GET /api/whmcs/sync/stats
```

**Respuesta:**
```json
{
  "success": true,
  "stats": {
    "total_synced": 45,
    "total_pending": 3,
    "total_errors": 2,
    "by_entity_type": {
      "client": 45,
      "station": 12
    }
  }
}
```

---

## 🎨 Integración con Angular

### Servicio Angular

Crea `whmcs.service.ts`:

```typescript
import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';

@Injectable({
  providedIn: 'root'
})
export class WhmcsService {
  private apiUrl = 'https://tu-api.com/api/whmcs';

  constructor(private http: HttpClient) {}

  // Push cliente a WHMCS
  pushClientToWhmcs(clientId: number): Observable<any> {
    return this.http.post(`${this.apiUrl}/clients/push/${clientId}`, {});
  }

  // Actualizar cliente en WHMCS
  updateClientInWhmcs(clientId: number): Observable<any> {
    return this.http.post(`${this.apiUrl}/clients/update/${clientId}`, {});
  }

  // Verificar sincronización
  checkSync(clientId: number): Observable<any> {
    return this.http.post(`${this.apiUrl}/clients/check/${clientId}`, {});
  }

  // Desvincular
  unlinkClient(clientId: number): Observable<any> {
    return this.http.post(`${this.apiUrl}/sync/unlink`, {
      entity_type: 'client',
      laravel_id: clientId
    });
  }

  // Test conexión
  testConnection(): Observable<any> {
    return this.http.get(`${this.apiUrl}/sync/test`);
  }

  // Get logs
  getLogs(filters: any = {}): Observable<any> {
    return this.http.post(`${this.apiUrl}/sync/logs`, filters);
  }
}
```

### Componente Angular

```typescript
export class ClientDetailsComponent {
  constructor(private whmcsService: WhmcsService) {}

  pushToWhmcs(clientId: number) {
    this.whmcsService.pushClientToWhmcs(clientId).subscribe(
      response => {
        if (response.success) {
          console.log('Cliente sincronizado con WHMCS:', response.whmcs_id);
          this.showSuccessMessage('Cliente enviado a WHMCS exitosamente');
        }
      },
      error => {
        console.error('Error:', error);
        this.showErrorMessage('Error al sincronizar con WHMCS');
      }
    );
  }

  checkSyncStatus(clientId: number) {
    this.whmcsService.checkSync(clientId).subscribe(
      response => {
        if (response.synced) {
          console.log('Cliente sincronizado. WHMCS ID:', response.whmcs_id);
        } else {
          console.log('Cliente NO sincronizado');
        }
      }
    );
  }
}
```

### Template HTML

```html
<!-- Client Details Component -->
<div class="client-actions">
  <!-- Botón: Enviar a WHMCS -->
  <button 
    *ngIf="!client.whmcs_synced"
    (click)="pushToWhmcs(client.id)"
    class="btn btn-primary">
    <i class="icon-sync"></i> Enviar a WHMCS
  </button>

  <!-- Badge: Sincronizado -->
  <span 
    *ngIf="client.whmcs_synced"
    class="badge badge-success">
    <i class="icon-check"></i> Sincronizado con WHMCS
  </span>

  <!-- Botón: Actualizar en WHMCS -->
  <button 
    *ngIf="client.whmcs_synced"
    (click)="updateInWhmcs(client.id)"
    class="btn btn-secondary">
    <i class="icon-refresh"></i> Actualizar en WHMCS
  </button>

  <!-- Botón: Desvincular -->
  <button 
    *ngIf="client.whmcs_synced"
    (click)="unlinkFromWhmcs(client.id)"
    class="btn btn-warning">
    <i class="icon-unlink"></i> Desvincular
  </button>
</div>
```

---

## 🏗️ Arquitectura

```
┌─────────────┐         ┌──────────────┐         ┌─────────┐
│   Angular   │ ──────> │  Laravel API │ ──────> │  WHMCS  │
│  Frontend   │ <────── │   (Backend)  │ <────── │   API   │
└─────────────┘         └──────────────┘         └─────────┘
                              │
                              ├─ WHMCSApiService
                              ├─ WHMCSClientService
                              ├─ WHMCSController
                              ├─ WhmcsSyncMap (Model)
                              └─ WhmcsSyncLog (Model)
```

### Base de Datos

```
whmcs_sync_map
├─ entity_type: 'client', 'station', etc
├─ laravel_id: ID en Laravel
├─ whmcs_id: ID en WHMCS
├─ sync_status: synced, pending, error
└─ last_synced_at

whmcs_sync_logs
├─ operation: push, pull, update, delete
├─ status: success, error
├─ request_data: JSON
├─ response_data: JSON
└─ created_at
```

---

## 🔍 Troubleshooting

### Error: "Authentication Failed"

**Problema:** Credenciales incorrectas

**Solución:**
1. Verifica que `WHMCS_API_IDENTIFIER` y `WHMCS_API_SECRET` sean correctos
2. Asegúrate que la IP de tu servidor esté whitelisted en WHMCS

### Error: "Connection Timeout"

**Problema:** No puede conectar a WHMCS

**Solución:**
1. Verifica que `WHMCS_API_URL` sea correcto
2. Verifica que WHMCS esté accesible desde tu servidor
3. Aumenta `WHMCS_API_TIMEOUT` en .env

### Error: "Client already exists"

**Problema:** El email ya existe en WHMCS

**Solución:**
1. Usa `POST /api/whmcs/clients/list` para buscar el cliente en WHMCS
2. Vincúlalo manualmente creando un registro en `whmcs_sync_map`

### Ver logs de Laravel

```bash
tail -f storage/logs/laravel.log | grep WHMCS
```

### Probar en modo debug

En `.env`:
```env
WHMCS_LOG_ENABLED=true
WHMCS_LOG_REQUESTS=true
WHMCS_LOG_RESPONSES=true
```

---

## 📚 Próximos Módulos

Esta integración está lista para expandirse a:

- ✅ Clientes (implementado)
- 📋 Productos/Servicios
- 💰 Facturas
- 📦 Órdenes
- 🎫 Tickets
- 🌐 Dominios

Cada módulo seguirá la misma arquitectura y patrón de uso.

---

## 🤝 Soporte

Para reportar problemas o sugerencias, contacta al equipo de desarrollo.

---

**Versión:** 1.0.0  
**Última actualización:** Octubre 2025

