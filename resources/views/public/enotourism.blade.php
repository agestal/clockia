@extends('public.layout')

@section('title', 'Clockia | Solución de enoturismo para bodegas')
@section('meta_description', 'Clockia adapta el motor de reservas al enoturismo: experiencias, aforo, franjas, mailing y encuestas post-experiencia por bodega.')

@section('content')
    <section class="page-hero" style="background-image: url('{{ asset('images/marketing/wine-pour.jpg') }}');">
        <div class="container page-hero-content">
            <span class="eyebrow">Solución vertical</span>
            <h1>Una solución hecha para vender visitas, catas y experiencias de bodega.</h1>
            <p>
                El enoturismo necesita algo más que un calendario genérico. Clockia organiza experiencias con aforo, duración y franjas
                horarias para que cada reserva entre donde debe entrar.
            </p>
        </div>
    </section>

    <section class="section">
        <div class="container">
            <div class="section-intro">
                <span class="section-kicker">Pensado para bodegas</span>
                <h2 class="section-title">La reserva cambia según el tipo de visita, el grupo y el momento del día.</h2>
                <p class="section-lead">
                    Una visita premium no se vende igual que una cata corta, un maridaje o una actividad privada. Por eso la lógica pública se
                    apoya en experiencias reales y no en huecos genéricos.
                </p>
            </div>

            <div class="feature-grid">
                <article class="feature-card">
                    <h3>Experiencias con identidad propia</h3>
                    <p>Cada propuesta tiene descripción, duración, aforo y ventana horaria para aparecer donde toca.</p>
                </article>

                <article class="feature-card">
                    <h3>Franjas vendibles de verdad</h3>
                    <p>La reserva solo se ofrece si la capacidad restante cubre al grupo que quiere venir.</p>
                </article>

                <article class="feature-card">
                    <h3>Bloqueos sin romper el catálogo</h3>
                    <p>Puedes cerrar una experiencia en una o varias franjas sin desmontar todo el resto de la operativa.</p>
                </article>
            </div>
        </div>
    </section>

    <section class="section section-soft">
        <div class="container split-section">
            <div class="image-panel">
                <img src="{{ asset('images/marketing/cellar-host.jpg') }}" alt="Atención al visitante en un entorno de bodega">
            </div>

            <div class="split-copy">
                <span class="section-kicker">Lo que resuelve</span>
                <h2 class="section-title">Menos coordinación manual y más control sobre lo que se puede reservar.</h2>
                <p class="section-lead">
                    La disponibilidad deja de depender de revisar mensajes, llamadas y calendarios cruzados. El frontal público muestra justo lo que
                    el equipo está dispuesto a vender ese día.
                </p>
                <ul class="bullet-list">
                    <li>Evita vender plazas donde el aforo ya no alcanza.</li>
                    <li>Permite cerrar experiencias concretas sin tocar otras franjas del día.</li>
                    <li>Mide ocupación por experiencia para detectar qué conviene reforzar o limitar.</li>
                    <li>Activa encuestas post-experiencia para medir satisfacción mientras la visita sigue fresca.</li>
                </ul>
            </div>
        </div>
    </section>

    <section class="section">
        <div class="container">
            <div class="section-intro centered">
                <span class="section-kicker">Operativa diaria</span>
                <h2 class="section-title">Desde la visita abierta al público hasta el grupo privado.</h2>
                <p class="section-lead">
                    Clockia encaja con la forma en la que una bodega organiza su semana y da flexibilidad para convivir con actividades recurrentes,
                    eventos especiales y cierres puntuales.
                </p>
            </div>

            <div class="timeline">
                <article class="timeline-step">
                    <span>01</span>
                    <h3>Se define la experiencia</h3>
                    <p>Descripción, duración, aforo y horarios de inicio y fin.</p>
                </article>

                <article class="timeline-step">
                    <span>02</span>
                    <h3>Se publican las franjas</h3>
                    <p>El sistema calcula qué días y horas se pueden enseñar al visitante.</p>
                </article>

                <article class="timeline-step">
                    <span>03</span>
                    <h3>Se aplican bloqueos</h3>
                    <p>Una experiencia concreta puede cerrarse por mantenimiento, eventos o capacidad interna.</p>
                </article>

                <article class="timeline-step">
                    <span>04</span>
                    <h3>Se reserva con contexto</h3>
                    <p>Calendario y chatbot usan la misma disponibilidad para confirmar la mejor opción.</p>
                </article>
            </div>
        </div>
    </section>

    @include('public.partials.cta-band', [
        'title' => 'Lleva la lógica de la bodega al momento de la reserva.',
        'copy' => 'El visitante entiende mejor la experiencia y tu equipo gestiona con más tranquilidad qué se puede vender en cada franja.',
        'primaryLabel' => 'Ver funcionalidades',
        'primaryUrl' => route('public.features'),
    ])
@endsection
