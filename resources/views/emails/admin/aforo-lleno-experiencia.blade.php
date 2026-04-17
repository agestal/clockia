@php
    $negocio = $reserva->negocio;
    $servicio = $reserva->servicio ?? $sesion->servicio;
    $fecha = optional($sesion->fecha)->locale('es')->translatedFormat('l j \d\e F \d\e Y');
    $hora = substr((string) $sesion->hora_inicio, 0, 5);
    $horaFin = substr((string) $sesion->hora_fin, 0, 5);
@endphp
<!DOCTYPE html>
<html lang="es">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"></head>
<body style="margin:0;padding:0;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;background:#f5f6f8;">
    <div style="max-width:560px;margin:40px auto;background:#fff;border-radius:12px;overflow:hidden;box-shadow:0 2px 12px rgba(0,0,0,0.06);">
        <div style="background:#e65100;padding:28px 32px;">
            <h1 style="margin:0;color:#fff;font-size:1.3rem;font-weight:600;">Aforo completo en sesión</h1>
            <p style="margin:6px 0 0;color:rgba(255,255,255,0.85);font-size:0.88rem;">{{ $negocio?->nombre }}</p>
        </div>

        <div style="padding:28px 32px;">
            <p style="margin:0 0 20px;color:#333;font-size:0.95rem;line-height:1.6;">
                La siguiente sesión ha alcanzado el aforo máximo. Ya no se aceptarán más reservas para esta franja.
            </p>

            <div style="background:#fff3e0;border:1px solid #ffcc80;border-radius:8px;padding:16px 20px;">
                <table style="width:100%;border-collapse:collapse;font-size:0.88rem;">
                    <tr><td style="padding:5px 0;color:#888;width:130px;">Experiencia</td><td style="padding:5px 0;font-weight:600;color:#333;">{{ $servicio?->nombre }}</td></tr>
                    <tr><td style="padding:5px 0;color:#888;">Fecha</td><td style="padding:5px 0;color:#333;text-transform:capitalize;">{{ $fecha }}</td></tr>
                    <tr><td style="padding:5px 0;color:#888;">Horario</td><td style="padding:5px 0;color:#333;">{{ $hora }} — {{ $horaFin }}</td></tr>
                    <tr><td style="padding:5px 0;color:#888;">Aforo total</td><td style="padding:5px 0;color:#333;">{{ $sesion->aforo_total }} personas</td></tr>
                </table>
            </div>

            <p style="margin:16px 0 0;font-size:0.82rem;color:#888;line-height:1.5;">
                Última reserva que completó el aforo: <strong>{{ $reserva->localizador }}</strong> ({{ $reserva->nombre_responsable }}, {{ $reserva->numero_personas }} personas).
            </p>
        </div>

        <div style="padding:16px 32px;background:#f8f9fa;text-align:center;">
            <p style="margin:0;font-size:0.75rem;color:#aaa;">Notificación automática de {{ $negocio?->nombre }} via Clockia.</p>
        </div>
    </div>
</body>
</html>
