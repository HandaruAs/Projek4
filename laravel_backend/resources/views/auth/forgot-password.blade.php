<!DOCTYPE html>
<html>
<head>
    <title>Lupa Password</title>
</head>
<body>

<h2>Lupa Password</h2>

@if(session('error'))
    <p style="color:red">{{ session('error') }}</p>
@endif

<form method="POST" action="/forgot-password">
    @csrf

    <input type="email" name="email" placeholder="Masukkan email" required>

    <br><br>

    <button type="submit">Kirim OTP</button>
</form>

<br>

<a href="/login">Kembali ke Login</a>

</body>
</html>
