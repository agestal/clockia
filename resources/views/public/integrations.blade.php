@extends('public.layout')

@section('title', 'Clockia | Integraciones para reservas y enoturismo')
@section('meta_description', 'Integraciones de Clockia: Google Calendar, pasarelas de pago, mailing, encuestas post-experiencia, widgets web y MCP.')

@section('content')
    <section class="page-hero" style="background-image: url('{{ asset('images/marketing/cellar-host.jpg') }}');">
        <div class="container page-hero-content">
            <span class="eyebrow">Integraciones</span>
            <h1>La capa pública funciona mejor cuando agenda, cobro y automatización ya están conectados.</h1>
            <p>
                Clockia se apoya en las integraciones que sostienen la operación diaria: agenda externa, pago, widgets embebidos y herramientas
                conversacionales para asistentes y agentes, además del envío automático de confirmaciones, recordatorios y encuestas.
            </p>
        </div>
    </section>

    <section class="section">
        <div class="container split-section">
            <div class="image-panel">
                <img src="{{ asset('images/marketing/wine-pour.jpg') }}" alt="Servicio de vino preparado para una experiencia reservada">
            </div>

            <div class="split-copy">
                <span class="section-kicker">Conexiones clave</span>
                <h2 class="section-title">Integraciones que aterrizan en reserva, no solo en promesa.</h2>
                <p class="section-lead">
                    Cada integración tiene un papel concreto: evitar solapes, cobrar cuando toca, automatizar comunicaciones o poner la disponibilidad al alcance de un canal nuevo.
                </p>

                <div class="integration-rail" style="margin-top: 1.2rem;">
                    <span class="integration-pill" style="color: var(--clockia-primary); background: var(--clockia-surface-soft); border-color: var(--clockia-border);">Google Calendar</span>
                    <span class="integration-pill" style="color: var(--clockia-primary); background: var(--clockia-surface-soft); border-color: var(--clockia-border);">Pasarelas de pago</span>
                    <span class="integration-pill" style="color: var(--clockia-primary); background: var(--clockia-surface-soft); border-color: var(--clockia-border);">Embeds web</span>
                    <span class="integration-pill" style="color: var(--clockia-primary); background: var(--clockia-surface-soft); border-color: var(--clockia-border);">Mailing automático</span>
                    <span class="integration-pill" style="color: var(--clockia-primary); background: var(--clockia-surface-soft); border-color: var(--clockia-border);">Encuestas post-experiencia</span>
                    <span class="integration-pill" style="color: var(--clockia-primary); background: var(--clockia-surface-soft); border-color: var(--clockia-border);">MCP</span>
                    <span class="integration-pill" style="color: var(--clockia-primary); background: var(--clockia-surface-soft); border-color: var(--clockia-border);">Notificaciones</span>
                </div>

                <ul class="bullet-list">
                    <li>Google Calendar ayuda a que un cierre operativo no se enseñe como hueco libre.</li>
                    <li>Las pasarelas convierten la reserva en una acción terminada y no en un pendiente.</li>
                    <li>El mailing de confirmación, recordatorio y encuesta sale de la misma reserva.</li>
                    <li>El MCP permite que asistentes consulten disponibilidad y reserven con tools conectadas.</li>
                </ul>
            </div>
        </div>
    </section>

    <section class="section section-soft">
        <div class="container">
            <div class="service-grid">
                <article class="service-card">
                    <h3>Google Calendar</h3>
                    <p>Sincroniza agendas, evita contradicciones y respeta cierres o compromisos externos.</p>
                </article>

                <article class="service-card">
                    <h3>Pagos</h3>
                    <p>Integra el cobro dentro del flujo para confirmar reservas con señal o pago completo.</p>
                </article>

                <article class="service-card">
                    <h3>Mailing y encuestas</h3>
                    <p>Confirma, recuerda y recoge feedback con envíos ligados a la experiencia realizada.</p>
                </article>
            </div>
        </div>
    </section>

    @include('public.partials.cta-band', [
        'title' => 'Conecta los canales de venta con la operativa de la bodega.',
        'copy' => 'Cuando calendario, pagos y conversación hablan el mismo idioma, la reserva se vuelve mucho más fiable.',
        'primaryLabel' => 'Ver servicios',
        'primaryUrl' => route('public.services'),
    ])
@endsection
