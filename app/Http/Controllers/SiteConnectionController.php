<?php

namespace App\Http\Controllers;

use App\Models\Log;
use App\Models\Site;
use App\Models\SiteConnection;
use App\Services\Remote\RemoteConnectorFactory;
use Illuminate\Http\Request;
use Throwable;

class SiteConnectionController extends Controller
{
    /**
     * Salva ou atualiza as configurações de conexão de um site.
     */
    public function storeOrUpdate(Request $request, Site $site)
    {
        $validated = $request->validate([
            'type'      => ['required', 'in:sftp,ftp,ftps,ssh,api'],
            'host'      => ['required', 'string', 'max:255'],
            'port'      => ['required', 'integer', 'min:1', 'max:65535'],
            'username'  => ['required', 'string', 'max:255'],
            'credential'=> ['nullable', 'string'], // Senha ou Chave SSH
            'passphrase'=> ['nullable', 'string'],
            'root_path' => ['required', 'string', 'max:255'],
        ]);

        $connection = $site->connection ?: new SiteConnection(['site_id' => $site->id]);

        $connection->type      = $validated['type'];
        $connection->host      = trim($validated['host']);
        $connection->port      = (int) $validated['port'];
        $connection->username  = trim($validated['username']);
        $connection->root_path = '/' . trim($validated['root_path'], '/');

        // Se uma nova credencial foi informada, atualiza criptografado
        if (!empty($validated['credential'])) {
            $connection->setCredential($validated['credential']);
        }

        if (isset($validated['passphrase'])) {
            $connection->setPassphrase($validated['passphrase']);
        }

        // Se é uma nova conexão sem credencial, exige credencial
        if (!$connection->exists && empty($validated['credential'])) {
            return back()->withErrors(['credential' => 'A senha ou chave privada é obrigatória para novas conexões.']);
        }

        $connection->save();

        // Executa teste automático de conexão
        $testResult = $this->runConnectionTest($connection);

        Log::registrar('configurou_conexao', 'sites', $site->id);

        if ($testResult['success']) {
            return back()->with('sucesso', 'Configurações salvas e conexão estabelecida com sucesso!');
        }

        return back()->with('aviso', 'Configurações salvas, porém o teste de conexão falhou: ' . $testResult['message']);
    }

    /**
     * Testa a conexão sob demanda e retorna JSON.
     */
    public function test(Site $site)
    {
        $connection = $site->connection;
        if (!$connection) {
            return response()->json([
                'success' => false,
                'message' => 'Este site ainda não possui conexão configurada.',
            ], 422);
        }

        $result = $this->runConnectionTest($connection);
        Log::registrar('testou_conexao', 'sites', $site->id);

        return response()->json($result);
    }

    /**
     * Executa o teste de conexão e atualiza o estado no banco de dados.
     */
    protected function runConnectionTest(SiteConnection $connection): array
    {
        try {
            $connector = RemoteConnectorFactory::make($connection);
            $result = $connector->testConnection();

            $connection->status = $result['success'] ? 'conectado' : 'erro';
            $connection->latency_ms = $result['latency_ms'];
            $connection->error_message = $result['success'] ? null : $result['message'];
            $connection->last_check_at = now();
            $connection->save();

            return $result;
        } catch (Throwable $e) {
            $connection->status = 'erro';
            $connection->latency_ms = null;
            $connection->error_message = $e->getMessage();
            $connection->last_check_at = now();
            $connection->save();

            return [
                'success' => false,
                'latency_ms' => null,
                'message' => $e->getMessage(),
            ];
        }
    }
}
