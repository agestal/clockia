@extends('layouts.app')

@section('title', 'Tipos de recurso')

@section('content_header_extra')
    <div class="d-flex flex-wrap align-items-start justify-content-between">
        <div>
            <h1 class="mb-1">Tipos de recurso</h1>
            <p class="text-muted mb-0">Gestiona el catálogo base de tipos de recurso.</p>
        </div>
        <div class="mt-3 mt-md-0">
            <a href="{{ route('admin.tipos-recurso.create') }}" class="btn btn-primary"><i class="fas fa-plus mr-1"></i>Nuevo tipo de recurso</a>
        </div>
    </div>
@stop

@section('content_body')
    @include('admin.catalogos-simple._index-content', [
        'items' => $tiposRecurso,
        'search' => $search,
        'sort' => $sort,
        'direction' => $direction,
        'indexRoute' => route('admin.tipos-recurso.index'),
        'countHeading' => 'Recursos',
        'countField' => 'recursos_count',
        'moduleKey' => 'tipos-recurso',
        'inlineRouteName' => 'admin.tipos-recurso.inline-update',
        'showRouteName' => 'admin.tipos-recurso.show',
        'editRouteName' => 'admin.tipos-recurso.edit',
        'destroyRouteName' => 'admin.tipos-recurso.destroy',
        'deleteMessagePrefix' => 'el tipo de recurso',
        'emptyMessage' => 'No hay tipos de recurso registrados con los filtros actuales.',
    ])
@stop
