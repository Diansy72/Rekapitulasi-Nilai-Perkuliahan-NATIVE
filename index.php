<?php
session_start();
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

require_once "config.php";

$level = $_SESSION["level"];
$user_id = $_SESSION["user_id"];

// Query berbeda berdasarkan level
if ($level == "Mahasiswa") {
    $stmt = $pdo->prepare("
        SELECT 
            nm.id,
            m.nim,
            m.nama as nama,
            m.program_studi,
            mk.nama_mk as mata_kuliah,
            nm.nilai,
            u.username as nama_dosen
        FROM mahasiswa m
        LEFT JOIN nilai_mahasiswa nm ON m.id = nm.mahasiswa_id
        LEFT JOIN mata_kuliah mk ON nm.mata_kuliah_id = mk.id
        LEFT JOIN users u ON nm.dosen_id = u.id
        WHERE m.user_id = ?
        ORDER BY mk.nama_mk
    ");
    $stmt->execute([$user_id]);
} elseif ($level == "Dosen") {
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
        WHERE nm.dosen_id = ?
    ");
    $stmt->execute([$user_id]);
} else {
    $stmt = $pdo->query("
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
    ");
}
$nilai = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rekapitulasi Nilai Perkuliahan</title>
    <link rel="stylesheet" href="index.css">
</head>
<body>
    <h1>Rekapitulasi Nilai Perkuliahan</h1>
    <p>Welcome, <?= htmlspecialchars($_SESSION["username"]) ?> (<?= htmlspecialchars($_SESSION["level"]) ?>)</p>

    <!-- Top navigation buttons -->
    <div class="top-buttons">
        <div class="left-buttons">
            <?php if ($level == "Admin"): ?>
                <a href="tambah_mahasiswa.php" class="action-button">Tambah Data Mahasiswa</a>
                <a href="verifikasi_mahasiswa.php" class="action-button">Verifikasi Mahasiswa Baru</a>
            <?php elseif ($level == "Dosen"): ?>
                <a href="input_nilai.php" class="action-button">Input Nilai Mahasiswa</a>
            <?php endif; ?>
        </div>
        <div class="right-buttons">
            <a href="logout.php" class="btn">Logout</a>
        </div>
    </div>

    <!-- Table of student grades -->
    <table border="1">
        <tr>
            <th>NIM</th>
            <th>Nama</th>
            <th>Program Studi</th>
            <th>Mata Kuliah</th>
            <?php if ($level == "Mahasiswa"): ?>
                <th>Dosen Pengampu</th>
            <?php endif; ?>
            <th>Nilai</th>
            <?php if ($level != "Mahasiswa"): ?>
                <th>Aksi</th>
            <?php endif; ?>
        </tr>
        <?php foreach ($nilai as $row): ?>
        <tr>
            <td><?= htmlspecialchars($row["nim"]) ?></td>
            <td><?= htmlspecialchars($row["nama"]) ?></td>
            <td><?= htmlspecialchars($row["program_studi"]) ?></td>
            <td><?= htmlspecialchars($row["mata_kuliah"]) ?></td>
            <?php if ($level == "Mahasiswa"): ?>
                <td><?= htmlspecialchars($row["nama_dosen"] ?? '-') ?></td>
            <?php endif; ?>
            <td><?= htmlspecialchars($row["nilai"] ?? 'Belum ada nilai') ?></td>
            <?php if ($level == "Dosen"): ?>
                <td>
                    <a href="edit_nilai.php?id=<?= $row['id'] ?>">Edit Nilai</a>
                </td>
            <?php elseif ($level == "Admin"): ?>
                <td>
                    <a href="edit.php?id=<?= $row['id'] ?>">Edit</a>
                    <a href="hapus.php?id=<?= $row['id'] ?>" onclick="return confirm('Yakin ingin menghapus?')">Hapus</a>
                </td>
            <?php endif; ?>
        </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>
