<?php

namespace Tests\Feature;

use App\Mail\ReservaConfirmada;
use App\Models\Cliente;
use App\Models\Encuesta;
use App\Models\EncuestaItem;
use App\Models\EncuestaPlantilla;
use App\Models\EstadoReserva;
use App\Models\Negocio;
use App\Models\PlantillaEmail;
use App\Models\Reserva;
use App\Models\Servicio;
use App\Models\TipoNegocio;
use App\Models\TipoPrecio;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SurveyModulesTest extends TestCase
{
    use RefreshDatabase;

    public function test_business_admin_can_access_email_templates_and_surveys_modules(): void
    {
        $user = User::factory()->businessAdmin()->create();
        $business = $this->createBusiness('Bodega Prueba');

        $user->negocios()->attach($business);

        $this->actingAs($user)
            ->get(route('admin.plantillas-email.index'))
            ->assertOk()
            ->assertSeeText('Plantillas de email')
            ->assertSeeText('Bodega Prueba');

        $template = PlantillaEmail::query()
            ->where('negocio_id', $business->id)
            ->where('tipo', PlantillaEmail::TIPO_CONFIRMACION)
            ->firstOrFail();

        $this->actingAs($user)
            ->get(route('admin.plantillas-email.edit', $template))
            ->assertOk()
            ->assertSeeText('Confirmacion');

        $this->actingAs($user)
            ->get(route('admin.encuesta-plantillas.index'))
            ->assertOk()
            ->assertSeeText('Encuestas')
            ->assertSeeText('Encuesta post-experiencia');

        $this->actingAs($user)
            ->get(route('admin.encuesta-plantillas.create'))
            ->assertOk()
            ->assertSeeText('Nueva encuesta');
    }

    public function test_public_survey_token_is_single_use_and_uses_snapshot_content(): void
    {
        $business = $this->createBusiness('Bodega Token');
        $reservation = $this->createReservation($business, 'TOKN1234');

        $template = EncuestaPlantilla::create([
            'negocio_id' => $business->id,
            'nombre' => 'Encuesta especial',
            'activo' => true,
            'predeterminada' => true,
            'escala_min' => 1,
            'escala_max' => 5,
            'permite_comentario_final' => true,
            'comentario_placeholder' => 'Cuéntanos algo más',
            'titulo_publico' => 'Queremos leerte',
            'intro_publica' => 'Tu visita ya ha pasado y queremos saber cómo te fue.',
            'agradecimiento_titulo' => 'Gracias por responder',
            'agradecimiento_texto' => 'Tu respuesta ya ha quedado guardada.',
        ]);

        $questionOne = EncuestaItem::create([
            'negocio_id' => $business->id,
            'encuesta_plantilla_id' => $template->id,
            'clave' => 'tpl_test_1',
            'etiqueta' => 'Atencion recibida',
            'descripcion' => 'Valora al equipo.',
            'orden' => 1,
            'activo' => true,
        ]);

        $questionTwo = EncuestaItem::create([
            'negocio_id' => $business->id,
            'encuesta_plantilla_id' => $template->id,
            'clave' => 'tpl_test_2',
            'etiqueta' => 'Calidad de la experiencia',
            'descripcion' => 'Valora la visita.',
            'orden' => 2,
            'activo' => true,
        ]);

        $survey = Encuesta::create([
            'reserva_id' => $reservation->id,
            'negocio_id' => $business->id,
            'encuesta_plantilla_id' => $template->id,
            'token' => Encuesta::generarToken(),
            'activo' => true,
            'enviada_en' => now(),
            'contenido_snapshot' => $template->buildSnapshot(),
        ]);

        $questionOne->update(['etiqueta' => 'Etiqueta modificada despues del envio']);

        $this->get(route('encuesta.show', $survey->token))
            ->assertOk()
            ->assertSeeText('Queremos leerte')
            ->assertSeeText('Atencion recibida')
            ->assertDontSeeText('Etiqueta modificada despues del envio');

        $this->post(route('encuesta.submit', $survey->token), [
            "item_{$questionOne->id}" => 4,
            "item_{$questionTwo->id}" => 5,
            'comentario_general' => 'Muy buena visita.',
        ])
            ->assertOk()
            ->assertSeeText('Gracias por responder');

        $survey->refresh();

        $this->assertFalse($survey->activo);
        $this->assertNotNull($survey->respondida_en);
        $this->assertSame('Muy buena visita.', $survey->comentario_general);

        $this->assertDatabaseHas('encuesta_respuestas', [
            'encuesta_id' => $survey->id,
            'encuesta_item_id' => $questionOne->id,
            'puntuacion' => 4,
        ]);

        $this->get(route('encuesta.show', $survey->token))->assertNotFound();
    }

    public function test_confirmation_email_uses_custom_subject_template(): void
    {
        $business = $this->createBusiness('Bodega Mail');
        $reservation = $this->createReservation($business, 'MAIL1234');

        PlantillaEmail::ensureDefaultsForBusiness($business);

        PlantillaEmail::query()
            ->where('negocio_id', $business->id)
            ->where('tipo', PlantillaEmail::TIPO_CONFIRMACION)
            ->firstOrFail()
            ->update([
                'asunto' => 'Reserva {{localizador}} confirmada en {{negocio}}',
            ]);

        $mail = new ReservaConfirmada($reservation->fresh(['negocio', 'servicio']));

        $this->assertSame(
            'Reserva MAIL1234 confirmada en Bodega Mail',
            $mail->envelope()->subject
        );
    }

    private function createBusiness(string $name): Negocio
    {
        $type = TipoNegocio::firstOrCreate(['nombre' => 'Bodega']);

        return Negocio::create([
            'nombre' => $name,
            'tipo_negocio_id' => $type->id,
            'zona_horaria' => 'Europe/Madrid',
            'activo' => true,
        ]);
    }

    private function createReservation(Negocio $business, string $locator): Reserva
    {
        $priceType = TipoPrecio::firstOrCreate(['nombre' => 'Precio fijo']);
        $status = EstadoReserva::firstOrCreate(['nombre' => 'Confirmada']);
        $client = Cliente::create([
            'nombre' => 'Cliente '.$locator,
            'email' => strtolower($locator).'@example.test',
        ]);

        $service = Servicio::create([
            'negocio_id' => $business->id,
            'nombre' => 'Visita guiada',
            'duracion_minutos' => 90,
            'precio_base' => '25.00',
            'tipo_precio_id' => $priceType->id,
            'requiere_pago' => false,
            'activo' => true,
        ]);

        return Reserva::withoutEvents(function () use ($business, $client, $locator, $service, $status) {
            return Reserva::create([
                'negocio_id' => $business->id,
                'servicio_id' => $service->id,
                'cliente_id' => $client->id,
                'fecha' => now()->addDay()->toDateString(),
                'hora_inicio' => '10:00:00',
                'hora_fin' => '11:30:00',
                'numero_personas' => 2,
                'precio_calculado' => '25.00',
                'precio_total' => '25.00',
                'estado_reserva_id' => $status->id,
                'localizador' => $locator,
                'nombre_responsable' => 'Cliente '.$locator,
                'email_responsable' => strtolower($locator).'@example.test',
                'documentacion_entregada' => false,
            ]);
        });
    }
}
