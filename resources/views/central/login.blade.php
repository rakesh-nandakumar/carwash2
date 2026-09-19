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
                <input type="password" name="password" required>
            </label>
            <label style="display:flex;align-items:center;gap:8px;font-weight:500;">
                <input type="checkbox" name="remember" style="width:auto;margin:0;"> Remember me
            </label>
            <button type="submit">Sign in</button>
        </form>
        <div class="footer">Powered by : Vellix Global</div>
    </div>
</body>
</html>
