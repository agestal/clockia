@extends('layouts.app')

@section('title', 'Nueva encuesta')

@section('content_header_extra')
    <div>
        <h1 class="mb-1">Nueva encuesta</h1>
        <p class="text-muted mb-0">Crea una encuesta de valoracion para enviarla tras la experiencia.</p>
    </div>
@stop

@section('content_body')
    @include('admin.partials.flash-messages')

    <form action="{{ route('admin.encuesta-plantillas.store') }}" method="POST" class="js-survey-template-form" novalidate>
        @include('admin.encuesta-plantillas._form', [
            'isEdit' => false,
            'submitLabel' => 'Crear encuesta',
        ])
    </form>
@stop
