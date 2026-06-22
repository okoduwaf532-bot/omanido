<?php
// Controleer of de 'user' tabel al bestaat
$checkTable = $pdo->query("SHOW TABLES LIKE 'user'");
if ($checkTable->rowCount() == 0) {
    // Maak de 'user' tabel als deze nog niet bestaat
   $pdo->exec("CREATE TABLE `user` (
        `id` int NOT NULL AUTO_INCREMENT,
        `username` varchar(50) NOT NULL,
        `password` varchar(255) NOT NULL,
        `balance` decimal(65,2) NOT NULL,
        `isAdmin` tinyint(1) NOT NULL DEFAULT '0',
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci");

    // Voeg de standaardgebruikers toe
    $insertUsersQuery = "
    INSERT INTO `user` (`id`, `username`, `password`, `balance`, `isAdmin`) VALUES
    (1, 'Admin', '\$2y\$10\$hRXiSwI9J8qZkJqw/9IdseaClZBEIIvO9LiIJ8xo1rUcYphDVbnuu', 1000.00, 0),
    (2, 'FerryKuhlman', '\$2y\$10\$cPgkKXVeYJXVzVqGXlQd6OiqW/gHr0pE8HgqDX/A3pFX4YB6QzUCa', 1255.36, 0),
    (5, 'Han2002', '\$2y\$10\$bX3f4GH9J2K8mLpQrStUvOy7Z1aXcDeFgHiJkLmNoPqRsT0uVwXsK', 1000000000000000000000000000000000000000000000000.00, 0),
    (6, 'RoyBos', '\$2y\$10\$eMkNxVwOpQ2R3sT4uVwXyZaAbCdEfGhIjKlMnOpQrStUvWxYzAbCd', 9.23, 0);
    ";

    // Voer de SQL-query uit om de gebruikers toe te voegen
    $pdo->exec($insertUsersQuery);
}

$pdo->exec("ALTER TABLE `user` MODIFY COLUMN `balance` decimal(65,2) NOT NULL");