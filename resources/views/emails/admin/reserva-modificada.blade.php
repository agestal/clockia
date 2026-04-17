@php
    $negocio = $reserva->negocio;
    $servicio = $reserva->servicio;
    $fecha = optional($reserva->fecha)->locale('es')->translatedFormat('l j \d\e F \d\e Y');
    $hora = substr((string) $reserva->hora_inicio, 0, 5);
@endphp
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Reserva modificada</title>
</head>
<body style="margin:0;padding:0;background:#f3f5f9;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Arial,sans-serif;color:#1f2937;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background:#f3f5f9;margin:0;padding:0;width:100%;">
        <tr>
            <td align="center" style="padding:32px 16px;">
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="max-width:640px;background:#ffffff;border-radius:18px;overflow:hidden;box-shadow:0 12px 34px rgba(15,23,42,0.08);">
                    <tr>
                        <td style="padding:0;">
                            <div style="height:6px;background:linear-gradient(90deg,#1d4ed8 0%,#60a5fa 100%);font-size:0;line-height:0;">&nbsp;</div>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:32px 36px 24px;background:linear-gradient(180deg,#f4f8ff 0%,#ffffff 100%);border-bottom:1px solid #e5e7eb;">
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0">
                                <tr>
                                    <td style="vertical-align:top;padding-right:16px;">
                                        <p style="margin:0 0 10px;font-size:12px;line-height:12px;font-weight:700;letter-spacing:0.08em;text-transform:uppercase;color:#1d4ed8;">
                                            Clockia Admin
                                        </p>
                                        <h1 style="margin:0 0 10px;font-size:30px;line-height:36px;font-weight:800;color:#111827;">
                                            Reserva modificada
                                        </h1>
                                        <p style="margin:0;font-size:15px;line-height:24px;color:#4b5563;">
                                            Se han aplicado cambios a una reserva de <strong style="color:#111827;">{{ $negocio?->nombre }}</strong>.
                                        </p>
                                    </td>
                                    <td align="right" style="vertical-align:top;white-space:nowrap;">
                                        <span style="display:inline-block;padding:10px 14px;border-radius:999px;background:#e8f0ff;color:#1d4ed8;font-size:13px;font-weight:700;">
                                            ACTUALIZADA
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
                                                Localizador
                                            </p>
                                            <p style="margin:0;font-size:24px;line-height:28px;font-weight:800;color:#111827;">
                                                {{ $reserva->localizador }}
                                            </p>
                                        </div>
                                    </td>
                                    <td width="50%" style="padding:0 0 16px 8px;vertical-align:top;">
                                        <div style="background:#f8fafc;border:1px solid #e5e7eb;border-radius:14px;padding:18px 18px 16px;">
                                            <p style="margin:0 0 8px;font-size:12px;font-weight:700;letter-spacing:0.06em;text-transform:uppercase;color:#6b7280;">
                                                Experiencia actual
                                            </p>
                                            <p style="margin:0;font-size:20px;line-height:26px;font-weight:700;color:#111827;">
                                                {{ $servicio?->nombre }}
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
                                    <p style="margin:0;font-size:15px;line-height:20px;font-weight:800;color:#111827;">Datos vigentes</p>
                                </div>

                                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="border-collapse:collapse;">
                                    <tr>
                                        <td style="padding:14px 20px;border-bottom:1px solid #eef2f7;font-size:13px;font-weight:700;color:#6b7280;width:150px;">Fecha</td>
                                        <td style="padding:14px 20px;border-bottom:1px solid #eef2f7;font-size:14px;line-height:22px;color:#111827;text-transform:capitalize;">{{ $fecha }}</td>
                                    </tr>
                                    <tr>
                                        <td style="padding:14px 20px;border-bottom:1px solid #eef2f7;font-size:13px;font-weight:700;color:#6b7280;">Hora</td>
                                        <td style="padding:14px 20px;border-bottom:1px solid #eef2f7;font-size:14px;line-height:22px;color:#111827;">{{ $hora }}</td>
                                    </tr>
                                    @if($reserva->numero_personas)
                                        <tr>
                                            <td style="padding:14px 20px;border-bottom:1px solid #eef2f7;font-size:13px;font-weight:700;color:#6b7280;">Personas</td>
                                            <td style="padding:14px 20px;border-bottom:1px solid #eef2f7;font-size:14px;line-height:22px;color:#111827;">{{ $reserva->numero_personas }}</td>
                                        </tr>
                                    @endif
                                    <tr>
                                        <td style="padding:14px 20px;font-size:13px;font-weight:700;color:#6b7280;">Cliente</td>
                                        <td style="padding:14px 20px;font-size:14px;line-height:22px;color:#111827;">{{ $reserva->nombreResponsableEfectivo() ?: 'Sin indicar' }}</td>
                                    </tr>
                                </table>
                            </div>
                        </td>
                    </tr>

                    @if(! empty($changeSummary))
                        <tr>
                            <td style="padding:20px 36px 0;">
                                <div style="background:linear-gradient(180deg,#eef4ff 0%,#ffffff 100%);border:1px solid #dbeafe;border-radius:16px;overflow:hidden;">
                                    <div style="padding:16px 20px 12px;">
                                        <p style="margin:0;font-size:15px;line-height:20px;font-weight:800;color:#1d4ed8;">Cambios aplicados</p>
                                    </div>

                                    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="border-collapse:collapse;">
                                        @foreach($changeSummary as $item)
                                            <tr>
                                                <td style="padding:12px 20px;border-top:1px solid #e8f0fb;font-size:13px;font-weight:700;color:#6b7280;width:150px;">{{ $item['label'] ?? $item['field'] }}</td>
                                                <td style="padding:12px 20px;border-top:1px solid #e8f0fb;font-size:14px;line-height:22px;color:#111827;">
                                                    <span style="color:#94a3b8;text-decoration:line-through;">{{ $item['before'] }}</span>
                                                    <span style="display:inline-block;margin:0 6px;color:#64748b;">→</span>
                                                    <strong>{{ $item['after'] }}</strong>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </table>
                                </div>
                            </td>
                        </tr>
                    @endif

                    <tr>
                        <td style="padding:28px 36px 34px;">
                            <div style="padding-top:18px;border-top:1px solid #e5e7eb;">
                                <p style="margin:0 0 6px;font-size:13px;line-height:20px;color:#6b7280;">
                                    Revisa la reserva en el panel si necesitas realizar mas ajustes o seguimiento.
                                </p>
                                <p style="margin:0;font-size:12px;line-height:18px;color:#9ca3af;">
                                    Notificacion automatica de {{ $negocio?->nombre }} via Clockia.
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
