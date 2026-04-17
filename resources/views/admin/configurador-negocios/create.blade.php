@extends('layouts.app')

@section('title', 'Nueva sesion de configurador')

@section('content_header_extra')
    <div>
        <h1 class="mb-1">Nueva sesion de configurador</h1>
        <p class="text-muted mb-0">Arranca el onboarding con la URL publica del negocio y los datos minimos que ya conozcas.</p>
    </div>
@stop

@section('content_body')
    @include('admin.partials.flash-messages')

    <form action="{{ route('admin.configurador-negocios.store') }}" method="POST" class="js-basic-validation-form" novalidate>
        @csrf

        <div class="row">
            <div class="col-lg-8">
                <div class="card shadow-sm border-0 mb-3">
                    <div class="card-header bg-white">
                        <h2 class="h5 mb-0">Entrada</h2>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label for="source_url">URL del negocio</label>
                            <input
                                type="url"
                                id="source_url"
                                name="source_url"
                                value="{{ old('source_url') }}"
                                class="form-control @error('source_url') is-invalid @enderror"
                                maxlength="500"
                                placeholder="https://www.tubodega.com"
                                required
                            >
                            @error('source_url')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @else
                                <small class="form-text text-muted">Se usara para descubrir la informacion publica del negocio y construir un borrador revisable.</small>
                            @enderror
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="requested_tipo_negocio_id">Tipo de negocio</label>
                                <select id="requested_tipo_negocio_id" name="requested_tipo_negocio_id" class="form-control @error('requested_tipo_negocio_id') is-invalid @enderror" required>
                                    <option value="">Selecciona un tipo</option>
                                    @foreach($businessTypes as $type)
                                        <option value="{{ $type->id }}" @selected((int) old('requested_tipo_negocio_id', $defaultBusinessType) === $type->id)>{{ $type->nombre }}</option>
                                    @endforeach
                                </select>
                                @error('requested_tipo_negocio_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group col-md-6">
                                <label for="requested_business_name">Nombre del negocio</label>
                                <input
                                    type="text"
                                    id="requested_business_name"
                                    name="requested_business_name"
                                    value="{{ old('requested_business_name') }}"
                                    class="form-control @error('requested_business_name') is-invalid @enderror"
                                    maxlength="255"
                                    placeholder="Opcional si quieres forzarlo desde el inicio"
                                >
                                @error('requested_business_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white">
                        <h2 class="h5 mb-0">Administrador del negocio</h2>
                    </div>
                    <div class="card-body">
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="requested_admin_name">Nombre</label>
                                <input
                                    type="text"
                                    id="requested_admin_name"
                                    name="requested_admin_name"
                                    value="{{ old('requested_admin_name') }}"
                                    class="form-control @error('requested_admin_name') is-invalid @enderror"
                                    maxlength="255"
                                    placeholder="Se puede completar mas tarde"
                                >
                                @error('requested_admin_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group col-md-6">
                                <label for="requested_admin_email">Email</label>
                                <input
                                    type="email"
                                    id="requested_admin_email"
                                    name="requested_admin_email"
                                    value="{{ old('requested_admin_email') }}"
                                    class="form-control @error('requested_admin_email') is-invalid @enderror"
                                    maxlength="255"
                                    placeholder="admin@negocio.test"
                                >
                                @error('requested_admin_email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="requested_admin_password">Contrasena</label>
                                <input
                                    type="password"
                                    id="requested_admin_password"
                                    name="requested_admin_password"
                                    class="form-control @error('requested_admin_password') is-invalid @enderror"
                                    minlength="8"
                                    placeholder="Minimo 8 caracteres"
                                >
                                @error('requested_admin_password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group col-md-6">
                                <label for="requested_admin_password_confirmation">Repite la contrasena</label>
                                <input
                                    type="password"
                                    id="requested_admin_password_confirmation"
                                    name="requested_admin_password_confirmation"
                                    class="form-control"
                                    minlength="8"
                                    placeholder="Repite la contrasena"
                                >
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white">
                        <h2 class="h5 mb-0">Que hara esta primera version</h2>
                    </div>
                    <div class="card-body">
                        <ul class="mb-0 pl-3 text-muted">
                            <li>Explorar la home y varias paginas internas relevantes.</li>
                            <li>Detectar nombre, contacto, descripcion, direccion y horario si aparecen estructurados.</li>
                            <li>Localizar paginas candidatas de experiencias.</li>
                            <li>Preparar un borrador y marcar los campos que falten.</li>
                            <li>Permitir crear ya el negocio si el minimo esta cubierto.</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-between mt-3">
            <a href="{{ route('admin.configurador-negocios.index') }}" class="btn btn-light border">Volver</a>
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-wand-magic-sparkles mr-1"></i>
                Crear sesion y explorar
            </button>
        </div>
    </form>
@stop

@push('js')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll('.js-basic-validation-form').forEach((form) => {
                form.addEventListener('submit', () => {
                    form.querySelectorAll('input[type="text"], input[type="email"], input[type="url"]').forEach((field) => {
                        field.value = field.value.trim();
                    });
                });
            });
        });
    </script>
@endpush
