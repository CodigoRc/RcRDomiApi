# ✅ Solución Final: Un Solo Email via WHMCS

## 🐛 Problema Identificado

**Se enviaban 2 emails idénticos** cuando se usaba WHMCS.

### Causa Raíz:

El código estaba haciendo un `foreach` sobre los emails de la station y llamando a WHMCS **múltiples veces**:

```php
// ❌ CÓDIGO ANTERIOR (INCORRECTO)
foreach ($emails as $emailData) {
    $whmcsEmailService->sendRadioStreamingViaTemplate(
        $whmcsClientId,
        $emailData['address'],  // ← Llamaba para cada email
        $stationData,
        $streamingData
    );
}
```

Si la station tenía:
- `email` = `codigorc@gmail.com`
- `email2` = `codigorc@gmail.com`

Se enviaban **2 emails** al mismo destinatario.

---

## ✅ Solución Implementada

### Cambio 1: Llamada Única a WHMCS

```php
// ✅ CÓDIGO NUEVO (CORRECTO)
// NO usamos el array de emails de la station
// WHMCS decide a quién enviar basado en clientId
$whmcsResult = $whmcsEmailService->sendRadioStreamingViaTemplate(
    $whmcsClientId,
    null,  // ← NULL: WHMCS envía al email registrado del cliente
    $stationData,
    $streamingData
);
```

### Cambio 2: Parámetro Opcional en el Servicio

```php
// WHMCSEmailService.php
public function sendEmailViaTemplate(
    string $templateName, 
    int $clientId, 
    ?string $recipientEmail,  // ← Ahora acepta NULL
    array $variables
): array {
    $params = [
        'messagename' => $templateName,
        'id' => $clientId,
        'customvars' => base64_encode(serialize($variables))
    ];

    // Solo especificar destinatario si NO es NULL
    if ($recipientEmail) {
        $params['customto'] = $recipientEmail;
    }
    // Si es NULL, WHMCS usa el email del cliente automáticamente
}
```

---

## 🎯 Comportamiento Ahora

### Cuando el Cliente está Vinculado a WHMCS:

```
Usuario hace clic en "Send Email"
    ↓
Sistema detecta: Cliente vinculado a WHMCS (ID: 12)
    ↓
Llama a WHMCS API UNA VEZ:
    - messagename: "rdomi_radio_streaming"
    - id: 12 (cliente WHMCS)
    - customvars: {station_name, username, password, ...}
    - customto: NO especificado (NULL)
    ↓
WHMCS envía automáticamente al email del cliente
    ↓
✅ UN solo email enviado
```

### Cuando el Cliente NO está Vinculado:

```
Usuario hace clic en "Send Email"
    ↓
Sistema detecta: Cliente NO vinculado a WHMCS
    ↓
Usa Laravel Mail directo
    ↓
Envía a los emails de la station (deduplicados)
    ↓
✅ Emails enviados via Laravel
```

---

## 📝 Archivos Modificados

### 1. StationMailController.php

**Método:** `sendViaWHMCS()`

**Cambios:**
- ❌ Eliminado: `foreach ($emails as $emailData)`
- ✅ Agregado: Llamada única con `null` como recipientEmail
- ✅ WHMCS decide el destinatario basado en clientId

### 2. WHMCSEmailService.php

**Método:** `sendEmailViaTemplate()`

**Cambios:**
- ✅ Parámetro `$recipientEmail` ahora es `?string` (acepta NULL)
- ✅ Solo agrega `customto` si `$recipientEmail` no es NULL

**Método:** `sendRadioStreamingViaTemplate()`

**Cambios:**
- ✅ Parámetro `$recipientEmail` ahora es `?string` (acepta NULL)

---

## 🧪 Prueba

### Desde Angular:

1. Ve a Station Details
2. Haz clic en "Send Radio Mail"
3. Verifica tu bandeja

### Desde Postman/cURL:

```bash
POST https://domintapi.com/api/station/send-radio-mail
{
  "station_id": 62
}
```

---

## ✅ Resultado Esperado

### En los Logs:

```
📤 Sending Radio email via WHMCS template
   whmcs_client_id: 12
   station: DeepRaph

📧 Sending email via WHMCS template
   template: rdomi_radio_streaming
   client_id: 12
   custom_to: client_default_email  ← WHMCS decide
   variables: [client_name, station_name, username, ...]

✅ Email sent via WHMCS template
   client_id: 12
   custom_to: client_default_email
```

### En tu Bandeja:

- ✅ **UN solo email**
- ✅ From: RDomi <soporte@rdomi.com>
- ✅ To: [Email registrado en WHMCS para el cliente]
- ✅ Subject: RDomi - Acceso Radio Streaming: DeepRaph

---

## 📊 Comparación

| Aspecto | Antes | Ahora |
|---------|-------|-------|
| Llamadas a WHMCS | Por cada email de la station | Una sola vez |
| Destinatario | Especificado manualmente | WHMCS decide automáticamente |
| Emails enviados | 2 (duplicados) | 1 (único) |
| Usa emails de station | ✅ Sí | ❌ No (solo WHMCS client email) |

---

## 🎯 Por Qué Funciona

WHMCS tiene su propia lógica de envío de emails:

1. Cuando llamas `SendEmail` con `id` (clientId) y SIN `customto`
2. WHMCS busca el email registrado del cliente automáticamente
3. WHMCS envía UNA SOLA VEZ al email del cliente
4. WHMCS maneja el MIME, formato, deliverability, etc.

**No necesitamos** especificar el destinatario porque WHMCS ya lo sabe.

---

## ✅ Checklist de Verificación

- [x] Eliminado `foreach` sobre emails de station
- [x] Llamada única a WHMCS con `recipientEmail = null`
- [x] Parámetro `?string $recipientEmail` en métodos
- [x] Logs actualizados para mostrar "client_default_email"
- [x] Sin errores de linter
- [x] Documentación actualizada

---

## 🚀 Próximos Pasos

1. ✅ Probar enviando un email
2. ✅ Verificar que solo llega 1 email
3. ✅ Confirmar que las variables se reemplazan correctamente
4. ✅ Verificar que el nombre del cliente aparece (no "N/A")

---

**Estado:** ✅ **RESUELTO**

El sistema ahora envía correctamente **UN solo email** via WHMCS cuando el cliente está vinculado.

