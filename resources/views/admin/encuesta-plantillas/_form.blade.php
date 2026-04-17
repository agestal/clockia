@csrf

@if($isEdit)
    @method('PUT')
@endif

<div class="row">
    <div class="col-lg-8">
        <div class="card shadow-sm border-0 mb-3">
            <div class="card-body">
                <div class="form-row">
                    <div class="form-group col-lg-6">
                        <label for="negocio_id" class="form-label">Negocio</label>
                        <select id="negocio_id" name="negocio_id" class="form-control @error('negocio_id') is-invalid @enderror" required>
                            <option value="">Selecciona un negocio</option>
                            @foreach($negocios as $negocio)
                                <option value="{{ $negocio->id }}" @selected((int) old('negocio_id', $encuestaPlantilla->negocio_id) === $negocio->id)>{{ $negocio->nombre }}</option>
                            @endforeach
                        </select>
                        @error('negocio_id')
                            <span class="invalid-feedback d-block">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group col-lg-6">
                        <label for="nombre" class="form-label">Nombre interno</label>
                        <input type="text" id="nombre" name="nombre" value="{{ old('nombre', $encuestaPlantilla->nombre) }}" class="form-control @error('nombre') is-invalid @enderror" maxlength="255" required>
                        @error('nombre')
                            <span class="invalid-feedback d-block">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group col-12">
                        <label for="descripcion" class="form-label">Descripcion</label>
                        <textarea id="descripcion" name="descripcion" rows="3" class="form-control @error('descripcion') is-invalid @enderror">{{ old('descripcion', $encuestaPlantilla->descripcion) }}</textarea>
                        @error('descripcion')
                            <span class="invalid-feedback d-block">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group col-lg-3">
                        <label for="escala_min" class="form-label">Escala minima</label>
                        <input type="number" id="escala_min" name="escala_min" value="{{ old('escala_min', $encuestaPlantilla->escala_min ?? 0) }}" class="form-control @error('escala_min') is-invalid @enderror" min="0" max="9" required>
                        @error('escala_min')
                            <span class="invalid-feedback d-block">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group col-lg-3">
                        <label for="escala_max" class="form-label">Escala maxima</label>
                        <input type="number" id="escala_max" name="escala_max" value="{{ old('escala_max', $encuestaPlantilla->escala_max ?? 10) }}" class="form-control @error('escala_max') is-invalid @enderror" min="1" max="10" required>
                        @error('escala_max')
                            <span class="invalid-feedback d-block">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group col-lg-3 d-flex align-items-center">
                        <div class="custom-control custom-switch mt-4">
                            <input type="hidden" name="activo" value="0">
                            <input type="checkbox" class="custom-control-input" id="activo" name="activo" value="1" @checked(old('activo', $encuestaPlantilla->activo ?? true))>
                            <label class="custom-control-label" for="activo">Activa</label>
                        </div>
                    </div>

                    <div class="form-group col-lg-3 d-flex align-items-center">
                        <div class="custom-control custom-switch mt-4">
                            <input type="hidden" name="predeterminada" value="0">
                            <input type="checkbox" class="custom-control-input" id="predeterminada" name="predeterminada" value="1" @checked(old('predeterminada', $encuestaPlantilla->predeterminada ?? false))>
                            <label class="custom-control-label" for="predeterminada">Predeterminada</label>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm border-0 mb-3">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <div>
                    <h3 class="card-title font-weight-semibold mb-0">Preguntas</h3>
                </div>
                <button type="button" class="btn btn-sm btn-outline-primary" data-add-question>Añadir pregunta</button>
            </div>
            <div class="card-body" data-questions-container>
                @error('preguntas')
                    <div class="alert alert-danger">{{ $message }}</div>
                @enderror

                @foreach($preguntas as $index => $pregunta)
                    <div class="border rounded p-3 mb-3" data-question-row>
                        <input type="hidden" name="preguntas[{{ $index }}][id]" value="{{ data_get($pregunta, 'id') }}">

                        <div class="form-row">
                            <div class="form-group col-lg-6">
                                <label class="form-label">Enunciado</label>
                                <input type="text" name="preguntas[{{ $index }}][etiqueta]" value="{{ data_get($pregunta, 'etiqueta') }}" class="form-control @error("preguntas.$index.etiqueta") is-invalid @enderror" maxlength="255" required>
                                @error("preguntas.$index.etiqueta")
                                    <span class="invalid-feedback d-block">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="form-group col-lg-4">
                                <label class="form-label d-block">Activa</label>
                                <div class="custom-control custom-switch mt-2">
                                    <input type="hidden" name="preguntas[{{ $index }}][activo]" value="0">
                                    <input type="checkbox" class="custom-control-input" id="pregunta_activa_{{ $index }}" name="preguntas[{{ $index }}][activo]" value="1" @checked((bool) data_get($pregunta, 'activo', true))>
                                    <label class="custom-control-label" for="pregunta_activa_{{ $index }}">Incluir en los envios</label>
                                </div>
                            </div>

                            <div class="form-group col-lg-2 d-flex align-items-end justify-content-end">
                                <button type="button" class="btn btn-outline-danger btn-sm" data-remove-question>Quitar</button>
                            </div>

                            <div class="form-group col-12 mb-0">
                                <label class="form-label">Ayuda para el usuario</label>
                                <textarea name="preguntas[{{ $index }}][descripcion]" rows="2" class="form-control @error("preguntas.$index.descripcion") is-invalid @enderror">{{ data_get($pregunta, 'descripcion') }}</textarea>
                                @error("preguntas.$index.descripcion")
                                    <span class="invalid-feedback d-block">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="card shadow-sm border-0 mb-3">
            <div class="card-body">
                <div class="form-row">
                    <div class="form-group col-12">
                        <label for="titulo_publico" class="form-label">Titulo publico</label>
                        <input type="text" id="titulo_publico" name="titulo_publico" value="{{ old('titulo_publico', $encuestaPlantilla->titulo_publico) }}" class="form-control @error('titulo_publico') is-invalid @enderror" maxlength="255">
                        @error('titulo_publico')
                            <span class="invalid-feedback d-block">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group col-12">
                        <label for="intro_publica" class="form-label">Introduccion publica</label>
                        <textarea id="intro_publica" name="intro_publica" rows="3" class="form-control @error('intro_publica') is-invalid @enderror">{{ old('intro_publica', $encuestaPlantilla->intro_publica) }}</textarea>
                        @error('intro_publica')
                            <span class="invalid-feedback d-block">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group col-lg-6">
                        <label for="agradecimiento_titulo" class="form-label">Titulo de agradecimiento</label>
                        <input type="text" id="agradecimiento_titulo" name="agradecimiento_titulo" value="{{ old('agradecimiento_titulo', $encuestaPlantilla->agradecimiento_titulo) }}" class="form-control @error('agradecimiento_titulo') is-invalid @enderror" maxlength="255">
                        @error('agradecimiento_titulo')
                            <span class="invalid-feedback d-block">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group col-lg-6 d-flex align-items-center">
                        <div class="custom-control custom-switch mt-4">
                            <input type="hidden" name="permite_comentario_final" value="0">
                            <input type="checkbox" class="custom-control-input" id="permite_comentario_final" name="permite_comentario_final" value="1" @checked(old('permite_comentario_final', $encuestaPlantilla->permite_comentario_final ?? true))>
                            <label class="custom-control-label" for="permite_comentario_final">Permitir comentario final</label>
                        </div>
                    </div>

                    <div class="form-group col-12">
                        <label for="agradecimiento_texto" class="form-label">Texto de agradecimiento</label>
                        <textarea id="agradecimiento_texto" name="agradecimiento_texto" rows="3" class="form-control @error('agradecimiento_texto') is-invalid @enderror">{{ old('agradecimiento_texto', $encuestaPlantilla->agradecimiento_texto) }}</textarea>
                        @error('agradecimiento_texto')
                            <span class="invalid-feedback d-block">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group col-12">
                        <label for="comentario_placeholder" class="form-label">Placeholder del comentario final</label>
                        <input type="text" id="comentario_placeholder" name="comentario_placeholder" value="{{ old('comentario_placeholder', $encuestaPlantilla->comentario_placeholder) }}" class="form-control @error('comentario_placeholder') is-invalid @enderror" maxlength="255">
                        @error('comentario_placeholder')
                            <span class="invalid-feedback d-block">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-between">
            <a href="{{ route('admin.encuesta-plantillas.index') }}" class="btn btn-outline-secondary">Volver</a>
            <button type="submit" class="btn btn-primary">{{ $submitLabel }}</button>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white border-0 pb-0">
                <h3 class="card-title font-weight-semibold">Como se usa</h3>
            </div>
            <div class="card-body pt-3 text-muted">
                <p class="mb-2">Las respuestas son siempre numericas y el enlace que llega por email solo puede usarse una vez.</p>
                <p class="mb-2">Las preguntas se muestran una a una en la parte publica.</p>
                <p class="mb-0">La encuesta predeterminada es la que Clockia enviara automaticamente tras cada reserva.</p>
            </div>
        </div>
    </div>
