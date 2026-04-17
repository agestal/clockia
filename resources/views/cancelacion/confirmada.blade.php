<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>{{ $error ? 'Error' : 'Reserva cancelada' }} — Clockia</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: #f5f6f8; color: #333; display: flex; justify-content: center; align-items: center; min-height: 100vh; padding: 20px; }
        .card { max-width: 480px; width: 100%; background: #fff; border-radius: 12px; overflow: hidden; box-shadow: 0 2px 12px rgba(0,0,0,0.06); }
        .header { padding: 28px 32px; color: #fff; }
        .header-ok { background: #6c757d; }
        .header-error { background: #dc3545; }
        .header h1 { font-size: 1.3rem; font-weight: 600; }
        .header p { margin-top: 6px; font-size: 0.88rem; opacity: 0.85; }
        .body { padding: 28px 32px; }
        .body p { font-size: 0.95rem; line-height: 1.6; margin-bottom: 16px; }
        .details { background: #f8f9fa; border-radius: 8px; padding: 16px 20px; margin-bottom: 20px; }
        .details table { width: 100%; border-collapse: collapse; font-size: 0.88rem; }
        .details td { padding: 5px 0; }
        .details td:first-child { color: #888; width: 120px; }
        .details td:last-child { color: #333; }
        .status { color: #dc3545; font-weight: 600; }
        .footer { padding: 16px 32px; background: #f8f9fa; text-align: center; }
        .footer p { font-size: 0.75rem; color: #aaa; }
    </style>
</head>
<body>
    <div class="card">
        @if($error)
            <div class="header header-error">
                <h1>No se pudo cancelar</h1>
            </div>
            <div class="body">
                <p>{{ $error }}</p>
            </div>
        @else
            <div class="header header-ok">
                <h1>Reserva cancelada correctamente</h1>
                <p>{{ $reserva->negocio?->nombre }}</p>
            </div>
            <div class="body">
                <p>Tu reserva ha sido cancelada. Te hemos enviado un email de confirmacion con los detalles.</p>

                <div class="details">
                    <table>
                        <tr><td>Localizador</td><td><strong>{{ $reserva->localizador }}</strong></td></tr>
                        <tr><td>Experiencia</td><td>{{ $reserva->servicio?->nombre }}</td></tr>
                        <tr><td>Fecha</td><td style="text-transform:capitalize;">{{ optional($reserva->fecha)->locale('es')->translatedFormat('l j \d\e F \d\e Y') }}</td></tr>
                        <tr><td>Hora</td><td>{{ substr((string) $reserva->hora_inicio, 0, 5) }}</td></tr>
                        <tr><td>Estado</td><td class="status">Cancelada</td></tr>
                    </table>
                </div>
            </div>
            <div class="footer">
                <p>{{ $reserva->negocio?->nombre }} — Clockia</p>
            </div>
        @endif
    </div>
</body>
</html>
