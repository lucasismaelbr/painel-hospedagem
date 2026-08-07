@extends('layouts.app')
@section('title', $cliente->nome)
@section('page-title', $cliente->nome)
@section('page-subtitle', $cliente->empresa ?? 'Cliente')
@section('topbar-actions')
    <div style="display: flex; gap: 10px;">
        <a href="{{ route('clientes.edit', $cliente) }}" class="btn btn-secondary">✏️ Editar</a>
        <form method="POST" action="{{ route('clientes.destroy', $cliente) }}" onsubmit="return confirm('Excluir este cliente?')">
            @csrf @method('DELETE')
            <button type="submit" class="btn btn-danger">🗑️ Excluir</button>
        </form>
        <a href="{{ route('clientes.index') }}" class="btn btn-secondary">← Voltar</a>
    </div>
@endsection

@section('content')
<div class="grid-2" style="gap: 24px; margin-bottom: 24px;">
    <!-- Info -->
    <div class="card">
        <div class="card-title" style="margin-bottom: 20px;">📋 Informações</div>
        <table style="font-size: 14px;">
            <tr><td style="color: #64748b; padding: 6px 12px 6px 0; width: 130px;">Status</td>
                <td>@if($cliente->status === 'ativo') <span class="badge badge-green">Ativo</span>
                    @elseif($cliente->status === 'inativo') <span class="badge badge-gray">Inativo</span>
                    @else <span class="badge badge-yellow">Prospect</span> @endif</td></tr>
            <tr><td style="color: #64748b; padding: 6px 12px 6px 0;">E-mail</td><td>{{ $cliente->email ?? '—' }}</td></tr>
            <tr><td style="color: #64748b; padding: 6px 12px 6px 0;">Telefone</td><td>{{ $cliente->telefone ?? '—' }}</td></tr>
            <tr><td style="color: #64748b; padding: 6px 12px 6px 0;">CPF/CNPJ</td><td>{{ $cliente->cnpj_cpf ?? '—' }}</td></tr>
            <tr><td style="color: #64748b; padding: 6px 12px 6px 0;">Origem</td><td>{{ $cliente->origem_lead ?? '—' }}</td></tr>
            <tr><td style="color: #64748b; padding: 6px 12px 6px 0;">Endereço</td><td>{{ $cliente->endereco ?? '—' }}</td></tr>
        </table>
    </div>

    <!-- Stats rápidos -->
    <div style="display: flex; flex-direction: column; gap: 16px;">
        <div class="stat-card" style="--accent-color: #6c63ff;">
            <div class="stat-label">Sites</div>
            <div class="stat-value">{{ $cliente->sites->count() }}</div>
            <div class="stat-icon">🌐</div>
        </div>
        <div class="stat-card" style="--accent-color: #f59e0b;">
            <div class="stat-label">Pagamentos Pendentes</div>
            <div class="stat-value">{{ $cliente->pagamentos->whereIn('status', ['pendente','atrasado'])->count() }}</div>
            <div class="stat-icon">⚠️</div>
        </div>
    </div>
</div>

<!-- Sites do cliente -->
<div class="card" style="margin-bottom: 24px;">
    <div class="card-header">
        <div class="card-title">🌐 Sites</div>
        <a href="{{ route('sites.create') }}" class="btn btn-secondary btn-sm">+ Site</a>
    </div>
    @if($cliente->sites->isEmpty())
        <div class="empty-state"><div class="icon">🌐</div><p>Nenhum site cadastrado</p></div>
    @else
        <table>
            <thead><tr><th>Domínio</th><th>Plano</th><th>Status</th><th>Renovação</th><th></th></tr></thead>
            <tbody>
                @foreach($cliente->sites as $site)
                <tr>
                    <td><a href="{{ route('sites.show', $site) }}" style="color: #6c63ff; text-decoration: none;">{{ $site->dominio }}</a></td>
                    <td>{{ $site->plano->nome ?? '—' }}</td>
                    <td>
                        @if($site->status === 'ativo') <span class="badge badge-green">Ativo</span>
                        @elseif($site->status === 'suspenso') <span class="badge badge-red">Suspenso</span>
                        @else <span class="badge badge-yellow">Em construção</span> @endif
                    </td>
                    <td>{{ $site->data_renovacao?->format('d/m/Y') ?? '—' }}</td>
                    <td><a href="{{ route('sites.show', $site) }}" class="btn btn-secondary btn-sm">Ver</a></td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>

<!-- Pagamentos do cliente -->
<div class="card">
    <div class="card-header">
        <div class="card-title">💳 Histórico de Pagamentos</div>
        <a href="{{ route('pagamentos.create') }}" class="btn btn-secondary btn-sm">+ Pagamento</a>
    </div>
    @if($cliente->pagamentos->isEmpty())
        <div class="empty-state"><div class="icon">💳</div><p>Nenhum pagamento registrado</p></div>
    @else
        <table>
            <thead><tr><th>Tipo</th><th>Valor</th><th>Vencimento</th><th>Status</th><th></th></tr></thead>
            <tbody>
                @foreach($cliente->pagamentos as $pag)
                <tr>
                    <td>{{ ucfirst($pag->tipo) }}</td>
                    <td>R$ {{ number_format($pag->valor, 2, ',', '.') }}</td>
                    <td>{{ $pag->data_vencimento->format('d/m/Y') }}</td>
                    <td>
                        @if($pag->status === 'pago') <span class="badge badge-green">Pago</span>
                        @elseif($pag->status === 'atrasado') <span class="badge badge-red">Atrasado</span>
                        @else <span class="badge badge-yellow">Pendente</span> @endif
                    </td>
                    <td>
                        @if($pag->status !== 'pago')
                        <form method="POST" action="{{ route('pagamentos.pagar', $pag) }}" style="display:inline;">
                            @csrf @method('PATCH')
                            <button type="submit" class="btn btn-success btn-sm">✓ Pagar</button>
                        </form>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>
@endsection
