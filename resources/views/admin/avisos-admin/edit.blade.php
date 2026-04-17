@extends('layouts.app')

@section('title', 'Configurar avisos al administrador')

@section('content_header_extra')
    <div>
        <h1 class="mb-1">Configurar avisos al administrador</h1>
        <p class="text-muted mb-0">{{ $negocio->nombre }}</p>
    </div>
@stop

@section('content_body')
    @include('admin.partials.flash-messages')

    <form action="{{ route('admin.avisos-admin.update', $negocio) }}" method="POST" novalidate>
        @csrf
        @method('PUT')

        <div class="row">
            <div class="col-lg-8">
                <div class="card shadow-sm border-0 mb-3">
                    <div class="card-body">
                        <div class="form-group mb-0">
                            <label for="notif_email_destino" class="form-label">Email de destino</label>
                            <input
                                type="email"
                                id="notif_email_destino"
                                name="notif_email_destino"
                                value="{{ old('notif_email_destino', $negocio->notif_email_destino) }}"
                                class="form-control @error('notif_email_destino') is-invalid @enderror"
                                placeholder="admin@tu-negocio.com">
                            <small class="form-text text-muted">
                                Si lo dejas vacio, Clockia intentará usar el primer usuario asociado al negocio o el email principal del negocio.
                            </small>
                            @error('notif_email_destino')
                                <span class="invalid-feedback d-block">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                @foreach($notificationGroups as $group)
                    <div class="card shadow-sm border-0 mb-3">
                        <div class="card-header bg-white border-0">
                            <h3 class="card-title font-weight-semibold mb-1">{{ $group['title'] }}</h3>
                            <p class="text-muted small mb-0">{{ $group['description'] }}</p>
                        </div>
                        <div class="card-body pt-2">
                            @foreach($group['items'] as $item)
                                <div class="custom-control custom-switch py-2 @if(! $loop->last) border-bottom @endif">
                                    <input
                                        type="checkbox"
                                        class="custom-control-input"
                                        id="{{ $item['field'] }}"
                                        name="{{ $item['field'] }}"
                                        value="1"
                                        @checked(old($item['field'], $negocio->{$item['field']}))>
                                    <label class="custom-control-label font-weight-semibold" for="{{ $item['field'] }}">
                                        {{ $item['label'] }}
                                    </label>
                                    <div class="text-muted small ml-1">{{ $item['description'] }}</div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach

                <div class="d-flex justify-content-between">
                    <a href="{{ route('admin.avisos-admin.index') }}" class="btn btn-outline-secondary">Volver</a>
                    <button type="submit" class="btn btn-primary">Guardar cambios</button>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white border-0 pb-0">
                        <h3 class="card-title font-weight-semibold">Notas</h3>
                    </div>
                    <div class="card-body pt-3 text-muted">
                        <p class="mb-2">Los avisos se envían por email al administrador configurado para el negocio.</p>
                        <p class="mb-2">Puedes activar solo lo imprescindible para no saturar al equipo.</p>
                        <p class="mb-0">Los cambios aplican a nuevas notificaciones; no reenvían avisos antiguos.</p>
                    </div>
                </div>
            </div>
        </div>
    </form>
@stop
