<?php
session_start();
if (!isset($_SESSION["user_id"]) || $_SESSION["level"] != "Admin") {
    header("Location: login.php");
    exit;
}

require_once "config.php";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['activate'])) {
    $mahasiswa_id = $_POST['mahasiswa_id'];
    
    $stmt = $pdo->prepare("INSERT INTO mahasiswa_aktif (mahasiswa_id) VALUES (?)");
    $stmt->execute([$mahasiswa_id]);
    
    $success = "Mahasiswa berhasil diaktifkan.";
}

$stmt = $pdo->query("
    SELECT m.* 
    FROM mahasiswa m
    LEFT JOIN mahasiswa_aktif ma ON m.id = ma.mahasiswa_id
    WHERE ma.id IS NULL
");
$mahasiswa_baru = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="index.css">
    <title>Verifikasi Mahasiswa</title>
</head>
<body>
    <h1>Verifikasi Mahasiswa Baru</h1>
    <?php if (isset($success)): ?>
        <p style="color: green;"><?= $success ?></p>
    <?php endif; ?>
    
    <table border="1">
        <tr>
            <th>NIM</th>
            <th>Nama</th>
            <th>Program Studi</th>
            <th>Aksi</th>
        </tr>
        <?php foreach ($mahasiswa_baru as $mhs): ?>
        <tr>
            <td><?= htmlspecialchars($mhs['nim']) ?></td>
            <td><?= htmlspecialchars($mhs['nama']) ?></td>
            <td><?= htmlspecialchars($mhs['program_studi']) ?></td>
            <td>
                <form method="POST">
                    <input type="hidden" name="mahasiswa_id" value="<?= $mhs['id'] ?>">
                    <button type="submit" name="activate" class="activate-button">Aktifkan</button>
                </form>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>