<?php
require_once "config.php";

$id = $_GET["id"];

$sql = "DELETE FROM nilai_mahasiswa WHERE id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$id]);

header("Location: index.php");
exit;
?>