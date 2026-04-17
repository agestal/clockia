@php
    $negocio = $reserva->negocio;
    $servicio = $reserva->servicio;
    $cliente = $reserva->nombre_responsable ?? $reserva->cliente?->nombre ?? 'Cliente';
@endphp
<!DOCTYPE html>
<html lang="es">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"></head>
<body style="margin:0;padding:0;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;background:#f5f6f8;">
    <div style="max-width:560px;margin:40px auto;background:#fff;border-radius:12px;overflow:hidden;box-shadow:0 2px 12px rgba(0,0,0,0.06);">
        <div style="background:#6f42c1;padding:28px 32px;">
            <h1 style="margin:0;color:#fff;font-size:1.3rem;font-weight:600;">Encuesta respondida</h1>
            <p style="margin:6px 0 0;color:rgba(255,255,255,0.85);font-size:0.88rem;">{{ $negocio?->nombre }}</p>
        </div>

        <div style="padding:28px 32px;">
            <p style="margin:0 0 20px;color:#333;font-size:0.95rem;line-height:1.6;">
                <strong>{{ $cliente }}</strong> ha respondido a la encuesta de satisfacción sobre la experiencia <strong>{{ $servicio?->nombre }}</strong>.
            </p>

            <div style="background:#f8f9fa;border-radius:8px;padding:16px 20px;margin-bottom:20px;">
                <p style="margin:0 0 10px;font-size:0.82rem;color:#555;font-weight:600;">Respuestas:</p>
                <table style="width:100%;border-collapse:collapse;font-size:0.88rem;">
                    @foreach($respuestas as $resp)
                        <tr>
                            <td style="padding:5px 0;color:#888;width:60%;">{{ $resp['pregunta'] ?? 'Pregunta' }}</td>
                            <td style="padding:5px 0;color:#333;font-weight:600;">{{ $resp['valor'] ?? '-' }}</td>
                        </tr>
                    @endforeach
                </table>
            </div>

            @if($comentario)
                <div style="background:#f3f0ff;border:1px solid #d8cff0;border-radius:8px;padding:12px 16px;">
                    <p style="margin:0;font-size:0.82rem;color:#6f42c1;font-weight:600;">Comentario del cliente</p>
                    <p style="margin:6px 0 0;font-size:0.88rem;color:#333;line-height:1.5;">{{ $comentario }}</p>
                </div>
            @endif
        </div>

        <div style="padding:16px 32px;background:#f8f9fa;text-align:center;">
            <p style="margin:0;font-size:0.75rem;color:#aaa;">Notificación automática de {{ $negocio?->nombre }} via Clockia.</p>
        </div>
    </div>
</body>
</html>
