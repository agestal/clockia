@extends('layouts.app')

@section('title', 'Tipos de pago')

@section('content_header_extra')
    <div class="d-flex flex-wrap align-items-start justify-content-between">
        <div>
            <h1 class="mb-1">Tipos de pago</h1>
            <p class="text-muted mb-0">Gestiona el catálogo base de tipos de pago.</p>
        </div>
        <div class="mt-3 mt-md-0">
            <a href="{{ route('admin.tipos-pago.create') }}" class="btn btn-primary"><i class="fas fa-plus mr-1"></i>Nuevo tipo de pago</a>
        </div>
    </div>
@stop

@section('content_body')
    @include('admin.catalogos-simple._index-content', [
        'items' => $tiposPago,
        'search' => $search,
        'sort' => $sort,
        'direction' => $direction,
        'indexRoute' => route('admin.tipos-pago.index'),
        'countHeading' => 'Pagos',
        'countField' => 'pagos_count',
        'moduleKey' => 'tipos-pago',
        'inlineRouteName' => 'admin.tipos-pago.inline-update',
        'showRouteName' => 'admin.tipos-pago.show',
        'editRouteName' => 'admin.tipos-pago.edit',
        'destroyRouteName' => 'admin.tipos-pago.destroy',
        'deleteMessagePrefix' => 'el tipo de pago',
        'emptyMessage' => 'No hay tipos de pago registrados con los filtros actuales.',
    ])
@stop
