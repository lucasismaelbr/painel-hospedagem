<?php

namespace App\Services\Remote;

use App\Models\SiteConnection;
use App\Services\Remote\Contracts\RemoteConnectorInterface;
use Exception;

class FtpConnector implements RemoteConnectorInterface
{
    protected SiteConnection $connection;
    protected $ftpConn = null;

    public function __construct(SiteConnection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * Estabelece conexão com o servidor FTP ou FTPS (SSL).
     */
    protected function connect()
    {
        if ($this->ftpConn) {
            return $this->ftpConn;
        }

        $host = $this->connection->host;
        $port = $this->connection->port ?: 21;
        $timeout = 15;
        $isFtps = ($this->connection->type === 'ftps');

        if ($isFtps && function_exists('ftp_ssl_connect')) {
            $conn = @ftp_ssl_connect($host, $port, $timeout);
        } else {
            $conn = @ftp_connect($host, $port, $timeout);
        }

        if (!$conn) {
            throw new Exception("Não foi possível conectar ao servidor FTP em $host:$port");
        }

        $credential = $this->connection->getDecryptedCredential();
        if (!@ftp_login($conn, $this->connection->username, $credential)) {
            @ftp_close($conn);
            throw new Exception("Falha de autenticação FTP para o usuário {$this->connection->username}.");
        }

        // Habilita modo passivo (padrão recomendado para conexões de servidores cloud)
        @ftp_pasv($conn, true);

        $this->ftpConn = $conn;
        return $this->ftpConn;
    }

    public function testConnection(): array
    {
        $start = microtime(true);
        try {
            $conn = $this->connect();
            $root = PathSecurityManager::sanitizeAndValidatePath('/', $this->connection->root_path);

            if (!@ftp_chdir($conn, $root)) {
                return [
                    'success' => false,
                    'latency_ms' => null,
                    'message' => "Diretório raiz ($root) não encontrado no servidor FTP.",
                ];
            }

            $latency = (int) round((microtime(true) - $start) * 1000);

            return [
                'success' => true,
                'latency_ms' => $latency,
                'message' => 'Conexão FTP estabelecida com sucesso!',
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'latency_ms' => null,
                'message' => $e->getMessage(),
            ];
        } finally {
            $this->disconnect();
        }
    }

    public function listFiles(string $path = '/'): array
    {
        $conn = $this->connect();
        $fullPath = PathSecurityManager::sanitizeAndValidatePath($path, $this->connection->root_path);

        $rawList = @ftp_rawlist($conn, $fullPath);
        if ($rawList === false) {
            // Tenta nlist como fallback
            $rawList = @ftp_nlist($conn, $fullPath);
            if ($rawList === false) {
                throw new Exception("Falha ao listar o diretório FTP: $fullPath");
            }
        }

        $items = [];
        foreach ($rawList as $entry) {
            $parsed = $this->parseFtpEntry($entry, $fullPath);
            if (!$parsed || $parsed['name'] === '.' || $parsed['name'] === '..') {
                continue;
            }

            $itemRelativePath = trim($path, '/') === '' ? $parsed['name'] : trim($path, '/') . '/' . $parsed['name'];

            $items[] = [
                'name' => $parsed['name'],
                'path' => '/' . $itemRelativePath,
                'is_dir' => $parsed['is_dir'],
                'size' => $parsed['size'],
                'size_formatted' => $parsed['is_dir'] ? '-' : $this->formatSize($parsed['size']),
                'mtime' => $parsed['mtime'],
                'extension' => $parsed['is_dir'] ? '' : PathSecurityManager::getFileExtension($parsed['name']),
                'is_editable' => $parsed['is_dir'] ? false : PathSecurityManager::isEditableFile($parsed['name']),
            ];
        }

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
        $conn = $this->connect();
        $fullPath = PathSecurityManager::sanitizeAndValidatePath($path, $this->connection->root_path);

        $temp = fopen('php://temp', 'r+');
        if (!@ftp_fget($conn, $temp, $fullPath, FTP_BINARY)) {
            fclose($temp);
            throw new Exception("Falha ao ler o arquivo remoto: $path");
        }

        rewind($temp);
        $content = stream_get_contents($temp);
        fclose($temp);

        return $content;
    }

    public function writeFile(string $path, string $content): bool
    {
        $conn = $this->connect();
        $fullPath = PathSecurityManager::sanitizeAndValidatePath($path, $this->connection->root_path);

        $temp = fopen('php://temp', 'r+');
        fwrite($temp, $content);
        rewind($temp);

        $result = @ftp_fput($conn, $fullPath, $temp, FTP_BINARY);
        fclose($temp);

        if (!$result) {
            throw new Exception("Falha ao escrever o arquivo remoto: $path");
        }

        return true;
    }

    public function createDirectory(string $path): bool
    {
        $conn = $this->connect();
        $fullPath = PathSecurityManager::sanitizeAndValidatePath($path, $this->connection->root_path);

        return (bool) @ftp_mkdir($conn, $fullPath);
    }

    public function deleteFile(string $path): bool
    {
        $conn = $this->connect();
        $fullPath = PathSecurityManager::sanitizeAndValidatePath($path, $this->connection->root_path);

        return @ftp_delete($conn, $fullPath);
    }

    public function deleteDirectory(string $path): bool
    {
        $conn = $this->connect();
        $fullPath = PathSecurityManager::sanitizeAndValidatePath($path, $this->connection->root_path);

        return @ftp_rmdir($conn, $fullPath);
    }

    public function rename(string $oldPath, string $newPath): bool
    {
        $conn = $this->connect();
        $oldFullPath = PathSecurityManager::sanitizeAndValidatePath($oldPath, $this->connection->root_path);
        $newFullPath = PathSecurityManager::sanitizeAndValidatePath($newPath, $this->connection->root_path);

        return @ftp_rename($conn, $oldFullPath, $newFullPath);
    }

    public function uploadFile(string $remotePath, string $localFilePath): bool
    {
        $conn = $this->connect();
        $fullPath = PathSecurityManager::sanitizeAndValidatePath($remotePath, $this->connection->root_path);

        return @ftp_put($conn, $fullPath, $localFilePath, FTP_BINARY);
    }

    protected function parseFtpEntry(string $entry, string $parentPath): ?array
    {
        // Se for simples string de nome
        if (!str_contains($entry, ' ')) {
            return [
                'name' => trim($entry),
                'is_dir' => false,
                'size' => 0,
                'mtime' => null,
            ];
        }

        // Padrão UNIX ls -l
        if (preg_match('/^([d\-])[rwx\-]{9}\s+\d+\s+\w+\s+\w+\s+(\d+)\s+(\w+\s+\d+\s+[\d:]+)\s+(.+)$/', $entry, $matches)) {
            return [
                'name' => $matches[4],
                'is_dir' => $matches[1] === 'd',
                'size' => (int) $matches[2],
                'mtime' => $matches[3],
            ];
        }

        // Padrão Windows IIS FTP
        if (preg_match('/^([\d\-]+)\s+([\d:]+[AP]M)\s+(<DIR>|\d+)\s+(.+)$/i', $entry, $matches)) {
            $isDir = strtoupper($matches[3]) === '<DIR>';
            return [
                'name' => $matches[4],
                'is_dir' => $isDir,
                'size' => $isDir ? 0 : (int) $matches[3],
                'mtime' => $matches[1] . ' ' . $matches[2],
            ];
        }

        return null;
    }

    protected function formatSize(int $bytes): string
    {
        if ($bytes < 1024) return $bytes . ' B';
        if ($bytes < 1048576) return round($bytes / 1024, 1) . ' KB';
        if ($bytes < 1073741824) return round($bytes / 1048576, 1) . ' MB';
        return round($bytes / 1073741824, 1) . ' GB';
    }

    public function disconnect(): void
    {
        if ($this->ftpConn) {
            @ftp_close($this->ftpConn);
            $this->ftpConn = null;
        }
    }

    public function __destruct()
    {
        $this->disconnect();
    }
}
