@extends('layouts.app')
@section('title', 'Novo Cliente')
@section('page-title', 'Novo Cliente')
@section('page-subtitle', 'Cadastrar um novo cliente')
@section('topbar-actions')
    <a href="{{ route('clientes.index') }}" class="btn btn-secondary">← Voltar</a>
@endsection

@section('content')
<div class="card" style="max-width: 800px;">
    <form method="POST" action="{{ route('clientes.store') }}">
        @csrf
        @include('clientes._form')
        <div style="margin-top: 24px; display: flex; gap: 12px;">
            <button type="submit" class="btn btn-primary">💾 Salvar Cliente</button>
            <a href="{{ route('clientes.index') }}" class="btn btn-secondary">Cancelar</a>
        </div>
    </form>
</div>
@endsection
