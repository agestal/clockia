@php
    $fechaStr = $fechaHumana;
@endphp
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Día completo</title>
</head>
<body style="margin:0;padding:0;background:#f3f5f9;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Arial,sans-serif;color:#1f2937;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background:#f3f5f9;margin:0;padding:0;width:100%;">
        <tr>
            <td align="center" style="padding:32px 16px;">
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="max-width:640px;background:#ffffff;border-radius:18px;overflow:hidden;box-shadow:0 12px 34px rgba(15,23,42,0.08);">
                    <tr>
                        <td style="padding:0;">
                            <div style="height:6px;background:linear-gradient(90deg,#991b1b 0%,#dc2626 100%);font-size:0;line-height:0;">&nbsp;</div>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:32px 36px 24px;background:linear-gradient(180deg,#fff8f8 0%,#ffffff 100%);border-bottom:1px solid #e5e7eb;">
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0">
                                <tr>
                                    <td style="vertical-align:top;padding-right:16px;">
                                        <p style="margin:0 0 10px;font-size:12px;line-height:12px;font-weight:700;letter-spacing:0.08em;text-transform:uppercase;color:#991b1b;">
                                            Clockia Admin
                                        </p>
                                        <h1 style="margin:0 0 10px;font-size:30px;line-height:36px;font-weight:800;color:#111827;">
                                            Día completo
                                        </h1>
                                        <p style="margin:0;font-size:15px;line-height:24px;color:#4b5563;">
                                            No quedan plazas disponibles para <strong style="color:#111827;">{{ $servicio?->nombre }}</strong> en
                                            <strong style="color:#111827;text-transform:capitalize;">{{ $fechaStr }}</strong>.
                                        </p>
                                    </td>
                                    <td align="right" style="vertical-align:top;white-space:nowrap;">
                                        <span style="display:inline-block;padding:10px 14px;border-radius:999px;background:#fdecec;color:#991b1b;font-size:13px;font-weight:700;">
                                            COMPLETO
                                        </span>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:28px 36px 8px;">
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0">
                                <tr>
                                    <td width="50%" style="padding:0 8px 16px 0;vertical-align:top;">
                                        <div style="background:#f8fafc;border:1px solid #e5e7eb;border-radius:14px;padding:18px 18px 16px;">
                                            <p style="margin:0 0 8px;font-size:12px;font-weight:700;letter-spacing:0.06em;text-transform:uppercase;color:#6b7280;">
                                                Experiencia
                                            </p>
                                            <p style="margin:0;font-size:20px;line-height:26px;font-weight:800;color:#111827;">
                                                {{ $servicio?->nombre }}
                                            </p>
                                        </div>
                                    </td>
                                    <td width="50%" style="padding:0 0 16px 8px;vertical-align:top;">
                                        <div style="background:#f8fafc;border:1px solid #e5e7eb;border-radius:14px;padding:18px 18px 16px;">
                                            <p style="margin:0 0 8px;font-size:12px;font-weight:700;letter-spacing:0.06em;text-transform:uppercase;color:#6b7280;">
                                                Fecha
                                            </p>
                                            <p style="margin:0;font-size:20px;line-height:26px;font-weight:800;color:#111827;text-transform:capitalize;">
                                                {{ $fechaStr }}
                                            </p>
                                        </div>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:8px 36px 0;">
                            <div style="background:#ffffff;border:1px solid #e5e7eb;border-radius:16px;overflow:hidden;">
                                <div style="padding:16px 20px;background:#f9fafb;border-bottom:1px solid #e5e7eb;">
                                    <p style="margin:0;font-size:15px;line-height:20px;font-weight:800;color:#111827;">Sesiones del día</p>
                                </div>

                                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="border-collapse:collapse;">
                                    <tr>
                                        <td style="padding:14px 20px;border-bottom:1px solid #eef2f7;font-size:12px;font-weight:700;letter-spacing:0.04em;text-transform:uppercase;color:#6b7280;">Horario</td>
                                        <td align="right" style="padding:14px 20px;border-bottom:1px solid #eef2f7;font-size:12px;font-weight:700;letter-spacing:0.04em;text-transform:uppercase;color:#6b7280;">Aforo</td>
                                    </tr>
                                    @foreach($sesiones as $s)
                                        <tr>
                                            <td style="padding:14px 20px;border-bottom:1px solid #eef2f7;font-size:14px;line-height:22px;color:#111827;">
                                                {{ substr((string) $s->hora_inicio, 0, 5) }} - {{ substr((string) $s->hora_fin, 0, 5) }}
                                            </td>
                                            <td align="right" style="padding:14px 20px;border-bottom:1px solid #eef2f7;">
                                                <span style="display:inline-block;padding:6px 10px;border-radius:999px;background:#fdecec;color:#991b1b;font-size:12px;font-weight:800;">
                                                    {{ $s->aforo_total }} / {{ $s->aforo_total }}
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </table>
                            </div>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:20px 36px 0;">
                            <div style="background:linear-gradient(180deg,#fff5f5 0%,#ffffff 100%);border:1px solid #fecaca;border-radius:16px;padding:18px 20px;">
                                <p style="margin:0;font-size:14px;line-height:24px;color:#7f1d1d;">
                                    Todas las franjas están al 100% de ocupación. Puede ser un buen momento para revisar bloqueos, ampliar capacidad o abrir nuevas opciones.
                                </p>
                            </div>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:28px 36px 34px;">
                            <div style="padding-top:18px;border-top:1px solid #e5e7eb;">
                                <p style="margin:0 0 6px;font-size:13px;line-height:20px;color:#6b7280;">
                                    Revisa el calendario para actuar sobre disponibilidad o capacidad si hace falta.
                                </p>
                                <p style="margin:0;font-size:12px;line-height:18px;color:#9ca3af;">
                                    Notificación automática de {{ $negocio->nombre }} via Clockia.
                                </p>
                            </div>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
