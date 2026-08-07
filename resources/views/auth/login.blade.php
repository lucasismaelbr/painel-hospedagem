<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — HostManager</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Inter', sans-serif;
            background: #0f1117;
            color: #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            background-image: radial-gradient(ellipse at 20% 50%, rgba(108,99,255,.12) 0%, transparent 60%),
                              radial-gradient(ellipse at 80% 20%, rgba(74,222,128,.06) 0%, transparent 50%);
        }
        .login-wrap {
            width: 100%;
            max-width: 420px;
            padding: 20px;
        }
        .login-brand {
            text-align: center;
            margin-bottom: 36px;
        }
        .login-brand .logo {
            font-size: 40px;
            display: block;
            margin-bottom: 12px;
        }
        .login-brand h1 {
            font-size: 24px;
            font-weight: 700;
            color: #6c63ff;
        }
        .login-brand p {
            font-size: 14px;
            color: #64748b;
            margin-top: 4px;
        }
        .login-card {
            background: #161b27;
            border: 1px solid #2a3248;
            border-radius: 16px;
            padding: 36px;
        }
        .login-card h2 {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 24px;
        }
        .form-group { margin-bottom: 18px; }
        .form-label { display: block; font-size: 13px; font-weight: 500; margin-bottom: 6px; }
        .form-control {
            width: 100%;
            background: #1e2535;
            border: 1px solid #2a3248;
            color: #e2e8f0;
            padding: 10px 14px;
            border-radius: 8px;
            font-size: 14px;
            font-family: inherit;
            transition: border-color .15s;
        }
        .form-control:focus { outline: none; border-color: #6c63ff; }
        .form-control::placeholder { color: #64748b; }
        .form-error { color: #f87171; font-size: 12px; margin-top: 5px; }
        .remember-row {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 24px;
            font-size: 13px;
            color: #64748b;
        }
        .remember-row input { accent-color: #6c63ff; }
        .btn-login {
            width: 100%;
            background: #6c63ff;
            color: #fff;
            border: none;
            padding: 11px;
            border-radius: 8px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            font-family: inherit;
            transition: background .15s;
        }
        .btn-login:hover { background: #5a52d5; }
    </style>
</head>
<body>
    <div class="login-wrap">
        <div class="login-brand">
            <span class="logo">🌐</span>
            <h1>HostManager</h1>
            <p>Painel de Gerenciamento de Hospedagem</p>
        </div>
        <div class="login-card">
            <h2>Entrar no painel</h2>
            <form method="POST" action="{{ route('login.attempt') }}">
                @csrf
                <div class="form-group">
                    <label class="form-label" for="email">E-mail</label>
                    <input id="email" type="email" name="email" class="form-control"
                           value="{{ old('email') }}" placeholder="seu@email.com" required autofocus>
                    @error('email')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>
                <div class="form-group">
                    <label class="form-label" for="password">Senha</label>
                    <input id="password" type="password" name="password" class="form-control"
                           placeholder="••••••••" required>
                    @error('password')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>
                <div class="remember-row">
                    <input type="checkbox" id="remember" name="remember">
                    <label for="remember">Lembrar de mim</label>
                </div>
                <button type="submit" class="btn-login">Entrar →</button>
            </form>
        </div>
    </div>
</body>
</html>
