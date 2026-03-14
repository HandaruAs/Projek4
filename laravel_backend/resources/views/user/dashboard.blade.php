<!DOCTYPE html>
<html>
<head>
<title>Dashboard User</title>
</head>

<body>

<h2>Dashboard User</h2>

<p>Selamat datang, {{ $user->name }}</p>

<hr>

<h3>Informasi Akun</h3>

<p>Email : {{ $user->email }}</p>
<p>Role  : {{ $user->role }}</p>

<hr>

<h3>Menu User</h3>

<ul>
<li>Lihat Harga Pangan</li>
<li>Cari Harga Berdasarkan Pasar</li>
<li>Lihat Grafik Harga</li>
</ul>

<br>

<form method="POST" action="/logout">
@csrf
<button type="submit">Logout</button>
</form>

</body>
</html>
