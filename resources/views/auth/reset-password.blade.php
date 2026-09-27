<!doctype html>
<html lang="en">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Reset password — CDTrack</title></head>
<body>
<main>
    <h1>Reset your CDTrack password</h1>
    <form method="post" action="{{ url('/api/reset-password') }}">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">
        <p><label>Email <input type="email" name="email" value="{{ $email }}" required autocomplete="email"></label></p>
        <p><label>New password <input type="password" name="password" minlength="12" required autocomplete="new-password"></label></p>
        <p><label>Confirm password <input type="password" name="password_confirmation" minlength="12" required autocomplete="new-password"></label></p>
        <button type="submit">Reset password</button>
    </form>
</main>
</body>
</html>
