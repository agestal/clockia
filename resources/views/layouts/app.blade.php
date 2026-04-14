@php($pageTitle = isset($header) ? trim(strip_tags((string) $header)) : config('app.name', 'Clockia'))

@extends('adminlte::page')

@section('title', $pageTitle)

@section('content_header')
    @isset($header)
        {!! $header !!}
    @endisset
    @yield('content_header_extra')
@stop

@section('content')
    @isset($slot)
        {{ $slot }}
    @endisset

    @yield('content_body')

    @stack('modals')
@stop

@section('adminlte_css')
    @stack('css')
@stop

@section('adminlte_js')
    @stack('js')
@stop
