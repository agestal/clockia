@extends('public.layout')

@section('title', 'Clockia | Servicios de implantación y activación')
@section('meta_description', 'Servicios de Clockia para activar el motor de reservas enoturístico: carga de experiencias, personalización, mailing, encuestas e integraciones.')

@section('content')
    <section class="page-hero" style="background-image: url('{{ asset('images/marketing/team-hosting.jpg') }}');">
        <div class="container page-hero-content">
            <span class="eyebrow">Servicios</span>
            <h1>Activación, personalización y puesta en marcha para salir a vender antes.</h1>
            <p>
                La parte pública no termina en publicar una web. Clockia acompaña la definición de experiencias, la configuración de widgets
                y la adaptación del mensaje para que la reserva empiece fina desde el primer día y continúe bien después de la visita.
            </p>
        </div>
    </section>

    <section class="section">
        <div class="container">
            <div class="service-grid">
                <article class="service-card">
                    <h3>Carga de catálogo</h3>
                    <p>Organización de experiencias, aforos, duraciones, horarios y bloqueos iniciales.</p>
                </article>

                <article class="service-card">
                    <h3>Personalización de widgets</h3>
                    <p>Textos, tono, preguntas guiadas, llamadas a la acción y encaje visual con la marca.</p>
                </article>

                <article class="service-card">
                    <h3>Puesta en producción</h3>
                    <p>Integraciones, validación del flujo de cobro, mailing de reserva y publicación en la web o en los canales necesarios.</p>
                </article>
            </div>
        </div>
    </section>

    <section class="section section-soft">
        <div class="container">
            <div class="section-intro centered">
                <span class="section-kicker">Cómo se trabaja</span>
                <h2 class="section-title">Una implantación enfocada a que el visitante reserve mejor y el equipo respire más.</h2>
                <p class="section-lead">
                    El servicio acompaña el cambio desde el catálogo hasta el mensaje. No se limita a abrir un formulario bonito.
                </p>
            </div>

            <div class="timeline">
                <article class="timeline-step">
                    <span>01</span>
                    <h3>Modelado</h3>
                    <p>Se aterrizan experiencias, aforos, duraciones y bloqueos según la operativa de la bodega.</p>
                </article>

                <article class="timeline-step">
                    <span>02</span>
                    <h3>Diseño del flujo</h3>
                    <p>Se decide cómo entran calendario y chatbot en la web y qué tono usarán.</p>
                </article>

                <article class="timeline-step">
                    <span>03</span>
                    <h3>Conexiones</h3>
                    <p>Se atan agenda, pagos, mailing y automatizaciones para que el flujo cierre bien.</p>
                </article>

                <article class="timeline-step">
                    <span>04</span>
                    <h3>Optimización</h3>
                    <p>Se revisa ocupación, conversión y feedback de encuestas post-experiencia para afinar.</p>
                </article>
            </div>
        </div>
    </section>

    <section class="section" id="demo">
        <div class="container split-section split-section--reverse">
            <div class="split-copy">
                <span class="section-kicker">Activación acompañada</span>
                <h2 class="section-title">La parte pública queda lista para enseñar producto, convertir y conectar con el panel.</h2>
                <p class="section-lead">
                    Así Clockia puede presentarse como motor de reservas especializado en enoturismo sin perder continuidad con el backoffice y con
                    los widgets que ya forman parte del producto.
                </p>
                <ul class="bullet-list">
                    <li>Mensaje comercial enfocado a calendario, chatbot y motor de reservas.</li>
                    <li>Mailing de confirmación, recordatorio y encuesta integrado en el ciclo completo.</li>
                    <li>Capas públicas preparadas para crecer con nuevas páginas o bloques sectoriales.</li>
                    <li>Acceso natural desde la web pública al login o al panel interno.</li>
                </ul>
            </div>

            <div class="image-panel">
                <img src="{{ asset('images/marketing/tasting-room.jpg') }}" alt="Experiencia de enoturismo preparada para su comercialización pública">
            </div>
        </div>
    </section>

    @include('public.partials.cta-band', [
        'title' => 'Clockia ya puede hablar en público como producto de enoturismo.',
        'copy' => 'Landing, páginas de producto y acceso al panel alineados con calendario, chatbot, integraciones y servicios.',
        'primaryLabel' => auth()->check() ? 'Ir al panel' : 'Entrar',
        'primaryUrl' => auth()->check() ? route('dashboard') : route('login'),
        'secondaryLabel' => 'Volver al inicio',
        'secondaryUrl' => route('public.home'),
    ])
@endsection
