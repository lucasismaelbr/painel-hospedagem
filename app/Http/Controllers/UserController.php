<?php

namespace App\Http\Controllers;

use App\Models\Log;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index()
    {
        $usuarioLogado = auth()->user();

        // Se for admin, lista todos os usuários. Se for colaborador, ele não vê a lista de usuários.
        $usuarios = $usuarioLogado->isAdmin() 
            ? User::orderBy('nome')->get() 
            : collect([]);

        return view('usuarios.index', [
            'usuarioLogado' => $usuarioLogado,
            'usuarios' => $usuarios,
            'isAdmin' => $usuarioLogado->isAdmin(),
        ]);
    }

    public function updatePerfil(Request $request)
    {
        $user = auth()->user();

        $request->validate([
            'nome' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:150', Rule::unique('users')->ignore($user->id)],
            'senha_atual' => ['nullable', 'required_with:nova_senha'],
            'nova_senha' => ['nullable', 'min:6', 'confirmed'],
            'foto_perfil' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:3072'],
        ]);

        if ($request->hasFile('foto_perfil')) {
            $file = $request->file('foto_perfil');
            $filename = 'user_' . $user->id . '_' . time() . '.' . $file->getClientOriginalExtension();
            
            $destinationPath = public_path('uploads/avatars');
            if (!File::exists($destinationPath)) {
                File::makeDirectory($destinationPath, 0755, true);
            }

            // Exclui a foto antiga se existir
            if ($user->foto_perfil && File::exists($destinationPath . '/' . $user->foto_perfil)) {
                File::delete($destinationPath . '/' . $user->foto_perfil);
            }

            $file->move($destinationPath, $filename);
            $user->foto_perfil = $filename;
        }

        if ($request->filled('nova_senha')) {
            if (!Hash::check($request->senha_atual, $user->password)) {
                return back()->withErrors(['senha_atual' => 'A senha atual está incorreta.']);
            }
            $user->password = $request->nova_senha;
        }

        $user->nome = $request->nome;
        $user->email = $request->email;
        $user->save();

        Log::registrar('perfil_atualizado', "Perfil de {$user->nome} atualizado.");

        return back()->with('sucesso', 'Seu perfil foi atualizado com sucesso!');
    }

    public function store(Request $request)
    {
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Acesso negado: Apenas administradores podem cadastrar novos usuários.');
        }

        $request->validate([
            'nome' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:150', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6'],
            'nivel' => ['required', Rule::in(['admin', 'colaborador'])],
        ]);

        User::create([
            'nome' => $request->nome,
            'email' => $request->email,
            'password' => $request->password,
            'nivel' => $request->nivel,
        ]);

        Log::registrar('usuario_criado', "Usuário {$request->nome} cadastrado como {$request->nivel}.");

        return back()->with('sucesso', "Usuário {$request->nome} cadastrado com sucesso!");
    }

    public function update(Request $request, User $usuario)
    {
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Acesso negado: Apenas administradores podem alterar permissões de usuários.');
        }

        $request->validate([
            'nome' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:150', Rule::unique('users')->ignore($usuario->id)],
            'nivel' => ['required', Rule::in(['admin', 'colaborador'])],
            'password' => ['nullable', 'string', 'min:6'],
        ]);

        if ($usuario->isAdmin() && $request->nivel === 'colaborador') {
            $totalAdmins = User::where('nivel', 'admin')->count();
            if ($totalAdmins <= 1) {
                return back()->with('erro', 'Operação bloqueada: O sistema não pode ficar sem nenhum Administrador!');
            }
        }

        $usuario->nome = $request->nome;
        $usuario->email = $request->email;
        $usuario->nivel = $request->nivel;

        if ($request->filled('password')) {
            $usuario->password = $request->password;
        }

        $usuario->save();

        Log::registrar('usuario_atualizado', "Usuário {$usuario->nome} atualizado.");

        return back()->with('sucesso', "Usuário {$usuario->nome} atualizado com sucesso!");
    }

    public function destroy(User $usuario)
    {
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Acesso negado: Apenas administradores podem excluir usuários.');
        }

        if ($usuario->id === auth()->id()) {
            return back()->with('erro', 'Você não pode excluir a sua própria conta!');
        }

        if ($usuario->isAdmin()) {
            $totalAdmins = User::where('nivel', 'admin')->count();
            if ($totalAdmins <= 1) {
                return back()->with('erro', 'Operação bloqueada: Não é possível excluir o único Administrador do sistema!');
            }
        }

        $nome = $usuario->nome;
        $usuario->delete();

        Log::registrar('usuario_deletado', "Usuário {$nome} excluído por " . auth()->user()->nome);

        return back()->with('sucesso', "Usuário {$nome} excluído com sucesso!");
    }
}
