# WHMCS Email Integration - Streaming Configuration Emails

## Descripción

Esta integración permite enviar emails de configuración de streaming (Radio y TV) **automáticamente** a través de WHMCS cuando el cliente está vinculado, o a través de Laravel/SendGrid cuando no lo está.

## ✨ Detección Automática de Método

El sistema **automáticamente detecta** si el cliente de la estación está vinculado a WHMCS:

- **Cliente vinculado a WHMCS** ✅ → Envía via WHMCS (queda en historial del cliente)
- **Cliente NO vinculado** → Envía via Laravel/SendGrid (método tradicional)

**No necesitas especificar el método** - el sistema lo hace automáticamente.

## Ventajas de Usar WHMCS para Enviar Emails

1. **Centralización**: Todos los emails se registran en WHMCS
2. **Tracking**: WHMCS mantiene un historial de todos los emails enviados
3. **Consistencia**: Usa la misma infraestructura de email que WHMCS
4. **Auditoría**: Fácil seguimiento de emails enviados a cada cliente
5. **Automático**: No requiere configuración adicional - funciona automáticamente

## Archivos Creados/Modificados

### Nuevos Archivos

1. **`app/Services/WHMCS/WHMCSEmailService.php`**
   - Servicio principal para envío de emails via WHMCS
   - Métodos:
     - `sendCustomEmail()` - Envío genérico de email
     - `sendRadioStreamingEmail()` - Email de configuración de radio
     - `sendTvStreamingEmail()` - Email de configuración de TV
     - `renderRadioStreamingTemplate()` - Renderiza template de radio
     - `renderTvStreamingTemplate()` - Renderiza template de TV

### Archivos Modificados

1. **`app/Http/Controllers/StationMailController.php`**
   - Agregados métodos:
     - `sendRadioMailViaWHMCS()` - Envía email de radio via WHMCS
     - `sendTvMailViaWHMCS()` - Envía email de TV via WHMCS

2. **`routes/api.php`**
   - Agregadas rutas:
     - `POST /station/send-radio-mail-whmcs`
     - `POST /station/send-tv-mail-whmcs`

## Endpoints API

### Enviar Email de Radio Streaming (Detección Automática)

```http
POST /api/station/send-radio-mail
Content-Type: application/json

{
  "station_id": 62
}
```

**Respuesta Exitosa (Con Cliente WHMCS):**
```json
{
  "success": true,
  "method": "whmcs",
  "station_name": "RADIO GUARACHITA INTERNACIONAL",
  "results": [
    {
      "email": "contact@example.com",
      "type": "primary",
      "status": "sent",
      "method": "whmcs",
      "sent_at": "2025-10-01T12:00:00.000Z"
    }
  ],
  "sent_count": 1,
  "failed_count": 0,
  "total_emails": 1,
  "whmcs_client_id": 12
}
```

**Respuesta Exitosa (Sin Cliente WHMCS):**
```json
{
  "success": true,
  "method": "direct",
  "station_name": "RADIO EJEMPLO",
  "results": [...]
  "sent_count": 1,
  "failed_count": 0,
  "total_emails": 1
}
```

### Enviar Email de TV Streaming (Detección Automática)

```http
POST /api/station/send-tv-mail
Content-Type: application/json

{
  "station_id": 62
}
```

**Respuesta similar a radio streaming**

### Endpoints Directos WHMCS (Opcionales)

Si necesitas forzar el uso de WHMCS (útil para testing):

```http
POST /api/station/send-radio-mail-whmcs
POST /api/station/send-tv-mail-whmcs
```

## Cómo Funciona

### Flujo del Proceso (Detección Automática)

```
1. Frontend llama a POST /api/station/send-radio-mail
   ↓
2. StationMailController recibe la solicitud
   ↓
3. Obtiene datos de la estación y streaming
   ↓
4. 🔍 VERIFICA si el cliente está vinculado a WHMCS
   ↓
   ├─ ✅ SI está vinculado:
   │    ↓
   │    5a. WHMCSEmailService renderiza template HTML
   │    ↓
   │    6a. Envía via WHMCS API (SendEmail)
   │    ↓
   │    7a. WHMCS procesa y envía
   │    ↓
   │    8a. Email queda en historial WHMCS del cliente
   │    ↓
   │    9a. Registra actividad: 'email_sent_whmcs'
   │
   └─ ❌ NO está vinculado:
        ↓
        5b. Usa Laravel Mail (SendGrid)
        ↓
        6b. Envía directamente
        ↓
        7b. Registra actividad: 'email_sent'
```

### Detalles Técnicos

#### 1. Verificación de Cliente WHMCS

```php
$clientSync = WhmcsSyncMap::where('entity_type', 'client')
    ->where('laravel_id', $station->client_id)
    ->first();

$whmcsClientId = $clientSync ? $clientSync->whmcs_id : null;
```

#### 2. Renderizado del Template

El sistema usa los mismos templates Blade que el sistema de email directo:
- `resources/views/emails/station-radio.blade.php`
- `resources/views/emails/station-tv.blade.php`

```php
$htmlMessage = view('emails.station-radio', [
    'station' => (object) $station,
    'streaming' => (object) $streaming,
    'embedCode' => $embedCode,
    'panelUrl' => $panelUrl,
    'streamUrl' => $streamUrl
])->render();
```

#### 3. Envío via WHMCS API

```php
$params = [
    'messagename' => 'custom',
    'customtype' => 'product',
    'customsubject' => $subject,
    'custommessage' => $htmlMessage,
    'customto' => $recipientEmail,
    'id' => $whmcsClientId
];

$response = $this->api->request('SendEmail', $params, true);
```

