@extends('layouts.app')

@section('title', 'Gerenciar ' . $site->dominio)
@section('page-title', 'Central de Gerenciamento: ' . $site->dominio)
@section('page-subtitle', 'Cliente: ' . ($site->cliente->nome ?? 'N/A') . ' | Dominio: ' . $site->dominio)

@push('styles')
<!-- Monaco Editor CDN -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/monaco-editor/0.45.0/min/vs/loader.min.js"></script>
<style>
    .manager-header-card {
        background: var(--bg-card);
        border: 1px solid var(--border);
        border-radius: 12px;
        padding: 20px;
        margin-bottom: 24px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 16px;
    }
    .status-pill {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 6px 14px;
        border-radius: 20px;
        font-size: 13px;
        font-weight: 600;
    }
    .status-conectado { background: rgba(34, 197, 94, 0.15); color: #4ade80; border: 1px solid rgba(34, 197, 94, 0.3); }
    .status-desconectado { background: rgba(239, 68, 68, 0.15); color: #f87171; border: 1px solid rgba(239, 68, 68, 0.3); }
    .status-erro { background: rgba(245, 158, 11, 0.15); color: #fbbf24; border: 1px solid rgba(245, 158, 11, 0.3); }
    
    .file-manager-grid {
        display: grid;
        grid-template-columns: 1fr;
        gap: 20px;
    }
    .editor-container-wrap {
        display: none;
        flex-direction: column;
        height: 600px;
        border: 1px solid var(--border);
        border-radius: 12px;
        overflow: hidden;
        background: #1e1e1e;
    }
    .editor-toolbar {
        background: #252526;
        padding: 10px 16px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        border-bottom: 1px solid #333;
        color: #cccccc;
    }
    #monaco-editor {
        width: 100%;
        height: 100%;
    }
    .breadcrumb-path {
        display: flex;
        align-items: center;
        gap: 6px;
        font-family: monospace;
        font-size: 13px;
        background: var(--bg-hover);
        padding: 8px 14px;
        border-radius: 8px;
        overflow-x: auto;
    }
    .breadcrumb-item {
        color: var(--accent);
        text-decoration: none;
    }
    .breadcrumb-item:hover { text-decoration: underline; }
</style>
@endpush

@section('content')
<!-- Card de Conexão e Ações Rápidas -->
<div class="manager-header-card">
    <div style="display: flex; align-items: center; gap: 16px;">
        <div style="width: 48px; height: 48px; border-radius: 12px; background: linear-gradient(135deg, var(--accent), #a78bfa); display: flex; align-items: center; justify-content: center; font-size: 24px; color: #fff;">
            🌐
        </div>
        <div>
            <div style="font-size: 18px; font-weight: 700; color: var(--text);">{{ $site->nome_site }}</div>
            <div style="font-size: 13px; color: var(--muted);">
                <a href="https://{{ $site->dominio }}" target="_blank" style="color: var(--accent); text-decoration: none;">https://{{ $site->dominio }} ↗</a>
                @if($connection)
                    &bull; <span style="text-transform: uppercase;">{{ $connection->type }}</span> ({{ $connection->host }}:{{ $connection->port }})
                @endif
            </div>
        </div>
    </div>

    <div style="display: flex; align-items: center; gap: 12px;">
        @if($connection)
            <span class="status-pill status-{{ $connection->status }}">
                <span style="width: 8px; height: 8px; border-radius: 50%; background: currentColor;"></span>
                {{ ucfirst($connection->status) }}
                @if($connection->latency_ms) ({{ $connection->latency_ms }}ms) @endif
            </span>

            <button type="button" class="btn btn-secondary btn-sm" id="btn-test-connection" onclick="testConnection()">
                ⚡ Testar Conexão
            </button>
        @else
            <span class="status-pill status-desconectado">● Sem Conexão</span>
        @endif

        <a href="{{ route('sites.backups', $site) }}" class="btn btn-outline btn-sm">
            💾 Backups
        </a>

        <button type="button" class="btn btn-outline btn-sm" onclick="openModal('modal-config-connection')">
            ⚙️ Configurar Conexão
        </button>
    </div>

</div>

@if($error)
    <div class="card" style="border-left: 4px solid var(--danger); margin-bottom: 24px;">
        <div style="color: #f87171; font-weight: 600; font-size: 14px;">⚠️ Falha na Conexão Remota</div>
        <div style="color: var(--muted); font-size: 13px; margin-top: 4px;">{{ $error }}</div>
        <div style="margin-top: 12px;">
            <button type="button" class="btn btn-primary btn-sm" onclick="openModal('modal-config-connection')">
                ⚙️ Configurar / Corrigir Credenciais SFTP
            </button>
        </div>
    </div>
@endif

@if($connection && !$error)
    <!-- Toolbar de Ações do Gerenciador de Arquivos -->
    <div class="card" style="margin-bottom: 20px;">
        <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px;">
            <!-- Breadcrumbs -->
            <div class="breadcrumb-path">
                <span>📁 {{ $connection->root_path }}</span>
                @php
                    $pathParts = array_filter(explode('/', $fileData['current_path'] ?? '/'));
                    $accumulatedPath = '';
                @endphp
                <a href="{{ route('sites.manager', [$site, 'path' => '/']) }}" class="breadcrumb-item">/ (Raiz)</a>
                @foreach($pathParts as $part)
                    @php $accumulatedPath .= '/' . $part; @endphp
                    <span>/</span>
                    <a href="{{ route('sites.manager', [$site, 'path' => $accumulatedPath]) }}" class="breadcrumb-item">{{ $part }}</a>
                @endforeach
            </div>

            <!-- Botões de Ação -->
            <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                <button type="button" class="btn btn-primary btn-sm" onclick="openModal('modal-create-file')">
                    📄 + Nova Página / Arquivo
                </button>
                <button type="button" class="btn btn-secondary btn-sm" onclick="openModal('modal-create-folder')">
                    📁 + Nova Pasta
                </button>
                <button type="button" class="btn btn-outline btn-sm" onclick="openModal('modal-upload-file')">
                    📤 Upload de Arquivo
                </button>
                <a href="{{ route('sites.manager', [$site, 'path' => $fileData['current_path'] ?? '/']) }}" class="btn btn-outline btn-sm">
                    🔄 Recarregar
                </a>
            </div>
        </div>
    </div>

    <!-- Painel Principal (Editor + Tabela de Arquivos) -->
    <div class="file-manager-grid">
        
        <!-- Editor de Código Monaco (Oculto até clicar em editar) -->
        <div class="editor-container-wrap" id="editor-wrapper">
            <div class="editor-toolbar">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <span style="font-size: 16px;">📝</span>
                    <span id="editor-file-path" style="font-family: monospace; font-weight: 600;">/public_html/index.html</span>
                    <span id="editor-save-status" style="font-size: 12px; color: var(--muted);"></span>
                </div>
                <div style="display: flex; gap: 8px;">
                    <button type="button" class="btn btn-primary btn-sm" onclick="saveEditorContent()">
                        💾 Salvar (Ctrl+S)
                    </button>
                    <button type="button" class="btn btn-secondary btn-sm" onclick="closeEditor()">
                        ✖ Fechar Editor
                    </button>
                </div>
            </div>
            <div id="monaco-editor"></div>
        </div>

        <!-- Tabela do Gerenciador de Arquivos -->
        <div class="card">
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Nome</th>
                            <th>Tamanho</th>
                            <th>Modificado</th>
                            <th style="text-align: right;">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if($fileData['parent_path'] !== null)
                            <tr>
                                <td colspan="4">
                                    <a href="{{ route('sites.manager', [$site, 'path' => $fileData['parent_path']]) }}" style="color: var(--accent); font-weight: 600; text-decoration: none;">
                                        📁 .. (Voltar Pasta Superior)
                                    </a>
                                </td>
                            </tr>
                        @endif

                        @forelse($fileData['items'] ?? [] as $item)
                            <tr>
                                <td>
                                    <div style="display: flex; align-items: center; gap: 10px;">
                                        <span style="font-size: 18px;">{{ $item['is_dir'] ? '📁' : ($item['is_editable'] ? '📄' : '📦') }}</span>
                                        @if($item['is_dir'])
                                            <a href="{{ route('sites.manager', [$site, 'path' => $item['path']]) }}" style="color: var(--text); font-weight: 600; text-decoration: none;">
                                                {{ $item['name'] }}
                                            </a>
                                        @else
                                            <span style="color: var(--text);">{{ $item['name'] }}</span>
                                        @endif
                                    </div>
                                </td>
                                <td><span style="font-size: 12px; color: var(--muted);">{{ $item['size_formatted'] }}</span></td>
                                <td><span style="font-size: 12px; color: var(--muted);">{{ $item['mtime'] ?? '-' }}</span></td>
                                <td style="text-align: right;">
                                    <div style="display: flex; gap: 6px; justify-content: flex-end;">
                                        @if($item['is_editable'])
                                            <button type="button" class="btn btn-primary btn-sm" onclick="openFileInEditor('{{ $item['path'] }}')">
                                                ✏️ Editar
                                            </button>
                                        @endif

                                        @if(!$item['is_dir'])
                                            <a href="{{ route('sites.files.download', [$site, 'path' => $item['path']]) }}" class="btn btn-outline btn-sm">
                                                ⬇️ Baixar
                                            </a>
                                        @endif

                                        <button type="button" class="btn btn-secondary btn-sm" onclick="openRenameModal('{{ $item['path'] }}', '{{ $item['name'] }}')">
                                            ✏️ Renomear
                                        </button>

                                        <form method="POST" action="{{ route('sites.files.delete', $site) }}" style="display: inline;" onsubmit="return confirm('Tem certeza que deseja excluir {{ $item['name'] }}?')">
                                            @csrf
                                            <input type="hidden" name="path" value="{{ $item['path'] }}">
                                            <input type="hidden" name="is_dir" value="{{ $item['is_dir'] ? '1' : '0' }}">
                                            <button type="submit" class="btn btn-danger btn-sm">🗑️ Excluir</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" style="text-align: center; color: var(--muted); padding: 30px;">
                                    Nenhum arquivo ou pasta encontrado neste diretório.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endif

<!-- MODAL: Configurar Conexão SFTP/FTP/SSH -->
<div id="modal-config-connection" class="modal-backdrop" style="display: none;">
    <div class="modal-box" style="max-width: 550px;">
        <div style="font-size: 18px; font-weight: 700; margin-bottom: 16px;">⚙️ Configurar Conexão Remota</div>
        
        <form method="POST" action="{{ route('sites.connection.save', $site) }}">
            @csrf
            <div class="form-group">
                <label class="form-label" for="type">Tipo de Conexão *</label>
                <select id="type" name="type" class="form-control" onchange="togglePortDefault(this.value)" required>
                    <option value="sftp" {{ ($connection->type ?? '') === 'sftp' ? 'selected' : '' }}>SFTP (Recomendado)</option>
                    <option value="ftp" {{ ($connection->type ?? '') === 'ftp' ? 'selected' : '' }}>FTP Normal</option>
                    <option value="ftps" {{ ($connection->type ?? '') === 'ftps' ? 'selected' : '' }}>FTPS (FTP sobre SSL)</option>
                    <option value="ssh" {{ ($connection->type ?? '') === 'ssh' ? 'selected' : '' }}>SSH Direct Access</option>
                </select>
            </div>

            <div class="grid-2" style="gap: 12px;">
                <div class="form-group">
                    <label class="form-label" for="host">Host / Servidor *</label>
                    <input id="host" type="text" name="host" class="form-control" placeholder="ftp.seu-site.com.br" value="{{ old('host', $connection->host ?? '') }}" required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="port">Porta *</label>
                    <input id="port" type="number" name="port" class="form-control" value="{{ old('port', $connection->port ?? 22) }}" required>
                </div>
            </div>

            <div class="grid-2" style="gap: 12px;">
                <div class="form-group">
                    <label class="form-label" for="username">Usuário *</label>
                    <input id="username" type="text" name="username" class="form-control" placeholder="usuario_ftp" value="{{ old('username', $connection->username ?? '') }}" required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="root_path">Diretório Raiz *</label>
                    <input id="root_path" type="text" name="root_path" class="form-control" value="{{ old('root_path', $connection->root_path ?? '/public_html') }}" required>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label" for="credential">Senha ou Chave Privada SSH {{ $connection ? '(Deixe em branco para manter a atual)' : '*' }}</label>
                <textarea id="credential" name="credential" class="form-control" rows="3" placeholder="Digite a senha do FTP/SFTP ou cole a Chave Privada SSH (-----BEGIN OPENSSH PRIVATE KEY-----)"></textarea>
            </div>

            <div class="form-group">
                <label class="form-label" for="passphrase">Passphrase da Chave SSH (opcional)</label>
                <input id="passphrase" type="password" name="passphrase" class="form-control" placeholder="Se a chave possuir senha">
            </div>

            <div style="display: flex; gap: 10px; justify-content: flex-end; margin-top: 20px;">
                <button type="button" class="btn btn-secondary" onclick="closeModal('modal-config-connection')">Cancelar</button>
                <button type="submit" class="btn btn-primary">💾 Salvar e Testar Conexão</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL: Criar Novo Arquivo / Nova Página -->
<div id="modal-create-file" class="modal-backdrop" style="display: none;">
    <div class="modal-box" style="max-width: 480px;">
        <div style="font-size: 18px; font-weight: 700; margin-bottom: 16px;">📄 Criar Nova Página / Arquivo</div>
        
        <form method="POST" action="{{ route('sites.files.create', $site) }}">
            @csrf
            <input type="hidden" name="parent_path" value="{{ $fileData['current_path'] ?? '/' }}">

            <div class="form-group">
                <label class="form-label" for="filename">Nome do Arquivo / Slug da Página *</label>
                <input id="filename" type="text" name="filename" class="form-control" placeholder="Ex: servicos.html ou sobre.php" required>
            </div>

            <div class="form-group">
                <label class="form-label" for="template">Modelo de Template Inicial</label>
                <select id="template" name="template" class="form-control">
                    <option value="html5">Página HTML5 Base (com head, body e título)</option>
                    <option value="php">Arquivo PHP Vazio (<?php ?>)</option>
                    <option value="empty">Arquivo Totalmente Vazio</option>
                </select>
            </div>

            <div style="display: flex; gap: 10px; justify-content: flex-end; margin-top: 20px;">
                <button type="button" class="btn btn-secondary" onclick="closeModal('modal-create-file')">Cancelar</button>
                <button type="submit" class="btn btn-primary">+ Criar Página</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL: Criar Nova Pasta -->
<div id="modal-create-folder" class="modal-backdrop" style="display: none;">
    <div class="modal-box" style="max-width: 420px;">
        <div style="font-size: 18px; font-weight: 700; margin-bottom: 16px;">📁 Criar Nova Pasta</div>
        
        <form method="POST" action="{{ route('sites.folders.create', $site) }}">
            @csrf
            <input type="hidden" name="parent_path" value="{{ $fileData['current_path'] ?? '/' }}">

            <div class="form-group">
                <label class="form-label" for="foldername">Nome da Pasta *</label>
                <input id="foldername" type="text" name="foldername" class="form-control" placeholder="Ex: css, js ou imagens" required>
            </div>

            <div style="display: flex; gap: 10px; justify-content: flex-end; margin-top: 20px;">
                <button type="button" class="btn btn-secondary" onclick="closeModal('modal-create-folder')">Cancelar</button>
                <button type="submit" class="btn btn-primary">+ Criar Pasta</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL: Upload de Arquivo -->
<div id="modal-upload-file" class="modal-backdrop" style="display: none;">
    <div class="modal-box" style="max-width: 480px;">
        <div style="font-size: 18px; font-weight: 700; margin-bottom: 16px;">📤 Upload de Arquivo para {{ $fileData['current_path'] ?? '/' }}</div>
        
        <form method="POST" action="{{ route('sites.files.upload', $site) }}" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="parent_path" value="{{ $fileData['current_path'] ?? '/' }}">

            <div class="form-group">
                <label class="form-label" for="file">Selecione o Arquivo *</label>
                <input id="file" type="file" name="file" class="form-control" required>
                <div style="font-size: 11px; color: var(--muted); margin-top: 4px;">Tamanho máximo: 50 MB</div>
            </div>

            <div style="display: flex; gap: 10px; justify-content: flex-end; margin-top: 20px;">
                <button type="button" class="btn btn-secondary" onclick="closeModal('modal-upload-file')">Cancelar</button>
                <button type="submit" class="btn btn-primary">📤 Fazer Upload</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL: Renomear Item -->
<div id="modal-rename-item" class="modal-backdrop" style="display: none;">
    <div class="modal-box" style="max-width: 420px;">
        <div style="font-size: 18px; font-weight: 700; margin-bottom: 16px;">✏️ Renomear Item</div>
        
        <form method="POST" action="{{ route('sites.files.rename', $site) }}">
            @csrf
            <input type="hidden" id="rename_old_path" name="old_path">

            <div class="form-group">
                <label class="form-label" for="new_name">Novo Nome *</label>
                <input id="new_name" type="text" name="new_name" class="form-control" required>
            </div>

            <div style="display: flex; gap: 10px; justify-content: flex-end; margin-top: 20px;">
                <button type="button" class="btn btn-secondary" onclick="closeModal('modal-rename-item')">Cancelar</button>
                <button type="submit" class="btn btn-primary">Salvar Nome</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    let editorInstance = null;
    let currentEditingPath = null;

    function openModal(id) {
        document.getElementById(id).style.display = 'flex';
    }
    function closeModal(id) {
        document.getElementById(id).style.display = 'none';
    }

    function togglePortDefault(type) {
        const portInput = document.getElementById('port');
        if (type === 'sftp' || type === 'ssh') {
            portInput.value = 22;
        } else {
            portInput.value = 21;
        }
    }

    function openRenameModal(path, currentName) {
        document.getElementById('rename_old_path').value = path;
        document.getElementById('new_name').value = currentName;
        openModal('modal-rename-item');
    }

    function testConnection() {
        const btn = document.getElementById('btn-test-connection');
        btn.disabled = true;
        btn.innerText = '⏳ Testando...';

        fetch("{{ route('sites.connection.test', $site) }}", {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            btn.disabled = false;
            btn.innerText = '⚡ Testar Conexão';
            if (data.success) {
                alert('✅ Conexão bem-sucedida! Latência: ' + data.latency_ms + 'ms');
                window.location.reload();
            } else {
                alert('❌ Falha na conexão: ' + data.message);
            }
        })
        .catch(err => {
            btn.disabled = false;
            btn.innerText = '⚡ Testar Conexão';
            alert('Erro ao testar conexão: ' + err);
        });
    }

    // Monaco Editor Integration
    require.config({ paths: { 'vs': 'https://cdnjs.cloudflare.com/ajax/libs/monaco-editor/0.45.0/min/vs' }});

    function openFileInEditor(filePath) {
        currentEditingPath = filePath;
        const wrapper = document.getElementById('editor-wrapper');
        const pathSpan = document.getElementById('editor-file-path');
        const saveStatus = document.getElementById('editor-save-status');

        pathSpan.innerText = filePath;
        saveStatus.innerText = 'Carregando arquivo...';
        wrapper.style.display = 'flex';
        wrapper.scrollIntoView({ behavior: 'smooth' });

        fetch("{{ route('sites.files.read', $site) }}?path=" + encodeURIComponent(filePath), {
            headers: { 'Accept': 'application/json' }
        })
        .then(res => res.json())
        .then(data => {
            if (data.error) {
                alert('Erro ao ler arquivo: ' + data.error);
                closeEditor();
                return;
            }

            saveStatus.innerText = '';
            const language = getMonacoLanguage(data.extension);

            if (!editorInstance) {
                require(['vs/editor/editor.main'], function () {
                    editorInstance = monaco.editor.create(document.getElementById('monaco-editor'), {
                        value: data.content,
                        language: language,
                        theme: 'vs-dark',
                        automaticLayout: true,
                        fontSize: 14,
                        scrollBeyondLastLine: false,
                        minimap: { enabled: true }
                    });

                    // Atalho Ctrl+S / Cmd+S
                    editorInstance.addCommand(monaco.KeyMod.CtrlCmd | monaco.KeyCode.KeyS, function () {
                        saveEditorContent();
                    });
                });
            } else {
                monaco.editor.setModelLanguage(editorInstance.getModel(), language);
                editorInstance.setValue(data.content);
            }
        })
        .catch(err => {
            alert('Erro na requisição: ' + err);
            closeEditor();
        });
    }

    function saveEditorContent() {
        if (!editorInstance || !currentEditingPath) return;

        const saveStatus = document.getElementById('editor-save-status');
        saveStatus.innerText = '⏳ Salvando...';

        const content = editorInstance.getValue();

        fetch("{{ route('sites.files.write', $site) }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                path: currentEditingPath,
                content: content
            })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                saveStatus.innerText = '✅ Salvo com sucesso às ' + new Date().toLocaleTimeString();
                setTimeout(() => { saveStatus.innerText = ''; }, 4000);
            } else {
                saveStatus.innerText = '❌ Erro ao salvar';
                alert('Erro: ' + data.error);
            }
        })
        .catch(err => {
            saveStatus.innerText = '❌ Erro de rede';
            alert('Erro de conexão ao salvar: ' + err);
        });
    }

    function closeEditor() {
        document.getElementById('editor-wrapper').style.display = 'none';
        currentEditingPath = null;
    }

    function getMonacoLanguage(ext) {
        switch (ext) {
            case 'html': case 'htm': return 'html';
            case 'css': return 'css';
            case 'js': return 'javascript';
            case 'json': return 'json';
            case 'php': return 'php';
            case 'xml': return 'xml';
            case 'md': return 'markdown';
            default: return 'plaintext';
        }
    }
</script>
@endpush
@endsection
