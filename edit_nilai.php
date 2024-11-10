<?php
session_start();
if (!isset($_SESSION["user_id"]) || $_SESSION["level"] != "Dosen") {
    header("Location: login.php");
    exit;
}

require_once "config.php";

// Mengambil ID nilai yang akan diedit
$nilai_id = isset($_GET['id']) ? $_GET['id'] : 0;

// Jika form disubmit
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        $nilai_baru = $_POST['nilai'];
        $nilai_id = $_POST['nilai_id'];
        
        // Update nilai
        $sql = "UPDATE nilai_mahasiswa SET nilai = ? WHERE id = ? AND dosen_id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$nilai_baru, $nilai_id, $_SESSION['user_id']]);

        $success = "Nilai berhasil diperbarui!";
        
        // Redirect setelah 2 detik
        header("refresh:2;url=index.php");
    } catch (PDOException $e) {
        $error = "Terjadi kesalahan: " . $e->getMessage();
    }
}

// Mengambil data nilai yang akan diedit
$stmt = $pdo->prepare("
    SELECT 
        nm.id,
        m.nim,
        m.nama,
        mk.kode_mk,
        mk.nama_mk,
        nm.nilai
    FROM nilai_mahasiswa nm
    JOIN mahasiswa m ON nm.mahasiswa_id = m.id
    JOIN mata_kuliah mk ON nm.mata_kuliah_id = mk.id
    WHERE nm.id = ? AND nm.dosen_id = ?
");
$stmt->execute([$nilai_id, $_SESSION['user_id']]);
$data_nilai = $stmt->fetch();

// Jika data tidak ditemukan
if (!$data_nilai) {
    header("Location: index.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Nilai Mahasiswa</title>
    <link rel="stylesheet" href="edit_nilai.css">
</head>
<body>
    <div class="container">
        <h1>Edit Nilai Mahasiswa</h1>

        <?php if (isset($success)): ?>
            <div class="success"><?= $success ?></div>
        <?php endif; ?>

        <?php if (isset($error)): ?>
            <div class="error"><?= $error ?></div>
        <?php endif; ?>

        <div class="info-box">
            <div class="info-item"><strong>NIM:</strong> <?= htmlspecialchars($data_nilai['nim']) ?></div>
            <div class="info-item"><strong>Nama:</strong> <?= htmlspecialchars($data_nilai['nama']) ?></div>
            <div class="info-item"><strong>Mata Kuliah:</strong> <?= htmlspecialchars($data_nilai['kode_mk']) ?> - <?= htmlspecialchars($data_nilai['nama_mk']) ?></div>
            <div class="info-item"><strong>Nilai Saat Ini:</strong> <?= htmlspecialchars($data_nilai['nilai']) ?></div>
        </div>

        <form method="POST">
            <input type="hidden" name="nilai_id" value="<?= $nilai_id ?>">
            
            <div class="form-group">
                <label for="nilai">Nilai Baru:</label>
                <input type="number" name="nilai" id="nilai" 
                    value="<?= htmlspecialchars($data_nilai['nilai']) ?>" 
                    min="0" max="100" required>
            </div>

            <div class="button-group">
                <button type="submit" class="btn btn-green">Update Nilai</button>
                <a href="index.php" class="btn btn-blue">Kembali</a>
            </div>
        </form>
    </div>

    <script>
        // Validasi input nilai
        document.querySelector('form').onsubmit = function(e) {
            const nilai = document.getElementById('nilai').value;
            if (nilai < 0 || nilai > 100) {
                alert('Nilai harus berada di antara 0 dan 100');
                e.preventDefault();
                return false;
            }
            return true;
        };
    </script>
</body>
</html>