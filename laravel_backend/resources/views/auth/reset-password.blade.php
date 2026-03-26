<!DOCTYPE html>
<html>
<head>
    <title>Reset Password</title>
</head>
<body>

<h2>Reset Password</h2>

@if(session('error'))
    <p style="color:red">{{ session('error') }}</p>
@endif

<form method="POST" action="/reset-password">
    @csrf

    <input type="hidden" name="email" value="{{ session('email') }}">

    <input type="password" name="password" placeholder="Password baru" required>
    <br><br>

    <input type="password" name="password_confirmation" placeholder="Konfirmasi password" required>

    <br><br>

    <button type="submit">Update Password</button>
</form>

</body>
</html>
