<?php
session_start();
require_once "config.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST["username"];
    $password = password_hash($_POST["password"], PASSWORD_DEFAULT);
    $level = $_POST["level"];

    try {
        $pdo->beginTransaction();

        // Insert ke tabel users
        $sql = "INSERT INTO users (username, password, level) VALUES (?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$username, $password, $level]);
        $userId = $pdo->lastInsertId();

        // Jika level Mahasiswa, tambahkan data ke tabel mahasiswa
        if ($level == "Mahasiswa") {
            $nim = $_POST["nim"];
            $nama = $_POST["nama"];
            $prodi = $_POST["program_studi"];
            
            $sql = "INSERT INTO mahasiswa (nim, nama, program_studi, user_id) VALUES (?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$nim, $nama, $prodi, $userId]);
        }

        $pdo->commit();
        header("Location: login.php");
        exit;
    } catch (Exception $e) {
        $pdo->rollBack();
        $error = "Terjadi kesalahan: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <link rel="stylesheet" href="register.css">
</head>
<body>
    <div class="container">
        <h1>Daftar Akun</h1>
        <?php if (isset($error)): ?>
            <div style="color: red;"><?= $error ?></div>
        <?php endif; ?>
        <form method="POST">
            <label for="username">Username:</label>
            <input type="text" name="username" required>

            <label for="password">Password:</label>
            <input type="password" name="password" required>

            <label for="level">Level:</label>
            <select name="level" id="level" required onchange="toggleMahasiswaFields()">
                <option value="Admin">Admin</option>
                <option value="Dosen">Dosen</option>
                <option value="Mahasiswa">Mahasiswa</option>
            </select>

            <!-- Mahasiswa Fields -->
            <div id="mahasiswa_fields">
                <label for="nim">NIM:</label>
                <input type="text" name="nim">

                <label for="nama">Nama Lengkap:</label>
                <input type="text" name="nama">

                <label for="program_studi">Program Studi:</label>
                <input type="text" name="program_studi">
            </div>

            <input type="submit" value="Daftar">
        </form>
        <p>Sudah punya akun? <a href="login.php">Login di sini.</a></p>
    </div>

    <script>
        function toggleMahasiswaFields() {
            var level = document.getElementById('level').value;
            var mahasiswaFields = document.getElementById('mahasiswa_fields');
            mahasiswaFields.style.display = level === 'Mahasiswa' ? 'block' : 'none';
        }
    </script>
</body>
</html>
