@extends('layouts.app')
@section('title','Pagamento #'.$pagamento->id) @section('page-title','Pagamento #'.$pagamento->id)
@section('topbar-actions')
<div style="display:flex;gap:10px;">
    @if($pagamento->status !== 'pago')
    <form method="POST" action="{{ route('pagamentos.pagar', $pagamento) }}">
        @csrf @method('PATCH')
        <button type="submit" class="btn btn-success">✓ Marcar como Pago</button>
    </form>
    @endif
    <a href="{{ route('pagamentos.edit', $pagamento) }}" class="btn btn-secondary">✏️ Editar</a>
    <a href="{{ route('pagamentos.index') }}" class="btn btn-secondary">← Voltar</a>
</div>
@endsection
@section('content')
<div class="card" style="max-width:600px;">
    <table style="font-size:14px;width:100%;">
        <tr><td style="color:#64748b;padding:8px 16px 8px 0;width:160px;">Cliente</td><td><a href="{{ route('clientes.show', $pagamento->cliente) }}" style="color:#6c63ff;text-decoration:none;">{{ $pagamento->cliente->nome }}</a></td></tr>
        <tr><td style="color:#64748b;padding:8px 16px 8px 0;">Site</td><td>{{ $pagamento->site?->dominio ?? '—' }}</td></tr>
        <tr><td style="color:#64748b;padding:8px 16px 8px 0;">Tipo</td><td>{{ ucfirst($pagamento->tipo) }}</td></tr>
        <tr><td style="color:#64748b;padding:8px 16px 8px 0;">Valor</td><td style="font-size:18px;font-weight:700;">R$ {{ number_format($pagamento->valor,2,',','.') }}</td></tr>
        <tr><td style="color:#64748b;padding:8px 16px 8px 0;">Status</td><td>
            @if($pagamento->status==='pago') <span class="badge badge-green">Pago</span>
            @elseif($pagamento->status==='atrasado') <span class="badge badge-red">Atrasado</span>
            @else <span class="badge badge-yellow">Pendente</span> @endif
        </td></tr>
        <tr><td style="color:#64748b;padding:8px 16px 8px 0;">Vencimento</td><td>{{ $pagamento->data_vencimento->format('d/m/Y') }}</td></tr>
        <tr><td style="color:#64748b;padding:8px 16px 8px 0;">Pago em</td><td>{{ $pagamento->data_pagamento?->format('d/m/Y') ?? '—' }}</td></tr>
        <tr><td style="color:#64748b;padding:8px 16px 8px 0;">Método</td><td>{{ $pagamento->metodo_pagamento ? ucfirst($pagamento->metodo_pagamento) : '—' }}</td></tr>
    </table>
</div>
@endsection
