@extends('layouts.app')

@section('title', 'Estados de pago')

@section('content_header_extra')
    <div class="d-flex flex-wrap align-items-start justify-content-between">
        <div>
            <h1 class="mb-1">Estados de pago</h1>
            <p class="text-muted mb-0">Gestiona el catálogo base de estados de pago.</p>
        </div>
        <div class="mt-3 mt-md-0">
            <a href="{{ route('admin.estados-pago.create') }}" class="btn btn-primary"><i class="fas fa-plus mr-1"></i>Nuevo estado de pago</a>
        </div>
    </div>
@stop

@section('content_body')
    @include('admin.catalogos-simple._index-content', [
        'items' => $estadosPago,
        'search' => $search,
        'sort' => $sort,
        'direction' => $direction,
        'indexRoute' => route('admin.estados-pago.index'),
        'countHeading' => 'Pagos',
        'countField' => 'pagos_count',
        'moduleKey' => 'estados-pago',
        'inlineRouteName' => 'admin.estados-pago.inline-update',
        'showRouteName' => 'admin.estados-pago.show',
        'editRouteName' => 'admin.estados-pago.edit',
        'destroyRouteName' => 'admin.estados-pago.destroy',
        'deleteMessagePrefix' => 'el estado de pago',
        'emptyMessage' => 'No hay estados de pago registrados con los filtros actuales.',
    ])
@stop
