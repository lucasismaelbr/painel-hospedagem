@extends('layouts.app')
@section('title','Editar Site') @section('page-title','Editar Site') @section('page-subtitle', $site->dominio)
@section('topbar-actions') <a href="{{ route('sites.show', $site) }}" class="btn btn-secondary">← Voltar</a> @endsection
@section('content')
<div class="card" style="max-width: 900px;">
    <form method="POST" action="{{ route('sites.update', $site) }}">
        @csrf @method('PUT')
        @include('sites._form')
        <div style="margin-top:24px;display:flex;gap:12px;">
            <button type="submit" class="btn btn-primary">💾 Atualizar</button>
            <a href="{{ route('sites.show', $site) }}" class="btn btn-secondary">Cancelar</a>
        </div>
    </form>
</div>
@endsection
