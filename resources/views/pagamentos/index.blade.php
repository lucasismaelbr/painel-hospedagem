@extends('layouts.app')
@section('title','Pagamentos') @section('page-title','Pagamentos') @section('page-subtitle','Controle financeiro')
@section('topbar-actions') <a href="{{ route('pagamentos.create') }}" class="btn btn-primary">+ Registrar Pagamento</a> @endsection

@section('content')
<div class="card">
    <form method="GET" class="filters">
        <select name="status" class="form-control">
            <option value="">Todos os status</option>
            <option value="pendente" {{ request('status')==='pendente' ? 'selected':'' }}>Pendente</option>
            <option value="pago"     {{ request('status')==='pago'     ? 'selected':'' }}>Pago</option>
            <option value="atrasado" {{ request('status')==='atrasado' ? 'selected':'' }}>Atrasado</option>
        </select>
        <button type="submit" class="btn btn-secondary">Filtrar</button>
        @if(request('status')) <a href="{{ route('pagamentos.index') }}" class="btn btn-secondary">✕</a> @endif
    </form>
    <div class="table-wrap">
        <table>
            <thead><tr><th>Cliente</th><th>Site</th><th>Tipo</th><th>Valor</th><th>Vencimento</th><th>Método</th><th>Status</th><th>Ações</th></tr></thead>
            <tbody>
                @forelse($pagamentos as $pag)
                <tr>
                    <td><a href="{{ route('clientes.show', $pag->cliente) }}" style="color:#6c63ff;text-decoration:none;">{{ $pag->cliente->nome }}</a></td>
                    <td>{{ $pag->site?->dominio ?? '—' }}</td>
                    <td>{{ ucfirst($pag->tipo) }}</td>
                    <td>R$ {{ number_format($pag->valor,2,',','.') }}</td>
                    <td>{{ $pag->data_vencimento->format('d/m/Y') }}</td>
                    <td>{{ $pag->metodo_pagamento ? ucfirst($pag->metodo_pagamento) : '—' }}</td>
                    <td>
                        @if($pag->status==='pago') <span class="badge badge-green">Pago</span>
                        @elseif($pag->status==='atrasado') <span class="badge badge-red">Atrasado</span>
                        @else <span class="badge badge-yellow">Pendente</span> @endif
                    </td>
                    <td style="display:flex;gap:6px;align-items:center;">
                        @if($pag->status !== 'pago')
                        <form method="POST" action="{{ route('pagamentos.pagar', $pag) }}" style="display:inline;">
                            @csrf @method('PATCH')
                            <button type="submit" class="btn btn-success btn-sm">✓</button>
                        </form>
                        @endif
                        <a href="{{ route('pagamentos.edit', $pag) }}" class="btn btn-secondary btn-sm">✏️</a>
                        <form method="POST" action="{{ route('pagamentos.destroy', $pag) }}" style="display:inline;" onsubmit="return confirm('Excluir?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm">🗑️</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="8"><div class="empty-state"><div class="icon">💳</div><p>Nenhum pagamento</p></div></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $pagamentos->links() }}
</div>
@endsection
