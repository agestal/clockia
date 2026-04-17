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
        <div style="background:#dc3545;padding:28px 32px;">
            <h1 style="margin:0;color:#fff;font-size:1.3rem;font-weight:600;">Reserva anulada</h1>
            <p style="margin:6px 0 0;color:rgba(255,255,255,0.85);font-size:0.88rem;">{{ $negocio?->nombre }}</p>
        </div>

        <div style="padding:28px 32px;">
            <p style="margin:0 0 20px;color:#333;font-size:0.95rem;line-height:1.6;">
                La siguiente reserva ha sido anulada:
            </p>

            <div style="background:#f8f9fa;border-radius:8px;padding:16px 20px;margin-bottom:20px;">
                <table style="width:100%;border-collapse:collapse;font-size:0.88rem;">
                    <tr><td style="padding:5px 0;color:#888;width:130px;">Localizador</td><td style="padding:5px 0;font-weight:600;color:#333;">{{ $reserva->localizador }}</td></tr>
                    <tr><td style="padding:5px 0;color:#888;">Experiencia</td><td style="padding:5px 0;color:#333;">{{ $servicio?->nombre }}</td></tr>
                    <tr><td style="padding:5px 0;color:#888;">Fecha</td><td style="padding:5px 0;color:#333;text-transform:capitalize;">{{ $fecha }}</td></tr>
                    <tr><td style="padding:5px 0;color:#888;">Hora</td><td style="padding:5px 0;color:#333;">{{ $hora }}</td></tr>
                    @if($reserva->numero_personas)
                        <tr><td style="padding:5px 0;color:#888;">Personas</td><td style="padding:5px 0;color:#333;">{{ $reserva->numero_personas }}</td></tr>
                    @endif
                    @if($reserva->motivo_cancelacion)
                        <tr><td style="padding:5px 0;color:#888;">Motivo</td><td style="padding:5px 0;color:#333;">{{ $reserva->motivo_cancelacion }}</td></tr>
                    @endif
                    @if($reserva->cancelada_por)
                        <tr><td style="padding:5px 0;color:#888;">Cancelada por</td><td style="padding:5px 0;color:#333;">{{ $reserva->cancelada_por }}</td></tr>
                    @endif
                </table>
            </div>

            <div style="background:#fce8e8;border-radius:8px;padding:16px 20px;">
                <p style="margin:0 0 8px;font-size:0.82rem;color:#721c24;font-weight:600;">Cliente</p>
                <table style="width:100%;border-collapse:collapse;font-size:0.88rem;">
                    <tr><td style="padding:4px 0;color:#888;width:130px;">Nombre</td><td style="padding:4px 0;color:#333;">{{ $reserva->nombre_responsable }}</td></tr>
                    <tr><td style="padding:4px 0;color:#888;">Teléfono</td><td style="padding:4px 0;color:#333;">{{ $reserva->telefono_responsable }}</td></tr>
                    @if($reserva->email_responsable)
                        <tr><td style="padding:4px 0;color:#888;">Email</td><td style="padding:4px 0;color:#333;">{{ $reserva->email_responsable }}</td></tr>
                    @endif
                </table>
            </div>
        </div>

        <div style="padding:16px 32px;background:#f8f9fa;text-align:center;">
            <p style="margin:0;font-size:0.75rem;color:#aaa;">Notificación automática de {{ $negocio?->nombre }} via Clockia.</p>
        </div>
    </div>
</body>
</html>
