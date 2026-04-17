<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Gracias - {{ $encuesta->negocio?->nombre }}</title>
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px 16px;
            background: #f4f0e9;
            color: #2c241d;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }
        .card {
            width: 100%;
            max-width: 520px;
            background: #fff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 2px 12px rgba(0,0,0,0.06);
            text-align: center;
        }
        .header {
            background: #4e8b31;
            color: #fff;
            padding: 32px;
        }
        .header h1 {
            margin: 0;
            font-size: 1.45rem;
        }
        .body {
            padding: 32px;
        }
        .body p {
            margin: 0 0 12px;
            color: #5c5045;
            line-height: 1.7;
        }
        .body p:last-child {
            margin-bottom: 0;
        }
        .footer {
            padding: 16px 32px;
            background: #faf7f3;
            color: #8e8072;
            font-size: 0.78rem;
        }
    </style>
</head>
<body>
    <div class="card">
        <div class="header">
            <h1>{{ $survey['agradecimiento_titulo'] ?? 'Gracias por tu valoracion' }}</h1>
        </div>
        <div class="body">
            <p>{{ $survey['agradecimiento_texto'] ?? 'Tu opinion nos ayuda a seguir mejorando.' }}</p>
            <p>Hasta pronto en <strong>{{ $encuesta->negocio?->nombre }}</strong>.</p>
        </div>
        <div class="footer">
            {{ $encuesta->negocio?->nombre }}
        </div>
    </div>
</body>
</html>
