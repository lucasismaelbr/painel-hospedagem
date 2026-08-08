<?php

namespace App\Services\Storage;

use App\Services\Storage\Contracts\StorageProviderInterface;
use Illuminate\Support\Facades\File;

class LocalStorageProvider implements StorageProviderInterface
{
    protected string $baseDir;

    public function __construct(?string $baseDir = null)
    {
        $this->baseDir = $baseDir ?: storage_path('app/backups');
        if (!File::isDirectory($this->baseDir)) {
            File::makeDirectory($this->baseDir, 0777, true, true);
        }
    }

    public function put(string $filename, string $contents): bool
    {
        $filePath = $this->getPath($filename);
        return (bool) file_put_contents($filePath, $contents);
    }

    public function get(string $filename): string
    {
        $filePath = $this->getPath($filename);
        if (!file_exists($filePath)) {
            throw new \Exception("Arquivo de backup não encontrado no armazenamento local: $filename");
        }
        return file_get_contents($filePath);
    }

    public function delete(string $filename): bool
    {
        $filePath = $this->getPath($filename);
        if (file_exists($filePath)) {
            return @unlink($filePath);
        }
        return true;
    }

    public function exists(string $filename): bool
    {
        return file_exists($this->getPath($filename));
    }

    public function getPath(string $filename): string
    {
        return rtrim($this->baseDir, '/') . '/' . ltrim($filename, '/');
    }
}
