<?php

namespace Tests\Feature;

use Tests\TestCase;

class PublicSiteTest extends TestCase
{
    public function test_public_marketing_pages_render(): void
    {
        $pages = [
            ['public.home', 'Clockia para enoturismo'],
            ['public.enotourism', 'Una solución hecha para vender visitas, catas y experiencias de bodega.'],
            ['public.features', 'Todo lo que hace falta para vender experiencias con reglas reales.'],
            ['public.widgets', 'Dos widgets personalizables para reservar con autonomía o con ayuda guiada.'],
            ['public.integrations', 'La capa pública funciona mejor cuando agenda, cobro y automatización ya están conectados.'],
            ['public.services', 'Activación, personalización y puesta en marcha para salir a vender antes.'],
        ];

        foreach ($pages as [$route, $text]) {
            $this->get(route($route))
                ->assertOk()
                ->assertSee($text, false);
        }
    }

    public function test_public_site_mentions_mailing_and_surveys(): void
    {
        $this->get(route('public.home'))
            ->assertOk()
            ->assertSee('Mailing y encuestas', false);

        $this->get(route('public.features'))
            ->assertOk()
            ->assertSee('Encuestas post-experiencia', false);

        $this->get(route('public.integrations'))
            ->assertOk()
            ->assertSee('confirmación, recordatorio y encuesta', false);
    }
}
