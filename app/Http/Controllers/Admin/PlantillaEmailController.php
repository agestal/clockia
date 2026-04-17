<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\InteractsWithAdminAccess;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdatePlantillaEmailRequest;
use App\Models\Negocio;
use App\Models\PlantillaEmail;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PlantillaEmailController extends Controller
{
    use InteractsWithAdminAccess;

    public function index(Request $request): View
    {
        $negocios = $this->accessibleBusinesses($request);

        $negocios->each(static function (Negocio $negocio): void {
            PlantillaEmail::ensureDefaultsForBusiness($negocio);
        });

        $selectedBusinessId = (int) $request->integer('negocio_id');

        $plantillas = PlantillaEmail::query()
            ->with('negocio')
            ->tap(fn ($query) => $this->scopeAccessibleBusinesses($query, $request, 'negocio_id'))
            ->when($selectedBusinessId > 0, fn ($query) => $query->where('negocio_id', $selectedBusinessId))
            ->orderBy('negocio_id')
            ->orderBy('tipo')
            ->get();

        return view('admin.plantillas-email.index', [
            'negocios' => $negocios,
            'plantillas' => $plantillas,
            'selectedBusinessId' => $selectedBusinessId,
        ]);
    }

    public function edit(Request $request, PlantillaEmail $plantillaEmail): View
    {
        abort_unless(
            $this->adminAccess()->canAccessModel($request->user(), $plantillaEmail),
            Response::HTTP_FORBIDDEN
        );

        $plantillaEmail->load('negocio');

        return view('admin.plantillas-email.edit', [
            'plantillaEmail' => $plantillaEmail,
            'placeholders' => $this->placeholders(),
        ]);
    }

    public function update(
        UpdatePlantillaEmailRequest $request,
        PlantillaEmail $plantillaEmail
    ): RedirectResponse {
        abort_unless(
            $this->adminAccess()->canAccessModel($request->user(), $plantillaEmail),
            Response::HTTP_FORBIDDEN
        );

        $plantillaEmail->update($request->validated());

        return redirect()
            ->route('admin.plantillas-email.edit', $plantillaEmail)
            ->with('success', 'La plantilla de email se ha actualizado correctamente.');
    }

    private function accessibleBusinesses(Request $request)
    {
        return $this->adminAccess()
            ->accessibleBusinessesQuery($request->user())
            ->orderBy('nombre')
            ->get(['id', 'nombre']);
    }

    private function placeholders(): array
    {
        return [
            '{{negocio}}' => 'Nombre del negocio',
            '{{servicio}}' => 'Nombre del servicio o experiencia',
            '{{fecha}}' => 'Fecha de la reserva',
            '{{hora}}' => 'Hora de inicio',
            '{{personas}}' => 'Numero de personas',
            '{{localizador}}' => 'Codigo de reserva',
            '{{direccion}}' => 'Direccion del negocio',
            '{{telefono}}' => 'Telefono del negocio',
            '{{nombre}}' => 'Nombre del cliente',
            '{{nombre_fragmento}}' => 'Coma mas nombre si existe',
            '{{encuesta_url}}' => 'Enlace unico a la encuesta',
        ];
    }
}
