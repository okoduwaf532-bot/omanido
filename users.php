<?php
session_start();
include 'includes/db.php';

if(!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true){
    header("location: index.php");
    exit;
}

// show users

$stmt = $pdo->prepare("SELECT * FROM user");
$stmt->execute();
$users = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gebruikers | Omanido</title>
    <!-- Voeg Tailwind CSS toe via CDN -->
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.15/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
<?php include 'includes/header.php'; ?>
<div class="container mx-auto mt-20 p-6 bg-white shadow-md rounded-md">
    <?php //show users ?>
    <h2 class="text-lg text-center font-bold mb-6">Gebruikers</h2>
    <table class="w-full">
        <thead>
        <tr>
            <th class="border-b-2 p-2">ID</th>
            <th class="border-b-2 p-2">Gebruikersnaam</th>
            <th class="border-b-2 p-2">Saldo</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($users as $user): ?>
            <tr>
                <td class="border-b p-2"><?= $user['id'] ?></td>
               <td class="border-b p-2"><a href="transacties.php?id=<?= $user['id'] ?>"><?= $user['username'] ?></a></td>
                <td class="border-b p-2">€<?= number_format($user['balance'], 2, ',', '.') ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
</div>
</body>

