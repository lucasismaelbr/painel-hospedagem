<?php

namespace App\Http\Controllers;

use App\Models\Log;
use App\Models\Site;
use App\Services\Remote\PathSecurityManager;
use App\Services\Remote\RemoteConnectorFactory;
use Illuminate\Http\Request;
use Throwable;

class SiteFileManagerController extends Controller
{
    /**
     * Exibe a interface do Gerenciador de Arquivos e Editor do Site.
     */
    public function index(Site $site, Request $request)
    {
        $site->load('connection', 'cliente');
        $connection = $site->connection;

        if (!$connection) {
            return view('sites.manager', [
                'site' => $site,
                'connection' => null,
                'error' => 'Nenhuma conexão configurada para este site. Configure a conexão SFTP/FTP primeiro.',
            ]);
        }

        $currentPath = $request->query('path', '/');

        try {
            $connector = RemoteConnectorFactory::make($connection);
            $fileData = $connector->listFiles($currentPath);

            return view('sites.manager', [
                'site' => $site,
                'connection' => $connection,
                'fileData' => $fileData,
                'error' => null,
            ]);
        } catch (Throwable $e) {
            return view('sites.manager', [
                'site' => $site,
                'connection' => $connection,
                'fileData' => null,
                'error' => 'Erro ao conectar/listar arquivos: ' . $e->getMessage(),
            ]);
        }
    }

