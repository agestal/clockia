@extends('layouts.app')

@section('title', 'Editar plantilla de email')

@section('content_header_extra')
    <div>
        <h1 class="mb-1">Editar plantilla de email</h1>
        <p class="text-muted mb-0">{{ $plantillaEmail->negocio?->nombre }} · {{ $plantillaEmail->etiquetaTipo() }}</p>
    </div>
@stop

@section('content_body')
    @include('admin.partials.flash-messages')

    <form action="{{ route('admin.plantillas-email.update', $plantillaEmail) }}" method="POST" class="js-basic-validation-form" novalidate>
        @csrf
        @method('PUT')

        <div class="row">
            <div class="col-lg-8">
                <div class="card shadow-sm border-0 mb-3">
                    <div class="card-body">
                        <div class="form-row">
                            <div class="form-group col-lg-6">
                                <label class="form-label">Negocio</label>
                                <input type="text" class="form-control" value="{{ $plantillaEmail->negocio?->nombre }}" disabled>
                            </div>

                            <div class="form-group col-lg-6">
                                <label class="form-label">Tipo</label>
                                <input type="text" class="form-control" value="{{ $plantillaEmail->etiquetaTipo() }}" disabled>
                            </div>

                            <div class="form-group col-12">
                                <label for="asunto" class="form-label">Asunto</label>
                                <input type="text" id="asunto" name="asunto" value="{{ old('asunto', $plantillaEmail->asunto) }}" class="form-control @error('asunto') is-invalid @enderror" maxlength="255">
                                @error('asunto')
                                    <span class="invalid-feedback d-block">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="form-group col-lg-6">
                                <label for="titulo" class="form-label">Titulo</label>
                                <input type="text" id="titulo" name="titulo" value="{{ old('titulo', $plantillaEmail->titulo) }}" class="form-control @error('titulo') is-invalid @enderror" maxlength="255">
                                @error('titulo')
                                    <span class="invalid-feedback d-block">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="form-group col-lg-6">
                                <label for="saludo" class="form-label">Saludo</label>
                                <input type="text" id="saludo" name="saludo" value="{{ old('saludo', $plantillaEmail->saludo) }}" class="form-control @error('saludo') is-invalid @enderror" maxlength="255">
                                @error('saludo')
                                    <span class="invalid-feedback d-block">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="form-group col-12">
                                <label for="introduccion" class="form-label">Introduccion</label>
                                <textarea id="introduccion" name="introduccion" rows="3" class="form-control @error('introduccion') is-invalid @enderror">{{ old('introduccion', $plantillaEmail->introduccion) }}</textarea>
                                @error('introduccion')
                                    <span class="invalid-feedback d-block">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="form-group col-12">
                                <label for="cuerpo" class="form-label">Cuerpo principal</label>
                                <textarea id="cuerpo" name="cuerpo" rows="6" class="form-control @error('cuerpo') is-invalid @enderror">{{ old('cuerpo', $plantillaEmail->cuerpo) }}</textarea>
                                @error('cuerpo')
                                    <span class="invalid-feedback d-block">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="form-group col-lg-6">
                                <label for="texto_boton" class="form-label">Texto del boton</label>
                                <input type="text" id="texto_boton" name="texto_boton" value="{{ old('texto_boton', $plantillaEmail->texto_boton) }}" class="form-control @error('texto_boton') is-invalid @enderror" maxlength="255">
                                <small class="form-text text-muted">Solo se usa en la plantilla de encuesta.</small>
                                @error('texto_boton')
                                    <span class="invalid-feedback d-block">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="form-group col-lg-6">
                                <label for="texto_pie" class="form-label">Texto del pie</label>
                                <input type="text" id="texto_pie" name="texto_pie" value="{{ old('texto_pie', $plantillaEmail->texto_pie) }}" class="form-control @error('texto_pie') is-invalid @enderror">
                                @error('texto_pie')
                                    <span class="invalid-feedback d-block">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="form-group col-lg-3">
                                <label for="color_primario" class="form-label">Color primario</label>
                                <input type="text" id="color_primario" name="color_primario" value="{{ old('color_primario', $plantillaEmail->color_primario) }}" class="form-control @error('color_primario') is-invalid @enderror" placeholder="#7B3F00">
                                @error('color_primario')
                                    <span class="invalid-feedback d-block">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="form-group col-lg-3">
                                <label for="color_boton" class="form-label">Color boton</label>
                                <input type="text" id="color_boton" name="color_boton" value="{{ old('color_boton', $plantillaEmail->color_boton) }}" class="form-control @error('color_boton') is-invalid @enderror" placeholder="#7B3F00">
                                @error('color_boton')
                                    <span class="invalid-feedback d-block">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="form-group col-lg-3">
                                <label for="color_fondo" class="form-label">Color fondo</label>
                                <input type="text" id="color_fondo" name="color_fondo" value="{{ old('color_fondo', $plantillaEmail->color_fondo) }}" class="form-control @error('color_fondo') is-invalid @enderror" placeholder="#F5F2EE">
                                @error('color_fondo')
                                    <span class="invalid-feedback d-block">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="form-group col-lg-3">
                                <label for="color_texto" class="form-label">Color texto</label>
                                <input type="text" id="color_texto" name="color_texto" value="{{ old('color_texto', $plantillaEmail->color_texto) }}" class="form-control @error('color_texto') is-invalid @enderror" placeholder="#2C241D">
                                @error('color_texto')
                                    <span class="invalid-feedback d-block">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-between">
                    <a href="{{ route('admin.plantillas-email.index') }}" class="btn btn-outline-secondary">Volver</a>
                    <button type="submit" class="btn btn-primary">Guardar cambios</button>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card shadow-sm border-0 mb-3">
                    <div class="card-header bg-white border-0 pb-0">
                        <h3 class="card-title font-weight-semibold">Variables disponibles</h3>
                    </div>
                    <div class="card-body pt-3">
                        <ul class="list-unstyled mb-0">
                            @foreach($placeholders as $placeholder => $description)
                                <li class="mb-2">
                                    <code>{{ $placeholder }}</code>
                                    <div class="text-muted small">{{ $description }}</div>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>

                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white border-0 pb-0">
                        <h3 class="card-title font-weight-semibold">Notas</h3>
                    </div>
                    <div class="card-body pt-3 text-muted">
                        <p class="mb-2">Si dejas un campo vacio, Clockia usa el texto por defecto.</p>
                        <p class="mb-0">Los colores aceptan formato HEX, por ejemplo <code>#7B3F00</code>.</p>
                    </div>
                </div>
            </div>
        </div>
    </form>
@stop

@push('js')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const form = document.querySelector('.js-basic-validation-form');

            form?.addEventListener('submit', () => {
                form.querySelectorAll('input[type="text"], textarea').forEach(field => {
                    field.value = field.value.trim();
                });
            });
        });
    </script>
@endpush
