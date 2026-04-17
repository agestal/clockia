@php
    $title = $title ?? 'Pon el canal de reserva a la altura de la experiencia.';
    $copy = $copy ?? 'Combina calendario, chatbot y pagos en un flujo que responde como tu negocio y reserva según la disponibilidad real.';
    $primaryLabel = $primaryLabel ?? 'Ver widgets';
    $primaryUrl = $primaryUrl ?? route('public.widgets');
    $secondaryLabel = $secondaryLabel ?? (auth()->check() ? 'Ir al panel' : 'Entrar');
    $secondaryUrl = $secondaryUrl ?? (auth()->check() ? route('dashboard') : route('login'));
@endphp

<section class="section">
    <div class="container">
        <div class="cta-band">
            <div>
                <h2>{{ $title }}</h2>
                <p>{{ $copy }}</p>
            </div>

            <div class="cta-row">
                <a class="button button-primary" href="{{ $primaryUrl }}">{{ $primaryLabel }}</a>
                <a class="button button-secondary" href="{{ $secondaryUrl }}">{{ $secondaryLabel }}</a>
            </div>
        </div>
    </div>
</section>
