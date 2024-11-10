<?php
session_start();
if (!isset($_SESSION["user_id"]) || $_SESSION["level"] != "Admin") {
    header("Location: login.php");
    exit;
}

require_once "config.php";

// Mengambil ID dari parameter URL
$id = isset($_GET['id']) ? $_GET['id'] : null;

if (!$id) {
    header("Location: index.php");
    exit;
}

// Jika form disubmit
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        // Update data (tanpa mengubah nilai)
        $stmt = $pdo->prepare("
            UPDATE nilai_mahasiswa nm
            JOIN mahasiswa m ON nm.mahasiswa_id = m.id
            JOIN mata_kuliah mk ON nm.mata_kuliah_id = mk.id
            SET 
                m.nim = ?,
                m.nama = ?,
                m.program_studi = ?,
                mk.nama_mk = ?
            WHERE nm.id = ?
        ");

        $stmt->execute([
            $_POST['nim'],
            $_POST['nama'],
            $_POST['program_studi'],
            $_POST['mata_kuliah'],
            $id
        ]);

        header("Location: index.php");
        exit;
    } catch (PDOException $e) {
        $error = "Terjadi kesalahan: " . $e->getMessage();
    }
}

// Mengambil data yang akan diedit
$stmt = $pdo->prepare("
    SELECT 
        nm.id,
        m.nim,
        m.nama,
        m.program_studi,
        mk.nama_mk as mata_kuliah,
        nm.nilai
    FROM nilai_mahasiswa nm
    JOIN mahasiswa m ON nm.mahasiswa_id = m.id
    JOIN mata_kuliah mk ON nm.mata_kuliah_id = mk.id
    WHERE nm.id = ?
");
$stmt->execute([$id]);
$data = $stmt->fetch();

if (!$data) {
    header("Location: index.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Data</title>
    <link rel="stylesheet" href="edit.css">
</head>
<body>
    <?php if (isset($error)): ?>
        <div class="error"><?= $error ?></div>
    <?php endif; ?>

    <form method="POST">
        <h1>Edit Data Mahasiswa</h1>

        <div class="form-group">
            <label for="nim">NIM:</label>
            <input type="text" id="nim" name="nim" value="<?= htmlspecialchars($data['nim']) ?>" required>
        </div>

        <div class="form-group">
            <label for="nama">Nama:</label>
            <input type="text" id="nama" name="nama" value="<?= htmlspecialchars($data['nama']) ?>" required>
        </div>

        <div class="form-group">
            <label for="program_studi">Program Studi:</label>
            <input type="text" id="program_studi" name="program_studi" value="<?= htmlspecialchars($data['program_studi']) ?>" required>
        </div>

        <div class="form-group">
            <label for="mata_kuliah">Mata Kuliah:</label>
            <input type="text" id="mata_kuliah" name="mata_kuliah" value="<?= htmlspecialchars($data['mata_kuliah']) ?>" required>
        </div>

        <div class="form-group">
            <label for="nilai">Nilai:</label>
            <input type="number" id="nilai" name="nilai" value="<?= htmlspecialchars($data['nilai']) ?>" readonly disabled>
        </div>

        <div class="form-group">
            <button type="submit" class="btn">Simpan Perubahan</button>
            <a href="index.php" class="back">Kembali</a>
        </div>
    </form>
</body>
</html>