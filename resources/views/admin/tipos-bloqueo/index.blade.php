@extends('layouts.app')

@section('title', 'Tipos de bloqueo')

@section('content_header_extra')
    <div class="d-flex flex-wrap align-items-start justify-content-between">
        <div>
            <h1 class="mb-1">Tipos de bloqueo</h1>
            <p class="text-muted mb-0">Gestiona el catálogo base de tipos de bloqueo.</p>
        </div>
        <div class="mt-3 mt-md-0">
            <a href="{{ route('admin.tipos-bloqueo.create') }}" class="btn btn-primary"><i class="fas fa-plus mr-1"></i>Nuevo tipo de bloqueo</a>
        </div>
    </div>
@stop

@section('content_body')
    @include('admin.catalogos-simple._index-content', [
        'items' => $tiposBloqueo,
        'search' => $search,
        'sort' => $sort,
        'direction' => $direction,
        'indexRoute' => route('admin.tipos-bloqueo.index'),
        'countHeading' => 'Bloqueos',
        'countField' => 'bloqueos_count',
        'moduleKey' => 'tipos-bloqueo',
        'inlineRouteName' => 'admin.tipos-bloqueo.inline-update',
        'showRouteName' => 'admin.tipos-bloqueo.show',
        'editRouteName' => 'admin.tipos-bloqueo.edit',
        'destroyRouteName' => 'admin.tipos-bloqueo.destroy',
        'deleteMessagePrefix' => 'el tipo de bloqueo',
        'emptyMessage' => 'No hay tipos de bloqueo registrados con los filtros actuales.',
    ])
@stop
