<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\Pagamento;
use App\Models\Site;
use App\Models\Tarefa;

class DashboardController extends Controller
{
    public function index()
    {
        // Cálculo do MRR: soma dos preços mensais dos planos de sites ativos
        $mrr = Site::where('sites.status', 'ativo')
            ->join('planos', 'sites.plano_id', '=', 'planos.id')
            ->sum('planos.preco_mensal');

        // Faturamento do Mês (pagamentos com status pago no mês atual)
        $faturamentoMes = Pagamento::where('status', 'pago')
            ->whereMonth('data_pagamento', now()->month)
            ->whereYear('data_pagamento', now()->year)
            ->sum('valor');

        // Faturamento do Ano (pagamentos com status pago no ano atual)
        $faturamentoAno = Pagamento::where('status', 'pago')
            ->whereYear('data_pagamento', now()->year)
            ->sum('valor');

        // Verificar se o usuário já teve tarefas criadas hoje (ativas ou excluídas)
        $jaInicializouHoje = Tarefa::withTrashed()
            ->where('user_id', auth()->id())
            ->whereDate('data_objetivo', now()->toDateString())
            ->exists();

        // Apenas no PRIMEIRO acesso do dia criamos as sugestões automáticas
        if (!$jaInicializouHoje) {
            $sugestoes = [
                ['titulo' => '📱 Mandar 10 mensagens de prospecção no WhatsApp', 'categoria' => 'prospeccao_whatsapp'],
                ['titulo' => '📍 Mapear 5 empresas sem site no Google Maps', 'categoria' => 'prospeccao_maps'],
                ['titulo' => '🔄 Fazer follow-up com 3 clientes em orçamento', 'categoria' => 'follow_up'],
                ['titulo' => '🚀 Oferecer upgrade de Hospedagem / SSL para 2 clientes', 'categoria' => 'upsell'],
            ];

            foreach ($sugestoes as $s) {
                Tarefa::create([
                    'user_id' => auth()->id(),
                    'titulo' => $s['titulo'],
                    'categoria' => $s['categoria'],
                    'concluida' => false,
                    'data_objetivo' => now()->toDateString(),
                ]);
            }
        }

        // Buscar tarefas ativas do dia
        $tarefasHoje = Tarefa::where('user_id', auth()->id())
            ->whereDate('data_objetivo', now()->toDateString())
            ->orderBy('concluida')
            ->orderBy('id', 'asc')
            ->get();

        return view('dashboard', [
            'mrr' => $mrr,
            'faturamentoMes' => $faturamentoMes,
            'faturamentoAno' => $faturamentoAno,
            'totalClientes' => Cliente::ativos()->count(),
            'totalSites' => Site::where('status', 'ativo')->count(),
            'pagamentosPendentes' => Pagamento::whereIn('status', ['pendente', 'atrasado'])
                ->with('cliente')
                ->orderBy('data_vencimento')
                ->take(10)
                ->get(),
            'renovacoesProximas' => Site::renovacaoProxima(30)
                ->with('cliente')
                ->orderBy('data_renovacao')
                ->get(),
            'tarefasHoje' => $tarefasHoje,
        ]);
    }
}
