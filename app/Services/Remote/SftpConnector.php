<?php

namespace App\Services\Remote;

use App\Models\SiteConnection;
use App\Services\Remote\Contracts\RemoteConnectorInterface;
use Exception;
use phpseclib3\Crypt\PublicKeyLoader;
use phpseclib3\Net\SFTP;

class SftpConnector implements RemoteConnectorInterface
{
    protected SiteConnection $connection;
    protected ?SFTP $sftp = null;

    public function __construct(SiteConnection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * Conecta ao servidor SFTP.
     */
    protected function connect(): SFTP
    {
        if ($this->sftp && $this->sftp->isConnected()) {
            return $this->sftp;
        }

        $sftp = new SFTP($this->connection->host, $this->connection->port ?: 22, 15);
        $credential = $this->connection->getDecryptedCredential();
        $passphrase = $this->connection->getDecryptedPassphrase();

        // Se a credencial for uma Chave Privada SSH (começa com -----BEGIN)
        if ($credential && str_contains($credential, '-----BEGIN')) {
            try {
                $key = PublicKeyLoader::load($credential, $passphrase ?: false);
                if (!$sftp->login($this->connection->username, $key)) {
                    throw new Exception('Falha na autenticação via Chave Privada SSH.');
                }
            } catch (Exception $e) {
                throw new Exception('Erro ao carregar Chave SSH: ' . $e->getMessage());
            }
        } else {
            // Autenticação normal via Usuário e Senha
            if (!$sftp->login($this->connection->username, $credential)) {
                throw new Exception('Falha na autenticação SFTP. Verifique usuário e senha.');
            }
        }

        $this->sftp = $sftp;
        return $this->sftp;
    }

    public function testConnection(): array
    {
        $start = microtime(true);
        try {
            $sftp = $this->connect();
            $root = PathSecurityManager::sanitizeAndValidatePath('/', $this->connection->root_path);
            
            if (!$sftp->is_dir($root)) {
                return [
                    'success' => false,
                    'latency_ms' => null,
                    'message' => "O diretório raiz configurado ($root) não existe no servidor.",
                ];
            }

            $latency = (int) round((microtime(true) - $start) * 1000);

            return [
                'success' => true,
                'latency_ms' => $latency,
                'message' => 'Conexão SFTP estabelecida com sucesso!',
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'latency_ms' => null,
                'message' => $e->getMessage(),
            ];
        }
    }

    public function listFiles(string $path = '/'): array
    {
        $sftp = $this->connect();
        $fullPath = PathSecurityManager::sanitizeAndValidatePath($path, $this->connection->root_path);

        $rawList = $sftp->rawlist($fullPath);
        if ($rawList === false) {
            throw new Exception("Não foi possível listar o diretório: $fullPath");
        }

        $items = [];
        foreach ($rawList as $name => $info) {
            if ($name === '.' || $name === '..') {
                continue;
            }

            $isDir = ($info['type'] === NET_SFTP_TYPE_DIRECTORY);
            $itemRelativePath = trim($path, '/') === '' ? $name : trim($path, '/') . '/' . $name;

            $items[] = [
                'name' => $name,
                'path' => '/' . $itemRelativePath,
                'is_dir' => $isDir,
                'size' => $isDir ? 0 : ($info['size'] ?? 0),
                'size_formatted' => $isDir ? '-' : $this->formatSize($info['size'] ?? 0),
                'mtime' => isset($info['mtime']) ? date('d/m/Y H:i', $info['mtime']) : null,
                'extension' => $isDir ? '' : PathSecurityManager::getFileExtension($name),
                'is_editable' => $isDir ? false : PathSecurityManager::isEditableFile($name),
            ];
        }

        // Ordena pastas primeiro, depois por nome
        usort($items, function ($a, $b) {
            if ($a['is_dir'] !== $b['is_dir']) {
                return $a['is_dir'] ? -1 : 1;
            }
            return strnatcasecmp($a['name'], $b['name']);
        });

        return [
            'current_path' => '/' . trim($path, '/'),
            'parent_path' => $path === '/' || $path === '' ? null : dirname('/' . trim($path, '/')),
            'items' => $items,
        ];
    }

    public function readFile(string $path): string
    {
        $sftp = $this->connect();
        $fullPath = PathSecurityManager::sanitizeAndValidatePath($path, $this->connection->root_path);

        $content = $sftp->get($fullPath);
        if ($content === false) {
            throw new Exception("Falha ao ler o arquivo: $path");
        }

        return $content;
    }

    public function writeFile(string $path, string $content): bool
    {
        $sftp = $this->connect();
        $fullPath = PathSecurityManager::sanitizeAndValidatePath($path, $this->connection->root_path);

        $result = $sftp->put($fullPath, $content);
        if (!$result) {
            throw new Exception("Falha ao salvar o arquivo: $path");
        }

        return true;
    }

    public function createDirectory(string $path): bool
    {
        $sftp = $this->connect();
        $fullPath = PathSecurityManager::sanitizeAndValidatePath($path, $this->connection->root_path);

        return $sftp->mkdir($fullPath);
    }

    public function deleteFile(string $path): bool
    {
        $sftp = $this->connect();
        $fullPath = PathSecurityManager::sanitizeAndValidatePath($path, $this->connection->root_path);

        return $sftp->delete($fullPath, false);
    }

    public function deleteDirectory(string $path): bool
    {
        $sftp = $this->connect();
        $fullPath = PathSecurityManager::sanitizeAndValidatePath($path, $this->connection->root_path);

        return $sftp->delete($fullPath, true); // recursivo
    }

    public function rename(string $oldPath, string $newPath): bool
    {
        $sftp = $this->connect();
        $oldFullPath = PathSecurityManager::sanitizeAndValidatePath($oldPath, $this->connection->root_path);
        $newFullPath = PathSecurityManager::sanitizeAndValidatePath($newPath, $this->connection->root_path);

        return $sftp->rename($oldFullPath, $newFullPath);
    }

    public function uploadFile(string $remotePath, string $localFilePath): bool
    {
        $sftp = $this->connect();
        $fullPath = PathSecurityManager::sanitizeAndValidatePath($remotePath, $this->connection->root_path);

        return $sftp->put($fullPath, $localFilePath, SFTP::SOURCE_LOCAL_FILE);
    }

    protected function formatSize(int $bytes): string
    {
        if ($bytes < 1024) return $bytes . ' B';
        if ($bytes < 1048576) return round($bytes / 1024, 1) . ' KB';
        if ($bytes < 1073741824) return round($bytes / 1048576, 1) . ' MB';
        return round($bytes / 1073741824, 1) . ' GB';
    }
}
