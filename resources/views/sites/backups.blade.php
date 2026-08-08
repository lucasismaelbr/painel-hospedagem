@extends('layouts.app')

@section('title', 'Backups de ' . $site->dominio)
@section('page-title', 'Gerenciador de Backups: ' . $site->dominio)
@section('page-subtitle', 'Histórico e criação de backups dos arquivos da hospedagem')

@section('topbar-actions')
<div style="display:flex;gap:10px;">
    <a href="{{ route('sites.manager', $site) }}" class="btn btn-primary">⚡ Gerenciador de Arquivos</a>
    <a href="{{ route('sites.show', $site) }}" class="btn btn-secondary">← Voltar para o Site</a>
</div>
@endsection

@section('content')
<!-- Top Actions & Summary Card -->
<div class="card" style="margin-bottom: 24px;">
    <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 16px;">
        <div>
            <div style="font-size: 18px; font-weight: 700; color: var(--text);">💾 Backups de {{ $site->nome_site }}</div>
            <div style="font-size: 13px; color: var(--muted); margin-top: 4px;">
                Os backups são armazenados com cópia de segurança antes de qualquer restauração.
            </div>
        </div>

        <form method="POST" action="{{ route('sites.backups.create', $site) }}" onsubmit="return confirm('Iniciar a criação de um novo backup dos arquivos remotos agora?')">
            @csrf
            <button type="submit" class="btn btn-primary" style="padding: 10px 20px;">
                📦 + Criar Backup Agora
            </button>
        </form>
    </div>
</div>

<!-- Backups Table -->
<div class="card">
    <div class="card-header" style="margin-bottom: 16px;">
        <div class="card-title">📋 Backups Disponíveis ({{ $backups->count() }})</div>
    </div>

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Data & Hora</th>
                    <th>Tipo</th>
                    <th>Arquivo</th>
                    <th>Tamanho</th>
                    <th>Status</th>
                    <th style="text-align: right;">Ações</th>
                </tr>
            </thead>
            <tbody>
                @forelse($backups as $backup)
                    <tr>
                        <td>
                            <div style="font-weight: 600; color: var(--text);">{{ $backup->created_at->format('d/m/Y H:i:s') }}</div>
                            <div style="font-size: 11px; color: var(--muted);">Criado por: {{ $backup->user->nome ?? 'Sistema' }}</div>
                        </td>
                        <td>
                            @if($backup->type === 'automatico')
                                <span class="badge badge-blue">Automático</span>
                            @else
                                <span class="badge badge-purple" style="background: rgba(108,99,255,0.15); color: #6c63ff;">Manual</span>
                            @endif
                        </td>
                        <td><span style="font-family: monospace; font-size: 12px; color: var(--text);">{{ $backup->filename }}</span></td>
                        <td><span style="font-weight: 600; color: var(--text);">{{ $backup->formatted_size }}</span></td>
                        <td>
                            @if($backup->status === 'completed')
                                <span class="badge badge-green">Concluído</span>
                            @elseif($backup->status === 'running')
                                <span class="badge badge-yellow">Em Andamento...</span>
                            @else
                                <span class="badge badge-red" title="{{ $backup->error_message }}">Falhou</span>
                            @endif
                        </td>
                        <td style="text-align: right;">
                            <div style="display: flex; gap: 6px; justify-content: flex-end;">
                                @if($backup->status === 'completed')
                                    <form method="POST" action="{{ route('sites.backups.restore', [$site, $backup]) }}" onsubmit="return confirm('⚠️ ATENÇÃO: Tem certeza que deseja restaurar este backup no site? Um backup de segurança do estado atual será criado automaticamente antes da restauração.')">
                                        @csrf
                                        <button type="submit" class="btn btn-secondary btn-sm" title="Restaurar este backup">
                                            🔄 Restaurar
                                        </button>
                                    </form>

                                    <a href="{{ route('sites.backups.download', [$site, $backup]) }}" class="btn btn-outline btn-sm" title="Baixar arquivo ZIP">
                                        ⬇️ Baixar
                                    </a>
                                @endif

                                <form method="POST" action="{{ route('sites.backups.destroy', [$site, $backup]) }}" onsubmit="return confirm('Tem certeza que deseja excluir este arquivo de backup?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm" title="Excluir backup">
                                        🗑️ Excluir
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" style="text-align: center; color: var(--muted); padding: 40px;">
                            <div style="font-size: 32px; margin-bottom: 8px;">📦</div>
                            <div>Nenhum backup encontrado para este site.</div>
                            <div style="font-size: 12px; margin-top: 4px;">Clique em "+ Criar Backup Agora" para gerar a primeira cópia.</div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
