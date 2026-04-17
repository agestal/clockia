@php
    $negocio = $reserva->negocio;
    $servicio = $reserva->servicio;
    $fecha = optional($reserva->fecha)->locale('es')->translatedFormat('l j \d\e F \d\e Y');
    $hora = substr((string) $reserva->hora_inicio, 0, 5);
    $precio = $reserva->precio_calculado ? number_format((float) $reserva->precio_calculado, 2, ',', '.') . ' EUR' : null;
    $estado = $reserva->estadoReserva?->nombre ?? 'Pendiente';
    $origen = $reserva->origen_reserva ?? 'manual';
@endphp
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Nueva reserva</title>
</head>
<body style="margin:0;padding:0;background:#f3f5f9;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Arial,sans-serif;color:#1f2937;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background:#f3f5f9;margin:0;padding:0;width:100%;">
        <tr>
            <td align="center" style="padding:32px 16px;">
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="max-width:640px;background:#ffffff;border-radius:18px;overflow:hidden;box-shadow:0 12px 34px rgba(15,23,42,0.08);">
                    <tr>
                        <td style="padding:0;">
                            <div style="height:6px;background:linear-gradient(90deg,#0f8a4b 0%,#34c759 100%);font-size:0;line-height:0;">&nbsp;</div>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:32px 36px 24px;background:linear-gradient(180deg,#f8fffb 0%,#ffffff 100%);border-bottom:1px solid #e5e7eb;">
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0">
                                <tr>
                                    <td style="vertical-align:top;padding-right:16px;">
                                        <p style="margin:0 0 10px;font-size:12px;line-height:12px;font-weight:700;letter-spacing:0.08em;text-transform:uppercase;color:#0f8a4b;">
                                            Clockia Admin
                                        </p>
                                        <h1 style="margin:0 0 10px;font-size:30px;line-height:36px;font-weight:800;color:#111827;">
                                            Nueva reserva recibida
                                        </h1>
                                        <p style="margin:0;font-size:15px;line-height:24px;color:#4b5563;">
                                            Se ha registrado una nueva reserva para <strong style="color:#111827;">{{ $negocio?->nombre }}</strong>.
                                        </p>
                                    </td>
                                    <td align="right" style="vertical-align:top;white-space:nowrap;">
                                        <span style="display:inline-block;padding:10px 14px;border-radius:999px;background:#e7f8ee;color:#0f8a4b;font-size:13px;font-weight:700;">
                                            {{ strtoupper($origen) }}
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
                                                Experiencia
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
                                    <p style="margin:0;font-size:15px;line-height:20px;font-weight:800;color:#111827;">Detalles de la reserva</p>
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
                                        <td style="padding:14px 20px;border-bottom:1px solid #eef2f7;font-size:13px;font-weight:700;color:#6b7280;">Estado</td>
                                        <td style="padding:14px 20px;border-bottom:1px solid #eef2f7;">
                                            <span style="display:inline-block;padding:6px 12px;border-radius:999px;background:#edf7ff;color:#0f5f8c;font-size:12px;font-weight:700;">
                                                {{ $estado }}
                                            </span>
                                        </td>
                                    </tr>
                                    @if($precio)
                                        <tr>
                                            <td style="padding:14px 20px;border-bottom:1px solid #eef2f7;font-size:13px;font-weight:700;color:#6b7280;">Precio</td>
                                            <td style="padding:14px 20px;border-bottom:1px solid #eef2f7;font-size:14px;line-height:22px;color:#111827;font-weight:700;">{{ $precio }}</td>
                                        </tr>
                                    @endif
                                    <tr>
                                        <td style="padding:14px 20px;font-size:13px;font-weight:700;color:#6b7280;">Origen</td>
                                        <td style="padding:14px 20px;font-size:14px;line-height:22px;color:#111827;">{{ $origen }}</td>
                                    </tr>
                                </table>
                            </div>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:20px 36px 0;">
                            <div style="background:linear-gradient(180deg,#f4f9ff 0%,#ffffff 100%);border:1px solid #dbeafe;border-radius:16px;overflow:hidden;">
                                <div style="padding:16px 20px 12px;">
                                    <p style="margin:0;font-size:15px;line-height:20px;font-weight:800;color:#0f3f6f;">Datos del cliente</p>
                                </div>

                                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="border-collapse:collapse;">
                                    <tr>
                                        <td style="padding:12px 20px;border-top:1px solid #e8f0fb;font-size:13px;font-weight:700;color:#6b7280;width:150px;">Nombre</td>
                                        <td style="padding:12px 20px;border-top:1px solid #e8f0fb;font-size:14px;line-height:22px;color:#111827;">{{ $reserva->nombre_responsable ?: 'Sin indicar' }}</td>
                                    </tr>
                                    <tr>
                                        <td style="padding:12px 20px;border-top:1px solid #e8f0fb;font-size:13px;font-weight:700;color:#6b7280;">Telefono</td>
                                        <td style="padding:12px 20px;border-top:1px solid #e8f0fb;font-size:14px;line-height:22px;color:#111827;">{{ $reserva->telefono_responsable ?: 'Sin indicar' }}</td>
                                    </tr>
                                    @if($reserva->email_responsable)
                                        <tr>
                                            <td style="padding:12px 20px;border-top:1px solid #e8f0fb;font-size:13px;font-weight:700;color:#6b7280;">Email</td>
                                            <td style="padding:12px 20px;border-top:1px solid #e8f0fb;font-size:14px;line-height:22px;color:#111827;">{{ $reserva->email_responsable }}</td>
                                        </tr>
                                    @endif
                                </table>
                            </div>
                        </td>
                    </tr>

                    @if($reserva->notas)
                        <tr>
                            <td style="padding:20px 36px 0;">
                                <div style="background:linear-gradient(180deg,#fff9e8 0%,#fffdf7 100%);border:1px solid #f4e2a6;border-radius:16px;padding:18px 20px;">
                                    <p style="margin:0 0 8px;font-size:15px;line-height:20px;font-weight:800;color:#8a5a00;">Notas del cliente</p>
                                    <p style="margin:0;font-size:14px;line-height:24px;color:#5b4a21;">{{ $reserva->notas }}</p>
                                </div>
                            </td>
                        </tr>
                    @endif

                    <tr>
                        <td style="padding:28px 36px 34px;">
                            <div style="padding-top:18px;border-top:1px solid #e5e7eb;">
                                <p style="margin:0 0 6px;font-size:13px;line-height:20px;color:#6b7280;">
                                    Revisa la reserva en el panel si necesitas actualizar estado, cobros o seguimiento.
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
