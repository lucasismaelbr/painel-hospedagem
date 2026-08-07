@extends('layouts.app')
@section('title', 'Clientes')
@section('page-title', 'Clientes')
@section('page-subtitle', 'Gerencie seus clientes')
@section('topbar-actions')
    <a href="{{ route('clientes.create') }}" class="btn btn-primary">+ Novo Cliente</a>
@endsection

@section('content')
<div class="card">
    <!-- Filtros -->
    <form method="GET" class="filters">
        <input type="text" name="busca" class="form-control" placeholder="🔍 Buscar por nome, e-mail..." value="{{ request('busca') }}">
        <select name="status" class="form-control">
            <option value="">Todos os status</option>
            <option value="prospect"  {{ request('status') === 'prospect'  ? 'selected' : '' }}>Prospect</option>
            <option value="ativo"     {{ request('status') === 'ativo'     ? 'selected' : '' }}>Ativo</option>
            <option value="inativo"   {{ request('status') === 'inativo'   ? 'selected' : '' }}>Inativo</option>
        </select>
        <button type="submit" class="btn btn-secondary">Filtrar</button>
        @if(request()->hasAny(['busca','status']))
            <a href="{{ route('clientes.index') }}" class="btn btn-secondary">✕ Limpar</a>
        @endif
    </form>

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Nome</th>
                    <th>E-mail</th>
                    <th>Empresa</th>
                    <th>Telefone</th>
                    <th>Status</th>
                    <th>Sites</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                @forelse($clientes as $cliente)
                <tr>
                    <td style="color: #64748b;">{{ $cliente->id }}</td>
                    <td><a href="{{ route('clientes.show', $cliente) }}" style="color: #6c63ff; text-decoration: none; font-weight: 500;">{{ $cliente->nome }}</a></td>
                    <td style="color: #94a3b8;">{{ $cliente->email ?? '—' }}</td>
                    <td>{{ $cliente->empresa ?? '—' }}</td>
                    <td>{{ $cliente->telefone ?? '—' }}</td>
                    <td>
                        @if($cliente->status === 'ativo')
                            <span class="badge badge-green">Ativo</span>
                        @elseif($cliente->status === 'inativo')
                            <span class="badge badge-gray">Inativo</span>
                        @else
                            <span class="badge badge-yellow">Prospect</span>
                        @endif
                    </td>
                    <td>{{ $cliente->sites_count ?? $cliente->sites->count() }}</td>
                    <td>
                        <a href="{{ route('clientes.show', $cliente) }}" class="btn btn-secondary btn-sm">Ver</a>
                        <a href="{{ route('clientes.edit', $cliente) }}" class="btn btn-secondary btn-sm">✏️</a>
                        <form method="POST" action="{{ route('clientes.destroy', $cliente) }}" style="display:inline;" onsubmit="return confirm('Excluir cliente?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm">🗑️</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8">
                        <div class="empty-state">
                            <div class="icon">👥</div>
                            <p>Nenhum cliente encontrado</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $clientes->links() }}
</div>
@endsection
