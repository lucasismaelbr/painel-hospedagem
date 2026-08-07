<?php

namespace App\Http\Controllers;

use App\Models\Log;
use App\Models\Plano;
use Illuminate\Http\Request;

class PlanoController extends Controller
{
    public function index()
    {
        $planos = Plano::withCount('sites')->orderBy('preco_mensal')->get();
        return view('planos.index', compact('planos'));
    }

    public function create()
    {
        return view('planos.create');
    }

    public function store(Request $request)
    {
        $dados = $this->validar($request);
        $plano = Plano::create($dados);
        Log::registrar('criou', 'planos', $plano->id);

        return redirect()->route('planos.index')->with('sucesso', 'Plano criado!');
    }

    public function show(Plano $plano)
    {
        $plano->load('sites.cliente');
        return view('planos.show', compact('plano'));
    }

    public function edit(Plano $plano)
    {
        return view('planos.edit', compact('plano'));
    }

    public function update(Request $request, Plano $plano)
    {
        $dados = $this->validar($request);
        $plano->update($dados);
        Log::registrar('atualizou', 'planos', $plano->id);

        return redirect()->route('planos.index')->with('sucesso', 'Plano atualizado!');
    }

    public function destroy(Plano $plano)
    {
        Log::registrar('excluiu', 'planos', $plano->id);
        $plano->delete();

        return redirect()->route('planos.index')->with('sucesso', 'Plano excluído.');
    }

    private function validar(Request $request): array
    {
        $dados = $request->validate([
            'nome' => ['required', 'string', 'max:100'],
            'preco_mensal' => ['required', 'numeric', 'min:0'],
            'preco_anual' => ['nullable', 'numeric', 'min:0'],
            'descricao' => ['nullable', 'string'],
            'recursos' => ['nullable', 'string'], // textarea, um recurso por linha
        ]);

        // Converte textarea (um por linha) em array para o cast JSON
        if (!empty($dados['recursos'])) {
            $dados['recursos'] = array_values(array_filter(
                array_map('trim', explode("\n", $dados['recursos']))
            ));
        }

        return $dados;
    }
}
