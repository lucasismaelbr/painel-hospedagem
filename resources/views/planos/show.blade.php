@extends('layouts.app')
@section('title', $plano->nome) @section('page-title', $plano->nome) @section('page-subtitle', 'Detalhes do plano')
@section('topbar-actions')
<div style="display:flex;gap:10px;">
    <a href="{{ route('planos.edit', $plano) }}" class="btn btn-secondary">✏️ Editar</a>
    <a href="{{ route('planos.index') }}" class="btn btn-secondary">← Voltar</a>
</div>
@endsection
@section('content')
<div class="grid-2" style="gap: 24px;">
    <div class="card">
        <div class="card-title" style="margin-bottom: 16px;">📋 Informações do Plano</div>
        <table style="font-size:14px;">
            <tr><td style="color:#64748b;padding:6px 12px 6px 0;width:130px;">Preço mensal</td><td>R$ {{ number_format($plano->preco_mensal, 2, ',', '.') }}</td></tr>
            <tr><td style="color:#64748b;padding:6px 12px 6px 0;">Preço anual</td><td>{{ $plano->preco_anual ? 'R$ '.number_format($plano->preco_anual,2,',','.') : '—' }}</td></tr>
            <tr><td style="color:#64748b;padding:6px 12px 6px 0;vertical-align:top;">Descrição</td><td>{{ $plano->descricao ?? '—' }}</td></tr>
        </table>
        @if($plano->recursos)
            <div style="margin-top:16px;">
                <div style="font-size:13px;color:#64748b;margin-bottom:8px;">Recursos inclusos:</div>
                @foreach($plano->recursos as $r)
                    <div style="padding:4px 0;font-size:13px;">✅ {{ $r }}</div>
                @endforeach
            </div>
        @endif
    </div>
    <div class="card">
        <div class="card-title" style="margin-bottom:16px;">🌐 Sites usando este plano ({{ $plano->sites->count() }})</div>
        @forelse($plano->sites as $site)
            <div style="padding:8px 0;border-bottom:1px solid #2a3248;font-size:14px;">
                <a href="{{ route('sites.show', $site) }}" style="color:#6c63ff;text-decoration:none;">{{ $site->dominio }}</a>
                <span style="color:#64748b;font-size:12px;margin-left:8px;">— {{ $site->cliente->nome }}</span>
            </div>
        @empty
            <div style="color:#64748b;font-size:13px;">Nenhum site usando este plano</div>
        @endforelse
    </div>
</div>
@endsection
