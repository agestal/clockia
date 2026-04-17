@php
    $negocio = $reserva->negocio;
    $servicio = $reserva->servicio ?? $sesion->servicio;
    $fecha = optional($sesion->fecha)->locale('es')->translatedFormat('l j \d\e F \d\e Y');
    $hora = substr((string) $sesion->hora_inicio, 0, 5);
    $horaFin = substr((string) $sesion->hora_fin, 0, 5);
@endphp
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Aforo completo en sesión</title>
</head>
<body style="margin:0;padding:0;background:#f3f5f9;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Arial,sans-serif;color:#1f2937;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background:#f3f5f9;margin:0;padding:0;width:100%;">
        <tr>
            <td align="center" style="padding:32px 16px;">
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="max-width:640px;background:#ffffff;border-radius:18px;overflow:hidden;box-shadow:0 12px 34px rgba(15,23,42,0.08);">
                    <tr>
                        <td style="padding:0;">
                            <div style="height:6px;background:linear-gradient(90deg,#c2410c 0%,#f97316 100%);font-size:0;line-height:0;">&nbsp;</div>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:32px 36px 24px;background:linear-gradient(180deg,#fffaf5 0%,#ffffff 100%);border-bottom:1px solid #e5e7eb;">
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0">
                                <tr>
                                    <td style="vertical-align:top;padding-right:16px;">
                                        <p style="margin:0 0 10px;font-size:12px;line-height:12px;font-weight:700;letter-spacing:0.08em;text-transform:uppercase;color:#c2410c;">
                                            Clockia Admin
                                        </p>
                                        <h1 style="margin:0 0 10px;font-size:30px;line-height:36px;font-weight:800;color:#111827;">
                                            Aforo completo en sesión
                                        </h1>
                                        <p style="margin:0;font-size:15px;line-height:24px;color:#4b5563;">
                                            La sesión de <strong style="color:#111827;">{{ $servicio?->nombre }}</strong> ha alcanzado el aforo máximo.
                                        </p>
                                    </td>
                                    <td align="right" style="vertical-align:top;white-space:nowrap;">
                                        <span style="display:inline-block;padding:10px 14px;border-radius:999px;background:#fff0e6;color:#c2410c;font-size:13px;font-weight:700;">
                                            AFORO LLENO
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
                                                Horario
                                            </p>
                                            <p style="margin:0;font-size:20px;line-height:26px;font-weight:800;color:#111827;">
                                                {{ $hora }} - {{ $horaFin }}
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
                                    <p style="margin:0;font-size:15px;line-height:20px;font-weight:800;color:#111827;">Datos de la sesión</p>
                                </div>

                                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="border-collapse:collapse;">
                                    <tr>
                                        <td style="padding:14px 20px;border-bottom:1px solid #eef2f7;font-size:13px;font-weight:700;color:#6b7280;width:150px;">Fecha</td>
                                        <td style="padding:14px 20px;border-bottom:1px solid #eef2f7;font-size:14px;line-height:22px;color:#111827;text-transform:capitalize;">{{ $fecha }}</td>
                                    </tr>
                                    <tr>
                                        <td style="padding:14px 20px;border-bottom:1px solid #eef2f7;font-size:13px;font-weight:700;color:#6b7280;">Horario</td>
                                        <td style="padding:14px 20px;border-bottom:1px solid #eef2f7;font-size:14px;line-height:22px;color:#111827;">{{ $hora }} - {{ $horaFin }}</td>
                                    </tr>
                                    <tr>
                                        <td style="padding:14px 20px;font-size:13px;font-weight:700;color:#6b7280;">Aforo total</td>
                                        <td style="padding:14px 20px;">
                                            <span style="display:inline-block;padding:6px 10px;border-radius:999px;background:#fff0e6;color:#c2410c;font-size:12px;font-weight:800;">
                                                {{ $sesion->aforo_total }} personas
                                            </span>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:20px 36px 0;">
                            <div style="background:linear-gradient(180deg,#fff7ed 0%,#ffffff 100%);border:1px solid #fdba74;border-radius:16px;padding:18px 20px;">
                                <p style="margin:0 0 8px;font-size:15px;line-height:20px;font-weight:800;color:#9a3412;">Última reserva que completó el aforo</p>
                                <p style="margin:0;font-size:14px;line-height:24px;color:#7c2d12;">
                                    <strong>{{ $reserva->localizador }}</strong> · {{ $reserva->nombre_responsable }}, {{ $reserva->numero_personas }} personas.
                                </p>
                            </div>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:28px 36px 34px;">
                            <div style="padding-top:18px;border-top:1px solid #e5e7eb;">
                                <p style="margin:0 0 6px;font-size:13px;line-height:20px;color:#6b7280;">
                                    La franja ya no acepta más reservas. Revisa el calendario si necesitas abrir alternativas.
                                </p>
                                <p style="margin:0;font-size:12px;line-height:18px;color:#9ca3af;">
                                    Notificación automática de {{ $negocio?->nombre }} via Clockia.
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
