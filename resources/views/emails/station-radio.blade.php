<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <title>RDomi - Radio Streaming Access</title>
    <style type="text/css">
        body {
            font-family: Arial, Helvetica, sans-serif;
            color: #000000;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        #bodyTable {
            background-color: #f4f4f4;
            height: 100% !important;
            margin: 0;
            padding: 0;
            width: 100% !important;
        }
        #bodyCell {
            padding: 30px 0;
        }
        #templateContainer {
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            width: 600px;
            max-width: 600px;
        }
        .headerContent {
            text-align: center;
            padding: 20px 20px 0 20px;
        }
        #headerImage {
            max-width: 600px;
            max-height: 60px;
            border: 0;
        }
        .bodyContent {
            color: #000000;
            font-size: 15px;
            line-height: 1.6;
            padding: 40px;
        }
        .bodyContent h2 {
            color: #000000;
            font-size: 20px;
            margin: 0 0 10px;
            font-weight: bold;
        }
        .bodyContent h3 {
            color: #000000;
            font-size: 16px;
            border-bottom: 1px solid #ccc;
            padding-bottom: 5px;
            margin: 20px 0 10px;
            font-weight: bold;
        }
        .bodyContent p {
            margin: 0 0 15px;
        }
        .bodyContent a {
            color: #1a73e8;
            text-decoration: none;
        }
        .player-embed {
            background-color: #f8f8f8;
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 4px;
            margin: 20px 0;
            font-family: Consolas, 'Courier New', monospace;
            font-size: 13px;
            color: #333;
            white-space: pre-wrap;
            word-break: break-all;
        }
        .footerContent {
            font-size: 13px;
            color: #555555;
            text-align: center;
            padding: 30px 20px;
        }
        .footerContent a {
            color: #0053A0;
            text-decoration: none;
        }
        .footerContent p {
            margin: 0 0 8px;
        }
        @media only screen and (max-width: 600px) {
            #templateContainer {
                width: 100% !important;
                max-width: 100% !important;
            }
            .bodyContent {
                padding: 20px;
            }
        }
    </style>
</head>
<body leftmargin="0" marginwidth="0" topmargin="0" marginheight="0" offset="0">
    <center>
        <table align="center" border="0" cellpadding="0" cellspacing="0" height="100%" width="100%" id="bodyTable">
            <tr>
                <td align="center" valign="top" id="bodyCell">
                    <table border="0" cellpadding="0" cellspacing="0" id="templateContainer">
                        <!-- Header -->
                        <tr>
                            <td align="center" valign="top">
                                <div class="headerContent">
                                    <a href="https://rdomi.com">
                                        <img src="https://rdomi.com/logomail.png" id="headerImage" alt="RDomi" />
                                    </a>
                                </div>
                            </td>
                        </tr>
                        
                        <!-- Body -->
                        <tr>
                            <td align="center" valign="top">
                                <div class="bodyContent">
                                    <h2>RDomi - Acceso a su Servicio de Radio Streaming</h2>
                                    <p>Hola <strong>{{ $streaming->username ?? 'Cliente' }}</strong>,</p>
                                    <p>¡Bienvenido/a a RDomi! Su cuenta de radio ha sido creada exitosamente y está lista para comenzar a transmitir.</p>
                                    
                                    <h3>Acceso al Panel de Control</h3>
                                    <p>🌐 <strong>URL del Panel:</strong><br>
                                        <a href="{{ $panelUrl }}">{{ $panelUrl }}</a></p>
                                    <p>📧 <strong>Usuario:</strong> {{ $streaming->username ?? 'N/A' }}<br>
                                        🔐 <strong>Contraseña:</strong> {{ $streaming->password ?? 'N/A' }}</p>
                                    
                                    <h3>Información de Transmisión</h3>
                                    <p>🌐 <strong>IP del servidor:</strong> {{ $streaming->host ?? 'N/A' }}<br>
                                        🔌 <strong>Puerto de transmisión:</strong> {{ $streaming->port ?? 'N/A' }}<br>
                                        🔑 <strong>Clave de transmisión:</strong> {{ $streaming->stream_password ?? 'N/A' }}</p>
                                    
                                    <h3>URL Segura (SSL) para Reproductores</h3>
                                    <p>🌐 <strong>URL de Stream:</strong> <a href="{{ $streamUrl }}">{{ $streamUrl }}</a></p>
                                    
                                    <h3>Reproductor RDomi</h3>
                                    <p>Puede usar este reproductor en su sitio web copiando el siguiente código:</p>
                                    <div class="player-embed" style="background-color:#f8f8f8;padding:15px;border:1px solid #ddd;border-radius:4px;margin:15px 0;font-family:Consolas,'Courier New',monospace;font-size:12px;color:#333;white-space:pre-wrap;word-break:break-all;">{{ $embedCode }}</div>
                                    
                                    <h3>Soporte Técnico RDomi</h3>
                                    <p>Para cualquier duda o asistencia, nuestro equipo está disponible para ayudarle en todo momento.</p>
                                </div>
                            </td>
                        </tr>
                        
                        <!-- Footer -->
                        <tr>
                            <td align="center" valign="top">
                                <div class="footerContent">
                                    <p>
                                        <a href="https://rdomi.com">Visitar nuestro sitio web</a> |
                                        <a href="https://rdomi.com/index.php?rp=/login">Acceder a su cuenta</a> |
                                        <a href="https://rdomi.com/submitticket.php">Soporte técnico</a>
                                    </p>
                                    <p><strong>Contáctenos:</strong><br>
                                        USA: +1 (415) 702-3258 | RD: +1 (829) 890-7166<br>
                                        WhatsApp: +1 (829) 890-7166<br>
                                        Email: <a href="mailto:info@rdomi.com">info@rdomi.com</a>
                                    </p>
                                    <p style="margin-top:15px;font-size:12px;color:#777777;">
                                        © RDomi. Todos los derechos reservados.<br>
                                        RDomi – Tecnología, innovación y estabilidad a su servicio.
                                    </p>
                                </div>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </center>
</body>
</html>
