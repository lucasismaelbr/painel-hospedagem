<?php

namespace App\Services\Remote\Contracts;

interface RemoteConnectorInterface
{
    /**
     * Testa se a autenticação e o acesso ao diretório funcionam.
     */
    public function testConnection(): array;

    /**
     * Lista arquivos e diretórios de um determinado caminho.
     */
    public function listFiles(string $path): array;

    /**
     * Lê o conteúdo de um arquivo remoto.
     */
    public function readFile(string $path): string;

    /**
     * Escreve o conteúdo em um arquivo remoto.
     */
    public function writeFile(string $path, string $content): bool;

    /**
     * Cria um novo diretório.
     */
    public function createDirectory(string $path): bool;

    /**
     * Exclui um arquivo remoto.
     */
    public function deleteFile(string $path): bool;

    /**
     * Exclui um diretório remoto.
     */
    public function deleteDirectory(string $path): bool;

    /**
     * Renomeia ou move um arquivo/pasta remoto.
     */
    public function rename(string $oldPath, string $newPath): bool;

    /**
     * Upload de arquivo local para remoto.
     */
    public function uploadFile(string $remotePath, string $localFilePath): bool;
}
