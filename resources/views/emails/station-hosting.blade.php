<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Acceso a cPanel - RDomi</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            font-family: Arial, sans-serif;
            color: #333333;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 640px;
            margin: auto;
            background-color: #ffffff;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            box-shadow: 0 0 12px rgba(0, 0, 0, 0.05);
        }
        .header {
            background-color: #ffffff;
            text-align: center;
            padding: 30px 20px 10px;
            border-bottom: 1px solid #dddddd;
        }
        .header img {
            max-width: 180px;
            margin-bottom: 10px;
        }
        .headline {
            font-size: 24px;
            color: #0053A0;
            margin: 10px 0 5px;
        }
        .subhead {
            font-size: 16px;
            color: #555555;
            margin-bottom: 20px;
        }
        .content {
            padding: 30px 25px;
        }
        .content h2 {
            font-size: 20px;
            color: #0053A0;
            margin-top: 25px;
            margin-bottom: 10px;
        }
        .content p {
            line-height: 1.6;
            margin-bottom: 16px;
            font-size: 15px;
        }
        .content ul {
            padding-left: 20px;
            margin-bottom: 20px;
        }
        .content ul li {
            margin-bottom: 10px;
        }
        .footer {
            background-color: #f9f9f9;
            padding: 25px 30px;
            font-size: 13px;
            color: #555555;
            text-align: center;
        }
        .footer a {
            color: #0053A0;
            text-decoration: none;
        }
        @media only screen and (max-width: 600px) {
            .container {
                width: 100% !important;
                max-width: 100% !important;
            }
            .content {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <img src="https://rdomi.com/logomail.png" alt="Logo RDomi">
            <h1 class="headline">Acceso a su cPanel</h1>
            <p class="subhead">Datos de acceso y administración de hosting</p>
        </div>
        
        <div class="content">
            <p>Estimado cliente,</p>
            <p>
                A continuación encontrará los accesos a su cuenta de cPanel para la estación <strong>{{ $station->name }}</strong>, 
                desde donde podrá gestionar sus archivos, bases de datos, dominios y otros recursos asociados a su servicio de hosting.
            </p>
            
            <h2>Datos de acceso a cPanel</h2>
            <ul>
                <li><strong>Dirección de acceso:</strong> <a href="{{ $hosting->cpanel }}" target="_blank">{{ $hosting->cpanel }}</a></li>
                <li><strong>Usuario:</strong> {{ $hosting->user_name }}</li>
                <li><strong>Contraseña:</strong> {{ $hosting->pass }}</li>
                @if($hosting->url)
                <li><strong>Sitio web:</strong> <a href="{{ $hosting->url }}" target="_blank">{{ $hosting->url }}</a></li>
                @endif
            </ul>
            
            <p>
                Si necesita asistencia o desea que le guiemos en alguna configuración específica, 
                nuestro equipo técnico está disponible 24/7 para brindarle soporte.
            </p>
            
            <p>
                Atentamente,<br>
                <strong>Equipo de Soporte RDomi</strong>
            </p>
        </div>
        
        <div class="footer">
            <p><strong>Contáctenos:</strong></p>
            <p>📞 USA: +1 (415) 702-3258</p>
            <p>📞 RD: +1 (829) 890-7166</p>
            <p>💬 WhatsApp: +1 (829) 890-7166</p>
            <p>📧 <a href="mailto:info@rdomi.com">info@rdomi.com</a></p>
            <p style="margin-top:15px;font-size:12px;color:#777777;">
                <strong>RDomi - Tecnología, innovación y estabilidad a su servicio.</strong>
            </p>
        </div>
    </div>
</body>
</html>
