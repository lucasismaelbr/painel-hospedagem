@extends('layouts.app')
@section('title', 'Planos')
@section('page-title', 'Planos de Hospedagem')
@section('page-subtitle', 'Gerencie os planos disponíveis')
@section('topbar-actions')
    <a href="{{ route('planos.create') }}" class="btn btn-primary">+ Novo Plano</a>
@endsection

@section('content')
<div class="card">
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Nome</th>
                    <th>Preço Mensal</th>
                    <th>Preço Anual</th>
                    <th>Sites Usando</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                @forelse($planos as $plano)
                <tr>
                    <td>
                        <a href="{{ route('planos.show', $plano) }}" style="color: #6c63ff; text-decoration: none; font-weight: 500;">
                            {{ $plano->nome }}
                        </a>
                        @if($plano->descricao)
                            <div style="font-size: 12px; color: #64748b; margin-top: 2px;">{{ Str::limit($plano->descricao, 60) }}</div>
                        @endif
                    </td>
                    <td>R$ {{ number_format($plano->preco_mensal, 2, ',', '.') }}/mês</td>
                    <td>{{ $plano->preco_anual ? 'R$ ' . number_format($plano->preco_anual, 2, ',', '.') . '/ano' : '—' }}</td>
                    <td><span class="badge badge-blue">{{ $plano->sites_count }} sites</span></td>
                    <td>
                        <a href="{{ route('planos.edit', $plano) }}" class="btn btn-secondary btn-sm">✏️</a>
                        <form method="POST" action="{{ route('planos.destroy', $plano) }}" style="display:inline;" onsubmit="return confirm('Excluir este plano?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm">🗑️</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5">
                        <div class="empty-state"><div class="icon">📋</div><p>Nenhum plano cadastrado</p></div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
