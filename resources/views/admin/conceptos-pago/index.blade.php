@extends('layouts.app')

@section('title', 'Conceptos de pago')

@section('content_header_extra')
    <div class="d-flex flex-wrap align-items-start justify-content-between">
        <div>
            <h1 class="mb-1">Conceptos de pago</h1>
            <p class="text-muted mb-0">Gestiona el catálogo de conceptos de pago (señal, pago final, reembolso...).</p>
        </div>
        <div class="mt-3 mt-md-0">
            <a href="{{ route('admin.conceptos-pago.create') }}" class="btn btn-primary"><i class="fas fa-plus mr-1"></i>Nuevo concepto de pago</a>
        </div>
    </div>
@stop

@section('content_body')
    @include('admin.catalogos-simple._index-content', [
        'items' => $conceptosPago,
        'search' => $search,
        'sort' => $sort,
        'direction' => $direction,
        'indexRoute' => route('admin.conceptos-pago.index'),
        'countHeading' => 'Pagos',
        'countField' => 'pagos_count',
        'moduleKey' => 'conceptos-pago',
        'inlineRouteName' => 'admin.conceptos-pago.inline-update',
        'showRouteName' => 'admin.conceptos-pago.show',
        'editRouteName' => 'admin.conceptos-pago.edit',
        'destroyRouteName' => 'admin.conceptos-pago.destroy',
        'deleteMessagePrefix' => 'el concepto de pago',
        'emptyMessage' => 'No hay conceptos de pago registrados con los filtros actuales.',
    ])
@stop