</div>

@push('js')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const form = document.querySelector('.js-survey-template-form');
            const container = document.querySelector('[data-questions-container]');
            const addButton = document.querySelector('[data-add-question]');

            if (!form || !container || !addButton) {
                return;
            }

            const buildQuestionRow = index => `
                <div class="border rounded p-3 mb-3" data-question-row>
                    <input type="hidden" name="preguntas[${index}][id]" value="">
                    <div class="form-row">
                        <div class="form-group col-lg-6">
                            <label class="form-label">Enunciado</label>
                            <input type="text" name="preguntas[${index}][etiqueta]" class="form-control" maxlength="255" required>
                        </div>
                        <div class="form-group col-lg-4">
                            <label class="form-label d-block">Activa</label>
                            <div class="custom-control custom-switch mt-2">
                                <input type="hidden" name="preguntas[${index}][activo]" value="0">
                                <input type="checkbox" class="custom-control-input" id="pregunta_activa_${index}" name="preguntas[${index}][activo]" value="1" checked>
                                <label class="custom-control-label" for="pregunta_activa_${index}">Incluir en los envios</label>
                            </div>
                        </div>
                        <div class="form-group col-lg-2 d-flex align-items-end justify-content-end">
                            <button type="button" class="btn btn-outline-danger btn-sm" data-remove-question>Quitar</button>
                        </div>
                        <div class="form-group col-12 mb-0">
                            <label class="form-label">Ayuda para el usuario</label>
                            <textarea name="preguntas[${index}][descripcion]" rows="2" class="form-control"></textarea>
                        </div>
                    </div>
                </div>
            `;

            const reindex = () => {
                const rows = Array.from(container.querySelectorAll('[data-question-row]'));

                rows.forEach((row, index) => {
                    row.querySelectorAll('input, textarea, label').forEach(element => {
                        if (element.name) {
                            element.name = element.name.replace(/preguntas\[\d+\]/, `preguntas[${index}]`);
                        }

                        if (element.id) {
                            element.id = element.id.replace(/_\d+$/, `_${index}`);
                        }

                        if (element.htmlFor) {
                            element.htmlFor = element.htmlFor.replace(/_\d+$/, `_${index}`);
                        }
                    });
                });
            };

            addButton.addEventListener('click', () => {
                const index = container.querySelectorAll('[data-question-row]').length;
                container.insertAdjacentHTML('beforeend', buildQuestionRow(index));
            });

            container.addEventListener('click', event => {
                const trigger = event.target.closest('[data-remove-question]');

                if (!trigger) {
                    return;
                }

                const rows = container.querySelectorAll('[data-question-row]');

                if (rows.length <= 1) {
                    return;
                }

                trigger.closest('[data-question-row]')?.remove();
                reindex();
            });

            form.addEventListener('submit', () => {
                form.querySelectorAll('input[type="text"], textarea').forEach(field => {
                    field.value = field.value.trim();
                });
            });
        });
    </script>
@endpush
