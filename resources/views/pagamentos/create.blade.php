@extends('layouts.app')
@section('title','Novo Pagamento') @section('page-title','Novo Pagamento')
@section('topbar-actions') <a href="{{ route('pagamentos.index') }}" class="btn btn-secondary">← Voltar</a> @endsection
@section('content')
<div class="card" style="max-width:900px;">
    <form method="POST" action="{{ route('pagamentos.store') }}">
        @csrf
        @include('pagamentos._form')
        <div style="margin-top:24px;display:flex;gap:12px;">
            <button type="submit" class="btn btn-primary">💾 Registrar</button>
            <a href="{{ route('pagamentos.index') }}" class="btn btn-secondary">Cancelar</a>
        </div>
    </form>
</div>
@endsection
