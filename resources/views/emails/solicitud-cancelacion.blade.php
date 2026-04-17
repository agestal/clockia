@php
    $negocio = $reserva->negocio;
    $servicio = $reserva->servicio;
    $fecha = optional($reserva->fecha)->locale('es')->translatedFormat('l j \d\e F \d\e Y');
    $hora = substr((string) $reserva->hora_inicio, 0, 5);
@endphp
<!DOCTYPE html>
<html lang="es">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"></head>
<body style="margin:0;padding:0;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;background:#f5f6f8;">
    <div style="max-width:560px;margin:40px auto;background:#fff;border-radius:12px;overflow:hidden;box-shadow:0 2px 12px rgba(0,0,0,0.06);">
        <div style="background:#e65100;padding:28px 32px;">
            <h1 style="margin:0;color:#fff;font-size:1.3rem;font-weight:600;">Confirma la cancelacion de tu reserva</h1>
            <p style="margin:6px 0 0;color:rgba(255,255,255,0.85);font-size:0.88rem;">{{ $negocio?->nombre }}</p>
        </div>

        <div style="padding:28px 32px;">
            <p style="margin:0 0 20px;color:#333;font-size:0.95rem;line-height:1.6;">
                Hemos recibido una solicitud para cancelar tu reserva. Si realmente quieres cancelarla, pulsa el boton de abajo.
                Si no has sido tu, ignora este email y tu reserva seguira activa.
            </p>

            <div style="background:#f8f9fa;border-radius:8px;padding:16px 20px;margin-bottom:24px;">
                <table style="width:100%;border-collapse:collapse;font-size:0.88rem;">
                    <tr><td style="padding:5px 0;color:#888;width:120px;">Localizador</td><td style="padding:5px 0;font-weight:600;color:#333;">{{ $reserva->localizador }}</td></tr>
                    <tr><td style="padding:5px 0;color:#888;">Experiencia</td><td style="padding:5px 0;color:#333;">{{ $servicio?->nombre }}</td></tr>
                    <tr><td style="padding:5px 0;color:#888;">Fecha</td><td style="padding:5px 0;color:#333;text-transform:capitalize;">{{ $fecha }}</td></tr>
                    <tr><td style="padding:5px 0;color:#888;">Hora</td><td style="padding:5px 0;color:#333;">{{ $hora }}</td></tr>
                    @if($reserva->numero_personas)
                        <tr><td style="padding:5px 0;color:#888;">Personas</td><td style="padding:5px 0;color:#333;">{{ $reserva->numero_personas }}</td></tr>
                    @endif
                </table>
            </div>

            <div style="text-align:center;margin-bottom:24px;">
                <a href="{{ $cancelUrl }}" style="display:inline-block;padding:14px 32px;background:#dc3545;color:#fff;text-decoration:none;border-radius:8px;font-weight:600;font-size:0.95rem;">
                    Confirmar cancelacion
                </a>
            </div>

            <p style="margin:0;font-size:0.78rem;color:#999;text-align:center;line-height:1.5;">
                Este enlace es valido durante 24 horas. Si expira, puedes solicitar uno nuevo desde el widget de reservas.
            </p>
        </div>

        <div style="padding:16px 32px;background:#f8f9fa;text-align:center;">
            <p style="margin:0;font-size:0.75rem;color:#aaa;">Este email fue enviado automaticamente por {{ $negocio?->nombre }}.</p>
        </div>
    </div>
</body>
</html>
