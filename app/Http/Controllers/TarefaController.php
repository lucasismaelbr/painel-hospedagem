<?php

namespace App\Http\Controllers;

use App\Models\Tarefa;
use Illuminate\Http\Request;

class TarefaController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'titulo' => 'required|string|max:255',
            'categoria' => 'required|in:prospeccao_whatsapp,prospeccao_maps,follow_up,upsell,geral',
        ]);

        Tarefa::create([
            'user_id' => auth()->id(),
            'titulo' => $request->titulo,
            'categoria' => $request->categoria,
            'concluida' => false,
            'data_objetivo' => now()->toDateString(),
        ]);

        return back()->with('sucesso', 'Objetivo adicionado com sucesso!');
    }

    public function toggle(Tarefa $tarefa)
    {
        if ($tarefa->user_id !== auth()->id()) {
            abort(403);
        }

        $tarefa->update([
            'concluida' => !$tarefa->concluida,
        ]);

        return back()->with('sucesso', $tarefa->concluida ? 'Objetivo concluído!' : 'Objetivo marcado como pendente.');
    }

    public function destroy(Tarefa $tarefa)
    {
        if ($tarefa->user_id !== auth()->id()) {
            abort(403);
        }

        $tarefa->delete();

        return back()->with('sucesso', 'Objetivo removido.');
    }
}
