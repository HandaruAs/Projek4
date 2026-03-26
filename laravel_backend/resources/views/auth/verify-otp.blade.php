<!DOCTYPE html>
<html>
<head>
    <title>Verifikasi OTP</title>
</head>
<body>

<h2>Verifikasi OTP</h2>

@if(session('error'))
    <p style="color:red">{{ session('error') }}</p>
@endif

<form method="POST" action="/verify-otp">
    @csrf

    <input type="hidden" name="email" value="{{ session('email') }}">

    <input type="text" name="otp" placeholder="Masukkan kode OTP" required>

    <br><br>

    <button type="submit">Verifikasi</button>
</form>

</body>
</html>
