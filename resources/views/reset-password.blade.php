<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#1a2747">
    <title>Reset password | Archon Nell Incorporated</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root { --navy: #1a2747; --navy-dark: #0f1b38; --accent: #4361ee; --accent-dark: #3651d5; --accent-soft: rgba(67, 97, 238, .12); --surface: #ffffff; --page: #f4f6fb; --text: #14213d; --muted: #6c7a93; --border: #e4e9f4; }
        * { box-sizing: border-box; }
        body { min-height: 100vh; margin: 0; display: grid; place-items: center; font-family: "Inter", sans-serif; color: var(--text); background: var(--page); padding: 2rem; }
        .card { width: min(100%, 420px); background: var(--surface); border: 1px solid var(--border); border-radius: 1rem; padding: 2.25rem; box-shadow: 0 20px 44px rgba(20, 33, 61, .08); }
        .card h2 { margin: 0; font-size: 1.55rem; letter-spacing: -.04em; }
        .card > p.lead { margin: .55rem 0 1.75rem; color: var(--muted); font-size: .9rem; line-height: 1.55; }
        .alert { display: flex; gap: .6rem; align-items: flex-start; margin-bottom: 1.25rem; padding: .8rem .9rem; border: 1px solid #f3c8cc; border-radius: .65rem; color: #9f2732; background: #fff4f5; font-size: .84rem; line-height: 1.45; }
        .field { display: grid; gap: .5rem; margin-bottom: 1.2rem; }
        label { font-size: .84rem; font-weight: 600; }
        input { width: 100%; min-height: 48px; padding: .75rem .95rem; border: 1px solid var(--border); border-radius: .65rem; outline: none; color: var(--text); background: var(--surface); font: inherit; font-size: .9rem; transition: border-color .18s ease, box-shadow .18s ease; }
        input:focus { border-color: var(--accent); box-shadow: 0 0 0 4px var(--accent-soft); }
        input[readonly] { background: var(--page); color: var(--muted); }
        .btn { width: 100%; min-height: 48px; margin-top: .35rem; border: 0; border-radius: .65rem; color: #fff; background: var(--accent); box-shadow: 0 8px 18px rgba(67, 97, 238, .24); font: inherit; font-size: .9rem; font-weight: 700; cursor: pointer; transition: background .18s ease, transform .18s ease; }
        .btn:hover { background: var(--accent-dark); transform: translateY(-1px); }
        .back { display: block; margin-top: 1.5rem; color: var(--accent); font-size: .84rem; font-weight: 700; text-decoration: none; text-align: center; }
        .back:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <section class="card" aria-labelledby="reset-heading">
        <h2 id="reset-heading">Reset your password</h2>
        <p class="lead">Choose a new password for your account.</p>

        @if ($errors->any())
            <div class="alert" role="alert">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('password.update') }}">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">
            <div class="field">
                <label for="email">Email address</label>
                <input type="email" id="email" name="email" value="{{ old('email', $email) }}" autocomplete="email" required>
            </div>
            <div class="field">
                <label for="password">New password</label>
                <input type="password" id="password" name="password" autocomplete="new-password" placeholder="At least 8 characters" required>
            </div>
            <div class="field">
                <label for="password_confirmation">Confirm new password</label>
                <input type="password" id="password_confirmation" name="password_confirmation" autocomplete="new-password" required>
            </div>
            <button type="submit" class="btn">Reset password</button>
        </form>
        <a class="back" href="{{ route('login') }}">&larr; Back to sign in</a>
    </section>
</body>
</html>
