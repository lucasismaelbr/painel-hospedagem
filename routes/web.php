<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PagamentoController;
use App\Http\Controllers\PlanoController;
use App\Http\Controllers\SiteController;
use App\Http\Controllers\TarefaController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

// ── Autenticação ──────────────────────────────────────────
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])
        ->middleware('throttle:5,1') // 5 tentativas por minuto
        ->name('login.attempt');
});

Route::post('/logout', [LoginController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

// ── Painel (protegido) ────────────────────────────────────
Route::middleware('auth')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    Route::resource('clientes', ClienteController::class);
    Route::resource('planos', PlanoController::class);
    Route::resource('sites', SiteController::class);
    Route::resource('pagamentos', PagamentoController::class);

    // Ação rápida: marcar pagamento como pago
    Route::patch('pagamentos/{pagamento}/pagar', [PagamentoController::class, 'marcarPago'])
        ->name('pagamentos.pagar');

    // Tarefas / Objetivos do dia
    Route::post('tarefas', [TarefaController::class, 'store'])->name('tarefas.store');
    Route::patch('tarefas/{tarefa}/toggle', [TarefaController::class, 'toggle'])->name('tarefas.toggle');
    Route::delete('tarefas/{tarefa}', [TarefaController::class, 'destroy'])->name('tarefas.destroy');

    // Perfil & Gestão de Usuários e Acessos
    Route::get('perfil-usuarios', [UserController::class, 'index'])->name('usuarios.index');
    Route::put('perfil', [UserController::class, 'updatePerfil'])->name('perfil.update');
    Route::post('usuarios', [UserController::class, 'store'])->name('usuarios.store');
    Route::put('usuarios/{usuario}', [UserController::class, 'update'])->name('usuarios.update');
    Route::delete('usuarios/{usuario}', [UserController::class, 'destroy'])->name('usuarios.destroy');
});
