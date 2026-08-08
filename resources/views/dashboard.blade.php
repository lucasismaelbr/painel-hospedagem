@extends('layouts.app')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')
@section('page-subtitle', 'Visão geral do painel')

@section('content')
<!-- Stats Cards (MRR, Faturamento Mês, Faturamento Ano, Clientes, Sites) -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(210px, 1fr)); gap: 20px; margin-bottom: 28px;">
    <!-- Card 1: MRR -->
    <div class="stat-card" style="--accent-color: #6c63ff;">
        <div class="stat-label">MRR (Recorrente)</div>
        <div class="stat-value">R$ {{ number_format($mrr, 2, ',', '.') }}</div>
        <div class="stat-icon">📈</div>
    </div>

    <!-- Card 2: Faturamento do Mês -->
    <div class="stat-card" style="--accent-color: #4ade80;">
        <div class="stat-label">Faturamento Mês</div>
        <div class="stat-value">R$ {{ number_format($faturamentoMes, 2, ',', '.') }}</div>
        <div class="stat-icon">💰</div>
    </div>

    <!-- Card 3: Faturamento do Ano -->
    <div class="stat-card" style="--accent-color: #f59e0b;">
        <div class="stat-label">Faturamento Ano</div>
        <div class="stat-value">R$ {{ number_format($faturamentoAno, 2, ',', '.') }}</div>
        <div class="stat-icon">🏆</div>
    </div>

    <!-- Card 4: Clientes Ativos -->
    <div class="stat-card" style="--accent-color: #38bdf8;">
        <div class="stat-label">Clientes Ativos</div>
        <div class="stat-value">{{ $totalClientes }}</div>
        <div class="stat-icon">👥</div>
    </div>

    <!-- Card 5: Sites Ativos -->
    <div class="stat-card" style="--accent-color: #a78bfa;">
        <div class="stat-label">Sites Ativos</div>
        <div class="stat-value">{{ $totalSites }}</div>
        <div class="stat-icon">🌐</div>
    </div>

    <!-- Card 6: Sites Conectados -->
    <div class="stat-card" style="--accent-color: #4ade80;">
        <div class="stat-label">Sites Conectados</div>
        <div class="stat-value">{{ $sitesConectados ?? 0 }}</div>
        <div class="stat-icon">⚡</div>
    </div>

    <!-- Card 7: Sites Com Problema -->
    <div class="stat-card" style="--accent-color: #f87171;">
        <div class="stat-label">Com Problema</div>
        <div class="stat-value">{{ $sitesProblema ?? 0 }}</div>
        <div class="stat-icon">⚠️</div>
    </div>

    <!-- Card 8: Backups Realizados -->
    <div class="stat-card" style="--accent-color: #38bdf8;">
        <div class="stat-label">Backups Salvos</div>
        <div class="stat-value">{{ $totalBackups ?? 0 }}</div>
        <div class="stat-icon">💾</div>
    </div>
</div>


<!-- 🎯 Objetivos do Dia & To-Do List -->
<div class="card" style="margin-bottom: 28px;">
    <div class="card-header" style="margin-bottom: 16px;">
        <div>
            <div class="card-title" style="display: flex; align-items: center; gap: 8px; font-size: 16px;">
                <span>🎯 Objetivos & Metas do Dia</span>
                @php 
                    $totalTarefas = $tarefasHoje->count();
                    $concluidas = $tarefasHoje->where('concluida', true)->count();
                    $pctConcluido = $totalTarefas > 0 ? round(($concluidas / $totalTarefas) * 100) : 0;
                @endphp
                <span class="badge {{ $pctConcluido == 100 ? 'badge-green' : 'badge-blue' }}" style="font-size: 12px;">
                    {{ $concluidas }}/{{ $totalTarefas }} Concluídos ({{ $pctConcluido }}%)
                </span>
            </div>
            <div style="font-size: 12px; color: var(--muted); margin-top: 4px;">
                Acompanhe a prospecção via WhatsApp, Google Maps, follow-ups de orçamentos e upsells do dia.
            </div>
        </div>
    </div>

    <!-- Barra de Progresso das Tarefas -->
    <div style="width: 100%; height: 6px; background: var(--bg3); border-radius: 10px; overflow: hidden; margin-bottom: 20px;">
        <div style="width: {{ $pctConcluido }}%; height: 100%; background: linear-gradient(90deg, #4ade80, #6c63ff); border-radius: 10px; transition: width 0.3s;"></div>
    </div>

    <!-- Formulário Rápido de Adição -->
    <form method="POST" action="{{ route('tarefas.store') }}" style="display: flex; gap: 10px; margin-bottom: 20px; flex-wrap: wrap;">
        @csrf
        <input type="text" name="titulo" class="form-control" style="flex: 1; min-width: 250px;" placeholder="➕ Digite um novo objetivo do dia..." required>
        <select name="categoria" class="form-control" style="width: auto; min-width: 180px;" required>
            <option value="prospeccao_whatsapp">📱 WhatsApp</option>
            <option value="prospeccao_maps">📍 Google Maps</option>
            <option value="follow_up">🔄 Follow-Up</option>
            <option value="upsell">🚀 Upsell</option>
            <option value="geral">📝 Geral</option>
        </select>
        <button type="submit" class="btn btn-primary">+ Adicionar</button>
    </form>

    <!-- Lista de Tarefas -->
    <div style="display: flex; flex-direction: column; gap: 8px;">
        @forelse($tarefasHoje as $tarefa)
            <div style="display: flex; align-items: center; justify-content: space-between; padding: 10px 14px; background: var(--bg3); border: 1px solid var(--border); border-radius: 10px; {{ $tarefa->concluida ? 'opacity: 0.6;' : '' }}">
                <div style="display: flex; align-items: center; gap: 12px; flex: 1;">
                    <form method="POST" action="{{ route('tarefas.toggle', $tarefa) }}">
                        @csrf @method('PATCH')
                        <button type="submit" style="background: none; border: none; cursor: pointer; font-size: 18px; line-height: 1;">
                            {{ $tarefa->concluida ? '✅' : '⬜' }}
                        </button>
                    </form>
                    
                    <span style="font-size: 14px; {{ $tarefa->concluida ? 'text-decoration: line-through; color: var(--muted);' : 'color: var(--text);' }}">
                        {{ $tarefa->titulo }}
                    </span>

                    @switch($tarefa->categoria)
                        @case('prospeccao_whatsapp')
                            <span class="badge badge-blue">📱 WhatsApp</span>
                            @break
                        @case('prospeccao_maps')
                            <span class="badge badge-green">📍 Maps</span>
                            @break
                        @case('follow_up')
                            <span class="badge badge-yellow">🔄 Follow-Up</span>
                            @break
                        @case('upsell')
                            <span class="badge badge-blue" style="background: rgba(167,139,250,0.15); color: #a78bfa;">🚀 Upsell</span>
                            @break
                        @default
                            <span class="badge badge-gray">📝 Geral</span>
                    @endswitch
                </div>

                <form method="POST" action="{{ route('tarefas.destroy', $tarefa) }}" onsubmit="return confirm('Remover este objetivo?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-danger btn-sm" style="padding: 4px 8px; font-size: 11px;">🗑️</button>
                </form>
            </div>
        @empty
            <div class="empty-state" style="padding: 20px;">
                <p>Nenhum objetivo registrado para hoje.</p>
            </div>
        @endforelse
    </div>
