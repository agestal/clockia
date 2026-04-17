@php
    $negocio = $reserva->negocio;
    $servicio = $reserva->servicio;
    $fecha = optional($reserva->fecha)->locale('es')->translatedFormat('l j \d\e F \d\e Y');
    $hora = substr((string) $reserva->hora_inicio, 0, 5);
@endphp
<!DOCTYPE html>
<html lang="es">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"></head>
<body style="margin:0;padding:24px 16px;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;background:{{ $template['color_fondo'] ?? '#F5F2EE' }};color:{{ $template['color_texto'] ?? '#2C241D' }};">
    <div style="max-width:560px;margin:0 auto;background:#ffffff;border-radius:12px;overflow:hidden;box-shadow:0 2px 12px rgba(0,0,0,0.06);">
        <div style="background:{{ $template['color_primario'] ?? '#7B3F00' }};padding:28px 32px;">
            <h1 style="margin:0;color:#ffffff;font-size:1.3rem;font-weight:600;">Reserva modificada</h1>
            <p style="margin:6px 0 0;color:rgba(255,255,255,0.88);font-size:0.88rem;">{{ $negocio?->nombre }}</p>
        </div>

        <div style="padding:28px 32px;">
            <p style="margin:0 0 12px;font-size:0.92rem;line-height:1.6;">Tu reserva se ha actualizado correctamente. Estos son los datos vigentes:</p>

            <div style="background:#f8f9fa;border-radius:8px;padding:16px 20px;margin-bottom:20px;">
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

            @if(! empty($changeSummary))
                <div style="background:#f4f8ff;border:1px solid #dbeafe;border-radius:8px;padding:14px 16px;margin-bottom:20px;">
                    <p style="margin:0 0 10px;font-size:0.82rem;color:#1d4ed8;font-weight:700;">Cambios aplicados</p>
                    <table style="width:100%;border-collapse:collapse;font-size:0.84rem;">
                        @foreach($changeSummary as $item)
                            <tr>
                                <td style="padding:6px 0;color:#64748b;width:110px;vertical-align:top;">{{ $item['label'] ?? $item['field'] }}</td>
                                <td style="padding:6px 0;color:#334155;">
                                    <span style="color:#94a3b8;text-decoration:line-through;">{{ $item['before'] }}</span>
                                    <span style="display:inline-block;margin:0 6px;color:#64748b;">→</span>
                                    <strong style="color:#0f172a;">{{ $item['after'] }}</strong>
                                </td>
                            </tr>
                        @endforeach
                    </table>
                </div>
            @endif

            @if($negocio?->direccion)
                <p style="margin:0 0 6px;font-size:0.82rem;color:#888;">{{ $negocio->direccion }}</p>
            @endif
            @if($negocio?->telefono)
                <p style="margin:0;font-size:0.82rem;color:#888;">{{ $negocio->telefono }}</p>
            @endif
        </div>

        <div style="padding:16px 32px;background:#f8f9fa;text-align:center;">
            <p style="margin:0;font-size:0.75rem;color:#888;">{{ $template['texto_pie'] ?? ($negocio?->nombre ?? 'Clockia') }}</p>
        </div>
    </div>
</body>
</html>
