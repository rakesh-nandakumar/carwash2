<!doctype html>
<html>
<head>
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>AutoCare Pro Login</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <style>
        .alert-error {
            background: #fee2e2;
            color: #991b1b;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 16px;
            font-size: 13px;
            border: 1px solid #fecaca
        }
        .brand-logo {
            max-height: 120px;
            max-width: 200px;
            object-fit: contain;
            border-radius: 8px;
            display: block;
            margin: 0 auto
        }
        .brand-text {
            font-size: 24px;
            font-weight: bold;
            color: #1e293b;
            text-align: center
        }
        .brand-text span {
            color: #4a90e2
        }
        .brand-text small {
            font-size: 14px;
            color: #4a90e2
        }
        .brand {
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px 10px
        }
        .footer {
            text-align: center;
            margin-top: 24px;
            color: #6b7280;
            font-size: 12px
        }
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background: linear-gradient(135deg, #0c182a 0%, #16213e 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            margin: 0;
            color: #1e293b
        }
        .login-card {
            background: #fff;
            border-radius: 16px;
            padding: 36px;
            width: 360px;
            box-shadow: 0 20px 50px rgba(0,0,0,.3)
        }
        .login-card h1 {
            font-size: 24px;
            margin: 0 0 4px;
            text-align: center
        }
        .login-card h1 span {
            color: #4a90e2
        }
        .login-card p {
            color: #64748b;
            font-size: 13px;
            margin: 0 0 22px;
            text-align: center
        }
        .login-card label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: #334155;
            margin-bottom: 14px
        }
        .login-card label input {
            width: 100%;
            padding: 11px 13px;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            font-size: 14px;
            margin-top: 5px;
            box-sizing: border-box
        }
        .login-card button {
            width: 100%;
            padding: 12px;
            background: #4a90e2;
            color: #fff;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 700;
            cursor: pointer;
            margin-top: 6px
        }
        .login-card .check {
            display: flex;
            align-items: center;
            gap: 8px;
            font-weight: 500
        }
        .login-card .check input {
            width: auto;
            margin: 0
        }
        .login-card .muted {
            color: #64748b;
            font-size: 13px;
            margin: 0 0 22px;
            text-align: center
        }
        .login-card .primary {
            background: #4a90e2;
            color: #fff
        }
        .login-card .full {
            width: 100%
        }

        /* ---------- Password field + show/hide toggle ---------- */
        .login-card .password-wrapper {
            position: relative;
            margin-top: 5px
        }
        .login-card .password-wrapper input {
            margin-top: 0;
            padding-right: 46px
        }
        .login-card .password-toggle {
            position: absolute;
            right: 6px;
            top: 50%;
            transform: translateY(-50%);
            width: 34px;
            height: 34px;
            margin: 0;
            padding: 0;
            background: transparent;
            border: none;
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer
        }
        .login-card .password-toggle:hover {
            background: #f1f5f9
        }
        .login-card .password-toggle svg {
            width: 20px;
            height: 20px;
            fill: #64748b
        }
        .login-card .password-toggle:hover svg {
            fill: #4a90e2
        }
        .password-toggle .eye-off {
            display: none
        }
        .password-toggle.active .eye-on {
            display: none
        }
        .password-toggle.active .eye-off {
            display: block
        }
    </style>
</head>
<body class="login">
    <div class="login-card">
        <div class="brand">
            @if($settings['logo_path'])
                <img src="{{ \App\Support\Media::url($settings['logo_path']) }}" alt="{{ $settings['company_name'] }}" class="brand-logo">
            @else
                <span class="brand-text">AUTO<span>CARE</span><small>PRO</small></span>
            @endif
        </div>
        <h1>Sign in</h1>
        <p class="muted">Vehicle service center management</p>
        @if(session('error'))
            <div class="alert-error">{{ session('error') }}</div>
        @endif
        <form method="post" action="{{ route('login.perform') }}">
            @csrf
            <label>Email
                <input type="email" name="email" value="{{ old('email') }}" required>
            </label>
            <label>Password
                <div class="password-wrapper">
                    <input type="password" name="password" id="password" required>
                    <button type="button" class="password-toggle" onclick="togglePassword()" aria-label="Show password">
                        <svg class="eye-on" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path d="M12 4.5C7 4.5 2.73 7.61 1 12c1.73 4.39 6 7.5 11 7.5s9.27-3.11 11-7.5c-1.73-4.39-6-7.5-11-7.5zM12 17c-2.76 0-5-2.24-5-5s2.24-5 5-5 5 2.24 5 5-2.24 5-5 5zm0-8c-1.66 0-3 1.34-3 3s1.34 3 3 3 3-1.34 3-3-1.34-3-3-3z"/>
                        </svg>
                        <svg class="eye-off" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path d="M12 7c2.76 0 5 2.24 5 5 0 .65-.13 1.26-.36 1.83l2.92 2.92c1.51-1.26 2.7-2.89 3.43-4.75-1.73-4.39-6-7.5-11-7.5-1.4 0-2.74.25-3.98.7l2.16 2.16C10.74 7.13 11.35 7 12 7zM2 4.27l2.28 2.28.46.46C3.08 8.3 1.78 10.02 1 12c1.73 4.39 6 7.5 11 7.5 1.55 0 3.03-.3 4.38-.84l.42.42L19.73 22 21 20.27 2 4.27zM7.53 9.8l1.55 1.55c-.05.21-.08.43-.08.65 0 1.66 1.34 3 3 3 .22 0 .44-.03.65-.08l1.55 1.55c-.67.33-1.41.53-2.2.53-2.76 0-5-2.24-5-5 0-.79.2-1.53.53-2.2zm4.31-.78l3.15 3.15.02-.16c0-1.66-1.34-3-3-3l-.17.01z"/>
                        </svg>
                    </button>
                </div>
            </label>
            <label class="check">
                <input type="checkbox" name="remember"> Remember me
            </label>
            <button class="primary full">Sign in</button>
        </form>
        <div class="footer">Powered by Vellix Global</div>
    </div>
    <script>
        function togglePassword() {
            const input = document.getElementById('password');
            const btn = document.querySelector('.password-toggle');
            const show = input.type === 'password';
            input.type = show ? 'text' : 'password';
            btn.classList.toggle('active', show);
            btn.setAttribute('aria-label', show ? 'Hide password' : 'Show password');
        }
    </script>
</body>
</html>