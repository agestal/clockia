@extends('public.layout')

@section('title', 'Clockia | Funcionalidades del motor de reservas')
@section('meta_description', 'Funcionalidades de Clockia: experiencias, aforo, franjas, bloqueos, pagos, mailing y encuestas post-experiencia para enoturismo.')

@section('content')
    <section class="page-hero" style="background-image: url('{{ asset('images/marketing/team-hosting.jpg') }}');">
        <div class="container page-hero-content">
            <span class="eyebrow">Funcionalidades</span>
            <h1>Todo lo que hace falta para vender experiencias con reglas reales.</h1>
            <p>
                Clockia lleva al motor de reservas la operativa de la bodega: catálogo de experiencias, aforo, franjas, bloqueos, pagos,
                comunicación, mailing y lectura de ocupación.
            </p>
        </div>
    </section>

    <section class="section">
        <div class="container">
            <div class="feature-grid">
                <article class="feature-card">
                    <h3>Experiencias y catálogo</h3>
                    <p>Organiza visitas, catas, maridajes y propuestas privadas con estructura comercial clara.</p>
                    <ul class="card-bullets">
                        <li>Descripción lista para web y chatbot</li>
                        <li>Duración definida por actividad</li>
                        <li>Aforo ajustado a cada experiencia</li>
                    </ul>
                </article>

                <article class="feature-card">
                    <h3>Franjas y disponibilidad</h3>
                    <p>Las plazas se reservan solo si la franja elegida puede asumir el tamaño del grupo.</p>
                    <ul class="card-bullets">
                        <li>Cálculo por capacidad restante</li>
                        <li>Horarios de inicio y fin por experiencia</li>
                        <li>Lectura pública de huecos libres</li>
                    </ul>
                </article>

                <article class="feature-card">
                    <h3>Bloqueos operativos</h3>
                    <p>Cierra una experiencia puntual o varias franjas sin tocar el resto del calendario.</p>
                    <ul class="card-bullets">
                        <li>Bloqueos por día y por tramo</li>
                        <li>Cierres puntuales por evento interno</li>
                        <li>Control total sobre lo que se enseña</li>
                    </ul>
                </article>

                <article class="feature-card">
                    <h3>Respuestas personalizadas</h3>
                    <p>El asistente puede hablar del negocio, orientar al visitante y proponer la mejor opción disponible.</p>
                    <ul class="card-bullets">
                        <li>Mensajes adaptados por negocio</li>
                        <li>Preguntas guiadas por tipo de plan</li>
                        <li>Reserva iniciada o cerrada desde chat</li>
                    </ul>
                </article>

                <article class="feature-card">
                    <h3>Pagos y confirmación</h3>
                    <p>La reserva puede salir ya cobrada o asegurada con señal según la política comercial del negocio.</p>
                    <ul class="card-bullets">
                        <li>Pasarelas de pago conectadas</li>
                        <li>Confirmación al finalizar la compra</li>
                        <li>Recordatorios antes de la visita</li>
                    </ul>
                </article>

                <article class="feature-card">
                    <h3>Mailing operativo</h3>
                    <p>La comunicación de la reserva acompaña al visitante desde el cierre hasta el día de la experiencia.</p>
                    <ul class="card-bullets">
                        <li>Confirmaciones inmediatas tras reservar</li>
                        <li>Recordatorios antes de la visita</li>
                        <li>Mensajes ligados a la reserva correcta</li>
                    </ul>
                </article>

                <article class="feature-card">
                    <h3>Encuestas post-experiencia</h3>
                    <p>El módulo de encuestas recoge feedback cuando la visita todavía está reciente.</p>
                    <ul class="card-bullets">
                        <li>Envío automático al terminar la experiencia</li>
                        <li>Formulario asociado a negocio y experiencia</li>
                        <li>Lectura de satisfacción para mejorar la oferta</li>
                    </ul>
                </article>

                <article class="feature-card">
                    <h3>Lectura de ocupación</h3>
                    <p>Visualiza el porcentaje de ocupación por día y detecta qué experiencia está tirando mejor.</p>
                    <ul class="card-bullets">
                        <li>Vista diaria de carga</li>
                        <li>Detalle por experiencia al entrar</li>
                        <li>Más criterio para abrir o cerrar franjas</li>
                    </ul>
                </article>
            </div>
        </div>
    </section>

    <section class="section section-soft">
        <div class="container">
            <div class="section-intro centered">
                <span class="section-kicker">Cómo fluye</span>
                <h2 class="section-title">Una cadena continua desde la intención hasta la confirmación.</h2>
                <p class="section-lead">
                    Lo importante no es solo mostrar disponibilidad, sino que el usuario llegue a reservar con la sensación de que todo estaba claro.
                </p>
            </div>

            <div class="timeline">
                <article class="timeline-step">
                    <span>01</span>
                    <h3>Descubre</h3>
                    <p>Ve la experiencia adecuada en la web y entiende qué incluye.</p>
                </article>

                <article class="timeline-step">
                    <span>02</span>
                    <h3>Consulta</h3>
                    <p>Abre calendario o chatbot para revisar fechas, grupo y opciones.</p>
                </article>

                <article class="timeline-step">
                    <span>03</span>
                    <h3>Reserva</h3>
                    <p>Elige la franja compatible y completa el pago o la señal.</p>
                </article>

                <article class="timeline-step">
                    <span>04</span>
                    <h3>Recuerda y responde</h3>
                    <p>Llega con su confirmación, recibe recordatorio y después puede valorar la experiencia en encuesta.</p>
                </article>
            </div>
        </div>
    </section>

    @include('public.partials.cta-band', [
        'title' => 'Saca partido a la disponibilidad en vez de pelearte con ella.',
        'copy' => 'Clockia deja que las reglas del negocio se noten justo donde convierten: en el frontal público.',
        'primaryLabel' => 'Ver widgets',
        'primaryUrl' => route('public.widgets'),
    ])
@endsection
