<?php

namespace App\Http\Controllers;

use App\Models\Log;
use App\Models\Site;
use App\Models\SiteBackup;
use App\Services\Backup\SiteBackupService;
use App\Services\Storage\LocalStorageProvider;
use Illuminate\Http\Request;
use Throwable;

class SiteBackupController extends Controller
{
    protected SiteBackupService $backupService;

    public function __construct(SiteBackupService $backupService)
    {
        $this->backupService = $backupService;
    }

    /**
     * Exibe a lista de backups do site.
     */
    public function index(Site $site)
    {
        $site->load(['connection', 'backups.user']);

        return view('sites.backups', [
            'site' => $site,
            'backups' => $site->backups,
        ]);
    }

    /**
     * Dispara a criação manual de um novo backup.
     */
    public function store(Site $site)
    {
        try {
            $backup = $this->backupService->createBackup($site, 'manual');
            return back()->with('sucesso', "Backup '{$backup->filename}' criado com sucesso!");
        } catch (Throwable $e) {
            return back()->with('erro', 'Erro ao criar backup: ' . $e->getMessage());
        }
    }

    /**
     * Executa a restauração de um backup com snapshot de segurança prévio.
     */
    public function restore(Site $site, SiteBackup $backup)
    {
        try {
            $this->backupService->restoreBackup($site, $backup);
            return back()->with('sucesso', 'Restauração concluída com sucesso! Um backup de segurança do estado anterior foi criado.');
        } catch (Throwable $e) {
            return back()->with('erro', 'Erro na restauração: ' . $e->getMessage());
        }
    }

    /**
     * Download do arquivo ZIP de backup.
     */
    public function download(Site $site, SiteBackup $backup)
    {
        $storage = new LocalStorageProvider();
        if (!$storage->exists($backup->filename)) {
            return back()->with('erro', 'Arquivo de backup não encontrado no armazenamento.');
        }

        return response()->download($storage->getPath($backup->filename));
    }

    /**
     * Exclui um registro e arquivo de backup.
     */
    public function destroy(Site $site, SiteBackup $backup)
    {
        try {
            $storage = new LocalStorageProvider();
            $storage->delete($backup->filename);
            $backup->delete();

            Log::registrar('excluiu_backup', 'sites', $site->id);

            return back()->with('sucesso', 'Backup excluído com sucesso.');
        } catch (Throwable $e) {
            return back()->with('erro', 'Erro ao excluir backup: ' . $e->getMessage());
        }
    }
}
