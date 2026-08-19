<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#1a2747">
    <title>Sign in | Archon Nell Incorporated</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root { --navy: #1a2747; --navy-dark: #0f1b38; --accent: #4361ee; --accent-dark: #3651d5; --accent-soft: rgba(67, 97, 238, .12); --surface: #ffffff; --page: #f4f6fb; --text: #14213d; --muted: #6c7a93; --border: #e4e9f4; }
        * { box-sizing: border-box; }
        body { min-height: 100vh; margin: 0; display: grid; grid-template-columns: minmax(280px, 44%) 1fr; font-family: "Inter", sans-serif; color: var(--text); background: var(--page); }
        .brand-panel { position: relative; overflow: hidden; display: flex; flex-direction: column; justify-content: space-between; padding: clamp(2rem, 5vw, 4.5rem); color: #fff; background: linear-gradient(155deg, var(--navy) 0%, var(--navy-dark) 100%); }
        .brand-panel::before, .brand-panel::after { position: absolute; content: ""; border-radius: 50%; background: rgba(76, 201, 240, .12); pointer-events: none; }
        .brand-panel::before { width: 32rem; height: 32rem; right: -15rem; bottom: -13rem; }
        .brand-panel::after { width: 16rem; height: 16rem; left: -9rem; top: -5rem; background: rgba(67, 97, 238, .36); }
        .brand, .brand-copy, .brand-footer { position: relative; z-index: 1; }
        .brand { display: inline-flex; align-items: center; gap: .75rem; width: fit-content; color: #fff; text-decoration: none; }
        .brand-logo { width: 46px; height: 46px; border-radius: 13px; object-fit: cover; border: 2px solid rgba(255, 255, 255, .22); box-shadow: 0 8px 20px rgba(0, 0, 0, .22); }
        .brand-name { display: block; font-size: 1.2rem; font-weight: 800; letter-spacing: -.03em; }
        .brand-subtitle { display: block; margin-top: .12rem; color: rgba(255, 255, 255, .62); font-size: .75rem; font-weight: 500; }
        .brand-copy { max-width: 29rem; margin: auto 0; }
        .brand-copy p { margin: 0 0 .75rem; color: #4cc9f0; font-size: .78rem; font-weight: 700; letter-spacing: .12em; text-transform: uppercase; }
        .brand-copy h1 { max-width: 9ch; margin: 0; font-size: clamp(2.3rem, 4vw, 4rem); line-height: 1.08; letter-spacing: -.055em; }
        .brand-copy .description { max-width: 28rem; margin-top: 1.25rem; color: rgba(255, 255, 255, .66); font-size: .98rem; line-height: 1.7; }
        .brand-footer { color: rgba(255, 255, 255, .45); font-size: .78rem; }
        .login-panel { display: grid; place-items: center; padding: 2rem; }
        .login-card { width: min(100%, 410px); }
        .login-card h2 { margin: 0; font-size: 1.8rem; letter-spacing: -.04em; }
        .login-card > p { margin: .55rem 0 2rem; color: var(--muted); font-size: .92rem; }
        .alert { display: flex; gap: .6rem; align-items: flex-start; margin-bottom: 1.25rem; padding: .8rem .9rem; border: 1px solid #f3c8cc; border-radius: .65rem; color: #9f2732; background: #fff4f5; font-size: .84rem; line-height: 1.45; }
        .alert svg { flex: 0 0 auto; width: 18px; height: 18px; }
        .login-form { display: grid; gap: 1.2rem; }
        .field { display: grid; gap: .5rem; }
        label { font-size: .84rem; font-weight: 600; }
        .input-wrap { position: relative; }
        .input-icon { position: absolute; top: 50%; left: .9rem; width: 18px; height: 18px; color: #8491a8; transform: translateY(-50%); pointer-events: none; }
        input { width: 100%; min-height: 48px; padding: .75rem 2.9rem; border: 1px solid var(--border); border-radius: .65rem; outline: none; color: var(--text); background: var(--surface); font: inherit; font-size: .9rem; transition: border-color .18s ease, box-shadow .18s ease; }
        input::placeholder { color: #9aa6b9; }
        input:focus { border-color: var(--accent); box-shadow: 0 0 0 4px var(--accent-soft); }
        .password-toggle { position: absolute; top: 50%; right: .45rem; display: grid; width: 38px; height: 38px; padding: 0; place-items: center; border: 0; border-radius: .45rem; color: var(--muted); background: transparent; cursor: pointer; transform: translateY(-50%); }
        .password-toggle:hover { color: var(--accent); background: var(--accent-soft); }
        .password-toggle svg { width: 19px; height: 19px; }
        .login-button { min-height: 48px; margin-top: .35rem; border: 0; border-radius: .65rem; color: #fff; background: var(--accent); box-shadow: 0 8px 18px rgba(67, 97, 238, .24); font: inherit; font-size: .9rem; font-weight: 700; cursor: pointer; transition: background .18s ease, transform .18s ease, box-shadow .18s ease; }
        .login-button:hover { background: var(--accent-dark); box-shadow: 0 10px 22px rgba(67, 97, 238, .32); transform: translateY(-1px); }
        .login-button:focus-visible, .password-toggle:focus-visible { outline: 3px solid rgba(67, 97, 238, .35); outline-offset: 2px; }
        .login-button:active { transform: translateY(0); }
        .login-footer { margin-top: 2rem; color: var(--muted); font-size: .76rem; text-align: center; }
        .auth-switch { margin: 1.25rem 0 0; color: var(--muted); font-size: .82rem; text-align: center; }
        .auth-switch a { color: var(--accent); font-weight: 700; text-decoration: none; }
        .auth-switch a:hover { text-decoration: underline; }
        @media (max-width: 760px) { body { display: block; } .brand-panel { min-height: 185px; padding: 1.6rem 1.5rem; } .brand-copy { margin: 2rem 0 0; } .brand-copy h1 { max-width: none; font-size: 1.8rem; } .brand-copy .description, .brand-footer { display: none; } .login-panel { min-height: calc(100vh - 185px); padding: 2.5rem 1.5rem; } }
    </style>
</head>
<body>
    <aside class="brand-panel">
        <a class="brand" href="{{ url('/') }}" aria-label="Archon Nell Incorporated home">
            <svg class="brand-logo" viewBox="0 0 52 52" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                <defs>
                    <linearGradient id="loginOrbitOne" x1="0" y1="0" x2="1" y2="1"><stop stop-color="#4ade80"/><stop offset=".52" stop-color="#ec4899"/><stop offset="1" stop-color="#3b82f6"/></linearGradient>
                    <linearGradient id="loginOrbitTwo" x1="0" y1="1" x2="1" y2="0"><stop stop-color="#a855f7"/><stop offset="1" stop-color="#facc15"/></linearGradient>
                    <radialGradient id="loginCore"><stop stop-color="#bbf7d0"/><stop offset=".72" stop-color="#4ade80"/><stop offset="1" stop-color="#15803d"/></radialGradient>
                </defs>
                <ellipse cx="26" cy="26" rx="22" ry="7.5" transform="rotate(-78 26 26)" stroke="url(#loginOrbitOne)" stroke-width="2"/>
                <ellipse cx="26" cy="26" rx="22" ry="7.5" transform="rotate(-18 26 26)" stroke="#38bdf8" stroke-width="2"/>
                <ellipse cx="26" cy="26" rx="22" ry="7.5" transform="rotate(42 26 26)" stroke="url(#loginOrbitTwo)" stroke-width="2"/>
                <ellipse cx="26" cy="26" rx="9" ry="5.5" fill="url(#loginCore)" stroke="#fef08a"/>
                <text x="26" y="28.5" fill="#14532d" font-family="Arial, sans-serif" font-size="5.5" font-weight="900" text-anchor="middle">ANI</text>
                <circle cx="26" cy="3.5" r="1.5" fill="#facc15"/><circle cx="46" cy="19" r="1.5" fill="#38bdf8"/><circle cx="6" cy="33" r="1.5" fill="#ec4899"/>
            </svg>
            <span><span class="brand-name">Archon Nell</span><span class="brand-subtitle">Incorporated</span></span>
        </a>
        <div class="brand-copy">
            <p>Operations intelligence</p>
            <h1>Keep every journey moving.</h1>
            <div class="description">Sign in to manage vehicles, drivers, reservations, and your day-to-day operations from one place.</div>
        </div>
        <div class="brand-footer">&copy; {{ now()->year }} Archon Nell Incorporated. All rights reserved.</div>
    </aside>
    <main class="login-panel">
        <section class="login-card" aria-labelledby="login-heading">
            <h2 id="login-heading">Welcome back</h2>
            <p>Enter your credentials to access your workspace.</p>
            @if ($errors->any())
                <div class="alert" role="alert">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="12" cy="12" r="9"></circle><path d="M12 8v4m0 4h.01"></path></svg>
                    <span>{{ $errors->first() }}</span>
                </div>
            @endif
            <form method="POST" action="{{ route('login.store') }}" class="login-form">
                @csrf
                <div class="field">
                    <label for="email">Email address</label>
                    <div class="input-wrap">
                        <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><rect x="3" y="5" width="18" height="14" rx="2"></rect><path d="m3 7 9 6 9-6"></path></svg>
                        <input type="email" id="email" name="email" value="{{ old('email') }}" autocomplete="email" placeholder="you@example.com" required autofocus>
                    </div>
                </div>
                <div class="field">
                    <label for="password">Password</label>
                    <div class="input-wrap">
                        <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><rect x="4" y="10" width="16" height="10" rx="2"></rect><path d="M8 10V7a4 4 0 0 1 8 0v3"></path></svg>
                        <input type="password" id="password" name="password" autocomplete="current-password" placeholder="Enter your password" required>
                        <button class="password-toggle" type="button" id="passwordToggle" aria-label="Show password" aria-pressed="false"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6S2 12 2 12Z"></path><circle cx="12" cy="12" r="2.5"></circle></svg></button>
                    </div>
                </div>
                <button type="submit" class="login-button">Sign in</button>
            </form>
            <p class="auth-switch">New to Archon Nell? <a href="{{ route('register') }}">Create an account</a></p>
            <div class="login-footer">Secure access to your Archon Nell workspace</div>
        </section>
    </main>
    <script>
        const passwordInput = document.getElementById('password');
        const passwordToggle = document.getElementById('passwordToggle');
        passwordToggle.addEventListener('click', () => {
            const isHidden = passwordInput.type === 'password';
            passwordInput.type = isHidden ? 'text' : 'password';
            passwordToggle.setAttribute('aria-label', isHidden ? 'Hide password' : 'Show password');
            passwordToggle.setAttribute('aria-pressed', String(isHidden));
        });
    </script>
</body>
</html>
