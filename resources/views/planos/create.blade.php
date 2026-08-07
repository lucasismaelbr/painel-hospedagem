@extends('layouts.app')
@section('title', 'Novo Plano') @section('page-title', 'Novo Plano')
@section('topbar-actions') <a href="{{ route('planos.index') }}" class="btn btn-secondary">← Voltar</a> @endsection
@section('content')
<div class="card" style="max-width: 700px;">
    <form method="POST" action="{{ route('planos.store') }}">
        @csrf
        @include('planos._form')
        <div style="margin-top: 24px; display: flex; gap: 12px;">
            <button type="submit" class="btn btn-primary">💾 Salvar Plano</button>
            <a href="{{ route('planos.index') }}" class="btn btn-secondary">Cancelar</a>
        </div>
    </form>
</div>
@endsection
