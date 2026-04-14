@extends('layouts.app')

@section('title', 'Tipos de precio')

@section('content_header_extra')
    <div class="d-flex flex-wrap align-items-start justify-content-between">
        <div>
            <h1 class="mb-1">Tipos de precio</h1>
            <p class="text-muted mb-0">Gestiona el catálogo base de tipos de precio.</p>
        </div>

        <div class="mt-3 mt-md-0">
            <a href="{{ route('admin.tipos-precio.create') }}" class="btn btn-primary">
                <i class="fas fa-plus mr-1"></i>
                Nuevo tipo de precio
            </a>
        </div>
    </div>
@stop

@section('content_body')
    @include('admin.catalogos-simple._index-content', [
        'items' => $tiposPrecio,
        'search' => $search,
        'sort' => $sort,
        'direction' => $direction,
        'indexRoute' => route('admin.tipos-precio.index'),
        'countHeading' => 'Servicios',
        'countField' => 'servicios_count',
        'moduleKey' => 'tipos-precio',
        'inlineRouteName' => 'admin.tipos-precio.inline-update',
        'showRouteName' => 'admin.tipos-precio.show',
        'editRouteName' => 'admin.tipos-precio.edit',
        'destroyRouteName' => 'admin.tipos-precio.destroy',
        'deleteMessagePrefix' => 'el tipo de precio',
        'emptyMessage' => 'No hay tipos de precio registrados con los filtros actuales.',
    ])
@stop
