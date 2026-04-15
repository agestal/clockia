<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Gracias - {{ $encuesta->negocio?->nombre }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #f5f6f8;
            color: #333;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px 16px;
        }
        .card {
            max-width: 500px;
            width: 100%;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.06);
            overflow: hidden;
            text-align: center;
        }
        .header {
            background: linear-gradient(135deg, #34c759, #28a745);
            padding: 32px;
        }
        .header .icon {
            font-size: 3rem;
            margin-bottom: 8px;
        }
        .header h1 {
            color: #fff;
            font-size: 1.4rem;
            font-weight: 600;
        }
        .body {
            padding: 32px;
        }
        .body p {
            font-size: 0.95rem;
            color: #555;
            line-height: 1.7;
            margin-bottom: 12px;
        }
        .body p:last-child {
            margin-bottom: 0;
        }
        .footer {
            padding: 16px 32px;
            background: #f8f9fa;
            font-size: 0.75rem;
            color: #aaa;
        }
    </style>
</head>
<body>
    <div class="card">
        <div class="header">
            <div class="icon">&#10003;</div>
            <h1>Gracias por tu valoracion</h1>
        </div>
        <div class="body">
            <p>Tu opinion nos ayuda a seguir mejorando.</p>
            <p>Esperamos verte de nuevo pronto en <strong>{{ $encuesta->negocio?->nombre }}</strong>.</p>
        </div>
        <div class="footer">
            {{ $encuesta->negocio?->nombre }}
        </div>
    </div>
</body>
</html>
