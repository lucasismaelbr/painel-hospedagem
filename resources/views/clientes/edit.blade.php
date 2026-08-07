@extends('layouts.app')
@section('title', 'Editar Cliente')
@section('page-title', 'Editar Cliente')
@section('page-subtitle', $cliente->nome)
@section('topbar-actions')
    <a href="{{ route('clientes.show', $cliente) }}" class="btn btn-secondary">← Voltar</a>
@endsection

@section('content')
<div class="card" style="max-width: 800px;">
    <form method="POST" action="{{ route('clientes.update', $cliente) }}">
        @csrf @method('PUT')
        @include('clientes._form')
        <div style="margin-top: 24px; display: flex; gap: 12px;">
            <button type="submit" class="btn btn-primary">💾 Atualizar</button>
            <a href="{{ route('clientes.show', $cliente) }}" class="btn btn-secondary">Cancelar</a>
        </div>
    </form>
</div>
@endsection
