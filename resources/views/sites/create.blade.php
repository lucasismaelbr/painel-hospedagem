@extends('layouts.app')
@section('title','Novo Site') @section('page-title','Novo Site')
@section('topbar-actions') <a href="{{ route('sites.index') }}" class="btn btn-secondary">← Voltar</a> @endsection
@section('content')
<div class="card" style="max-width: 900px;">
    <form method="POST" action="{{ route('sites.store') }}">
        @csrf
        @include('sites._form')
        <div style="margin-top:24px;display:flex;gap:12px;">
            <button type="submit" class="btn btn-primary">💾 Salvar Site</button>
            <a href="{{ route('sites.index') }}" class="btn btn-secondary">Cancelar</a>
        </div>
    </form>
</div>
@endsection
