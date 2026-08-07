@extends('layouts.app')

@section('title', 'Meu Perfil' . ($isAdmin ? ' & Acessos' : ''))
@section('page-title', 'Meu Perfil' . ($isAdmin ? ' & Gestão de Acessos' : ''))
@section('page-subtitle', $isAdmin ? 'Gerencie seus dados de acesso e a equipe do painel' : 'Gerencie seus dados de perfil e senha')

@section('content')
<div class="{{ $isAdmin ? 'grid-2' : '' }}" style="gap: 24px;">
    <!-- 👤 Meu Perfil -->
    <div class="card">
        <div class="card-header" style="margin-bottom: 20px;">
            <div class="card-title" style="display: flex; align-items: center; gap: 8px;">
                <span>👤 Meu Perfil</span>
            </div>
            <span class="badge {{ $isAdmin ? 'badge-purple' : 'badge-gray' }}" style="{{ $isAdmin ? 'background: rgba(108,99,255,0.15); color: #6c63ff;' : '' }}">
                {{ ucfirst($usuarioLogado->nivel) }}
            </span>
        </div>

        <form method="POST" action="{{ route('perfil.update') }}" enctype="multipart/form-data">
            @csrf @method('PUT')

            <!-- Foto de Perfil Upload -->
            <div class="form-group" style="display: flex; align-items: center; gap: 16px; margin-bottom: 24px; padding-bottom: 20px; border-bottom: 1px solid var(--border);">
                @if($usuarioLogado->avatar_url)
                    <img src="{{ $usuarioLogado->avatar_url }}" alt="{{ $usuarioLogado->nome }}" style="width: 64px; height: 64px; border-radius: 50%; object-fit: cover; border: 2px solid var(--accent); flex-shrink: 0;">
                @else
                    <div style="width: 64px; height: 64px; border-radius: 50%; background: linear-gradient(135deg, var(--accent), #a78bfa); display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 24px; color: #fff; flex-shrink: 0;">
                        {{ strtoupper(substr($usuarioLogado->nome, 0, 1)) }}
                    </div>
                @endif
                
                <div style="flex: 1;">
                    <label class="form-label" for="foto_perfil">Foto de Perfil</label>
                    <input id="foto_perfil" type="file" name="foto_perfil" accept="image/*" class="form-control" style="padding: 6px 12px; font-size: 13px;">
                    <div style="font-size: 11px; color: var(--muted); margin-top: 4px;">PNG, JPG ou WEBP até 3MB</div>
                    @error('foto_perfil') <div class="form-error">{{ $message }}</div> @enderror
                </div>
            </div>

            <div class="form-group">
                <label class="form-label" for="nome">Seu Nome *</label>
                <input id="nome" type="text" name="nome" class="form-control" value="{{ old('nome', $usuarioLogado->nome) }}" required>
                @error('nome') <div class="form-error">{{ $message }}</div> @enderror
            </div>

            <div class="form-group">
                <label class="form-label" for="email">Seu E-mail *</label>
                <input id="email" type="email" name="email" class="form-control" value="{{ old('email', $usuarioLogado->email) }}" required>
                @error('email') <div class="form-error">{{ $message }}</div> @enderror
            </div>

            <div style="border-top: 1px solid var(--border); margin: 20px 0; padding-top: 16px;">
                <div style="font-size: 13px; font-weight: 600; color: var(--text); margin-bottom: 12px;">
                    🔑 Alterar Senha (opcional)
                </div>

                <div class="form-group">
                    <label class="form-label" for="senha_atual">Senha Atual</label>
                    <input id="senha_atual" type="password" name="senha_atual" class="form-control" placeholder="••••••••">
                    @error('senha_atual') <div class="form-error">{{ $message }}</div> @enderror
                </div>

                <div class="grid-2" style="gap: 12px;">
                    <div class="form-group">
                        <label class="form-label" for="nova_senha">Nova Senha</label>
                        <input id="nova_senha" type="password" name="nova_senha" class="form-control" placeholder="mínimo 6 caracteres">
                        @error('nova_senha') <div class="form-error">{{ $message }}</div> @enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="nova_senha_confirmation">Confirmar Nova Senha</label>
                        <input id="nova_senha_confirmation" type="password" name="nova_senha_confirmation" class="form-control" placeholder="repetir nova senha">
                    </div>
                </div>
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center;">
                💾 Salvar Meu Perfil
            </button>
        </form>
    </div>

    <!-- 🔑 Gestão de Usuários & Acessos (EXCLUSIVO PARA ADMINISTRADORES) -->
    @if($isAdmin)
    <div style="display: flex; flex-direction: column; gap: 24px;">
        <!-- Cadastrar Novo Usuário -->
        <div class="card">
            <div class="card-header" style="margin-bottom: 16px;">
                <div class="card-title">➕ Cadastrar Novo Usuário / Colaborador</div>
            </div>

            <form method="POST" action="{{ route('usuarios.store') }}">
                @csrf
                <div class="grid-2" style="gap: 12px;">
                    <div class="form-group">
                        <label class="form-label" for="new_nome">Nome *</label>
                        <input id="new_nome" type="text" name="nome" class="form-control" placeholder="Ex: João Silva" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="new_email">E-mail *</label>
                        <input id="new_email" type="email" name="email" class="form-control" placeholder="joao@empresa.com" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="new_password">Senha Inicial *</label>
                        <input id="new_password" type="password" name="password" class="form-control" placeholder="••••••••" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="new_nivel">Nível de Acesso *</label>
                        <select id="new_nivel" name="nivel" class="form-control" required>
                            <option value="colaborador">Colaborador</option>
                            <option value="admin">Administrador</option>
                        </select>
                    </div>
                </div>

                <button type="submit" class="btn btn-secondary" style="width: 100%; justify-content: center;">
                    + Cadastrar Usuário
                </button>
            </form>
        </div>

        <!-- Lista de Usuários Existentes -->
        <div class="card">
            <div class="card-header" style="margin-bottom: 16px;">
                <div class="card-title">👥 Usuários Com Acesso ao Painel ({{ $usuarios->count() }})</div>
            </div>

            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Usuário</th>
                            <th>Nível</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($usuarios as $user)
                        <tr>
                            <td>
                                <div style="display: flex; align-items: center; gap: 10px;">
                                    @if($user->avatar_url)
                                        <img src="{{ $user->avatar_url }}" alt="{{ $user->nome }}" style="width: 32px; height: 32px; border-radius: 50%; object-fit: cover;">
                                    @else
                                        <div style="width: 32px; height: 32px; border-radius: 50%; background: linear-gradient(135deg, #6c63ff, #a78bfa); display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 12px; color: #fff;">
                                            {{ strtoupper(substr($user->nome, 0, 1)) }}
                                        </div>
                                    @endif
                                    <div>
                                        <div style="font-weight: 600; color: var(--text);">
                                            {{ $user->nome }}
                                            @if($user->id === auth()->id())
                                                <span style="font-size: 10px; color: #6c63ff;">(Você)</span>
                                            @endif
                                        </div>
                                        <div style="font-size: 12px; color: var(--muted);">{{ $user->email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                @if($user->nivel === 'admin')
                                    <span class="badge badge-blue">Admin</span>
                                @else
                                    <span class="badge badge-gray">Colaborador</span>
                                @endif
                            </td>
                            <td>
                                <div style="display: flex; gap: 6px; align-items: center;">
                                    @if($user->id !== auth()->id())
                                        <form method="POST" action="{{ route('usuarios.destroy', $user) }}" onsubmit="return confirm('Tem certeza que deseja revogar o acesso de {{ $user->nome }}?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-sm" title="Revogar Acesso">🗑️ Excluir</button>
                                        </form>
                                    @else
                                        <span style="font-size: 11px; color: var(--muted);">Ativo</span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection
