# 🚀 Instrucciones de Instalación - Integración WHMCS

## Paso a Paso - Setup Completo

### 1️⃣ Configurar WHMCS API (En tu panel WHMCS)

1. **Accede a WHMCS Admin Panel**
   ```
   https://tu-dominio.com/whmcs/admin
   ```

2. **Navega a API Credentials**
   - Ve a: `Setup` → `Staff Management` → `API Credentials`
   - O directo: `https://tu-dominio.com/whmcs/admin/configaddonmods.php`

3. **Crear API Credential**
   - Click en **"Generate New API Credential"**
   - Configura:
     ```
     Admin User: [Selecciona un usuario admin]
     Description: Laravel API Integration
     IP Restriction: [IP de tu servidor Laravel] (IMPORTANTE!)
     ```
   
4. **Copiar Credenciales**
   - Una vez creado, verás:
     - **API Identifier:** `xxxxxxxxxxxxxxxxxxxxx`
     - **API Secret:** `yyyyyyyyyyyyyyyyyyyyyyy`
   - **⚠️ IMPORTANTE:** Guarda el Secret inmediatamente, solo se muestra una vez

5. **Whitelist IP**
   - Si no configuraste IP antes, ve a:
     `Setup` → `General Settings` → `Security` → `API IP Access Restriction`
   - Agrega la IP de tu servidor Laravel

---

### 2️⃣ Configurar Laravel API

#### A. Agregar variables al .env

Abre el archivo `.env` de tu proyecto Laravel y agrega:

```bash
# ============================================
# WHMCS API Configuration
# ============================================
WHMCS_API_URL=https://tu-dominio.com/whmcs
WHMCS_API_IDENTIFIER=xxxxxxxxxxxxxxxxxxxxx
WHMCS_API_SECRET=yyyyyyyyyyyyyyyyyyyyyyy

# Enable/Disable WHMCS integration
WHMCS_ENABLED=true

# API Timeout (seconds)
WHMCS_API_TIMEOUT=30

# Cache Settings
WHMCS_CACHE_ENABLED=true
WHMCS_CACHE_TTL=300

# Logging Settings
WHMCS_LOG_ENABLED=true
WHMCS_LOG_REQUESTS=true
WHMCS_LOG_RESPONSES=true

# Testing Mode
WHMCS_TEST_MODE=false

# Auto-retry failed syncs
WHMCS_AUTO_RETRY=false
```

**Reemplaza:**
- `https://tu-dominio.com/whmcs` → Tu URL de WHMCS
- `xxxxxxxxxxxxxxxxxxxxx` → Tu API Identifier
- `yyyyyyyyyyyyyyyyyyyyyyy` → Tu API Secret

#### B. Ejecutar migraciones

```bash
cd RcDomintApi
php artisan migrate
```

Esto creará las tablas:
- ✅ `whmcs_sync_map` - Mapeo de entidades Laravel ↔ WHMCS
- ✅ `whmcs_sync_logs` - Historial de operaciones

#### C. Limpiar cache de configuración

```bash
php artisan config:clear
php artisan cache:clear
```

---

### 3️⃣ Probar la Conexión

#### Opción A: Via CURL

```bash
curl -X GET http://tu-laravel-api.com/api/whmcs/sync/test
```

#### Opción B: Via Postman/Insomnia

```
GET http://tu-laravel-api.com/api/whmcs/sync/test
```

#### Opción C: Via PHP Artisan Tinker

```bash
php artisan tinker

>>> $api = app(\App\Services\WHMCS\WHMCSApiService::class);
>>> $result = $api->testConnection();
>>> print_r($result);
```

#### Respuesta Exitosa ✅

```json
{
  "success": true,
  "message": "Connection successful",
  "whmcs_version": "8.x",
  "response_time_ms": 123
}
```

#### Respuesta con Error ❌

```json
{
  "success": false,
  "message": "WHMCS Error: Invalid API Credentials",
  "error_type": "authentication"
}
```

---

### 4️⃣ Verificar Logs

Si hay problemas, revisa los logs de Laravel:

```bash
tail -f storage/logs/laravel.log | grep WHMCS
```

---

### 5️⃣ Prueba Básica: Listar Clientes WHMCS

Una vez conectado, prueba listar clientes de WHMCS:

