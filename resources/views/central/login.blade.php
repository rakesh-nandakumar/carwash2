<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Master Control — Sign in</title>
    <style>
        * { box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background: linear-gradient(135deg, #0c182a 0%, #16213e 100%);
            display: flex; align-items: center; justify-content: center;
            min-height: 100vh; margin: 0; color: #1e293b;
        }
        .card { background: #fff; border-radius: 16px; padding: 36px; width: 360px; box-shadow: 0 20px 50px rgba(0,0,0,.3); }
        .card h1 { font-size: 24px; margin: 0 0 4px; text-align: center; }
        .card h1 span { color: #4a90e2; }
        .card p { color: #64748b; font-size: 13px; margin: 0 0 22px; text-align: center; }
        label { display: block; font-size: 13px; font-weight: 600; color: #334155; margin-bottom: 14px; }
        label input { width: 100%; padding: 11px 13px; border: 2px solid #e2e8f0; border-radius: 8px; font-size: 14px; margin-top: 5px; }
        button { width: 100%; padding: 12px; background: #4a90e2; color: #fff; border: none; border-radius: 8px; font-size: 14px; font-weight: 700; cursor: pointer; margin-top: 6px; }
        .flash { background: #fee2e2; color: #991b1b; padding: 10px 12px; border-radius: 8px; font-size: 13px; margin-bottom: 14px; }
        .footer { text-align: center; margin-top: 24px; color: #64748b; font-size: 12px; }
        .password-wrapper { position: relative; }
        .password-wrapper input { padding-right: 48px; }
        .password-toggle {
            position: absolute;
            right: 8px;
            top: 50%;
            transform: translateY(-50%);
            background: transparent;
            border: none;
            cursor: pointer;
            padding: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 32px;
            height: 32px
        }
        .password-toggle:hover { background: #f8f9fa; border-radius: 4px; }
        .password-toggle svg {
            width: 20px;
            height: 20px;
            fill: #64748b
        }
        .password-toggle:hover svg { fill: #4a90e2; }
    </style>
</head>
<body>
    <div class="card">
        <div style="text-align: center; margin-bottom: 20px;">
            <h1 style="font-size: 28px; margin: 0;">AutoCare Pro</h1>
            <span style="color: #4a90e2; font-size: 14px; font-weight: 600;">Master Control</span>
        </div>
        <p>Platform operators only.</p>
        @if($errors->any())
            <div class="flash">{{ $errors->first() }}</div>
        @endif
        <form method="post" action="{{ route('central.login.perform') }}">
            @csrf
            <label>Email
                <input type="email" name="email" value="{{ old('email') }}" required autofocus>
            </label>
            <label>Password
                <div class="password-wrapper">
                    <input type="password" name="password" id="password" required>
                    <button type="button" class="password-toggle" onclick="togglePassword()">
                        <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path d="M12 4.5C7 4.5 2.73 7.61 1 12c1.73 4.39 6 7.5 11 7.5s9.27-3.11 11-7.5c-1.73-4.39-6-7.5-11-7.5zM12 17c-2.76 0-5-2.24-5-5s2.24-5 5-5 5 2.24 5 5-2.24 5-5 5zm0-8c-1.66 0-3 1.34-3 3s1.34 3 3 3 3-1.34 3-3-1.34-3-3-3z"/>
                        </svg>
                    </button>
                </div>
            </label>
            <label style="display:flex;align-items:center;gap:8px;font-weight:500;">
                <input type="checkbox" name="remember" style="width:auto;margin:0;"> Remember me
            </label>
            <button type="submit">Sign in</button>
        </form>
        <div class="footer">Powered by Vellix </div>
    </div>
    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const toggleBtn = document.querySelector('.password-toggle');
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleBtn.innerHTML = '<svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M12 7c2.76 0 5 2.24 5 5 0 .65-.13 1.26-.36 1.83l2.92 2.92c1.51-1.26 2.7-2.89 3.43-4.75-1.73-4.39-6-7.5-11-7.5-1.4 0-2.74.25-3.98.7l2.16 2.16C10.74 7.13 11.35 7 12 7zM2 4.27l2.28 2.28.46.46C3.08 8.3 1.78 10.02 1 12c1.73 4.39 6 7.5 11 7.5 1.55 0 3.03-.3 4.38-.84l.42.42L19.73 22 21 20.27 2 4.27 2 4.27zM7.53 9.8l1.55 1.55c-.05.21-.08.43-.08.65 0 1.66 1.34 3 3 3 .22 0 .44-.03.65-.08l1.55 1.55c-.67.33-1.41.53-2.2.53-2.76 0-5-2.24-5-5 0-.79.2-1.53.53-2.2zm4.31-.78l3.15 3.15.02-.16c0-1.66-1.34-3-3-3l-.17.01z"/></svg>';
            } else {
                passwordInput.type = 'password';
                toggleBtn.innerHTML = '<svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M12 4.5C7 4.5 2.73 7.61 1 12c1.73 4.39 6 7.5 11 7.5s9.27-3.11 11-7.5c-1.73-4.39-6-7.5-11-7.5zM12 17c-2.76 0-5-2.24-5-5s2.24-5 5-5 5 2.24 5 5-2.24 5-5 5zm0-8c-1.66 0-3 1.34-3 3s1.34 3 3 3 3-1.34 3-3-1.34-3-3-3z"/></svg>';
            }
        }
    </script>
</body>
</html>
