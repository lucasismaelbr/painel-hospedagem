@extends('layouts.app')
@section('title', 'Editar Plano') @section('page-title', 'Editar Plano') @section('page-subtitle', $plano->nome)
@section('topbar-actions') <a href="{{ route('planos.index') }}" class="btn btn-secondary">← Voltar</a> @endsection
@section('content')
<div class="card" style="max-width: 700px;">
    <form method="POST" action="{{ route('planos.update', $plano) }}">
        @csrf @method('PUT')
        @include('planos._form')
        <div style="margin-top: 24px; display: flex; gap: 12px;">
            <button type="submit" class="btn btn-primary">💾 Atualizar</button>
            <a href="{{ route('planos.index') }}" class="btn btn-secondary">Cancelar</a>
        </div>
    </form>
</div>
@endsection