```bash
curl -X POST http://tu-laravel-api.com/api/whmcs/clients/list \
  -H "Content-Type: application/json" \
  -d '{"limit": 5}'
```

Respuesta esperada:

```json
{
  "success": true,
  "clients": [
    {
      "id": 1,
      "firstname": "John",
      "lastname": "Doe",
      "email": "john@example.com"
    }
  ],
  "total": 150,
  "num_returned": 5
}
```

---

### 6️⃣ Primera Sincronización: Enviar Cliente a WHMCS

#### Paso 1: Obtener ID de un cliente en Laravel

```bash
# Via tinker
php artisan tinker
>>> $client = \App\Models\RcControlClient::first();
>>> echo $client->id;
```

#### Paso 2: Enviar cliente a WHMCS

```bash
curl -X POST http://tu-laravel-api.com/api/whmcs/clients/push/1 \
  -H "Content-Type: application/json"
```

#### Paso 3: Verificar sincronización

```bash
curl -X POST http://tu-laravel-api.com/api/whmcs/clients/check/1
```

Respuesta:

```json
{
  "synced": true,
  "whmcs_id": 456,
  "sync_status": "synced",
  "last_synced_at": "2025-10-01 10:30:00"
}
```

---

## 🔍 Troubleshooting Común

### Error: "Invalid API Credentials"

**Causa:** Credenciales incorrectas en .env

**Solución:**
1. Verifica que copiaste bien el `WHMCS_API_IDENTIFIER` y `WHMCS_API_SECRET`
2. No debe tener espacios al inicio/final
3. Ejecuta `php artisan config:clear`

---

### Error: "IP Address Not Whitelisted"

**Causa:** IP del servidor no está permitida en WHMCS

**Solución:**
1. En WHMCS Admin: `Setup` → `General Settings` → `Security`
2. En "API IP Access Restriction", agrega la IP de tu servidor
3. Para obtener la IP de tu servidor:
   ```bash
   curl ifconfig.me
   ```

---

### Error: "Connection Timeout"

**Causa:** No puede conectar a WHMCS

**Solución:**
1. Verifica que `WHMCS_API_URL` sea correcto (sin `/` al final)
2. Verifica que WHMCS esté online: `curl https://tu-dominio.com/whmcs`
3. Aumenta timeout en .env: `WHMCS_API_TIMEOUT=60`

---

### Error: "CURL error: SSL certificate problem"

**Causa:** Certificado SSL de WHMCS inválido

**Solución (Temporal - Solo desarrollo):**
Edita `app/Services/WHMCS/WHMCSApiService.php` línea ~100:

```php
$response = Http::withoutVerifying()  // Solo para desarrollo!
    ->timeout($this->timeout)
    ->asForm()
    ->post($this->getApiEndpoint(), $requestData);
```

**⚠️ IMPORTANTE:** No usar en producción!

---

## ✅ Checklist de Instalación

- [ ] API Credentials creadas en WHMCS
- [ ] IP del servidor whitelisted en WHMCS
- [ ] Variables agregadas al .env de Laravel
- [ ] Migraciones ejecutadas (`php artisan migrate`)
- [ ] Cache limpiado (`php artisan config:clear`)
- [ ] Test de conexión exitoso (`GET /api/whmcs/sync/test`)
- [ ] Primera sincronización exitosa

---

## 📞 Soporte

Si después de seguir estos pasos sigues teniendo problemas:

1. **Revisa logs de Laravel:**
   ```bash
   tail -f storage/logs/laravel.log
   ```

2. **Revisa logs de WHMCS:**
   ```
   /whmcs/logs/api/
   ```

3. **Habilita modo debug:**
   ```env
   WHMCS_LOG_ENABLED=true
   WHMCS_LOG_REQUESTS=true
   WHMCS_LOG_RESPONSES=true
   ```

4. **Prueba directamente la API de WHMCS:**
   ```bash
   curl -X POST https://tu-dominio.com/whmcs/includes/api.php \
     -d "action=GetCurrencies" \
     -d "identifier=xxxxx" \
     -d "secret=yyyyy" \
     -d "responsetype=json"
   ```

---

**¡Listo! 🎉 Tu integración WHMCS está configurada.**

Ahora puedes proceder con la integración en Angular frontend.

