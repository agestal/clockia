@extends('public.layout')

@section('title', 'Clockia | Reservas de enoturismo con calendario y chatbot')
@section('meta_description', 'Landing pública de Clockia para enoturismo: calendario widget, chatbot widget, pagos, Google Calendar, mailing y encuestas post-experiencia.')

@section('content')
    <section class="hero" style="background-image: url('{{ asset('images/marketing/white-wine-hero.jpg') }}');">
        <div class="container hero-content">
            <span class="eyebrow">Clockia para enoturismo</span>
            <h1>Reservas pensadas para visitas, catas y experiencias de bodega.</h1>
            <p>
                Clockia convierte la disponibilidad real en reservas listas para vender: calendario widget, chatbot widget,
                respuestas personalizadas, pasarelas de pago, mailing automático y sincronización con Google Calendar en un mismo flujo.
            </p>

            <div class="hero-actions">
                <a class="button button-primary" href="{{ route('public.widgets') }}">Explorar widgets</a>
                <a class="button button-secondary" href="{{ route('public.features') }}">Ver funcionalidades</a>
            </div>

            <div class="hero-pills">
                <span class="hero-pill">Calendario widget</span>
                <span class="hero-pill">Chatbot widget</span>
                <span class="hero-pill">Pagos integrados</span>
                <span class="hero-pill">Google Calendar</span>
                <span class="hero-pill">Mailing y encuestas</span>
                <span class="hero-pill">Reservas por experiencia</span>
            </div>
        </div>
    </section>

    <section class="showcase-overlap">
        <div class="container">
            @include('public.partials.widget-stage')
        </div>
    </section>

    <section class="section">
        <div class="container">
            <div class="section-intro centered">
                <span class="section-kicker">Lo que vende Clockia</span>
                <h2 class="section-title">Una capa pública que entiende aforo, franjas, bloqueos y contexto comercial.</h2>
                <p class="section-lead">
                    Cada experiencia tiene duración, aforo y horarios propios. El visitante reserva solo donde de verdad caben sus plazas
                    y el negocio conserva el control sobre cierres, pagos y ocupación.
                </p>
            </div>

            <div class="feature-grid">
                <article class="feature-card">
                    <h3>Catálogo de experiencias</h3>
                    <p>Visitas, catas, maridajes, vendimias y propuestas privadas con sus propias reglas de reserva.</p>
                    <ul class="card-bullets">
                        <li>Duración y aforo por experiencia</li>
                        <li>Franjas horarias operativas</li>
                        <li>Descripción comercial lista para vender</li>
                    </ul>
                </article>

                <article class="feature-card">
                    <h3>Widgets personalizables</h3>
                    <p>El calendario y el chatbot pueden vivir por separado o juntos dentro de la misma web.</p>
                    <ul class="card-bullets">
                        <li>Colores, textos y tono de marca</li>
                        <li>Campos y preguntas adaptadas</li>
                        <li>Disponibilidad visible en tiempo real</li>
                    </ul>
                </article>

                <article class="feature-card">
                    <h3>Reserva y seguimiento</h3>
                    <p>El visitante encuentra fecha, entiende el plan y sigue conectado antes y después de venir.</p>
                    <ul class="card-bullets">
                        <li>Pago con señal o total</li>
                        <li>Confirmaciones y recordatorios</li>
                        <li>Encuestas post-experiencia automáticas</li>
                    </ul>
                </article>
            </div>
        </div>
    </section>

    <section class="section section-soft">
        <div class="container split-section split-section--reverse">
            <div class="split-copy">
                <span class="section-kicker">Dos widgets, una sola experiencia</span>
                <h2 class="section-title">Calendario y chatbot pueden compartir la misma reserva sin duplicar lógica.</h2>
                <p class="section-lead">
                    El calendario deja ver huecos claros por experiencia y aforo. El chatbot añade contexto, resuelve dudas, propone opciones
                    y termina la reserva con respuestas propias del negocio.
                </p>
                <ul class="bullet-list">
                    <li>El usuario entra por calendario y remata con chatbot cuando necesita ayuda.</li>
                    <li>El usuario entra por chatbot y acaba reservando la franja que realmente está libre.</li>
                    <li>Ambos canales obedecen la misma política de bloqueos y la misma capacidad.</li>
                </ul>
            </div>

            <div class="image-panel">
                <img src="{{ asset('images/marketing/tasting-room.jpg') }}" alt="Grupo disfrutando de una experiencia de cata en bodega">
            </div>
        </div>
    </section>

    <section class="section">
        <div class="container split-section">
            <div class="image-panel">
                <img src="{{ asset('images/marketing/team-hosting.jpg') }}" alt="Equipo de bodega preparando una atención personalizada para visitantes">
            </div>

            <div class="split-copy">
                <span class="section-kicker">Integraciones que sí importan</span>
                <h2 class="section-title">Disponibilidad, agenda, cobro y automatización alineados con la operación diaria.</h2>
                <p class="section-lead">
                    Clockia conecta la capa pública con el calendario del negocio, el cobro de la reserva y los procesos que necesitas para que
                    el día de la visita llegue ordenado y el seguimiento posterior no se quede en tareas manuales.
                </p>

                <div class="integration-rail" style="margin-top: 1.2rem;">
                    <span class="integration-pill" style="color: var(--clockia-primary); background: var(--clockia-surface-soft); border-color: var(--clockia-border);">Google Calendar</span>
                    <span class="integration-pill" style="color: var(--clockia-primary); background: var(--clockia-surface-soft); border-color: var(--clockia-border);">Pasarelas de pago</span>
                    <span class="integration-pill" style="color: var(--clockia-primary); background: var(--clockia-surface-soft); border-color: var(--clockia-border);">Email transaccional</span>
                    <span class="integration-pill" style="color: var(--clockia-primary); background: var(--clockia-surface-soft); border-color: var(--clockia-border);">Encuestas post-experiencia</span>
                    <span class="integration-pill" style="color: var(--clockia-primary); background: var(--clockia-surface-soft); border-color: var(--clockia-border);">MCP y tools</span>
                    <span class="integration-pill" style="color: var(--clockia-primary); background: var(--clockia-surface-soft); border-color: var(--clockia-border);">Widget embebido</span>
                </div>

                <ul class="bullet-list">
                    <li>Las franjas bloqueadas o la agenda externa dejan de mostrarse como disponibles.</li>
                    <li>El cobro forma parte de la reserva y no de un proceso aparte.</li>
                    <li>Confirmaciones, recordatorios y encuestas salen del mismo ciclo de la reserva.</li>
                    <li>Las respuestas del asistente se adaptan a cada negocio y experiencia.</li>
                </ul>
            </div>
        </div>
    </section>

    <section class="section section-soft">
        <div class="container">
            <div class="section-intro">
                <span class="section-kicker">Visión operativa</span>
                <h2 class="section-title">La capa pública también sirve para decidir mejor el día a día.</h2>
                <p class="section-lead">
                    Además de vender, la disponibilidad por experiencia te deja leer la ocupación y reaccionar antes: reforzar una franja,
                    cerrar otra o impulsar la experiencia que mejor encaja.
                </p>
            </div>

            <div class="metric-grid">
                <article class="metric-card">
                    <strong>68%</strong>
                    <h3>Ocupación diaria</h3>
                    <p>Lectura rápida del volumen de reservas para cada día operativo.</p>
                </article>

                <article class="metric-card">
                    <strong>3</strong>
                    <h3>Experiencias activas</h3>
                    <p>El visitante ve solo las propuestas disponibles en sus horarios reales.</p>
                </article>

                <article class="metric-card">
                    <strong>16</strong>
                    <h3>Plazas por franja</h3>
                    <p>Capacidad calculada con el aforo específico de cada experiencia.</p>
                </article>

                <article class="metric-card">
                    <strong>100%</strong>
                    <h3>Contexto compartido</h3>
                    <p>Calendario, chatbot, pagos y mailing operan con la misma reserva.</p>
                </article>
            </div>
        </div>
    </section>

    @include('public.partials.cta-band', [
        'title' => 'Haz que reservar una visita sea tan claro como vivirla.',
        'copy' => 'Clockia lleva la lógica del negocio al frontal público para que el usuario vea opciones reales, entienda el plan y reserve sin fricción.',
        'primaryLabel' => 'Ver integraciones',
        'primaryUrl' => route('public.integrations'),
    ])
@endsection
