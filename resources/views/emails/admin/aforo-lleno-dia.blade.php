@php
    $fechaStr = $fechaHumana;
@endphp
<!DOCTYPE html>
<html lang="es">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"></head>
<body style="margin:0;padding:0;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;background:#f5f6f8;">
    <div style="max-width:560px;margin:40px auto;background:#fff;border-radius:12px;overflow:hidden;box-shadow:0 2px 12px rgba(0,0,0,0.06);">
        <div style="background:#b71c1c;padding:28px 32px;">
            <h1 style="margin:0;color:#fff;font-size:1.3rem;font-weight:600;">Día completo</h1>
            <p style="margin:6px 0 0;color:rgba(255,255,255,0.85);font-size:0.88rem;">{{ $negocio->nombre }}</p>
        </div>

        <div style="padding:28px 32px;">
            <p style="margin:0 0 20px;color:#333;font-size:0.95rem;line-height:1.6;">
                Todas las sesiones de <strong>{{ $servicio?->nombre }}</strong> para el <strong style="text-transform:capitalize;">{{ $fechaStr }}</strong> están completas. No quedan plazas disponibles para ese día.
            </p>

            <div style="background:#ffebee;border:1px solid #ef9a9a;border-radius:8px;padding:16px 20px;">
                <p style="margin:0 0 10px;font-size:0.82rem;color:#b71c1c;font-weight:600;">Sesiones del día:</p>
                <table style="width:100%;border-collapse:collapse;font-size:0.88rem;">
                    <tr style="border-bottom:1px solid #ef9a9a;">
                        <th style="padding:6px 0;color:#888;text-align:left;font-weight:500;">Horario</th>
                        <th style="padding:6px 0;color:#888;text-align:right;font-weight:500;">Aforo</th>
                    </tr>
                    @foreach($sesiones as $s)
                        <tr>
                            <td style="padding:5px 0;color:#333;">{{ substr((string) $s->hora_inicio, 0, 5) }} — {{ substr((string) $s->hora_fin, 0, 5) }}</td>
                            <td style="padding:5px 0;color:#b71c1c;font-weight:600;text-align:right;">{{ $s->aforo_total }} / {{ $s->aforo_total }}</td>
                        </tr>
                    @endforeach
                </table>
            </div>
        </div>

        <div style="padding:16px 32px;background:#f8f9fa;text-align:center;">
            <p style="margin:0;font-size:0.75rem;color:#aaa;">Notificación automática de {{ $negocio->nombre }} via Clockia.</p>
        </div>
    </div>
</body>
</html>
