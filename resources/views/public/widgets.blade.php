@extends('public.layout')

@section('title', 'Clockia | Widgets de calendario y chatbot')
@section('meta_description', 'Widgets personalizables de Clockia para enoturismo: calendario embebido, chatbot embebido y ambos trabajando juntos.')

@section('content')
    <section class="page-hero" style="background-image: url('{{ asset('images/marketing/tasting-room.jpg') }}');">
        <div class="container page-hero-content">
            <span class="eyebrow">Widgets</span>
            <h1>Dos widgets personalizables para reservar con autonomía o con ayuda guiada.</h1>
            <p>
                El calendario widget deja ver plazas y franjas. El chatbot widget acompaña la decisión, resuelve dudas y empuja la conversión.
                Juntos forman un punto de reserva mucho más expresivo.
            </p>
        </div>
    </section>

    <section class="section">
        <div class="container">
            @include('public.partials.widget-stage', [
                'days' => [
                    ['label' => 'Vie 17', 'active' => false],
                    ['label' => 'Sab 18', 'active' => true],
                    ['label' => 'Dom 19', 'active' => false],
                    ['label' => 'Lun 20', 'active' => false],
                    ['label' => 'Mar 21', 'active' => false],
                ],
                'slots' => [
                    ['time' => '10:30', 'subtitle' => '6 plazas libres de 12', 'occupancy' => 50],
                    ['time' => '12:30', 'subtitle' => '2 plazas libres de 12', 'occupancy' => 83],
                    ['time' => '18:00', 'subtitle' => '8 plazas libres de 12', 'occupancy' => 33],
                ],
                'messages' => [
                    ['author' => 'assistant', 'body' => 'Puedo proponerte una cata premium o una visita familiar según el plan que buscáis.'],
                    ['author' => 'user', 'body' => 'Queremos algo corto y con opción de compra al final.'],
                    ['author' => 'assistant', 'body' => 'La experiencia Express a las 12:30 encaja y queda espacio para 2 personas.'],
                ],
            ])
        </div>
    </section>

    <section class="section section-soft">
        <div class="container">
            <div class="feature-grid">
                <article class="feature-card">
                    <h3>Calendario widget</h3>
                    <p>Ideal para quien quiere ver fechas y decidir con rapidez.</p>
                    <ul class="card-bullets">
                        <li>Huecos por experiencia y aforo</li>
                        <li>Porcentaje de ocupación legible</li>
                        <li>Reserva desde la propia web</li>
                    </ul>
                </article>

                <article class="feature-card">
                    <h3>Chatbot widget</h3>
                    <p>Perfecto cuando hay que orientar, vender mejor o resolver condiciones especiales.</p>
                    <ul class="card-bullets">
                        <li>Respuestas personalizadas</li>
                        <li>Propuesta de experiencia adecuada</li>
                        <li>Recogida de contexto antes de reservar</li>
                    </ul>
                </article>

                <article class="feature-card">
                    <h3>Uso combinado</h3>
                    <p>Los dos comparten disponibilidad y se apoyan mutuamente según el momento del usuario.</p>
                    <ul class="card-bullets">
                        <li>El chat puede abrir huecos concretos</li>
                        <li>El calendario puede derivar a conversación</li>
                        <li>Un solo motor por debajo</li>
                    </ul>
                </article>
            </div>
        </div>
    </section>

    <section class="section">
        <div class="container split-section split-section--reverse">
            <div class="split-copy">
                <span class="section-kicker">Personalización real</span>
                <h2 class="section-title">No son widgets genéricos, sino piezas que hablan como la bodega.</h2>
                <p class="section-lead">
                    Puedes ajustar colores, mensajes, llamadas a la acción y tono de reserva para que el frontal público no parezca una capa ajena.
                </p>
                <ul class="bullet-list">
                    <li>Textos de ayuda y mensajes finales adaptados.</li>
                    <li>Widgets embebidos con apariencia alineada a la web.</li>
                    <li>Opciones de reserva distintas según el negocio y el tipo de experiencia.</li>
                </ul>
            </div>

            <div class="image-panel">
                <img src="{{ asset('images/marketing/hero-vineyard.jpg') }}" alt="Viñedo asociado a la marca pública de una bodega">
            </div>
        </div>
    </section>

    @include('public.partials.cta-band', [
        'title' => 'Haz visible la disponibilidad sin renunciar al acompañamiento comercial.',
        'copy' => 'El calendario acelera la decisión y el chatbot añade la capa consultiva cuando el visitante la necesita.',
        'primaryLabel' => 'Ver integraciones',
        'primaryUrl' => route('public.integrations'),
    ])
@endsection
