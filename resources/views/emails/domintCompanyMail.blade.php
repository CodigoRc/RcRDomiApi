<!DOCTYPE html>
<html>
<head>
    <title>{{ $details['subject'] }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #e6f7ff; /* Azul muy pálido */
        }
        .container {
            width: 90%;
            max-width: 600px;
            margin: auto;
            background-color: #ffffff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        .header {
            background-color: #7ea7d4;
            color: #ffffff;
            padding: 10px 0;
            text-align: center;
            border-radius: 8px 8px 0 0;
        }
        .content {
            padding: 20px;
        }
        .footer {
            text-align: center;
            padding: 10px 0;
            color: #777777;
            font-size: 12px;
        }
        .logo {
            max-width: 100%;
            height: auto;
        }
        @media only screen and (max-width: 600px) {
            .container {
                width: 100%;
                padding: 10px;
            }
            .content {
                padding: 10px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <img src="https://domintapi2.com/images/logo/logo1.png" alt="DomintCompany Logo" class="logo">
            <h1>{{ $details['subject'] }}</h1>
        </div>
        <div class="content">
            <p>{{ $details['body'] }}</p>
        </div>
        <div class="footer">
            <p>&copy; {{ date('Y') }} Dominican Internet Group. All rights reserved.</p>
        </div>
    </div>
</body>
</html>