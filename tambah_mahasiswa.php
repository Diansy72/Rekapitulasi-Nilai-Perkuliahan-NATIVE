<?php
session_start();
if (!isset($_SESSION["user_id"]) || $_SESSION["level"] != "Admin") {
    header("Location: login.php");
    exit;
}

require_once "config.php";

// Fungsi untuk memvalidasi format NIM
function validateNIM($nim) {
    return preg_match('/^[0-9.]+$/', $nim);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nim = $_POST["nim"];
    
    // Validasi NIM
    if (!validateNIM($nim)) {
        $error = "NIM hanya boleh berisi angka dan titik";
    } else {
        try {
            $pdo->beginTransaction();

            // Generate password default (bisa diganti nanti oleh mahasiswa)
            $default_password = password_hash("123456", PASSWORD_DEFAULT);
            
            // Insert ke tabel mahasiswa
            $sql = "INSERT INTO mahasiswa (nim, nama, program_studi, password) VALUES (?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $nim,
                $_POST["nama"],
                $_POST["program_studi"],
                $default_password
            ]);

            $pdo->commit();
            $success = "Data mahasiswa berhasil ditambahkan! Password default: 123456";
        } catch (Exception $e) {
            $pdo->rollBack();
            $error = "Terjadi kesalahan: " . $e->getMessage();
        }
    }
}

// Ambil daftar program studi (bisa ditambahkan ke database jika perlu)
$program_studi = [
    "Teknik Informatika",
    "Sistem Informasi",
    "Teknologi Informasi",
    "Manajemen Informatika"
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Data Mahasiswa</title>
    <link rel="stylesheet" href="tambah_mahasiswa.css">
</head>
<body>
    <div class="container">
        <h1>Tambah Data Mahasiswa</h1>
        
        <a href="index.php" class="btn btn-back">Kembali ke Dashboard</a>
        
        <?php if (isset($success)): ?>
            <div class="success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <?php if (isset($error)): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label for="nim">NIM:</label>
                <input type="text" name="nim" required 
                       pattern="[0-9.]+" 
                       title="NIM hanya boleh berisi angka dan titik"
                       maxlength="20">
            </div>

            <div class="form-group">
                <label for="nama">Nama Lengkap:</label>
                <input type="text" name="nama" required>
            </div>

            <div class="form-group">
                <label for="program_studi">Program Studi:</label>
                <select name="program_studi" required>
                    <option value="">Pilih Program Studi</option>
                    <?php foreach ($program_studi as $prodi): ?>
                        <option value="<?= htmlspecialchars($prodi) ?>"><?= htmlspecialchars($prodi) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <button type="submit" class="btn">Tambah Mahasiswa</button>
        </form>
    </div>
</body>
</html>