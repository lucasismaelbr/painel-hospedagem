@extends('layouts.app')
@section('title', $site->dominio) @section('page-title', $site->nome_site) @section('page-subtitle', $site->dominio)
@section('topbar-actions')
<div style="display:flex;gap:10px;">
    <a href="{{ route('sites.manager', $site) }}" class="btn btn-primary">⚡ Abrir Gerenciador de Arquivos</a>
    <a href="{{ route('sites.edit', $site) }}" class="btn btn-secondary">✏️ Editar</a>
    <a href="{{ route('sites.index') }}" class="btn btn-secondary">← Voltar</a>
</div>
@endsection

@section('content')
<div class="grid-2" style="gap:24px;margin-bottom:24px;">
    <div class="card">
        <div class="card-title" style="margin-bottom:16px;">🌐 Informações do Site</div>
        <table style="font-size:14px;">
            <tr><td style="color:#64748b;padding:6px 12px 6px 0;width:140px;">Cliente</td><td><a href="{{ route('clientes.show', $site->cliente) }}" style="color:#6c63ff;text-decoration:none;">{{ $site->cliente->nome }}</a></td></tr>
            <tr><td style="color:#64748b;padding:6px 12px 6px 0;">Plano</td><td>{{ $site->plano->nome ?? '—' }}</td></tr>
            <tr><td style="color:#64748b;padding:6px 12px 6px 0;">Status</td><td>
                @if($site->status==='ativo') <span class="badge badge-green">Ativo</span>
                @elseif($site->status==='suspenso') <span class="badge badge-red">Suspenso</span>
                @else <span class="badge badge-yellow">Em construção</span> @endif
            </td></tr>
            <tr><td style="color:#64748b;padding:6px 12px 6px 0;">URL Vercel</td><td>{{ $site->url_vercel ? '<a href="'.$site->url_vercel.'" target="_blank" style="color:#6c63ff;">'.$site->url_vercel.'</a>' : '—' }}</td></tr>
            <tr><td style="color:#64748b;padding:6px 12px 6px 0;">Publicação</td><td>{{ $site->data_publicacao?->format('d/m/Y') ?? '—' }}</td></tr>
            <tr><td style="color:#64748b;padding:6px 12px 6px 0;">Renovação</td><td>{{ $site->data_renovacao?->format('d/m/Y') ?? '—' }}</td></tr>
            @if($site->observacoes)
            <tr><td style="color:#64748b;padding:6px 12px 6px 0;vertical-align:top;">Observações</td><td>{{ $site->observacoes }}</td></tr>
            @endif
        </table>
    </div>
    <div class="card">
        <div class="card-title" style="margin-bottom:16px;">💳 Pagamentos ({{ $site->pagamentos->count() }})</div>
        @forelse($site->pagamentos as $pag)
            <div style="display:flex;justify-content:space-between;align-items:center;padding:8px 0;border-bottom:1px solid #2a3248;font-size:13px;">
                <div>{{ $pag->tipo }} — R$ {{ number_format($pag->valor,2,',','.') }}</div>
                <div style="display:flex;align-items:center;gap:8px;">
                    <span style="color:#64748b;">{{ $pag->data_vencimento->format('d/m/Y') }}</span>
                    @if($pag->status==='pago') <span class="badge badge-green">Pago</span>
                    @elseif($pag->status==='atrasado') <span class="badge badge-red">Atrasado</span>
                    @else <span class="badge badge-yellow">Pendente</span> @endif
                </div>
            </div>
        @empty
            <div style="color:#64748b;font-size:13px;">Nenhum pagamento registrado</div>
        @endforelse
    </div>
</div>
@endsection
