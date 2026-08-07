<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\Log;
use Illuminate\Http\Request;

class ClienteController extends Controller
{
    public function index(Request $request)
    {
        $clientes = Cliente::query()
            ->when($request->status, fn ($q, $status) => $q->where('status', $status))
            ->when($request->busca, fn ($q, $busca) => $q->where(function ($q) use ($busca) {
                $q->where('nome', 'like', "%{$busca}%")
                  ->orWhere('email', 'like', "%{$busca}%")
                  ->orWhere('empresa', 'like', "%{$busca}%");
            }))
            ->orderBy('nome')
            ->paginate(20)
            ->withQueryString();

        return view('clientes.index', compact('clientes'));
    }

    public function create()
    {
        return view('clientes.create');
    }

    public function store(Request $request)
    {
        $dados = $request->validate([
            'nome' => ['required', 'string', 'max:150'],
            'email' => ['nullable', 'email', 'max:150'],
            'telefone' => ['nullable', 'string', 'max:20'],
            'empresa' => ['nullable', 'string', 'max:150'],
            'cnpj_cpf' => ['nullable', 'string', 'max:18', 'unique:clientes,cnpj_cpf'],
            'endereco' => ['nullable', 'string'],
            'status' => ['required', 'in:prospect,ativo,inativo'],
            'origem_lead' => ['nullable', 'string', 'max:100'],
        ]);

        $cliente = Cliente::create($dados);
        Log::registrar('criou', 'clientes', $cliente->id);

        return redirect()->route('clientes.show', $cliente)
            ->with('sucesso', 'Cliente cadastrado com sucesso!');
    }

    public function show(Cliente $cliente)
    {
        $cliente->load(['sites.plano', 'pagamentos' => fn ($q) => $q->latest('data_vencimento')]);
        return view('clientes.show', compact('cliente'));
    }

    public function edit(Cliente $cliente)
    {
        return view('clientes.edit', compact('cliente'));
    }

    public function update(Request $request, Cliente $cliente)
    {
        $dados = $request->validate([
            'nome' => ['required', 'string', 'max:150'],
            'email' => ['nullable', 'email', 'max:150'],
            'telefone' => ['nullable', 'string', 'max:20'],
            'empresa' => ['nullable', 'string', 'max:150'],
            'cnpj_cpf' => ['nullable', 'string', 'max:18', 'unique:clientes,cnpj_cpf,' . $cliente->id],
            'endereco' => ['nullable', 'string'],
            'status' => ['required', 'in:prospect,ativo,inativo'],
            'origem_lead' => ['nullable', 'string', 'max:100'],
        ]);

        $cliente->update($dados);
        Log::registrar('atualizou', 'clientes', $cliente->id);

        return redirect()->route('clientes.show', $cliente)
            ->with('sucesso', 'Cliente atualizado!');
    }

    public function destroy(Cliente $cliente)
    {
        Log::registrar('excluiu', 'clientes', $cliente->id);
        $cliente->delete();

        return redirect()->route('clientes.index')
            ->with('sucesso', 'Cliente excluído.');
    }
}
