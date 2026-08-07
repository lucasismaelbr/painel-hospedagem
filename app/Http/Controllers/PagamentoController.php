<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\Log;
use App\Models\Pagamento;
use App\Models\Site;
use Illuminate\Http\Request;

class PagamentoController extends Controller
{
    public function index(Request $request)
    {
        $pagamentos = Pagamento::with(['cliente', 'site'])
            ->when($request->status, fn ($q, $status) => $q->where('status', $status))
            ->when($request->cliente_id, fn ($q, $id) => $q->where('cliente_id', $id))
            ->orderByDesc('data_vencimento')
            ->paginate(20)
            ->withQueryString();

        return view('pagamentos.index', compact('pagamentos'));
    }

    public function create()
    {
        return view('pagamentos.create', [
            'clientes' => Cliente::orderBy('nome')->get(['id', 'nome']),
            'sites' => Site::orderBy('nome_site')->get(['id', 'nome_site', 'cliente_id']),
        ]);
    }

    public function store(Request $request)
    {
        $dados = $this->validar($request);
        $pagamento = Pagamento::create($dados);
        Log::registrar('criou', 'pagamentos', $pagamento->id);

        return redirect()->route('pagamentos.index')->with('sucesso', 'Pagamento registrado!');
    }

    public function show(Pagamento $pagamento)
    {
        $pagamento->load(['cliente', 'site']);
        return view('pagamentos.show', compact('pagamento'));
    }

    public function edit(Pagamento $pagamento)
    {
        return view('pagamentos.edit', [
            'pagamento' => $pagamento,
            'clientes' => Cliente::orderBy('nome')->get(['id', 'nome']),
            'sites' => Site::orderBy('nome_site')->get(['id', 'nome_site', 'cliente_id']),
        ]);
    }

    public function update(Request $request, Pagamento $pagamento)
    {
        $dados = $this->validar($request);
        $pagamento->update($dados);
        Log::registrar('atualizou', 'pagamentos', $pagamento->id);

        return redirect()->route('pagamentos.index')->with('sucesso', 'Pagamento atualizado!');
    }

    // Ação rápida: marcar como pago
    public function marcarPago(Pagamento $pagamento)
    {
        $pagamento->update([
            'status' => 'pago',
            'data_pagamento' => now(),
        ]);
        Log::registrar('marcou_pago', 'pagamentos', $pagamento->id);

        return back()->with('sucesso', 'Pagamento marcado como pago!');
    }

    public function destroy(Pagamento $pagamento)
    {
        Log::registrar('excluiu', 'pagamentos', $pagamento->id);
        $pagamento->delete();

        return redirect()->route('pagamentos.index')->with('sucesso', 'Pagamento excluído.');
    }

    private function validar(Request $request): array
    {
        return $request->validate([
            'cliente_id' => ['required', 'exists:clientes,id'],
            'site_id' => ['nullable', 'exists:sites,id'],
            'tipo' => ['required', 'in:site,mensalidade,anuidade'],
            'valor' => ['required', 'numeric', 'min:0.01'],
            'status' => ['required', 'in:pendente,pago,atrasado'],
            'data_vencimento' => ['required', 'date'],
            'data_pagamento' => ['nullable', 'date'],
            'metodo_pagamento' => ['nullable', 'string', 'max:50'],
        ]);
    }
}
