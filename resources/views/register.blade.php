<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#1a2747">
    <title>Create account | Archon Nell Incorporated</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root { --navy: #1a2747; --navy-dark: #0f1b38; --accent: #4361ee; --accent-dark: #3651d5; --accent-soft: rgba(67, 97, 238, .12); --surface: #fff; --page: #f4f6fb; --text: #14213d; --muted: #6c7a93; --border: #e4e9f4; }
        * { box-sizing: border-box; }
        body { min-height: 100vh; margin: 0; display: grid; grid-template-columns: minmax(280px, 44%) 1fr; font-family: Inter, sans-serif; color: var(--text); background: var(--page); }
        .brand-panel { position: relative; overflow: hidden; display: flex; flex-direction: column; justify-content: space-between; padding: clamp(2rem, 5vw, 4.5rem); color: #fff; background: linear-gradient(155deg, var(--navy), var(--navy-dark)); }
        .brand-panel::before, .brand-panel::after { position: absolute; content: ""; border-radius: 50%; pointer-events: none; }
        .brand-panel::before { width: 32rem; height: 32rem; right: -15rem; bottom: -13rem; background: rgba(76, 201, 240, .12); }
        .brand-panel::after { width: 16rem; height: 16rem; top: -5rem; left: -9rem; background: rgba(67, 97, 238, .36); }
        .brand, .brand-copy, .brand-footer { position: relative; z-index: 1; }
        .brand { display: inline-flex; align-items: center; gap: .75rem; width: fit-content; color: #fff; text-decoration: none; }
        .brand-mark { width: 46px; height: 46px; padding: 5px; border: 2px solid rgba(255,255,255,.22); border-radius: 13px; background: rgba(255,255,255,.05); box-shadow: 0 8px 20px rgba(0,0,0,.22); }
        .brand-mark svg { width: 100%; height: 100%; }
        .brand-name { display: block; font-size: 1.2rem; font-weight: 800; letter-spacing: -.03em; }
        .brand-subtitle { display: block; margin-top: .12rem; color: rgba(255,255,255,.62); font-size: .75rem; font-weight: 500; }
        .brand-copy { max-width: 29rem; margin: auto 0; }
        .brand-copy p { margin: 0 0 .75rem; color: #4cc9f0; font-size: .78rem; font-weight: 700; letter-spacing: .12em; text-transform: uppercase; }
        .brand-copy h1 { max-width: 10ch; margin: 0; font-size: clamp(2.3rem, 4vw, 4rem); line-height: 1.08; letter-spacing: -.055em; }
        .brand-copy .description { max-width: 28rem; margin-top: 1.25rem; color: rgba(255,255,255,.66); font-size: .98rem; line-height: 1.7; }
        .brand-footer { color: rgba(255,255,255,.45); font-size: .78rem; }
        .auth-panel { display: grid; place-items: center; padding: 2rem; }
        .auth-card { width: min(100%, 410px); }
        h1 { margin: 0; font-size: 1.8rem; letter-spacing: -.04em; }
        .intro { margin: .55rem 0 1.6rem; color: var(--muted); font-size: .92rem; }
        .alert { margin-bottom: 1.15rem; padding: .8rem .9rem; border: 1px solid #f3c8cc; border-radius: .65rem; color: #9f2732; background: #fff4f5; font-size: .84rem; line-height: 1.45; }
        .alert ul { margin: 0; padding-left: 1.1rem; }
        form { display: grid; gap: 1rem; }
        .field { display: grid; gap: .45rem; }
        label { font-size: .84rem; font-weight: 600; }
        input { width: 100%; min-height: 47px; padding: .75rem .9rem; border: 1px solid var(--border); border-radius: .65rem; outline: none; color: var(--text); background: var(--surface); font: inherit; font-size: .9rem; transition: border-color .18s, box-shadow .18s; }
        input:focus { border-color: var(--accent); box-shadow: 0 0 0 4px var(--accent-soft); }
        .hint { color: var(--muted); font-size: .75rem; }
        button { min-height: 48px; margin-top: .3rem; border: 0; border-radius: .65rem; color: #fff; background: var(--accent); box-shadow: 0 8px 18px rgba(67,97,238,.24); font: inherit; font-size: .9rem; font-weight: 700; cursor: pointer; transition: background .18s, transform .18s; }
        button:hover { background: var(--accent-dark); transform: translateY(-1px); }
        .auth-switch { margin: 1.25rem 0 0; color: var(--muted); font-size: .82rem; text-align: center; }
        .auth-switch a { color: var(--accent); font-weight: 700; text-decoration: none; }
        .auth-switch a:hover { text-decoration: underline; }
        @media (max-width: 760px) { body { display: block; } .brand-panel { min-height: 185px; padding: 1.6rem 1.5rem; } .brand-copy { margin: 2rem 0 0; } .brand-copy h1 { max-width: none; font-size: 1.8rem; } .brand-copy .description, .brand-footer { display: none; } .auth-panel { min-height: calc(100vh - 185px); padding: 2.5rem 1.5rem; } }
    </style>
</head>
<body>
    <aside class="brand-panel">
        <a class="brand" href="{{ url('/') }}" aria-label="Archon Nell Incorporated home">
            <span class="brand-mark" aria-hidden="true"><svg viewBox="0 0 48 48" fill="none"><ellipse cx="24" cy="24" rx="20" ry="7" transform="rotate(-75 24 24)" stroke="#4ade80" stroke-width="2"/><ellipse cx="24" cy="24" rx="20" ry="7" transform="rotate(-17 24 24)" stroke="#38bdf8" stroke-width="2"/><ellipse cx="24" cy="24" rx="20" ry="7" transform="rotate(42 24 24)" stroke="#ec4899" stroke-width="2"/><ellipse cx="24" cy="24" rx="8" ry="5" fill="#4ade80" stroke="#fef08a"/><text x="24" y="26.5" fill="#14532d" font-family="Arial" font-size="5" font-weight="900" text-anchor="middle">ANI</text></svg></span>
            <span><span class="brand-name">Archon Nell</span><span class="brand-subtitle">Incorporated</span></span>
        </a>
        <div class="brand-copy"><p>Operations intelligence</p><h1>Start moving with confidence.</h1><div class="description">Create your workspace account to manage fleet operations from one secure place.</div></div>
        <div class="brand-footer">&copy; {{ now()->year }} Archon Nell Incorporated. All rights reserved.</div>
    </aside>
    <main class="auth-panel">
        <section class="auth-card" aria-labelledby="register-heading">
            <h1 id="register-heading">Create your account</h1>
            <p class="intro">Your account will be created with Staff access.</p>
            @if ($errors->any())
                <div class="alert" role="alert"><ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
            @endif
            <form method="POST" action="{{ route('register.store') }}">
                @csrf
                <div class="field"><label for="name">Full name</label><input id="name" name="name" type="text" value="{{ old('name') }}" autocomplete="name" required autofocus></div>
                <div class="field"><label for="email">Email address</label><input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="email" required></div>
                <div class="field"><label for="password">Password</label><input id="password" name="password" type="password" autocomplete="new-password" required><span class="hint">At least 8 characters.</span></div>
                <div class="field"><label for="password_confirmation">Confirm password</label><input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" required></div>
                <button type="submit">Create account</button>
            </form>
            <p class="auth-switch">Already have an account? <a href="{{ route('login') }}">Sign in</a></p>
        </section>
    </main>
</body>
</html>
