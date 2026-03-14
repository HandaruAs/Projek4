<!DOCTYPE html>
<html>
<head>
<title>Dashboard Admin</title>
</head>

<body>

<h2>Dashboard Admin</h2>

<p>Selamat datang, {{ $user->name }}</p>

<hr>

<h3>Informasi Akun</h3>

<p>Email : {{ $user->email }}</p>
<p>Role  : {{ $user->role }}</p>

<hr>

<h3>Menu Admin</h3>

<ul>
<li>Kelola Data Harga</li>
<li>Kelola Data Pasar</li>
<li>Lihat Laporan Harga</li>
</ul>

<br>

<form method="POST" action="/logout">
@csrf
<button type="submit">Logout</button>
</form>

</body>
</html>
