<?php

namespace App\Services\Storage\Contracts;

interface StorageProviderInterface
{
    /**
     * Salva o arquivo no provedor de armazenamento.
     */
    public function put(string $filename, string $contents): bool;

    /**
     * Recupera o conteúdo do arquivo.
     */
    public function get(string $filename): string;

    /**
     * Exclui o arquivo do armazenamento.
     */
    public function delete(string $filename): bool;

    /**
     * Retorna se o arquivo existe.
     */
    public function exists(string $filename): bool;

    /**
     * Retorna o caminho completo ou URL para o arquivo.
     */
    public function getPath(string $filename): string;
}
