<?php

namespace App\Services\Remote;

use InvalidArgumentException;

class PathSecurityManager
{
    /**
     * Sanitiza e garante que o caminho remoto esteja contido estritamente dentro do root_path.
     * Previne ataques de Path Traversal (../, directory climbing).
     */
    public static function sanitizeAndValidatePath(string $path, string $rootPath = '/public_html'): string
    {
        // Garante barras normais
        $root = '/' . trim(str_replace('\\', '/', $rootPath), '/');
        $relative = str_replace('\\', '/', $path);

        // Se o caminho já for absoluto e começar com o root, extrai a parte relativa
        if (str_starts_with($relative, $root)) {
            $relative = substr($relative, strlen($root));
        }

        // Divide partes do caminho e remove referências relativas
        $parts = explode('/', $relative);
        $safeParts = [];

        foreach ($parts as $part) {
            $part = trim($part);
            if ($part === '' || $part === '.') {
                continue;
            }

            if ($part === '..') {
                // Tentar subir além da raiz é uma violação de segurança
                if (!empty($safeParts)) {
                    array_pop($safeParts);
                }
                continue;
            }

            // Sanitiza caracteres nulos e injeção de comandos
            $part = str_replace(["\0", "\r", "\n"], '', $part);
            $safeParts[] = $part;
        }

        $finalPath = $root . ($safeParts ? '/' . implode('/', $safeParts) : '');
        
        return $finalPath;
    }

    /**
     * Retorna a extensão do arquivo sanitizada
     */
    public static function getFileExtension(string $filename): string
    {
        return strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    }

    /**
     * Verifica se o arquivo é um tipo editável de código/texto
     */
    public static function isEditableFile(string $filename): bool
    {
        $editableExtensions = [
            'html', 'htm', 'css', 'js', 'json', 'php', 'xml', 'md', 'txt',
            'env', 'htaccess', 'svg', 'yml', 'yaml', 'ini', 'sql', 'blade.php'
        ];

        $ext = self::getFileExtension($filename);
        return in_array($ext, $editableExtensions, true);
    }
}
