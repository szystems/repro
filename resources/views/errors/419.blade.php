@extends('errors.layout')

@section('codigo', '419')
@section('icono', 'bi-arrow-clockwise')
@section('titulo', 'La página se venció')
@section('mensaje', 'Esa pantalla ya no sirve. Ábrela de nuevo y vuelve a intentar. No recargues este aviso.')

@section('acciones')
    <a href="{{ route('login') }}" class="btn-home">
        <i class="bi bi-box-arrow-in-right me-1"></i> Abrir el ingreso
    </a>
@endsection