</div>

<div class="grid-2" style="gap: 24px;">
    <!-- Pagamentos Pendentes -->
    <div class="card">
        <div class="card-header">
            <div class="card-title">💳 Pagamentos Pendentes ({{ $pagamentosPendentes->count() }})</div>
            <a href="{{ route('pagamentos.index', ['status' => 'pendente']) }}" class="btn btn-secondary btn-sm">Ver todos</a>
        </div>
        @if($pagamentosPendentes->isEmpty())
            <div class="empty-state">
                <div class="icon">✅</div>
                <p>Nenum pagamento pendente</p>
            </div>
        @else
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Cliente</th>
                            <th>Valor</th>
                            <th>Vencimento</th>
                            <th>Status</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($pagamentosPendentes as $p)
                        <tr>
                            <td>{{ $p->cliente->nome ?? 'N/A' }}</td>
                            <td>R$ {{ number_format($p->valor, 2, ',', '.') }}</td>
                            <td>{{ $p->data_vencimento ? \Carbon\Carbon::parse($p->data_vencimento)->format('d/m/Y') : '-' }}</td>
                            <td>
                                @if($p->status === 'atrasado')
                                    <span class="badge badge-red">Atrasado</span>
                                @else
                                    <span class="badge badge-yellow">Pendente</span>
                                @endif
                            </td>
                            <td>
                                <div style="display: flex; gap: 6px;">
                                    <a href="{{ route('pagamentos.show', $p->id) }}" class="btn btn-secondary btn-sm">Ver</a>
                                    @if($p->status !== 'pago')
                                        <form method="POST" action="{{ route('pagamentos.pagar', $p) }}" style="display:inline;">
                                            @csrf @method('PATCH')
                                            <button type="submit" class="btn btn-success btn-sm">✓ Pagar</button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    <!-- Renovações Próximas -->
    <div class="card">
        <div class="card-header">
            <div class="card-title">🔄 Renovações nos próximos 30 dias ({{ $renovacoesProximas->count() }})</div>
            <a href="{{ route('sites.index') }}" class="btn btn-secondary btn-sm">Ver sites</a>
        </div>
        @if($renovacoesProximas->isEmpty())
            <div class="empty-state">
                <div class="icon">🗓️</div>
                <p>Nenhuma renovação próxima</p>
            </div>
        @else
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Site</th>
                            <th>Cliente</th>
                            <th>Renovação</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($renovacoesProximas as $site)
                        <tr>
                            <td>
                                <a href="{{ route('sites.show', $site) }}" style="color: #6c63ff; text-decoration: none;">
                                    {{ $site->nome_site }}
                                </a>
                            </td>
                            <td>{{ $site->cliente->nome ?? 'N/A' }}</td>
                            <td>
                                @php $dias = now()->diffInDays($site->data_renovacao, false) @endphp
                                <span class="badge {{ $dias <= 7 ? 'badge-red' : 'badge-yellow' }}">
                                    {{ $site->data_renovacao ? \Carbon\Carbon::parse($site->data_renovacao)->format('d/m/Y') : '-' }}
                                    ({{ $dias }}d)
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
@endsection
