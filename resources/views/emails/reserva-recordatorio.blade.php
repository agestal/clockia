@php
    $negocio = $reserva->negocio;
    $servicio = $reserva->servicio;
    $fecha = optional($reserva->fecha)->locale('es')->translatedFormat('l j \d\e F');
    $hora = substr((string) $reserva->hora_inicio, 0, 5);
@endphp
<!DOCTYPE html>
<html lang="es">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"></head>
<body style="margin:0;padding:24px 16px;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;background:{{ $template['color_fondo'] ?? '#F7F3EC' }};color:{{ $template['color_texto'] ?? '#2C241D' }};">
    <div style="max-width:560px;margin:0 auto;background:#ffffff;border-radius:12px;overflow:hidden;box-shadow:0 2px 12px rgba(0,0,0,0.06);">
        <div style="background:{{ $template['color_primario'] ?? '#A85C00' }};padding:28px 32px;">
            <h1 style="margin:0;color:#ffffff;font-size:1.3rem;font-weight:600;">{{ $template['titulo'] ?? 'Tu reserva se acerca' }}</h1>
            <p style="margin:6px 0 0;color:rgba(255,255,255,0.88);font-size:0.88rem;">{{ $negocio?->nombre }}</p>
        </div>

        <div style="padding:28px 32px;">
            @if(! empty($template['saludo']))
                <p style="margin:0 0 10px;font-size:0.95rem;line-height:1.6;">{{ $template['saludo'] }}</p>
            @endif

            @if(! empty($template['introduccion']))
                <p style="margin:0 0 12px;font-size:0.92rem;line-height:1.6;">{{ $template['introduccion'] }}</p>
            @endif

            @if(! empty($template['cuerpo']))
                <p style="margin:0 0 20px;font-size:0.92rem;line-height:1.6;">{!! nl2br(e($template['cuerpo'])) !!}</p>
            @endif

            <div style="background:#f8f9fa;border-radius:8px;padding:16px 20px;margin-bottom:20px;">
                <table style="width:100%;border-collapse:collapse;font-size:0.88rem;">
                    <tr><td style="padding:5px 0;color:#888;width:120px;">Servicio</td><td style="padding:5px 0;color:#333;">{{ $servicio?->nombre }}</td></tr>
                    <tr><td style="padding:5px 0;color:#888;">Fecha</td><td style="padding:5px 0;color:#333;font-weight:600;text-transform:capitalize;">{{ $fecha }}</td></tr>
                    <tr><td style="padding:5px 0;color:#888;">Hora</td><td style="padding:5px 0;color:#333;font-weight:600;">{{ $hora }}</td></tr>
                    @if($reserva->numero_personas)
                        <tr><td style="padding:5px 0;color:#888;">Personas</td><td style="padding:5px 0;color:#333;">{{ $reserva->numero_personas }}</td></tr>
                    @endif
                    <tr><td style="padding:5px 0;color:#888;">Localizador</td><td style="padding:5px 0;color:#333;">{{ $reserva->localizador }}</td></tr>
                </table>
            </div>

            @if($servicio?->instrucciones_previas)
                <p style="margin:0 0 16px;font-size:0.82rem;color:#665;line-height:1.5;">{{ $servicio->instrucciones_previas }}</p>
            @endif

            @if($negocio?->direccion)
                <p style="margin:0;font-size:0.82rem;color:#888;">{{ $negocio->direccion }}</p>
            @endif
        </div>

        <div style="padding:16px 32px;background:#f8f9fa;text-align:center;">
            <p style="margin:0;font-size:0.75rem;color:#888;">{{ $template['texto_pie'] ?? ($negocio?->nombre ?? 'Clockia') }}</p>
        </div>
    </div>
</body>
</html>