## Contenido del Email de Radio Streaming

El email incluye:

1. **Acceso al Panel de Control**
   - URL del panel
   - Usuario
   - Contraseña

2. **Información de Transmisión**
   - IP del servidor
   - Puerto de transmisión
   - Clave de transmisión

3. **URL Segura (SSL)**
   - URL de stream para reproductores

4. **Reproductor RDomi**
   - Código HTML para embed
   - `<iframe src="https://rdomiplayer.com/embed/radio/{station_id}/2" />`

5. **Soporte Técnico**
   - Links y contactos de soporte

## Uso desde el Frontend

### Angular Service

Agrega el método al servicio de estaciones:

```typescript
// station.service.ts
sendRadioConfigEmailViaWHMCS(stationId: number): Observable<any> {
  return this.http.post(
    `${environment.backend}station/send-radio-mail-whmcs`,
    { station_id: stationId }
  );
}

sendTvConfigEmailViaWHMCS(stationId: number): Observable<any> {
  return this.http.post(
    `${environment.backend}station/send-tv-mail-whmcs`,
    { station_id: stationId }
  );
}
```

### Uso en Componente

```typescript
sendEmailViaWHMCS(): void {
  this.stationService.sendRadioConfigEmailViaWHMCS(this.stationId)
    .subscribe({
      next: (response) => {
        console.log('Email sent via WHMCS:', response);
        this.showSuccess(`Email enviado a ${response.sent_count} destinatario(s)`);
      },
      error: (error) => {
        console.error('Error sending email:', error);
        this.showError('Error al enviar email via WHMCS');
      }
    });
}
```

## Comparación: Laravel vs WHMCS

| Característica | Laravel/SendGrid | WHMCS |
|---------------|------------------|-------|
| **Velocidad** | Más rápido | Similar |
| **Registro** | Log files | Base de datos WHMCS |
| **Tracking** | Limitado | Completo en WHMCS |
| **Historial** | No centralizado | Centralizado en WHMCS |
| **Auditoría** | Manual | Automática |
| **Vinculación** | No | Vinculado a cliente/producto |

## Endpoints Disponibles

### Endpoints Laravel (Actuales)
- `POST /station/send-radio-mail` - Email directo via Laravel/SendGrid
- `POST /station/send-hosting-mail` - Email de hosting via Laravel
- `POST /station/send-tv-mail` - Email de TV via Laravel

### Endpoints WHMCS (Nuevos)
- `POST /station/send-radio-mail-whmcs` - Email via WHMCS ✨
- `POST /station/send-tv-mail-whmcs` - Email de TV via WHMCS ✨

## Logging y Debugging

### Logs Generados (Con Cliente WHMCS)

```
📧 sendRadioMail called
  → Station ID: 62
  
✅ Station found
  → Station: {...}
  
✅ Radio streaming found
  → Streaming: {...}
  
📧 Station emails detected
  → Emails: ["contact@example.com"]
  
✨ Client linked to WHMCS, using WHMCS method
  → WHMCS Client ID: 12
  
📤 Sending Radio email via WHMCS
  → To: contact@example.com
  
✅ Radio email sent via WHMCS
  → Response: {...}
  
📊 Radio email sending via WHMCS completed
  → Sent: 1
  → Failed: 0
```

### Logs Generados (Sin Cliente WHMCS)

```
📧 sendRadioMail called
  → Station ID: 99
  
✅ Station found
✅ Radio streaming found
📧 Station emails detected
  
💡 Client not linked to WHMCS, using direct mail method
  
📤 Attempting to send email
  → To: contact@example.com
  → Method: direct
  
✅ Email sent successfully
```

## Registro de Actividad

Cada email enviado se registra como actividad:

```php
Activity::create([
    'station_id' => $station->id,
    'client_id' => $station->client_id,
    'action' => 'email_sent_whmcs',
    'description' => "Radio streaming email sent via WHMCS to {$email}"
]);
```

## Manejo de Errores

### Sin Configuración de Streaming
```json
{
  "error": "No radio streaming configuration found"
}
```

### Sin Emails Configurados
```json
{
  "error": "No emails configured for this station"
}
```

### Cliente No Vinculado a WHMCS
⚠️ El sistema envía de todas formas, pero registra un warning:
```
⚠️ Client not linked to WHMCS, will send without client ID
```

### Error de WHMCS API
```json
{
  "success": false,
  "method": "whmcs",
  "error": "Failed to send emails via WHMCS: [error message]"
}
```

## Testing

### Test Manual

1. Usa Postman o similar:
```bash
POST https://domintapi.com/api/station/send-radio-mail-whmcs
Content-Type: application/json

{
  "station_id": 62
}
```

2. Verifica en WHMCS:
   - Admin Area → Utilities → Email Message Log
   - Busca el email por destinatario o asunto

3. Verifica logs:
```bash
tail -f storage/logs/laravel.log
```

## Próximos Pasos

- [ ] Agregar botón en el frontend para elegir método de envío
- [ ] Crear template de email en WHMCS nativo
- [ ] Agregar opción de envío automático al activar servicio
- [ ] Integrar con automation de WHMCS

## Notas Importantes

1. **WHMCS debe estar configurado** con sus credenciales de email SMTP
2. **El cliente debe estar vinculado** a WHMCS para mejor tracking (opcional)
3. **Los templates Blade existentes** se reutilizan sin cambios
4. **El sistema funciona en paralelo** con el método Laravel actual

## Soporte

Para problemas o preguntas:
- Revisar logs en `storage/logs/laravel.log`
- Verificar configuración WHMCS en `config/whmcs.php`
- Contactar al equipo de desarrollo

