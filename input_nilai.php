<?php
session_start();
if (!isset($_SESSION["user_id"]) || $_SESSION["level"] != "Dosen") {
    header("Location: login.php");
    exit;
}

require_once "config.php";

// Jika form disubmit
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $mahasiswa_id = $_POST["mahasiswa_id"];
    $mata_kuliah_id = $_POST["mata_kuliah_id"];
    $nilai = $_POST["nilai"];
    $dosen_id = $_SESSION["user_id"];

    try {
        // Cek apakah nilai sudah ada
        $check = $pdo->prepare("SELECT id FROM nilai_mahasiswa 
                               WHERE mahasiswa_id = ? AND mata_kuliah_id = ? AND dosen_id = ?");
        $check->execute([$mahasiswa_id, $mata_kuliah_id, $dosen_id]);
        
        if ($check->rowCount() > 0) {
            // Update nilai yang sudah ada
            $sql = "UPDATE nilai_mahasiswa 
                   SET nilai = ? 
                   WHERE mahasiswa_id = ? AND mata_kuliah_id = ? AND dosen_id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$nilai, $mahasiswa_id, $mata_kuliah_id, $dosen_id]);
        } else {
            // Insert nilai baru
            $sql = "INSERT INTO nilai_mahasiswa (mahasiswa_id, mata_kuliah_id, dosen_id, nilai) 
                    VALUES (?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$mahasiswa_id, $mata_kuliah_id, $dosen_id, $nilai]);
        }

        $success = "Nilai berhasil disimpan!";
    } catch (PDOException $e) {
        $error = "Terjadi kesalahan: " . $e->getMessage();
    }
}

// Ambil daftar mahasiswa yang aktif
$stmt = $pdo->query("
    SELECT m.id, m.nim, m.nama 
    FROM mahasiswa m
    JOIN mahasiswa_aktif ma ON m.id = ma.mahasiswa_id
    WHERE ma.status = 'aktif'
");
$mahasiswa = $stmt->fetchAll();

// Ambil daftar mata kuliah
$stmt = $pdo->query("SELECT id, kode_mk, nama_mk FROM mata_kuliah");
$mata_kuliah = $stmt->fetchAll();

// Ambil daftar nilai yang sudah diinput
$stmt = $pdo->prepare("
    SELECT 
        m.nim,
        m.nama,
        mk.kode_mk,
        mk.nama_mk,
        nm.nilai
    FROM nilai_mahasiswa nm
    JOIN mahasiswa m ON nm.mahasiswa_id = m.id
    JOIN mata_kuliah mk ON nm.mata_kuliah_id = mk.id
    WHERE nm.dosen_id = ?
    ORDER BY m.nim
");
$stmt->execute([$_SESSION["user_id"]]);
$nilai_existing = $stmt->fetchAll();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Input Nilai Mahasiswa</title>
    <link rel="stylesheet" href="input_nilai.css">
</head>
<body>
    <div class="container">
        <h1>Input Nilai Mahasiswa</h1>
        <a href="index.php" class="btn">Kembali ke Dashboard</a>

        <?php if (isset($success)): ?>
            <div class="success"><?= $success ?></div>
        <?php endif; ?>

        <?php if (isset($error)): ?>
            <div class="error"><?= $error ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label for="mahasiswa_id">Mahasiswa:</label>
                <select name="mahasiswa_id" required>
                    <option value="">Pilih Mahasiswa</option>
                    <?php foreach ($mahasiswa as $mhs): ?>
                        <option value="<?= $mhs['id'] ?>">
                            <?= $mhs['nim'] ?> - <?= $mhs['nama'] ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="mata_kuliah_id">Mata Kuliah:</label>
                <select name="mata_kuliah_id" required>
                    <option value="">Pilih Mata Kuliah</option>
                    <?php foreach ($mata_kuliah as $mk): ?>
                        <option value="<?= $mk['id'] ?>">
                            <?= $mk['kode_mk'] ?> - <?= $mk['nama_mk'] ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="nilai">Nilai:</label>
                <input type="number" name="nilai" min="0" max="100" required>
            </div>

            <button type="submit" class="btn">Simpan Nilai</button>
        </form>

        <h2>Daftar Nilai yang Sudah Diinput</h2>
        <table>
            <thead>
                <tr>
                    <th>NIM</th>
                    <th>Nama</th>
                    <th>Kode MK</th>
                    <th>Mata Kuliah</th>
                    <th>Nilai</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($nilai_existing as $nilai): ?>
                    <tr>
                        <td><?= $nilai['nim'] ?></td>
                        <td><?= $nilai['nama'] ?></td>
                        <td><?= $nilai['kode_mk'] ?></td>
                        <td><?= $nilai['nama_mk'] ?></td>
                        <td><?= $nilai['nilai'] ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>