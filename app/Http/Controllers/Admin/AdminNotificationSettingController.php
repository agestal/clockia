<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\InteractsWithAdminAccess;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateAdminNotificationSettingsRequest;
use App\Models\Negocio;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminNotificationSettingController extends Controller
{
    use InteractsWithAdminAccess;

    public function index(Request $request): View
    {
        $negocios = $this->adminAccess()
            ->accessibleBusinessesQuery($request->user())
            ->orderBy('nombre')
            ->get([
                'id',
                'nombre',
                'notif_email_destino',
                'notif_reserva_nueva',
                'notif_reserva_modificada',
                'notif_anulacion_reserva',
                'notif_encuesta_respondida',
                'notif_aforo_lleno_experiencia',
                'notif_aforo_lleno_dia',
                'updated_at',
            ]);

        $selectedBusinessId = (int) $request->integer('negocio_id');

        $businesses = $selectedBusinessId > 0
            ? $negocios->where('id', $selectedBusinessId)->values()
            : $negocios->values();

        return view('admin.avisos-admin.index', [
            'negocios' => $negocios,
            'businesses' => $businesses,
            'selectedBusinessId' => $selectedBusinessId,
            'notificationLabels' => $this->notificationLabels(),
        ]);
    }

    public function edit(Request $request, Negocio $negocio): View
    {
        abort_unless(
            $this->adminAccess()->canAccessModel($request->user(), $negocio),
            Response::HTTP_FORBIDDEN
        );

        return view('admin.avisos-admin.edit', [
            'negocio' => $negocio,
            'notificationGroups' => $this->notificationGroups(),
        ]);
    }

    public function update(
        UpdateAdminNotificationSettingsRequest $request,
        Negocio $negocio
    ): RedirectResponse {
        abort_unless(
            $this->adminAccess()->canAccessModel($request->user(), $negocio),
            Response::HTTP_FORBIDDEN
        );

        $negocio->update($request->validated());

        return redirect()
            ->route('admin.avisos-admin.edit', $negocio)
            ->with('success', 'Los avisos del administrador se han actualizado correctamente.');
    }

    private function notificationLabels(): array
    {
        return collect($this->notificationGroups())
            ->flatMap(fn (array $group) => collect($group['items'])->mapWithKeys(
                fn (array $item) => [$item['field'] => $item['label']]
            ))
            ->all();
    }

    private function notificationGroups(): array
    {
        return [
            [
                'title' => 'Reservas',
                'description' => 'Avisos operativos cuando una reserva cambia de estado.',
                'items' => [
                    [
                        'field' => 'notif_reserva_nueva',
                        'label' => 'Nueva reserva',
                        'description' => 'Recibe un email cuando entra una reserva nueva.',
                    ],
                    [
                        'field' => 'notif_reserva_modificada',
                        'label' => 'Reserva modificada',
                        'description' => 'Recibe un aviso cuando el cliente o el equipo actualiza una reserva existente.',
                    ],
                    [
                        'field' => 'notif_anulacion_reserva',
                        'label' => 'Reserva cancelada',
                        'description' => 'Recibe un email cuando una reserva queda anulada.',
                    ],
                ],
            ],
            [
                'title' => 'Feedback',
                'description' => 'Seguimiento de encuestas y valoraciones tras la experiencia.',
                'items' => [
                    [
                        'field' => 'notif_encuesta_respondida',
                        'label' => 'Encuesta respondida',
                        'description' => 'Recibe un aviso cuando un cliente completa una encuesta post-experiencia.',
                    ],
                ],
            ],
            [
                'title' => 'Aforo',
                'description' => 'Alertas para detectar saturación en experiencias y jornadas.',
                'items' => [
                    [
                        'field' => 'notif_aforo_lleno_experiencia',
                        'label' => 'Aforo lleno por experiencia',
                        'description' => 'Avisa cuando una sesión o experiencia concreta se queda sin plazas.',
                    ],
                    [
                        'field' => 'notif_aforo_lleno_dia',
                        'label' => 'Aforo lleno del día',
                        'description' => 'Avisa cuando ese día ya no queda disponibilidad para la experiencia.',
                    ],
                ],
            ],
        ];
    }
}
