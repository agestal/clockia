@php
    $negocio = $reserva->negocio;
    $servicio = $reserva->servicio;
    $fecha = optional($reserva->fecha)->locale('es')->translatedFormat('j \d\e F');
    $surveyUrl = url("/encuesta/{$encuesta->token}");
@endphp
<!DOCTYPE html>
<html lang="es">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"></head>
<body style="margin:0;padding:0;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;background:#f5f6f8;">
    <div style="max-width:560px;margin:40px auto;background:#fff;border-radius:12px;overflow:hidden;box-shadow:0 2px 12px rgba(0,0,0,0.06);">
        <div style="background:#34c759;padding:28px 32px;">
            <h1 style="margin:0;color:#fff;font-size:1.3rem;font-weight:600;">⭐ ¿Qué tal tu experiencia?</h1>
            <p style="margin:6px 0 0;color:rgba(255,255,255,0.85);font-size:0.88rem;">{{ $negocio?->nombre }}</p>
        </div>

        <div style="padding:28px 32px;text-align:center;">
            <p style="margin:0 0 10px;color:#333;font-size:0.95rem;line-height:1.6;">
                ¡Hola{{ $reserva->nombre_responsable ? ', '.$reserva->nombre_responsable : '' }}!
            </p>
            <p style="margin:0 0 24px;color:#555;font-size:0.9rem;line-height:1.6;">
                Gracias por tu visita del {{ $fecha }} ({{ $servicio?->nombre }}).<br>
                Nos encantaría saber qué tal fue tu experiencia.
            </p>

            <a href="{{ $surveyUrl }}" style="display:inline-block;background:#007bff;color:#fff;text-decoration:none;padding:12px 32px;border-radius:8px;font-size:0.9rem;font-weight:600;">
                Valorar experiencia
            </a>

            <p style="margin:20px 0 0;font-size:0.78rem;color:#aaa;">Solo te llevará 30 segundos 😊</p>
        </div>

        <div style="padding:16px 32px;background:#f8f9fa;text-align:center;">
            <p style="margin:0;font-size:0.75rem;color:#aaa;">{{ $negocio?->nombre }} · Encuesta de satisfacción</p>
        </div>
    </div>
</body>
</html>
