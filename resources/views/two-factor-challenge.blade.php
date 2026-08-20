<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#1a2747">
    <title>Verify it's you | Archon Nell Incorporated</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root { --navy: #1a2747; --navy-dark: #0f1b38; --accent: #4361ee; --accent-dark: #3651d5; --accent-soft: rgba(67, 97, 238, .12); --surface: #ffffff; --page: #f4f6fb; --text: #14213d; --muted: #6c7a93; --border: #e4e9f4; }
        * { box-sizing: border-box; }
        body { min-height: 100vh; margin: 0; display: grid; place-items: center; font-family: "Inter", sans-serif; color: var(--text); background: var(--page); padding: 2rem; }
        .card { width: min(100%, 420px); background: var(--surface); border: 1px solid var(--border); border-radius: 1rem; padding: 2.25rem; box-shadow: 0 20px 44px rgba(20, 33, 61, .08); }
        .icon-wrap { display: grid; place-items: center; width: 52px; height: 52px; margin-bottom: 1.1rem; border-radius: 14px; background: var(--accent-soft); color: var(--accent); }
        .icon-wrap svg { width: 26px; height: 26px; }
        .card h2 { margin: 0; font-size: 1.55rem; letter-spacing: -.04em; }
        .card > p.lead { margin: .55rem 0 1.75rem; color: var(--muted); font-size: .9rem; line-height: 1.55; }
        .alert { display: flex; gap: .6rem; align-items: flex-start; margin-bottom: 1.25rem; padding: .8rem .9rem; border: 1px solid #f3c8cc; border-radius: .65rem; color: #9f2732; background: #fff4f5; font-size: .84rem; line-height: 1.45; }
        .status { margin-bottom: 1.25rem; padding: .8rem .9rem; border: 1px solid #bfe3cc; border-radius: .65rem; color: #1f7a45; background: #f2fbf5; font-size: .84rem; line-height: 1.45; }
        .field { display: grid; gap: .5rem; margin-bottom: 1.2rem; }
        label { font-size: .84rem; font-weight: 600; }
        input { width: 100%; min-height: 52px; padding: .75rem .95rem; border: 1px solid var(--border); border-radius: .65rem; outline: none; color: var(--text); background: var(--surface); font: inherit; font-size: 1.4rem; font-weight: 700; letter-spacing: .35em; text-align: center; transition: border-color .18s ease, box-shadow .18s ease; }
        input:focus { border-color: var(--accent); box-shadow: 0 0 0 4px var(--accent-soft); }
        .btn { width: 100%; min-height: 48px; margin-top: .35rem; border: 0; border-radius: .65rem; color: #fff; background: var(--accent); box-shadow: 0 8px 18px rgba(67, 97, 238, .24); font: inherit; font-size: .9rem; font-weight: 700; cursor: pointer; transition: background .18s ease, transform .18s ease; }
        .btn:hover { background: var(--accent-dark); transform: translateY(-1px); }
        .resend-form { margin-top: 1.5rem; text-align: center; }
        .resend-btn { border: 0; background: none; padding: 0; color: var(--accent); font: inherit; font-size: .84rem; font-weight: 700; cursor: pointer; }
        .resend-btn:hover { text-decoration: underline; }
        .back { display: block; margin-top: .85rem; color: var(--muted); font-size: .82rem; text-decoration: none; text-align: center; }
        .back:hover { color: var(--accent); }
        .remember { display: flex; align-items: center; gap: .5rem; margin-bottom: 1.2rem; font-size: .84rem; color: var(--text); }
        .remember input { width: auto; min-height: auto; }
    </style>
</head>
<body>
    <section class="card" aria-labelledby="tfa-heading">
        <div class="icon-wrap" aria-hidden="true">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="5" width="18" height="14" rx="2"></rect><path d="m3 7 9 6 9-6"></path></svg>
        </div>
        <h2 id="tfa-heading">Check your email</h2>
        <p class="lead">We've sent a 6-digit verification code to your email address. Enter it below to finish signing in.</p>

        @if (session('status'))
            <div class="status" role="status">{{ session('status') }}</div>
        @endif

        @if ($errors->any())
            <div class="alert" role="alert">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('two-factor.verify') }}">
            @csrf
            <div class="field">
                <label for="code">Verification code</label>
                <input type="text" id="code" name="code" inputmode="numeric" pattern="[0-9]*" maxlength="6" placeholder="------" autocomplete="one-time-code" required autofocus>
            </div>
            <label class="remember" for="remember_device">
                <input type="checkbox" id="remember_device" name="remember_device" value="1">
                Remember this device for 7 days
            </label>
            <button type="submit" class="btn">Verify &amp; sign in</button>
        </form>

        <form class="resend-form" method="POST" action="{{ route('two-factor.resend') }}">
            @csrf
            <button type="submit" class="resend-btn">Resend code</button>
        </form>
        <a class="back" href="{{ route('login') }}">&larr; Back to sign in</a>
    </section>
</body>
</html>
