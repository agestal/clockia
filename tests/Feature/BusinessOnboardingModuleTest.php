<?php

namespace Tests\Feature;

use App\Models\BusinessOnboardingSession;
use App\Models\Negocio;
use App\Models\PlantillaEmail;
use App\Models\TipoNegocio;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class BusinessOnboardingModuleTest extends TestCase
{
    use RefreshDatabase;

    public function test_platform_admin_can_create_explore_and_provision_a_business_from_onboarding(): void
    {
        $user = User::factory()->platformAdmin()->create();
        $businessType = TipoNegocio::create([
            'nombre' => 'Bodega',
            'descripcion' => 'Bodega para enoturismo',
        ]);

        Http::fake([
            'https://bodega-demo.test/*' => Http::response($this->pageHtml(), 200, [
                'Content-Type' => 'text/html; charset=UTF-8',
            ]),
        ]);

        $response = $this->actingAs($user)->post(route('admin.configurador-negocios.store'), [
            'source_url' => 'bodega-demo.test',
            'requested_tipo_negocio_id' => $businessType->id,
            'requested_business_name' => 'Bodega Demo',
            'requested_admin_name' => 'Admin Demo',
            'requested_admin_email' => 'admin@bodega-demo.test',
            'requested_admin_password' => 'password123',
            'requested_admin_password_confirmation' => 'password123',
        ]);

        $session = BusinessOnboardingSession::query()->firstOrFail();

        $response->assertRedirect(route('admin.configurador-negocios.show', $session));

        $session->refresh();

        $this->assertSame(BusinessOnboardingSession::STATUS_READY_FOR_REVIEW, $session->status);
        $this->assertSame('Bodega Demo', data_get($session->draft_payload, 'business.nombre'));
        $this->assertSame('contacto@bodega-demo.test', data_get($session->draft_payload, 'business.email'));

        $this->actingAs($user)
            ->post(route('admin.configurador-negocios.provision', $session))
            ->assertRedirect();

        $session->refresh();
        $business = Negocio::query()->firstOrFail();
        $admin = User::query()->where('email', 'admin@bodega-demo.test')->firstOrFail();

        $this->assertSame(BusinessOnboardingSession::STATUS_PROVISIONED, $session->status);
        $this->assertSame($business->id, $session->provisioned_negocio_id);
        $this->assertTrue($admin->negocios->contains($business));
        $this->assertDatabaseCount('plantillas_email', PlantillaEmail::tipos() === [] ? 0 : count(PlantillaEmail::tipos()));
    }

    public function test_business_admin_cannot_access_onboarding_module(): void
    {
        $user = User::factory()->businessAdmin()->create();

        $this->actingAs($user)
            ->get(route('admin.configurador-negocios.index'))
            ->assertForbidden();
    }

    private function pageHtml(): string
    {
        return <<<'HTML'
<!DOCTYPE html>
<html lang="es">
    <head>
        <title>Bodega Demo | Visitas y catas</title>
        <meta name="description" content="Enoturismo, visitas guiadas y catas frente al mar.">
        <meta property="og:site_name" content="Bodega Demo">
        <script type="application/ld+json">
        {
            "@context": "https://schema.org",
            "@type": "Winery",
            "name": "Bodega Demo",
            "email": "contacto@bodega-demo.test",
            "telephone": "+34 986 000 000",
            "address": {
                "@type": "PostalAddress",
                "streetAddress": "Rua do Viño 12",
                "postalCode": "36630",
                "addressLocality": "Cambados",
                "addressRegion": "Pontevedra",
                "addressCountry": "ES"
            },
            "openingHoursSpecification": [
                {
                    "@type": "OpeningHoursSpecification",
                    "dayOfWeek": ["Monday", "Tuesday", "Wednesday", "Thursday", "Friday"],
                    "opens": "10:00",
                    "closes": "18:00"
                }
            ]
        }
        </script>
    </head>
    <body>
        <header>
            <a href="/visitas">Visitas</a>
            <a href="/contacto">Contacto</a>
            <a href="/horarios">Horarios</a>
        </header>
        <main>
            <h1>Bodega Demo</h1>
            <p>Reservas y experiencias para pequenos grupos.</p>
        </main>
    </body>
</html>
HTML;
    }
}
