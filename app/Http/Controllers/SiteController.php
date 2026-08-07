<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\Log;
use App\Models\Plano;
use App\Models\Site;
use Illuminate\Http\Request;

class SiteController extends Controller
{
    public function index(Request $request)
    {
        $sites = Site::with(['cliente', 'plano'])
            ->when($request->status, fn ($q, $status) => $q->where('status', $status))
            ->when($request->busca, fn ($q, $busca) => $q->where('dominio', 'like', "%{$busca}%")
                ->orWhere('nome_site', 'like', "%{$busca}%"))
            ->orderBy('nome_site')
            ->paginate(20)
            ->withQueryString();

        return view('sites.index', compact('sites'));
    }

    public function create()
    {
        return view('sites.create', [
            'clientes' => Cliente::orderBy('nome')->get(['id', 'nome']),
            'planos' => Plano::orderBy('nome')->get(['id', 'nome']),
        ]);
    }

    public function store(Request $request)
    {
        $dados = $this->validar($request);
        $site = Site::create($dados);
        Log::registrar('criou', 'sites', $site->id);

        return redirect()->route('sites.show', $site)->with('sucesso', 'Site cadastrado!');
    }

    public function show(Site $site)
    {
        $site->load(['cliente', 'plano', 'pagamentos']);
        return view('sites.show', compact('site'));
    }

    public function edit(Site $site)
    {
        return view('sites.edit', [
            'site' => $site,
            'clientes' => Cliente::orderBy('nome')->get(['id', 'nome']),
            'planos' => Plano::orderBy('nome')->get(['id', 'nome']),
        ]);
    }

    public function update(Request $request, Site $site)
    {
        $dados = $this->validar($request, $site->id);
        $site->update($dados);
        Log::registrar('atualizou', 'sites', $site->id);

        return redirect()->route('sites.show', $site)->with('sucesso', 'Site atualizado!');
    }

    public function destroy(Site $site)
    {
        Log::registrar('excluiu', 'sites', $site->id);
        $site->delete();

        return redirect()->route('sites.index')->with('sucesso', 'Site excluído.');
    }

    private function validar(Request $request, ?int $ignorarId = null): array
    {
        return $request->validate([
            'cliente_id' => ['required', 'exists:clientes,id'],
            'plano_id' => ['nullable', 'exists:planos,id'],
            'dominio' => ['required', 'string', 'max:255', 'unique:sites,dominio,' . $ignorarId],
            'nome_site' => ['required', 'string', 'max:150'],
            'status' => ['required', 'in:em_construcao,ativo,suspenso'],
            'data_publicacao' => ['nullable', 'date'],
            'url_vercel' => ['nullable', 'url', 'max:255'],
            'data_renovacao' => ['nullable', 'date'],
            'observacoes' => ['nullable', 'string'],
        ]);
    }
}
