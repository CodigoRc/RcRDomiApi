# 📺 Guía Completa: Email TV Streaming via WHMCS

## ✅ Todo Está Listo en el Código

El código PHP ya está completamente implementado. Solo necesitas crear la plantilla en WHMCS.

---

## 🎯 PASO 1: Crear Plantilla en WHMCS Admin

### A. Acceso

1. Ve a: `https://rdomi.com/admin/`
2. Navega a: **Setup → Email Templates**
3. Haz clic en: **"Create New Email Template"**

### B. Configuración de la Plantilla

| Campo | Valor |
|-------|-------|
| **Email Type** | `General` |
| **Unique Name** | `rdomi_tv_streaming` |
| **Subject** | `RDomi - Acceso TV Streaming: {$station_name}` |
| **Custom** | ✅ Marcar (es plantilla personalizada) |
| **Disabled** | ⬜ NO marcar (debe estar activa) |

### C. HTML Message (Contenido del Email)

1. Abre el archivo:
   ```
   📂 C:\RDOMI\DOMINTAPI2\RcDomintApi\WHMCS_EMAIL_TEMPLATE_TV_FIXED.html
   ```

2. Copia **TODO** el contenido

3. Pega en el campo **"HTML Message"** de WHMCS

4. **Guarda** los cambios

### D. Plain Text Message (Opcional pero Recomendado)

```
RDomi - Acceso a su Servicio de TV Streaming

Estimado/a {$client_name},

¡Bienvenido/a a RDomi! Su servicio de TV Streaming para {$station_name} 
ha sido configurado exitosamente.

ACCESO AL PANEL DE CONTROL
URL: {$panel_url}
Usuario: {$username}
Contraseña: {$password}

INFORMACIÓN DEL SERVIDOR
Servidor: {$server_host}
Puerto: {$server_port}
Aplicación: {$application}

INFORMACIÓN DE TRANSMISIÓN
Stream Name: {$stream_name}
Stream Key: {$stream_key}

CONFIGURACIÓN PARA OBS
Server: rtmp://{$server_host}:{$server_port}/{$application}
Stream Key: {$stream_key}

Para más información, visite: https://rdomi.com
Soporte: info@rdomi.com
```

---

## 🎯 PASO 2: Variables Smarty Utilizadas

La plantilla usa estas variables (se reemplazan automáticamente):

| Variable | Descripción | Ejemplo |
|----------|-------------|---------|
| `{$client_name}` | Nombre del cliente desde WHMCS | Juan Pérez / Empresa ABC |
| `{$station_name}` | Nombre de la estación | TV CANAL 5 |
| `{$username}` | Usuario del panel | tvcanal5 |
| `{$password}` | Contraseña del panel | ABC123XYZ |
| `{$server_host}` | Host del servidor | tv.rdomitv.com |
| `{$server_port}` | Puerto del servidor | 1935 |
| `{$application}` | Aplicación RTMP | live |
| `{$stream_name}` | Nombre del stream | tvcanal5 |
| `{$stream_key}` | Clave del stream | abc123 |
| `{$panel_url}` | URL del panel | https://tv.rdomitv.com |
| `{$hls_url}` | URL HLS | https://tv.rdomitv.com/live/tvcanal5.m3u8 |
| `{$embed_code}` | Código iframe del reproductor | `<iframe src="...">` |

---

## 🎯 PASO 3: Probar

### Desde Angular:

1. Ve a Station Details (una station con TV)
2. Haz clic en "Send TV Mail"
3. Verifica tu bandeja

### Desde Postman/cURL:

```bash
POST https://domintapi.com/api/station/send-tv-mail
{
  "station_id": 123
}
```

### Endpoint de Prueba:

```bash
POST https://domintapi.com/api/station/test-send-tv-whmcs
{
  "station_id": 123,
  "email": "tu_email@gmail.com"
}
```

---

## ✅ Resultado Esperado

### Email Recibido:

