# 🏠 Guía Completa: Email Hosting Web via WHMCS

## ✅ Todo Está Listo en el Código

El código PHP y Angular ya están completamente implementados. Solo necesitas crear la plantilla en WHMCS.

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
| **Unique Name** | `rdomi_hosting_web` |
| **Subject** | `RDomi - Acceso Hosting Web: {$station_name}` |
| **Custom** | ✅ Marcar (es plantilla personalizada) |
| **Disabled** | ⬜ NO marcar (debe estar activa) |

### C. HTML Message (Contenido del Email)

1. Abre el archivo:
   ```
   📂 C:\RDOMI\DOMINTAPI2\RcDomintApi\WHMCS_EMAIL_TEMPLATE_HOSTING_FIXED.html
   ```

2. Copia **TODO** el contenido

3. Pega en el campo **"HTML Message"** de WHMCS

4. **Guarda** los cambios

### D. Plain Text Message (Opcional pero Recomendado)

```
RDomi - Acceso a su Servicio de Hosting Web

Estimado/a {$client_name},

¡Bienvenido/a a RDomi! Su servicio de Hosting Web para {$station_name} 
ha sido configurado exitosamente.

ACCESO AL CPANEL
URL: {$cpanel_url}
Usuario: {$cpanel_username}
Contraseña: {$cpanel_password}

ACCESO FTP
Servidor: {$ftp_host}
Usuario: {$ftp_username}
Contraseña: {$ftp_password}
Puerto: 21 (o 22 para SFTP)

URL DE SU SITIO WEB
{$site_url}

Para más información, visite: https://rdomi.com
Soporte: info@rdomi.com
```

---

## 🎯 PASO 2: Variables Smarty Utilizadas

La plantilla usa estas variables (se reemplazan automáticamente):

| Variable | Descripción | Ejemplo |
|----------|-------------|---------|
| `{$client_name}` | Nombre del cliente desde WHMCS | Juan Pérez / Empresa ABC |
| `{$station_name}` | Nombre de la estación | Mi Sitio Web |
| `{$cpanel_url}` | URL de cPanel | https://cpanel.midominio.com |
| `{$cpanel_username}` | Usuario de cPanel | usuario123 |
| `{$cpanel_password}` | Contraseña de cPanel | pass123 |
| `{$ftp_host}` | Servidor FTP | ftp.midominio.com |
| `{$ftp_username}` | Usuario FTP | usuario123 |
| `{$ftp_password}` | Contraseña FTP | pass123 |
| `{$site_url}` | URL del sitio web | https://midominio.com |

---

## 🎯 PASO 3: Probar

### Desde Angular:

1. Ve a Station Details (una station con Hosting)
2. Haz clic en "Send Hosting Mail"
3. Verifica tu bandeja

### Desde Postman/cURL:

```bash
POST https://domintapi.com/api/station/send-hosting-mail
{
  "station_id": 123
}
```

---

## ✅ Resultado Esperado

### Email Recibido:

```
═══════════════════════════════════════════════════
RDomi - Acceso a su Servicio de Hosting Web
═══════════════════════════════════════════════════

Estimado/a Rafael Calderon,

¡Bienvenido/a a RDomi! Su servicio de Hosting Web 
para Mi Sitio Web ha sido configurado exitosamente...

┌─────────────────────────────────────────────────┐
│ Acceso al Panel cPanel                          │
├────────────────┬────────────────────────────────┤
│ URL de cPanel: │ https://cpanel.midominio.com   │
│ Usuario:       │ usuario123                     │
│ Contraseña:    │ pass123                        │
└────────────────┴────────────────────────────────┘

Acceso FTP
┌────────────────┬────────────────────────────────┐
│ Servidor FTP:  │ ftp.midominio.com              │
│ Usuario FTP:   │ usuario123                     │
│ Contraseña:    │ pass123                        │
│ Puerto FTP:    │ 21 (o 22 para SFTP)           │
└────────────────┴────────────────────────────────┘

URL de su Sitio Web
https://midominio.com

Información Importante:
• Use cPanel para administrar su sitio
• Use FTP para subir archivos
• Los cambios DNS pueden tardar 24 horas

[Footer de WHMCS]
```

---

## 🔍 Verificación en Logs

### Logs de Laravel:

```
🏠 sendHostingMail called
✅ Station found
✅ Hosting station found
✨ Client linked to WHMCS, using WHMCS method
   whmcs_client_id: 12
📤 Sending Hosting email via WHMCS template
   whmcs_client_id: 12
   station: Mi Sitio Web
   type: hosting
✅ Client name retrieved from WHMCS for Hosting email
   client_id: 12
   client_name: "Rafael Calderon"
🏠 Sending Hosting email with all variables
   cpanel_url: https://cpanel.midominio.com
   ftp_host: ftp.midominio.com
   site_url: https://midominio.com
✅ Email sent via WHMCS template successfully
   template: rdomi_hosting_web
```

---

## 📊 Campos del Modelo HostingStation

| Campo BD | Variable WHMCS | Descripción |
|----------|----------------|-------------|
| `cpanel` | `{$cpanel_url}` | URL de cPanel |
| `user_name` | `{$cpanel_username}` | Usuario cPanel |
| `pass` | `{$cpanel_password}` | Contraseña cPanel |
| `ftp_user` | `{$ftp_username}` | Usuario FTP |
| `ftp_pass` | `{$ftp_password}` | Contraseña FTP |
| `url` | `{$site_url}` | URL del sitio web |

---

## ✅ Características

- ✅ Nombre real del cliente desde WHMCS
- ✅ Nombre de la estación incluido
- ✅ Sin iconos (solo texto)
- ✅ Sin header/footer duplicados
- ✅ Un solo email (sin duplicación)
- ✅ URLs limpias (sin doble https://)
- ✅ Información de cPanel y FTP completa
- ✅ Botón siempre visible en Angular

---

## 🚀 Checklist

- [ ] Plantilla `rdomi_hosting_web` creada en WHMCS
- [ ] HTML copiado desde `WHMCS_EMAIL_TEMPLATE_HOSTING_FIXED.html`
- [ ] Subject configurado correctamente
- [ ] Plantilla NO deshabilitada
- [ ] Probado enviando un email
- [ ] Email recibido con formato correcto
- [ ] Variables reemplazadas (no "N/A")
- [ ] Un solo email recibido

---

**¡Listo para usar!** 🚀

