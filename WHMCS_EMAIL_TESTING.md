# 🧪 Cómo Probar el Email WHMCS con Plantilla HTML

## ✅ Código Actualizado y Listo

El código ha sido modificado correctamente para usar la plantilla de WHMCS.

---

## 🎯 Dos Formas de Probar

### **Opción 1: Endpoint de Prueba (RECOMENDADO)** 🧪

Usa el endpoint especial de prueba para verificar que todo funciona:

```bash
POST https://domintapi.com/api/station/test-send-html-whmcs
```

**Body (JSON):**
```json
{
  "station_id": 62,
  "email": "tu_email@gmail.com"
}
```

**Respuesta Esperada:**
```json
{
  "success": true,
  "message": "Email sent successfully via WHMCS template",
  "method": "whmcs_template_test",
  "recipient": "tu_email@gmail.com",
  "template": "rdomi_radio_streaming",
  "whmcs_client_id": 12,
  "station_name": "RADIO GUARACHITA INTERNACIONAL",
  "variables_sent": [
    "station_name",
    "username",
    "password",
    "server_host",
    "server_port",
    "stream_password",
    "panel_url",
    "stream_url",
    "embed_code"
  ]
}
```

---

### **Opción 2: Desde Angular (Normal)** 🚀

Usa el método normal que ya tienes en Angular:

```bash
POST https://domintapi.com/api/station/send-radio-mail
```

**Body (JSON):**
```json
{
  "station_id": 62
}
```

**Comportamiento:**
1. ✅ Si el cliente está vinculado a WHMCS → Envía via plantilla WHMCS
2. ⚠️ Si falla WHMCS → Hace fallback a Laravel Mail (método anterior)

---

## 🔍 Cómo Verificar que Funciona

### A. En los Logs de Laravel

Busca estas líneas en el log:

```
✅ Email sent via WHMCS template
   - template: rdomi_radio_streaming
   - to: destinatario@email.com
   - method: whmcs_template
```

### B. En tu Email

El email debe:
- ✅ Tener formato HTML perfecto (sin código fuente visible)
- ✅ Mostrar el logo de RDomi
- ✅ Mostrar colores y estilos correctos
- ✅ Mostrar las variables reemplazadas (nombre de estación, host, puerto, etc.)
- ✅ Tener enlaces funcionando

### C. En WHMCS Admin

1. Ve a **Activity Log** en WHMCS
2. Busca entradas de "SendEmail"
3. Deberías ver algo como:
   ```
   API Call: SendEmail
   Template: rdomi_radio_streaming
   Client ID: 12
   Recipient: destinatario@email.com
   ```

---

## 🧪 Prueba con Postman o cURL

### cURL (Test Endpoint):

```bash
curl -X POST https://domintapi.com/api/station/test-send-html-whmcs \
  -H "Content-Type: application/json" \
  -d '{
    "station_id": 62,
    "email": "tu_email@gmail.com"
  }'
```

### cURL (Método Normal):

```bash
curl -X POST https://domintapi.com/api/station/send-radio-mail \
  -H "Content-Type: application/json" \
  -d '{
    "station_id": 62
  }'
```

---

## ❓ Troubleshooting

### Problema 1: "Station client is not linked to WHMCS"

**Solución:** La estación necesita tener un cliente vinculado a WHMCS. Verifica:
```sql
SELECT * FROM whmcs_sync_map 
WHERE entity_type = 'client' 
  AND laravel_id = (SELECT client_id FROM stations WHERE id = 62);
```

### Problema 2: "Template not found in WHMCS"

**Solución:** Verifica que la plantilla existe en WHMCS:
1. WHMCS Admin → Setup → Email Templates
2. Busca: `rdomi_radio_streaming`
3. Si no existe, créala siguiendo `WHMCS_EMAIL_SETUP_GUIDE.md`

### Problema 3: Email llega en texto plano

**Causas posibles:**
- ⚠️ La plantilla en WHMCS está marcada como "Plain-Text" (desmarca esa opción)
- ⚠️ El cliente de correo no soporta HTML (poco probable)

### Problema 4: Variables aparecen como {$variable}

**Causas posibles:**
- ⚠️ Las variables no se están pasando correctamente desde Laravel
- ⚠️ Los nombres de variables en la plantilla no coinciden

**Solución:** Verifica en los logs que las variables se envíen:
```
📧 Sending email via WHMCS template
   variables: ['station_name', 'username', 'password', ...]
```

---

## 📊 Flujo Completo

```
Usuario hace clic en "Send Email" (Angular)
    ↓
POST /api/station/send-radio-mail { station_id: 62 }
    ↓
Laravel: ¿Cliente vinculado a WHMCS?
    ↓
Sí → Intenta enviar via WHMCS template
    ↓
    ├─ ✅ Éxito → Email enviado via WHMCS
    │              (Aparece en historial WHMCS)
    │
    └─ ❌ Falla → Fallback a Laravel Mail
                   (Log en notas WHMCS + SendGrid)
```

---

## 🎯 Lo Que Deberías Ver

### Email Perfecto:

```
┌────────────────────────────────────────┐
│         [Logo RDomi]                   │
├────────────────────────────────────────┤
│                                        │
│  RDomi - Acceso a su Servicio         │
│  de Radio Streaming                    │
│                                        │
│  Hola radioguarachita,                 │
│                                        │
│  ¡Bienvenido/a a RDomi!                │
│                                        │
│  Acceso al Panel de Control            │
│  🌐 URL: https://radiordomi.com/...   │
│  📧 Usuario: radioguarachita           │
│  🔐 Contraseña: PTHP8WKGFZ72          │
│                                        │
│  Información de Transmisión            │
│  🌐 Servidor: radiordomi.com          │
│  🔌 Puerto: 8534                       │
│  🔑 Clave: man5on                      │
│                                        │
│  [Tabla de extensiones de audio]       │
│                                        │
├────────────────────────────────────────┤
│  © RDomi. Todos los derechos          │
│  reservados.                           │
└────────────────────────────────────────┘
```

---

## ✅ Checklist Final

Antes de dar por terminado:

- [ ] Plantilla creada en WHMCS (`rdomi_radio_streaming`)
- [ ] Código Laravel actualizado
- [ ] Probado con endpoint de test
- [ ] Email recibido con HTML perfecto
- [ ] Variables reemplazadas correctamente
- [ ] Sin código HTML visible en el email
- [ ] Probado desde Angular
- [ ] Fallback a Laravel Mail funciona si WHMCS falla

---

## 🚀 ¡Ya Estás Listo!

Ahora puedes:
1. ✅ Probar con el endpoint de test
2. ✅ Probar desde Angular
3. ✅ Ver emails perfectos en la bandeja de entrada
4. ✅ Tener historial en WHMCS

¿Algún problema? Revisa la sección de Troubleshooting.

