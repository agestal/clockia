@php
    $negocio = $reserva->negocio;
    $servicio = $reserva->servicio;
    $fecha = optional($reserva->fecha)->locale('es')->translatedFormat('j \d\e F');
    $surveyUrl = $template['encuesta_url'] ?? url("/encuesta/{$encuesta->token}");
@endphp
<!DOCTYPE html>
<html lang="es">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"></head>
<body style="margin:0;padding:24px 16px;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;background:{{ $template['color_fondo'] ?? '#F3F7F1' }};color:{{ $template['color_texto'] ?? '#213025' }};">
    <div style="max-width:560px;margin:0 auto;background:#ffffff;border-radius:12px;overflow:hidden;box-shadow:0 2px 12px rgba(0,0,0,0.06);">
        <div style="background:{{ $template['color_primario'] ?? '#4E8B31' }};padding:28px 32px;">
            <h1 style="margin:0;color:#ffffff;font-size:1.3rem;font-weight:600;">{{ $template['titulo'] ?? 'Comparte tu valoracion' }}</h1>
            <p style="margin:6px 0 0;color:rgba(255,255,255,0.88);font-size:0.88rem;">{{ $negocio?->nombre }}</p>
        </div>

        <div style="padding:28px 32px;text-align:center;">
            @if(! empty($template['saludo']))
                <p style="margin:0 0 10px;font-size:0.95rem;line-height:1.6;">{{ $template['saludo'] }}</p>
            @endif

            @if(! empty($template['introduccion']))
                <p style="margin:0 0 12px;font-size:0.92rem;line-height:1.6;">{{ $template['introduccion'] }}</p>
            @endif

            @if(! empty($template['cuerpo']))
                <p style="margin:0 0 24px;font-size:0.92rem;line-height:1.6;">{!! nl2br(e($template['cuerpo'])) !!}</p>
            @else
                <p style="margin:0 0 24px;font-size:0.92rem;line-height:1.6;">
                    Gracias por tu visita del {{ $fecha }} ({{ $servicio?->nombre }}).
                </p>
            @endif

            <a href="{{ $surveyUrl }}" style="display:inline-block;background:{{ $template['color_boton'] ?? '#2E7D32' }};color:#ffffff;text-decoration:none;padding:12px 32px;border-radius:8px;font-size:0.9rem;font-weight:600;">
                {{ $template['texto_boton'] ?? 'Responder encuesta' }}
            </a>
        </div>

        <div style="padding:16px 32px;background:#f8f9fa;text-align:center;">
            <p style="margin:0;font-size:0.75rem;color:#888;">{{ $template['texto_pie'] ?? ($negocio?->nombre ?? 'Clockia') }}</p>
        </div>
    </div>
</body>
</html>
