@extends('layouts.app')
@section('title', 'Sites') @section('page-title', 'Sites') @section('page-subtitle', 'Todos os sites gerenciados')
@section('topbar-actions') <a href="{{ route('sites.create') }}" class="btn btn-primary">+ Novo Site</a> @endsection

@section('content')
<div class="card">
    <form method="GET" class="filters">
        <input type="text" name="busca" class="form-control" placeholder="🔍 Domínio ou nome..." value="{{ request('busca') }}">
        <select name="status" class="form-control">
            <option value="">Todos</option>
            <option value="ativo"         {{ request('status') === 'ativo'         ? 'selected':'' }}>Ativo</option>
            <option value="em_construcao" {{ request('status') === 'em_construcao' ? 'selected':'' }}>Em construção</option>
            <option value="suspenso"      {{ request('status') === 'suspenso'      ? 'selected':'' }}>Suspenso</option>
        </select>
        <button type="submit" class="btn btn-secondary">Filtrar</button>
        @if(request()->hasAny(['busca','status'])) <a href="{{ route('sites.index') }}" class="btn btn-secondary">✕</a> @endif
    </form>
    <div class="table-wrap">
        <table>
            <thead><tr><th>Domínio</th><th>Cliente</th><th>Plano</th><th>Status</th><th>Renovação</th><th>Ações</th></tr></thead>
            <tbody>
                @forelse($sites as $site)
                <tr>
                    <td><a href="{{ route('sites.show', $site) }}" style="color:#6c63ff;text-decoration:none;font-weight:500;">{{ $site->dominio }}</a>
                        <div style="font-size:12px;color:#64748b;">{{ $site->nome_site }}</div></td>
                    <td><a href="{{ route('clientes.show', $site->cliente) }}" style="color:#94a3b8;text-decoration:none;">{{ $site->cliente->nome }}</a></td>
                    <td>{{ $site->plano->nome ?? '—' }}</td>
                    <td>
                        @if($site->status==='ativo') <span class="badge badge-green">Ativo</span>
                        @elseif($site->status==='suspenso') <span class="badge badge-red">Suspenso</span>
                        @else <span class="badge badge-yellow">Em construção</span> @endif
                    </td>
                    <td>{{ $site->data_renovacao?->format('d/m/Y') ?? '—' }}</td>
                    <td>
                        <a href="{{ route('sites.edit', $site) }}" class="btn btn-secondary btn-sm">✏️</a>
                        <form method="POST" action="{{ route('sites.destroy', $site) }}" style="display:inline;" onsubmit="return confirm('Excluir site?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm">🗑️</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6"><div class="empty-state"><div class="icon">🌐</div><p>Nenhum site</p></div></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $sites->links() }}
</div>
@endsection