    /**
     * Retorna o conteúdo de um arquivo em JSON para o Monaco Editor.
     */
    public function readFile(Site $site, Request $request)
    {
        $path = $request->input('path');
        if (empty($path)) {
            return response()->json(['error' => 'Caminho do arquivo não informado.'], 400);
        }

        try {
            $connector = RemoteConnectorFactory::make($site->connection);
            $content = $connector->readFile($path);

            return response()->json([
                'success' => true,
                'path' => $path,
                'filename' => basename($path),
                'extension' => PathSecurityManager::getFileExtension(basename($path)),
                'content' => $content,
            ]);
        } catch (Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Salva o conteúdo do arquivo editado no Monaco Editor.
     */
    public function writeFile(Site $site, Request $request)
    {
        $request->validate([
            'path' => ['required', 'string'],
            'content' => ['required', 'string'],
        ]);

        $path = $request->input('path');
        $content = $request->input('content');

        try {
            $connector = RemoteConnectorFactory::make($site->connection);

            // Tenta criar cópia temporária de rollback
            try {
                $originalContent = $connector->readFile($path);
                session(['file_rollback_' . md5($path) => $originalContent]);
            } catch (Throwable $ignored) {}

            $connector->writeFile($path, $content);

            Log::registrar('editou_arquivo', 'sites', $site->id);

            return response()->json([
                'success' => true,
                'message' => 'Arquivo salvo com sucesso!',
            ]);
        } catch (Throwable $e) {
            return response()->json(['error' => 'Erro ao salvar arquivo: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Criar novo arquivo ou nova página HTML/PHP.
     */
    public function createFile(Site $site, Request $request)
    {
        $request->validate([
            'parent_path' => ['required', 'string'],
            'filename' => ['required', 'string', 'max:150'],
            'template' => ['nullable', 'string'],
        ]);

        $parentPath = $request->input('parent_path', '/');
        $filename = trim($request->input('filename'));

        // Se o nome não tiver extensão, adiciona .html como padrão
        if (!str_contains($filename, '.')) {
            $filename .= '.html';
        }

        $fullPath = rtrim($parentPath, '/') . '/' . ltrim($filename, '/');

        // Conteúdo inicial de acordo com o template escolhido
        $initialContent = match ($request->input('template')) {
            'html5' => "<!DOCTYPE html>\n<html lang=\"pt-BR\">\n<head>\n    <meta charset=\"UTF-8\">\n    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">\n    <title>" . htmlspecialchars(basename($filename, '.')) . "</title>\n</head>\n<body>\n    <h1>Nova Página - " . htmlspecialchars($site->nome_site) . "</h1>\n</body>\n</html>",
            'php'   => "<?php\n// Página criada via Painel de Hospedagem\n?>\n",
            default => "",
        };

        try {
            $connector = RemoteConnectorFactory::make($site->connection);
            $connector->writeFile($fullPath, $initialContent);

            Log::registrar('criou_arquivo', 'sites', $site->id);

            return back()->with('sucesso', "Arquivo '$filename' criado com sucesso!");
        } catch (Throwable $e) {
            return back()->with('erro', 'Erro ao criar arquivo: ' . $e->getMessage());
        }
    }

    /**
     * Criar nova pasta.
     */
    public function createFolder(Site $site, Request $request)
    {
        $request->validate([
            'parent_path' => ['required', 'string'],
            'foldername' => ['required', 'string', 'max:150'],
        ]);

        $parentPath = $request->input('parent_path', '/');
        $foldername = trim($request->input('foldername'));
        $fullPath = rtrim($parentPath, '/') . '/' . ltrim($foldername, '/');

        try {
            $connector = RemoteConnectorFactory::make($site->connection);
            $connector->createDirectory($fullPath);

            Log::registrar('criou_pasta', 'sites', $site->id);

            return back()->with('sucesso', "Pasta '$foldername' criada com sucesso!");
        } catch (Throwable $e) {
            return back()->with('erro', 'Erro ao criar pasta: ' . $e->getMessage());
        }
    }

    /**
     * Excluir arquivo ou pasta.
     */
    public function deleteItem(Site $site, Request $request)
    {
        $request->validate([
            'path' => ['required', 'string'],
            'is_dir' => ['required', 'boolean'],
        ]);

        $path = $request->input('path');
        $isDir = $request->boolean('is_dir');

        try {
            $connector = RemoteConnectorFactory::make($site->connection);
            if ($isDir) {
                $connector->deleteDirectory($path);
                Log::registrar('excluiu_pasta', 'sites', $site->id);
            } else {
                $connector->deleteFile($path);
                Log::registrar('excluiu_arquivo', 'sites', $site->id);
            }

            return back()->with('sucesso', 'Item excluído com sucesso!');
        } catch (Throwable $e) {
            return back()->with('erro', 'Erro ao excluir item: ' . $e->getMessage());
        }
    }

    /**
     * Renomear ou mover item.
     */
    public function renameItem(Site $site, Request $request)
    {
        $request->validate([
            'old_path' => ['required', 'string'],
            'new_name' => ['required', 'string', 'max:150'],
        ]);

        $oldPath = $request->input('old_path');
        $newName = trim($request->input('new_name'));
        $newPath = dirname($oldPath) . '/' . $newName;

        try {
            $connector = RemoteConnectorFactory::make($site->connection);
            $connector->rename($oldPath, $newPath);

            Log::registrar('renomeou_item', 'sites', $site->id);

            return back()->with('sucesso', 'Item renomeado com sucesso!');
        } catch (Throwable $e) {
            return back()->with('erro', 'Erro ao renomear: ' . $e->getMessage());
        }
    }

    /**
     * Upload de arquivo para o servidor remoto.
     */
    public function uploadFile(Site $site, Request $request)
    {
        $request->validate([
            'parent_path' => ['required', 'string'],
            'file' => ['required', 'file', 'max:51200'], // max 50MB por arquivo
        ]);

        $parentPath = $request->input('parent_path', '/');
        $file = $request->file('file');
        $remotePath = rtrim($parentPath, '/') . '/' . $file->getClientOriginalName();

        try {
            $connector = RemoteConnectorFactory::make($site->connection);
            $connector->uploadFile($remotePath, $file->getRealPath());

            Log::registrar('upload_arquivo', 'sites', $site->id);

            return back()->with('sucesso', 'Arquivo enviado com sucesso!');
        } catch (Throwable $e) {
            return back()->with('erro', 'Erro ao enviar arquivo: ' . $e->getMessage());
        }
    }

    /**
     * Download de arquivo remoto.
     */
    public function downloadFile(Site $site, Request $request)
    {
        $path = $request->query('path');
        if (empty($path)) {
            abort(400, 'Caminho não informado.');
        }

        try {
            $connector = RemoteConnectorFactory::make($site->connection);
            $content = $connector->readFile($path);

            return response($content, 200, [
                'Content-Type' => 'application/octet-stream',
                'Content-Disposition' => 'attachment; filename="' . basename($path) . '"',
            ]);
        } catch (Throwable $e) {
            return back()->with('erro', 'Erro no download: ' . $e->getMessage());
        }
    }
}