```
═══════════════════════════════════════════════════
RDomi - Acceso a su Servicio de TV Streaming
═══════════════════════════════════════════════════

Estimado/a Juan Pérez,

¡Bienvenido/a a RDomi! Su servicio de TV Streaming 
para TV CANAL 5 ha sido configurado exitosamente y 
está listo para comenzar a transmitir.

Acceso al Panel de Control
URL del Panel: https://tv.rdomitv.com
Usuario: tvcanal5
Contraseña: ABC123XYZ

Información del Servidor
Servidor: tv.rdomitv.com
Puerto: 1935
Aplicación: live

Información de Transmisión
Stream Name: tvcanal5
Stream Key: abc123

URLs de Transmisión
┌─────────────┬──────────────────────────────────┐
│ RTMP URL:   │ rtmp://tv.rdomitv.com:1935/live  │
│ HLS URL:    │ https://tv.rdomitv.com/...m3u8   │
└─────────────┴──────────────────────────────────┘

Reproductor RDomi TV
[Código iframe...]

Configuración para OBS / Software de Streaming
Server: rtmp://tv.rdomitv.com:1935/live
Stream Key: abc123

Compatibilidad
• OBS Studio
• XSplit
• Wirecast
• vMix
• FFmpeg

[Footer de WHMCS con contactos]
```

---

## 🔍 Verificación

### En los Logs de Laravel:

```
📤 Sending TV email via WHMCS template
   whmcs_client_id: 12
   station: TV CANAL 5

✅ Client name retrieved from WHMCS for TV email
   client_id: 12
   client_name: "Juan Pérez"

📺 Sending TV email with client name
   client_id: 12
   client_name: "Juan Pérez"
   station_name: "TV CANAL 5"

✅ Email sent via WHMCS template successfully
   method: whmcs_template
   template: rdomi_tv_streaming
```

### En WHMCS Activity Log:

```
API Call: SendEmail
Template: rdomi_tv_streaming
Client ID: 12
Variables: client_name, station_name, username, ...
```

---

## 📊 Comparación Radio vs TV

| Aspecto | Radio Streaming | TV Streaming |
|---------|----------------|--------------|
| Template Name | `rdomi_radio_streaming` | `rdomi_tv_streaming` |
| Reproductor | Audio player | Video player (480px height) |
| URLs | Stream URLs + Playlists | RTMP + HLS URLs |
| Configuración | Panel de control | OBS / Software streaming |
| Variables | host, port, stream_password | host, port, application, stream_key |

---

## 🎯 Características Implementadas

### ✅ Sin Iconos/Emojis
- Solo texto simple y símbolos HTML
- Máxima compatibilidad con clientes de correo

### ✅ Nombre del Cliente Real
- Obtiene nombre desde WHMCS automáticamente
- Prioriza: Empresa → Nombre completo → Email

### ✅ Nombre de la Estación
- Incluido en el saludo y contexto

### ✅ Un Solo Email
- No duplicación
- WHMCS decide destinatario automáticamente

### ✅ Sin Header/Footer Propios
- WHMCS agrega los suyos automáticamente
- Evita duplicación

### ✅ Información Completa
- Configuración para OBS/XSplit
- URLs RTMP y HLS
- Código embed del reproductor
- Lista de software compatible

---

## 🚀 Checklist Final

Antes de dar por terminado:

- [ ] Plantilla `rdomi_tv_streaming` creada en WHMCS
- [ ] HTML Message copiado desde `WHMCS_EMAIL_TEMPLATE_TV_FIXED.html`
- [ ] Subject configurado: `RDomi - Acceso TV Streaming: {$station_name}`
- [ ] Plain Text configurado (opcional)
- [ ] Plantilla NO está deshabilitada
- [ ] Probado enviando un email
- [ ] Email recibido con formato correcto
- [ ] Variables reemplazadas (no aparece "N/A")
- [ ] Un solo email recibido (sin duplicados)

---

## ❓ Troubleshooting

### Variables aparecen como "N/A"

**Causa:** Los datos no existen en la base de datos de streaming

**Solución:** Verifica que la station tenga configurado:
- `video_streaming.username`
- `video_streaming.password`
- `video_streaming.host`
- `video_streaming.port`
- `video_streaming.application`
- `video_streaming.stream_name`
- `video_streaming.stream_key`

### Email no llega

**Causa:** Cliente no está vinculado a WHMCS

**Solución:** Verifica en `whmcs_sync_map`:
```sql
SELECT * FROM whmcs_sync_map 
WHERE entity_type = 'client' 
  AND laravel_id = [CLIENT_ID];
```

### Se envían 2 emails

**Causa:** Código antiguo en caché

**Solución:** El código actual ya está arreglado. Asegúrate de que esté en producción.

---

## 📝 Archivos Relevantes

- `WHMCS_EMAIL_TEMPLATE_TV_FIXED.html` - Plantilla HTML para WHMCS
- `WHMCSEmailService.php::sendTvStreamingViaTemplate()` - Método PHP
- `StationMailController.php::sendViaWHMCS()` - Controller actualizado

---

**¡Listo para usar!** 🚀

