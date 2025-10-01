# 📧 Guía: Configurar Email HTML en WHMCS

## 🎯 PASO 1: Crear Plantilla en WHMCS Admin

### A. Acceso Inicial
1. Ve a tu WHMCS Admin: `https://rdomi.com/admin/`
2. Navega a: **Setup → Email Templates**
3. Haz clic en **"Create New Email Template"**

---

### B. Configuración de la Plantilla

Completa los siguientes campos:

| Campo | Valor |
|-------|-------|
| **Email Type** | `General` |
| **Unique Name** | `rdomi_radio_streaming` |
| **Subject** | `RDomi - Acceso Radio Streaming: {$station_name}` |
| **Custom** | ✅ **Marcar** (es plantilla personalizada) |
| **Disabled** | ⬜ **NO marcar** (debe estar activa) |

---

### C. HTML Message (Contenido del Email)

1. En el campo **"HTML Message"**, pega el contenido del archivo:
   ```
   WHMCS_EMAIL_TEMPLATE_RADIO.html
   ```

2. **IMPORTANTE:** 
   - ✅ Pega TODO el contenido del archivo
   - ✅ Incluye desde `<center>` hasta `</center>`
   - ❌ NO incluyas `<html>`, `<head>`, o `<body>` tags

---

### D. Plain Text Message (Opcional pero Recomendado)

Pega este texto plano como fallback:

```
RDomi - Acceso a su Servicio de Radio Streaming

Hola {$username},

¡Bienvenido/a a RDomi! Su cuenta de radio ha sido creada exitosamente.

ACCESO AL PANEL DE CONTROL
URL: {$panel_url}
Usuario: {$username}
Contraseña: {$password}

INFORMACIÓN DE TRANSMISIÓN
Servidor: {$server_host}
Puerto: {$server_port}
Clave: {$stream_password}

URL DE STREAM
{$stream_url}

REPRODUCTOR RDOMI
{$embed_code}

Para más información, visite: https://rdomi.com
Soporte: info@rdomi.com
```

---

### E. Guardar la Plantilla

1. Haz clic en **"Save Changes"**
2. Verifica que aparezca el mensaje de éxito
3. Anota el **nombre de la plantilla**: `rdomi_radio_streaming`

---

## 🎯 PASO 2: Variables Smarty Utilizadas

La plantilla usa estas variables (WHMCS las reemplazará con valores reales):

| Variable | Descripción | Ejemplo |
|----------|-------------|---------|
| `{$station_name}` | Nombre de la estación | RADIO GUARACHITA |
| `{$username}` | Usuario del panel | radioguarachita |
| `{$password}` | Contraseña del panel | PTHP8WKGFZ72 |
| `{$server_host}` | Host del servidor | radiordomi.com |
| `{$server_port}` | Puerto del servidor | 8534 |
| `{$stream_password}` | Clave de transmisión | man5on |
| `{$panel_url}` | URL completa del panel | https://radiordomi.com/cp/log.php |
| `{$stream_url}` | URL completa del stream | https://radiordomi.com/8534/stream |
| `{$embed_code}` | Código iframe del reproductor | `<iframe src="...">` |

---

## 🎯 PASO 3: Verificar la Plantilla

### A. En WHMCS Admin

1. Ve a **Setup → Email Templates**
2. Busca `rdomi_radio_streaming` en la lista
3. Haz clic para editarla
4. Verifica que:
   - ✅ El Subject tenga `{$station_name}`
   - ✅ El HTML Message esté completo
   - ✅ No esté marcada como "Disabled"
   - ✅ El tipo sea "General"

---

## 🎯 PASO 4: Próximos Pasos (Código Laravel)

Una vez creada la plantilla en WHMCS, el siguiente paso será:

1. ✅ Modificar `WHMCSEmailService.php` para llamar la plantilla
2. ✅ Pasar las variables correctamente
3. ✅ Probar el envío

---

## ✅ Checklist de Verificación

Antes de continuar con el código, asegúrate de:

- [ ] La plantilla está creada en WHMCS
- [ ] El nombre es exactamente: `rdomi_radio_streaming`
- [ ] El HTML está completo (con todas las variables `{$...}`)
- [ ] El Plain Text está configurado
- [ ] La plantilla NO está deshabilitada
- [ ] Has guardado los cambios

---

## 📝 Notas Importantes

### ¿Por qué solo el `<center>` y no el `<html>` completo?

WHMCS tiene su propio sistema de wrapping de emails:
- ✅ WHMCS agrega automáticamente `<!DOCTYPE>`, `<html>`, `<head>`, `<body>`
- ✅ WHMCS aplica su propio CSS global
- ✅ WHMCS maneja el encoding y MIME multipart
- ✅ Tu plantilla solo necesita el **contenido del body**

### ¿Qué pasa con los estilos CSS?

- ✅ Los estilos **inline** (en los tags) funcionan perfectamente
- ✅ WHMCS respeta todos los `style="..."` que pongas
- ❌ Los estilos en `<style>` en el `<head>` no funcionan (por eso usamos inline)

### ¿Cómo pruebo que funciona?

Después de configurar el código Laravel, podrás:
1. Llamar la API: `POST /api/station/send-radio-mail`
2. WHMCS enviará el email usando esta plantilla
3. El email se verá perfecto con HTML

---

## 🚀 ¿Listo para el Paso 2?

Una vez que hayas creado la plantilla en WHMCS, avísame y te guiaré para modificar el código Laravel para usarla.

