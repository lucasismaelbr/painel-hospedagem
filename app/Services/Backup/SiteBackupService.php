<?php

namespace App\Services\Backup;

use App\Models\Log;
use App\Models\Site;
use App\Models\SiteBackup;
use App\Services\Remote\RemoteConnectorFactory;
use App\Services\Storage\LocalStorageProvider;
use Exception;
use ZipArchive;

class SiteBackupService
{
    protected LocalStorageProvider $storage;

    public function __construct(?LocalStorageProvider $storage = null)
    {
        $this->storage = $storage ?: new LocalStorageProvider();
    }

    /**
     * Cria um backup completo dos arquivos remotos do site.
     */
    public function createBackup(Site $site, string $type = 'manual'): SiteBackup
    {
        $connection = $site->connection;
        if (!$connection) {
            throw new Exception("O site '{$site->dominio}' não possui conexão SFTP/FTP configurada.");
        }

        $filename = 'backup_' . preg_replace('/[^a-zA-Z0-9_\-]/', '_', $site->dominio) . '_' . date('Ymd_His') . '.zip';

        $backup = SiteBackup::create([
            'site_id' => $site->id,
            'user_id' => auth()->id(),
            'type' => $type,
            'filename' => $filename,
            'file_size_bytes' => 0,
            'storage_driver' => 'local',
            'status' => 'running',
        ]);

        try {
            $connector = RemoteConnectorFactory::make($connection);
            $zipPath = $this->storage->getPath($filename);

            $zip = new ZipArchive();
            if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
                throw new Exception("Não foi possível criar o arquivo ZIP local em $zipPath");
            }

            // Baixa e adiciona arquivos ao ZIP recursivamente
            $this->addRemoteFilesToZip($connector, '/', $zip);
            $zip->close();

            $fileSize = filesize($zipPath);
            $backup->update([
                'status' => 'completed',
                'file_size_bytes' => $fileSize,
            ]);

            Log::registrar('criou_backup', 'sites', $site->id);

            // Aplica política de retenção de backups (mantém últimos 10)
            $this->applyRetentionPolicy($site);

            return $backup;
        } catch (Exception $e) {
            $backup->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);

            Log::registrar('falha_backup', 'sites', $site->id);
            throw $e;
        }
    }

    /**
     * Restaura um backup selecionado para o site remoto.
     */
    public function restoreBackup(Site $site, SiteBackup $backup): bool
    {
        if ($backup->status !== 'completed') {
            throw new Exception('Apenas backups concluídos podem ser restaurados.');
        }

        $zipPath = $this->storage->getPath($backup->filename);
        if (!file_exists($zipPath)) {
            throw new Exception('O arquivo físico do backup não existe no armazenamento.');
        }

        // Pass 1: Cria backup de segurança pré-restauração
        try {
            $this->createBackup($site, 'manual');
        } catch (Exception $e) {
            // Loga mas prossegue se falhar o snapshot
            Log::registrar('falha_snapshot_prerestauracao', 'sites', $site->id);
        }

        // Pass 2: Descompacta e envia os arquivos de volta ao servidor remoto
        $tempExtractDir = storage_path('app/temp_extract_' . time());
        mkdir($tempExtractDir, 0777, true);

        $zip = new ZipArchive();
        if ($zip->open($zipPath) !== true) {
            throw new Exception('Falha ao abrir o arquivo ZIP para restauração.');
        }

        $zip->extractTo($tempExtractDir);
        $zip->close();

        try {
            $connector = RemoteConnectorFactory::make($site->connection);
            $this->uploadDirectoryRecursive($connector, $tempExtractDir, '');

            Log::registrar('restaurou_backup', 'sites', $site->id);
            return true;
        } finally {
            $this->deleteLocalDirRecursive($tempExtractDir);
        }
    }

    /**
     * Adiciona arquivos remotos recursivamente ao ZIP.
     */
    protected function addRemoteFilesToZip($connector, string $remoteDir, ZipArchive $zip): void
    {
        $data = $connector->listFiles($remoteDir);

        foreach ($data['items'] as $item) {
            if ($item['is_dir']) {
                $zip->addEmptyDir(ltrim($item['path'], '/'));
                $this->addRemoteFilesToZip($connector, $item['path'], $zip);
            } else {
                try {
                    $content = $connector->readFile($item['path']);
                    $zip->addFromString(ltrim($item['path'], '/'), $content);
                } catch (Exception $ignored) {}
            }
        }
    }

    /**
     * Envia pasta local recursivamente para o servidor remoto.
     */
    protected function uploadDirectoryRecursive($connector, string $localDir, string $remoteSubDir): void
    {
        $files = scandir($localDir);
        foreach ($files as $file) {
            if ($file === '.' || $file === '..') continue;

            $localPath = $localDir . '/' . $file;
            $remotePath = rtrim($remoteSubDir, '/') . '/' . $file;

            if (is_dir($localPath)) {
                $connector->createDirectory($remotePath);
                $this->uploadDirectoryRecursive($connector, $localPath, $remotePath);
            } else {
                $connector->uploadFile($remotePath, $localPath);
            }
        }
    }

    /**
     * Limpa backups antigos excedentes.
     */
    protected function applyRetentionPolicy(Site $site): void
    {
        $backups = SiteBackup::where('site_id', $site->id)
            ->where('status', 'completed')
            ->orderBy('created_at', 'desc')
            ->get();

        if ($backups->count() > 10) {
            $oldBackups = $backups->slice(10);
            foreach ($oldBackups as $old) {
                $this->storage->delete($old->filename);
                $old->delete();
            }
        }
    }

    protected function deleteLocalDirRecursive(string $dir): void
    {
        if (!is_dir($dir)) return;
        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            (is_dir("$dir/$file")) ? $this->deleteLocalDirRecursive("$dir/$file") : @unlink("$dir/$file");
        }
        @rmdir($dir);
    }
}
