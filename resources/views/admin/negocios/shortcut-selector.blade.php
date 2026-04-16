@extends('layouts.app')

@section('title', $shortcutTitle)

@section('content_header_extra')
    <div class="d-flex flex-wrap align-items-start justify-content-between">
        <div>
            <h1 class="mb-1">{{ $shortcutTitle }}</h1>
            <p class="text-muted mb-0">{{ $shortcutDescription }}</p>
        </div>

        <div class="mt-3 mt-md-0">
            <a href="{{ route('admin.negocios.index') }}" class="btn btn-light border">Ver negocios</a>
        </div>
    </div>
@stop

@section('content_body')
    @include('admin.partials.flash-messages')

    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th class="border-top-0">Negocio</th>
                            <th class="border-top-0">Estado</th>
                            <th class="border-top-0 text-right">Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($shortcutItems as $item)
                            <tr>
                                <td>
                                    <div class="font-weight-bold text-dark">{{ $item['negocio']->nombre }}</div>
                                    <div class="small text-muted">
                                        {{ $item['negocio']->tipoNegocio?->nombre ?: 'Sin tipo' }}
                                    </div>
                                </td>
                                <td>
                                    <span class="badge badge-{{ $item['status']['badge'] }} px-2 py-1">
                                        {{ $item['status']['label'] }}
                                    </span>
                                    <div class="small text-muted mt-1">{{ $item['status']['detail'] }}</div>
                                </td>
                                <td class="text-right">
                                    <a href="{{ $item['configure_url'] }}" class="btn btn-primary btn-sm">
                                        {{ $shortcutButtonLabel }}
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@stop
