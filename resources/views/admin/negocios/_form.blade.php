@csrf

@if($isEdit)
    @method('PUT')
@endif

@php
    $widgetSettings = $negocio->widgetSettingsResolved();
    $chatBehavior = old() ? [
        'human_role' => old('chat_behavior_human_role'),
        'default_register' => old('chat_behavior_default_register'),
        'question_style' => old('chat_behavior_question_style'),
        'option_style' => old('chat_behavior_option_style'),
        'offer_naming_style' => old('chat_behavior_offer_naming_style'),
        'inventory_exposure_policy' => old('chat_behavior_inventory_exposure_policy'),
        'no_availability_policy' => old('chat_behavior_no_availability_policy'),
        'vocabulary_hints' => old('chat_behavior_vocabulary_hints'),
    ] : ($negocio->chat_behavior_overrides ?? []);
@endphp

<div class="card shadow-sm border-0">
    <div class="card-body">
        <div class="form-row">
            <div class="form-group col-lg-6">
                <label for="nombre" class="form-label">Nombre</label>
                <input
                    type="text"
                    id="nombre"
                    name="nombre"
                    value="{{ old('nombre', $negocio->nombre) }}"
                    class="form-control @error('nombre') is-invalid @enderror"
                    maxlength="255"
                    minlength="2"
                    required
                    autofocus
                    placeholder="Ejemplo: Centro Médico Sol"
                >
                @error('nombre')
                    <span class="invalid-feedback d-block" role="alert">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group col-lg-6">
                <label for="tipo_negocio_id" class="form-label">Tipo de negocio</label>
                <select
                    id="tipo_negocio_id"
                    name="tipo_negocio_id"
                    class="form-control @error('tipo_negocio_id') is-invalid @enderror js-select2-tipo-negocio"
                    required
                    data-placeholder="Selecciona un tipo de negocio"
                >
                    @if($selectedTipoNegocio)
                        <option value="{{ $selectedTipoNegocio->id }}" selected>{{ $selectedTipoNegocio->nombre }}</option>
                    @endif
                </select>
                @error('tipo_negocio_id')
                    <span class="invalid-feedback d-block" role="alert">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group col-lg-6">
                <label for="email" class="form-label">Email</label>
                <input
                    type="email"
                    id="email"
                    name="email"
                    value="{{ old('email', $negocio->email) }}"
                    class="form-control @error('email') is-invalid @enderror"
                    maxlength="255"
                    placeholder="negocio@ejemplo.com"
                >
                @error('email')
                    <span class="invalid-feedback d-block" role="alert">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group col-lg-6">
                <label for="telefono" class="form-label">Teléfono</label>
                <input
                    type="text"
                    id="telefono"
                    name="telefono"
                    value="{{ old('telefono', $negocio->telefono) }}"
                    class="form-control @error('telefono') is-invalid @enderror"
                    maxlength="255"
                    placeholder="981 123 123"
                >
                @error('telefono')
                    <span class="invalid-feedback d-block" role="alert">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group col-lg-6">
                <label for="zona_horaria" class="form-label">Zona horaria</label>
                <select
                    id="zona_horaria"
                    name="zona_horaria"
                    class="form-control @error('zona_horaria') is-invalid @enderror js-select2-timezone"
                    required
                    data-placeholder="Selecciona una zona horaria"
                >
                    @foreach($timezones as $timezone)
                        <option value="{{ $timezone }}" @selected(old('zona_horaria', $negocio->zona_horaria ?: 'Europe/Madrid') === $timezone)>
                            {{ $timezone }}
                        </option>
                    @endforeach
                </select>
                @error('zona_horaria')
                    <span class="invalid-feedback d-block" role="alert">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group col-lg-6 d-flex align-items-center">
                <div class="custom-control custom-switch mt-4">
                    <input type="hidden" name="activo" value="0">
                    <input
                        type="checkbox"
                        class="custom-control-input @error('activo') is-invalid @enderror"
                        id="activo"
                        name="activo"
                        value="1"
                        @checked(old('activo', $negocio->activo ?? true))
                    >
                    <label class="custom-control-label" for="activo">Activo</label>
                </div>
                @error('activo')
                    <span class="invalid-feedback d-block ml-3" role="alert">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group col-lg-6 d-flex align-items-center">
                <div>
                    <div class="custom-control custom-switch mt-4">
                        <input type="hidden" name="google_calendar_enabled" value="0">
                        <input
                            type="checkbox"
                            class="custom-control-input @error('google_calendar_enabled') is-invalid @enderror"
                            id="google_calendar_enabled"
                            name="google_calendar_enabled"
                            value="1"
                            @checked(old('google_calendar_enabled', $googleCalendarIntegration?->activo ?? false))
                        >
                        <label class="custom-control-label" for="google_calendar_enabled">Activar Google Calendar</label>
                    </div>
                    @if($googleCalendarIntegration)
                        <div class="small text-muted mt-2">
                            Estado de conexión: {{ $googleCalendarIntegration->estado }}
                        </div>
                    @endif
                </div>
                @error('google_calendar_enabled')
                    <span class="invalid-feedback d-block ml-3" role="alert">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group col-lg-6">
                <label for="direccion" class="form-label">Dirección</label>
                <input
                    type="text"
                    id="direccion"
                    name="direccion"
                    value="{{ old('direccion', $negocio->direccion) }}"
                    class="form-control @error('direccion') is-invalid @enderror"
                    maxlength="500"
                    placeholder="Ejemplo: Calle Mayor, 12, 28001 Madrid"
                >
                @error('direccion')
                    <span class="invalid-feedback d-block" role="alert">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group col-lg-6">
                <label for="url_publica" class="form-label">URL pública</label>
                <input
                    type="url"
                    id="url_publica"
                    name="url_publica"
                    value="{{ old('url_publica', $negocio->url_publica) }}"
                    class="form-control @error('url_publica') is-invalid @enderror"
                    maxlength="500"
                    placeholder="https://www.ejemplo.com"
                >
                @error('url_publica')
                    <span class="invalid-feedback d-block" role="alert">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group col-lg-6">
                <label for="horas_minimas_cancelacion" class="form-label">Horas mínimas de cancelación</label>
                <input
                    type="number"
                    id="horas_minimas_cancelacion"
                    name="horas_minimas_cancelacion"
                    value="{{ old('horas_minimas_cancelacion', $negocio->horas_minimas_cancelacion) }}"
                    class="form-control @error('horas_minimas_cancelacion') is-invalid @enderror"
                    min="0"
                    step="1"
                    placeholder="Ejemplo: 24"
                >
                @error('horas_minimas_cancelacion')
                    <span class="invalid-feedback d-block" role="alert">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group col-lg-6 d-flex align-items-center">
                <div class="custom-control custom-switch mt-4">
                    <input type="hidden" name="permite_modificacion" value="0">
                    <input
                        type="checkbox"
                        class="custom-control-input @error('permite_modificacion') is-invalid @enderror"
                        id="permite_modificacion"
                        name="permite_modificacion"
                        value="1"
                        @checked(old('permite_modificacion', $negocio->permite_modificacion ?? true))
                    >
                    <label class="custom-control-label" for="permite_modificacion">Permite modificación</label>
                </div>
                @error('permite_modificacion')
                    <span class="invalid-feedback d-block ml-3" role="alert">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group col-lg-6">
                <label for="max_recursos_combinables" class="form-label">Máximo de recursos combinables</label>
                <input
                    type="number"
                    id="max_recursos_combinables"
                    name="max_recursos_combinables"
                    value="{{ old('max_recursos_combinables', $negocio->max_recursos_combinables) }}"
                    class="form-control @error('max_recursos_combinables') is-invalid @enderror"
                    min="1"
                    max="5"
                    step="1"
                    placeholder="Vacío = 1 (sin combinaciones)"
                >
                <small class="form-text text-muted">1 = no combinar automáticamente; 2 o más permite unir recursos compatibles hasta ese límite.</small>
                @error('max_recursos_combinables')
                    <span class="invalid-feedback d-block" role="alert">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group col-12">
                <label for="descripcion_publica" class="form-label">Descripción pública</label>
                <textarea
                    id="descripcion_publica"
                    name="descripcion_publica"
                    rows="4"
                    class="form-control @error('descripcion_publica') is-invalid @enderror"
                    placeholder="Descripción visible para los clientes del negocio"
                >{{ old('descripcion_publica', $negocio->descripcion_publica) }}</textarea>
                @error('descripcion_publica')
                    <span class="invalid-feedback d-block" role="alert">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group col-12">
                <label for="politica_cancelacion" class="form-label">Política de cancelación</label>
                <textarea
                    id="politica_cancelacion"
                    name="politica_cancelacion"
                    rows="4"
                    class="form-control @error('politica_cancelacion') is-invalid @enderror"
                    placeholder="Texto libre con la política de cancelación del negocio"
                >{{ old('politica_cancelacion', $negocio->politica_cancelacion) }}</textarea>
                @error('politica_cancelacion')
                    <span class="invalid-feedback d-block" role="alert">{{ $message }}</span>
                @enderror
            </div>
            <div class="form-group col-12">
                <hr>
                <h3 class="h6 text-uppercase text-muted mb-3">Configuración del chatbot</h3>
            </div>

            <div class="form-group col-12">
                <label for="chat_personality" class="form-label">Personalidad del chatbot</label>
                <textarea
                    id="chat_personality"
                    name="chat_personality"
                    rows="3"
                    class="form-control @error('chat_personality') is-invalid @enderror"
                    placeholder="Ej: Cercano, breve y amable. Habla como un restaurante elegante pero accesible."
                >{{ old('chat_personality', $negocio->chat_personality) }}</textarea>
                <small class="form-text text-muted">Instrucciones de tono, estilo o trato al cliente para este negocio.</small>
                @error('chat_personality')
                    <span class="invalid-feedback d-block" role="alert">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group col-12">
                <label for="chat_system_rules" class="form-label">Prompt base editable / instrucciones maestras</label>
                <textarea
                    id="chat_system_rules"
                    name="chat_system_rules"
                    rows="4"
                    class="form-control @error('chat_system_rules') is-invalid @enderror"
                    placeholder="Ej: No ofrezcas reservas para más de 8 personas sin derivar a humano. Para el menú degustación, recuerda siempre que requiere señal. Pregunta siempre por alergias."
                >{{ old('chat_system_rules', $negocio->chat_system_rules) }}</textarea>
                <small class="form-text text-muted">Bloque editable del prompt inicial del negocio. Aquí defines objetivos, límites, lenguaje y criterios que el LLM debe respetar siempre.</small>
                @error('chat_system_rules')
                    <span class="invalid-feedback d-block" role="alert">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group col-12">
                <label for="chat_required_fields" class="form-label">Campos requeridos por tool (JSON)</label>
                <textarea
                    id="chat_required_fields"
                    name="chat_required_fields"
                    rows="6"
                    class="form-control @error('chat_required_fields') is-invalid @enderror"
                    placeholder='{"search_availability": ["servicio_id", "fecha", "numero_personas"], "create_quote": ["servicio_id", "numero_personas"]}'
                    style="font-family: monospace; font-size: 0.85rem;"
                >{{ old('chat_required_fields', $negocio->chat_required_fields ? json_encode($negocio->chat_required_fields, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : '') }}</textarea>
                <small class="form-text text-muted">Opcional. Define qué campos debe recopilar el chatbot antes de ejecutar cada tool. Si se deja vacío, se usan los defaults del sistema.</small>
                @error('chat_required_fields')
                    <span class="invalid-feedback d-block" role="alert">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group col-12">
                <hr>
                <h3 class="h6 text-uppercase text-muted mb-3">Política conversacional</h3>
                <p class="text-muted small mb-0">Estos ajustes afinan cómo se expresa el asistente de este negocio sin depender solo de texto libre. Si se dejan vacíos, se aplica el comportamiento automático según el sector.</p>
            </div>

            <div class="form-group col-lg-6">
                <label for="chat_behavior_human_role" class="form-label">Rol humano a imitar</label>
                <input
                    type="text"
                    id="chat_behavior_human_role"
                    name="chat_behavior_human_role"
                    value="{{ old('chat_behavior_human_role', data_get($chatBehavior, 'human_role')) }}"
                    class="form-control @error('chat_behavior_overrides.human_role') is-invalid @enderror"
                    maxlength="255"
                    placeholder="Ej: maître, recepcionista, community manager"
                >
                <small class="form-text text-muted">Opcional. Sirve para orientar el estilo del asistente hacia un rol real del negocio.</small>
                @error('chat_behavior_overrides.human_role')
                    <span class="invalid-feedback d-block" role="alert">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group col-lg-6">
                <label for="chat_behavior_default_register" class="form-label">Registro / tono base</label>
                <select
                    id="chat_behavior_default_register"
                    name="chat_behavior_default_register"
                    class="form-control @error('chat_behavior_overrides.default_register') is-invalid @enderror"
                >
                    @foreach($conversationBehaviorOptions['default_register'] as $value => $label)
                        <option value="{{ $value }}" @selected(old('chat_behavior_default_register', data_get($chatBehavior, 'default_register')) === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                @error('chat_behavior_overrides.default_register')
                    <span class="invalid-feedback d-block" role="alert">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group col-lg-6">
                <label for="chat_behavior_question_style" class="form-label">Estilo de preguntas</label>
                <select
                    id="chat_behavior_question_style"
                    name="chat_behavior_question_style"
                    class="form-control @error('chat_behavior_overrides.question_style') is-invalid @enderror"
                >
                    @foreach($conversationBehaviorOptions['question_style'] as $value => $label)
                        <option value="{{ $value }}" @selected(old('chat_behavior_question_style', data_get($chatBehavior, 'question_style')) === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                @error('chat_behavior_overrides.question_style')
                    <span class="invalid-feedback d-block" role="alert">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group col-lg-6">
                <label for="chat_behavior_option_style" class="form-label">Cuándo ofrecer opciones</label>
                <select
                    id="chat_behavior_option_style"
                    name="chat_behavior_option_style"
                    class="form-control @error('chat_behavior_overrides.option_style') is-invalid @enderror"
                >
                    @foreach($conversationBehaviorOptions['option_style'] as $value => $label)
                        <option value="{{ $value }}" @selected(old('chat_behavior_option_style', data_get($chatBehavior, 'option_style')) === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                @error('chat_behavior_overrides.option_style')
                    <span class="invalid-feedback d-block" role="alert">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group col-lg-6">
                <label for="chat_behavior_offer_naming_style" class="form-label">Cómo nombrar la oferta</label>
                <select
                    id="chat_behavior_offer_naming_style"
                    name="chat_behavior_offer_naming_style"
                    class="form-control @error('chat_behavior_overrides.offer_naming_style') is-invalid @enderror"
                >
                    @foreach($conversationBehaviorOptions['offer_naming_style'] as $value => $label)
                        <option value="{{ $value }}" @selected(old('chat_behavior_offer_naming_style', data_get($chatBehavior, 'offer_naming_style')) === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                <small class="form-text text-muted">Ejemplo: hablar de “servicios”, de “lo que ofrecemos” o usar un lenguaje comercial del sector.</small>
                @error('chat_behavior_overrides.offer_naming_style')
                    <span class="invalid-feedback d-block" role="alert">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group col-lg-6">
                <label for="chat_behavior_inventory_exposure_policy" class="form-label">Exposición del inventario interno</label>
                <select
                    id="chat_behavior_inventory_exposure_policy"
                    name="chat_behavior_inventory_exposure_policy"
                    class="form-control @error('chat_behavior_overrides.inventory_exposure_policy') is-invalid @enderror"
                >
                    @foreach($conversationBehaviorOptions['inventory_exposure_policy'] as $value => $label)
                        <option value="{{ $value }}" @selected(old('chat_behavior_inventory_exposure_policy', data_get($chatBehavior, 'inventory_exposure_policy')) === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                <small class="form-text text-muted">Controla si el asistente debe ocultar recursos internos como mesas concretas, cabinas o IDs técnicos.</small>
                @error('chat_behavior_overrides.inventory_exposure_policy')
                    <span class="invalid-feedback d-block" role="alert">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group col-lg-6">
                <label for="chat_behavior_no_availability_policy" class="form-label">Qué hacer si no hay disponibilidad</label>
                <select
                    id="chat_behavior_no_availability_policy"
                    name="chat_behavior_no_availability_policy"
                    class="form-control @error('chat_behavior_overrides.no_availability_policy') is-invalid @enderror"
                >
                    @foreach($conversationBehaviorOptions['no_availability_policy'] as $value => $label)
                        <option value="{{ $value }}" @selected(old('chat_behavior_no_availability_policy', data_get($chatBehavior, 'no_availability_policy')) === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                @error('chat_behavior_overrides.no_availability_policy')
                    <span class="invalid-feedback d-block" role="alert">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group col-12">
                <label for="chat_behavior_vocabulary_hints" class="form-label">Vocabulario / expresiones preferidas</label>
                <textarea
                    id="chat_behavior_vocabulary_hints"
                    name="chat_behavior_vocabulary_hints"
                    rows="3"
                    class="form-control @error('chat_behavior_overrides.vocabulary_hints.*') is-invalid @enderror"
                    placeholder="Una pista por línea o separadas por comas: mesa, turno, zona, sala..."
                >{{ old('chat_behavior_vocabulary_hints', is_array(data_get($chatBehavior, 'vocabulary_hints')) ? implode("\n", data_get($chatBehavior, 'vocabulary_hints')) : data_get($chatBehavior, 'vocabulary_hints')) }}</textarea>
                <small class="form-text text-muted">Opcional. Ayuda al LLM a sonar más cercano al lenguaje real del equipo humano.</small>
                @error('chat_behavior_overrides.vocabulary_hints.*')
                    <span class="invalid-feedback d-block" role="alert">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group col-12">
                <hr>
                <h3 class="h6 text-uppercase text-muted mb-3">Configuracion de mailings</h3>
                <p class="text-muted small mb-0">Activa o desactiva el envio automatico de emails para este negocio. Los recordatorios y encuestas se procesan periodicamente.</p>
            </div>

            <div class="form-group col-lg-4 d-flex align-items-center">
                <div class="custom-control custom-switch mt-2">
                    <input type="hidden" name="mail_confirmacion_activo" value="0">
                    <input
                        type="checkbox"
                        class="custom-control-input @error('mail_confirmacion_activo') is-invalid @enderror"
                        id="mail_confirmacion_activo"
                        name="mail_confirmacion_activo"
                        value="1"
                        @checked(old('mail_confirmacion_activo', $negocio->mail_confirmacion_activo ?? false))
                    >
                    <label class="custom-control-label" for="mail_confirmacion_activo">Email de confirmacion</label>
                </div>
                @error('mail_confirmacion_activo')
                    <span class="invalid-feedback d-block ml-3" role="alert">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group col-lg-4 d-flex align-items-center">
                <div class="custom-control custom-switch mt-2">
                    <input type="hidden" name="mail_recordatorio_activo" value="0">
                    <input
                        type="checkbox"
                        class="custom-control-input @error('mail_recordatorio_activo') is-invalid @enderror"
                        id="mail_recordatorio_activo"
                        name="mail_recordatorio_activo"
                        value="1"
                        @checked(old('mail_recordatorio_activo', $negocio->mail_recordatorio_activo ?? false))
                    >
                    <label class="custom-control-label" for="mail_recordatorio_activo">Email de recordatorio</label>
                </div>
                @error('mail_recordatorio_activo')
                    <span class="invalid-feedback d-block ml-3" role="alert">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group col-lg-4 d-flex align-items-center">
                <div class="custom-control custom-switch mt-2">
                    <input type="hidden" name="mail_encuesta_activo" value="0">
                    <input
                        type="checkbox"
                        class="custom-control-input @error('mail_encuesta_activo') is-invalid @enderror"
                        id="mail_encuesta_activo"
                        name="mail_encuesta_activo"
                        value="1"
                        @checked(old('mail_encuesta_activo', $negocio->mail_encuesta_activo ?? false))
                    >
                    <label class="custom-control-label" for="mail_encuesta_activo">Email de encuesta</label>
                </div>
                @error('mail_encuesta_activo')
                    <span class="invalid-feedback d-block ml-3" role="alert">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group col-lg-6">
                <label for="mail_recordatorio_horas_antes" class="form-label">Horas antes para recordatorio</label>
                <input
                    type="number"
                    id="mail_recordatorio_horas_antes"
                    name="mail_recordatorio_horas_antes"
                    value="{{ old('mail_recordatorio_horas_antes', $negocio->mail_recordatorio_horas_antes ?? 24) }}"
                    class="form-control @error('mail_recordatorio_horas_antes') is-invalid @enderror"
                    min="1"
                    max="168"
                    step="1"
                    placeholder="24"
                >
                <small class="form-text text-muted">Cuantas horas antes de la reserva se envia el recordatorio (1-168).</small>
                @error('mail_recordatorio_horas_antes')
                    <span class="invalid-feedback d-block" role="alert">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group col-lg-6">
                <label for="mail_encuesta_horas_despues" class="form-label">Horas despues para encuesta</label>
                <input
                    type="number"
                    id="mail_encuesta_horas_despues"
                    name="mail_encuesta_horas_despues"
                    value="{{ old('mail_encuesta_horas_despues', $negocio->mail_encuesta_horas_despues ?? 24) }}"
                    class="form-control @error('mail_encuesta_horas_despues') is-invalid @enderror"
                    min="1"
                    max="168"
                    step="1"
                    placeholder="24"
                >
                <small class="form-text text-muted">Cuantas horas despues de finalizar la reserva se envia la encuesta (1-168).</small>
                @error('mail_encuesta_horas_despues')
                    <span class="invalid-feedback d-block" role="alert">{{ $message }}</span>
                @enderror
            </div>

            @if($isEdit)
            <div class="form-group col-12" id="widget-calendar-settings">
                <hr>
                <h3 class="h6 text-uppercase text-muted mb-3">Widget embebible</h3>
                <p class="text-muted small mb-0">Permite a tus clientes reservar desde una web externa. Comparte la misma lógica del chatbot: mismos emails, mismas encuestas, mismas validaciones.</p>
            </div>

            <div class="form-group col-lg-6 d-flex align-items-center">
                <div class="custom-control custom-switch mt-2">
                    <input type="hidden" name="widget_enabled" value="0">
                    <input
                        type="checkbox"
                        class="custom-control-input @error('widget_enabled') is-invalid @enderror"
                        id="widget_enabled"
                        name="widget_enabled"
                        value="1"
                        @checked(old('widget_enabled', $negocio->widget_enabled ?? false))
                    >
                    <label class="custom-control-label" for="widget_enabled">Activar widget público</label>
                </div>
                @error('widget_enabled')
                    <span class="invalid-feedback d-block ml-3" role="alert">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group col-lg-6">
                <label class="form-label">Clave pública del widget</label>
                <div class="input-group">
                    <input
                        type="text"
                        id="widget_public_key_display"
                        class="form-control"
                        value="{{ $negocio->widget_public_key }}"
                        readonly
                    >
                    <div class="input-group-append">
                        <button
                            type="button"
                            class="btn btn-outline-secondary"
                            id="btn-regenerate-widget-key"
                            data-url="{{ route('admin.negocios.widget.regenerate-key', $negocio) }}"
                            title="Regenerar clave pública (invalidará la actual)"
                        >
                            <i class="fas fa-sync-alt"></i> Regenerar
                        </button>
                    </div>
                </div>
                <small class="form-text text-muted">Identifica al negocio en el widget embebible. Regenerarla invalida inmediatamente la clave anterior.</small>
            </div>

            <div class="form-group col-lg-3">
                <label for="widget_primary_color" class="form-label">Color principal</label>
                <input type="text" id="widget_primary_color" name="widget_primary_color"
                    value="{{ old('widget_primary_color', $widgetSettings['primary_color']) }}"
                    class="form-control" placeholder="#7B3F00">
            </div>

            <div class="form-group col-lg-3">
                <label for="widget_secondary_color" class="form-label">Color secundario</label>
                <input type="text" id="widget_secondary_color" name="widget_secondary_color"
                    value="{{ old('widget_secondary_color', $widgetSettings['secondary_color']) }}"
                    class="form-control" placeholder="#EAD7C5">
            </div>

            <div class="form-group col-lg-3">
                <label for="widget_text_color" class="form-label">Color de texto</label>
                <input type="text" id="widget_text_color" name="widget_text_color"
                    value="{{ old('widget_text_color', $widgetSettings['text_color']) }}"
                    class="form-control" placeholder="#2B2B2B">
            </div>

            <div class="form-group col-lg-3">
                <label for="widget_background_color" class="form-label">Color de fondo</label>
                <input type="text" id="widget_background_color" name="widget_background_color"
                    value="{{ old('widget_background_color', $widgetSettings['background_color']) }}"
                    class="form-control" placeholder="#FFFFFF">
            </div>

            <div class="form-group col-lg-4">
                <label for="widget_font_family" class="form-label">Tipografía</label>
                <input type="text" id="widget_font_family" name="widget_font_family"
                    value="{{ old('widget_font_family', $widgetSettings['font_family']) }}"
                    class="form-control" placeholder="Inter, sans-serif">
            </div>

            <div class="form-group col-lg-2">
                <label for="widget_font_size_base" class="form-label">Tamaño base</label>
                <input type="text" id="widget_font_size_base" name="widget_font_size_base"
                    value="{{ old('widget_font_size_base', $widgetSettings['font_size_base']) }}"
                    class="form-control" placeholder="14px">
            </div>

            <div class="form-group col-lg-2">
                <label for="widget_border_radius" class="form-label">Border radius</label>
                <input type="text" id="widget_border_radius" name="widget_border_radius"
                    value="{{ old('widget_border_radius', $widgetSettings['border_radius']) }}"
                    class="form-control" placeholder="10px">
            </div>

            <div class="form-group col-lg-2">
                <label for="widget_locale" class="form-label">Idioma</label>
                <input type="text" id="widget_locale" name="widget_locale"
                    value="{{ old('widget_locale', $widgetSettings['locale']) }}"
                    class="form-control" placeholder="es">
            </div>

            <div class="form-group col-12">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <label class="form-label mb-0">Snippet de integración para la web del cliente</label>
                    <button type="button" class="btn btn-sm btn-outline-primary" id="btn-copy-widget-snippet">
                        <i class="far fa-copy"></i> Copiar
                    </button>
                </div>
                <textarea
                    id="widget_snippet_code"
                    class="form-control bg-light small"
                    rows="6"
                    readonly
                    style="font-family: Menlo, Consolas, monospace; font-size: 0.82rem;"
>&lt;script src="{{ url('/widget/clockia-widget.js') }}" charset="utf-8" defer&gt;&lt;/script&gt;
&lt;clockia-widget
    business-id="{{ $negocio->id }}"
    widget-key="{{ $negocio->widget_public_key }}"
    api-base="{{ url('/api/widget') }}"
&gt;&lt;/clockia-widget&gt;</textarea>
                <small class="form-text text-muted">
                    Pega este bloque en cualquier web externa para mostrar el widget de reservas de <strong>{{ $negocio->nombre }}</strong>. Los colores configurados arriba se aplican automáticamente tras guardar. Si regeneras la clave, vuelve a pegar el nuevo snippet.
                </small>
            </div>

            <div class="form-group col-12">
                <details class="border rounded p-3 bg-light">
                    <summary class="text-muted small" style="cursor:pointer;">Alternativa: inicialización por JavaScript (click para ver)</summary>
                    <pre class="mb-0 mt-2 small" style="white-space:pre-wrap;">&lt;div id="clockia-widget"&gt;&lt;/div&gt;
&lt;script src="{{ url('/widget/clockia-widget.js') }}" charset="utf-8"&gt;&lt;/script&gt;
&lt;script&gt;
  Clockia.init({
    businessId: {{ $negocio->id }},
    widgetKey: '{{ $negocio->widget_public_key }}',
    apiBase: '{{ url('/api/widget') }}',
    container: '#clockia-widget',
  });
&lt;/script&gt;</pre>
                </details>
            </div>

            @push('js')
            <script>
            (function() {
                const regenBtn = document.getElementById('btn-regenerate-widget-key');
                const keyInput = document.getElementById('widget_public_key_display');
                const snippetBox = document.getElementById('widget_snippet_code');
                const copyBtn = document.getElementById('btn-copy-widget-snippet');

                if (copyBtn && snippetBox) {
                    copyBtn.addEventListener('click', async function () {
                        try {
                            await navigator.clipboard.writeText(snippetBox.value);
                            const original = copyBtn.innerHTML;
                            copyBtn.innerHTML = '<i class="fas fa-check"></i> Copiado';
                            copyBtn.classList.remove('btn-outline-primary');
                            copyBtn.classList.add('btn-success');
                            setTimeout(function () {
                                copyBtn.innerHTML = original;
                                copyBtn.classList.remove('btn-success');
                                copyBtn.classList.add('btn-outline-primary');
                            }, 1800);
                        } catch (err) {
                            snippetBox.select();
                            document.execCommand('copy');
                        }
                    });
                }

                if (regenBtn && keyInput && snippetBox) {
                    regenBtn.addEventListener('click', async function () {
                        if (!confirm('¿Regenerar la clave pública del widget? La clave actual dejará de funcionar inmediatamente y cualquier web que la esté usando dejará de ver el widget hasta que actualices el snippet.')) {
                            return;
                        }
                        regenBtn.disabled = true;
                        const originalText = regenBtn.innerHTML;
                        regenBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Regenerando…';

                        try {
                            const response = await fetch(regenBtn.dataset.url, {
                                method: 'POST',
                                headers: {
                                    'Accept': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                    'X-Requested-With': 'XMLHttpRequest',
                                },
                                credentials: 'same-origin',
                            });

                            if (!response.ok) throw new Error('Error ' + response.status);
                            const payload = await response.json();
                            const newKey = payload.data.widget_public_key;

                            keyInput.value = newKey;
                            snippetBox.value = snippetBox.value.replace(/widget-key="[^"]*"/, 'widget-key="' + newKey + '"');

                            regenBtn.innerHTML = '<i class="fas fa-check"></i> Nueva clave';
                            setTimeout(function () {
                                regenBtn.innerHTML = originalText;
                                regenBtn.disabled = false;
                            }, 2000);
                        } catch (err) {
                            alert('No se pudo regenerar la clave: ' + err.message);
                            regenBtn.innerHTML = originalText;
                            regenBtn.disabled = false;
                        }
                    });
                }
            })();
            </script>
            @endpush
            @endif
            {{-- end widget section --}}
        </div>
    </div>

    <div class="card-footer bg-white d-flex justify-content-between align-items-center">
        <a href="{{ route('admin.negocios.index') }}" class="btn btn-light border">Volver</a>
        <button type="submit" class="btn btn-primary">{{ $submitLabel }}</button>
    </div>
</div>
