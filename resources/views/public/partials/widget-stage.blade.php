@php
    $days = $days ?? [
        ['label' => 'Vie 17', 'active' => true],
        ['label' => 'Sab 18', 'active' => false],
        ['label' => 'Dom 19', 'active' => false],
        ['label' => 'Lun 20', 'active' => false],
        ['label' => 'Mar 21', 'active' => false],
    ];

    $slots = $slots ?? [
        ['time' => '11:00', 'subtitle' => '12 plazas libres de 16', 'occupancy' => 25],
        ['time' => '13:00', 'subtitle' => '4 plazas libres de 16', 'occupancy' => 75],
        ['time' => '17:00', 'subtitle' => '9 plazas libres de 16', 'occupancy' => 44],
    ];

    $messages = $messages ?? [
        ['author' => 'assistant', 'body' => 'Hola, puedo ayudarte a reservar una visita con degustación o una cata premium.'],
        ['author' => 'user', 'body' => 'Somos 4 personas y queremos ir el sábado por la tarde.'],
        ['author' => 'assistant', 'body' => 'La franja de las 17:00 tiene disponibilidad y admite pago con señal. ¿La confirmo?'],
    ];

    $footerStats = $footerStats ?? [
        ['label' => 'Google Calendar', 'value' => 'Sincronización de agendas y cierres'],
        ['label' => 'Pagos', 'value' => 'Reserva con señal, pago total o cobro posterior'],
        ['label' => 'Mailing y encuestas', 'value' => 'Confirmación, recordatorio y seguimiento post-experiencia'],
    ];
@endphp

<div class="showcase-shell">
    <div class="showcase-bar">
        <div class="showcase-heading">
            <span class="showcase-dots">
                <span></span>
                <span></span>
                <span></span>
            </span>
            <div>
                <div>Clockia para bodegas</div>
                <div class="showcase-label">Calendario widget y chatbot widget trabajando a la vez</div>
            </div>
        </div>

        <span class="tiny-pill">Motor personalizable</span>
    </div>

    <div class="showcase-grid">
        <section class="showcase-panel">
            <div class="panel-head">
                <div>
                    <p class="panel-kicker">Widget calendario</p>
                    <h3>Visita con degustación</h3>
                </div>
                <span class="panel-chip">90 min</span>
            </div>

            <div class="day-strip">
                @foreach ($days as $day)
                    <div class="day-pill {{ $day['active'] ? 'is-active' : '' }}">{{ $day['label'] }}</div>
                @endforeach
            </div>

            <div class="slot-list">
                @foreach ($slots as $slot)
                    <div class="slot-item">
                        <div class="slot-row">
                            <span>{{ $slot['time'] }}</span>
                            <span>{{ $slot['occupancy'] }}% ocupado</span>
                        </div>
                        <div class="slot-subtitle">{{ $slot['subtitle'] }}</div>
                        <div class="meter" style="margin-top: 0.7rem;">
                            <span style="width: {{ $slot['occupancy'] }}%;"></span>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="panel-note">
                Aforo, bloqueos, duración y franjas alineadas con cada experiencia.
            </div>
        </section>

        <section class="showcase-panel showcase-panel--chat">
            <div class="panel-head">
                <div>
                    <p class="panel-kicker">Widget chatbot</p>
                    <h3>Reserva guiada con respuestas propias</h3>
                </div>
                <span class="panel-chip">Asistente</span>
            </div>

            <div class="chat-thread">
                @foreach ($messages as $message)
                    <div class="chat-message {{ $message['author'] === 'assistant' ? 'is-assistant' : 'is-user' }}">
                        <span>{{ $message['body'] }}</span>
                    </div>
                @endforeach
            </div>

            <div class="choice-row">
                <span class="choice-chip">2 adultos</span>
                <span class="choice-chip">4 adultos</span>
                <span class="choice-chip">Con maridaje</span>
                <span class="choice-chip">Con niños</span>
            </div>

            <div class="panel-note">
                El chatbot capta contexto, propone experiencias y cierra la reserva con la franja correcta.
            </div>
        </section>
    </div>

    <div class="showcase-footer">
        @foreach ($footerStats as $stat)
            <div class="footer-stat">
                <span>{{ $stat['label'] }}</span>
                <strong>{{ $stat['value'] }}</strong>
            </div>
        @endforeach
    </div>
</div>
