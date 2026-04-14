@csrf

@if($isEdit)
    @method('PUT')
@endif

@php
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
        </div>
    </div>

    <div class="card-footer bg-white d-flex justify-content-between align-items-center">
        <a href="{{ route('admin.negocios.index') }}" class="btn btn-light border">Volver</a>
        <button type="submit" class="btn btn-primary">{{ $submitLabel }}</button>
    </div>
</div>
