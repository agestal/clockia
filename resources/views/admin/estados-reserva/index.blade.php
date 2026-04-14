@extends('layouts.app')

@section('title', 'Estados de reserva')

@section('content_header_extra')
    <div class="d-flex flex-wrap align-items-start justify-content-between">
        <div>
            <h1 class="mb-1">Estados de reserva</h1>
            <p class="text-muted mb-0">Gestiona el catálogo base de estados de reserva.</p>
        </div>
        <div class="mt-3 mt-md-0">
            <a href="{{ route('admin.estados-reserva.create') }}" class="btn btn-primary"><i class="fas fa-plus mr-1"></i>Nuevo estado de reserva</a>
        </div>
    </div>
@stop

@section('content_body')
    @include('admin.catalogos-simple._index-content', [
        'items' => $estadosReserva,
        'search' => $search,
        'sort' => $sort,
        'direction' => $direction,
        'indexRoute' => route('admin.estados-reserva.index'),
        'countHeading' => 'Reservas',
        'countField' => 'reservas_count',
        'moduleKey' => 'estados-reserva',
        'inlineRouteName' => 'admin.estados-reserva.inline-update',
        'showRouteName' => 'admin.estados-reserva.show',
        'editRouteName' => 'admin.estados-reserva.edit',
        'destroyRouteName' => 'admin.estados-reserva.destroy',
        'deleteMessagePrefix' => 'el estado de reserva',
        'emptyMessage' => 'No hay estados de reserva registrados con los filtros actuales.',
    ])
@stop
