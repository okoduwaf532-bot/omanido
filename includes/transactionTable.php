<?php
// Controleer of de 'transaction' tabel al bestaat
$checkTable = $pdo->query("SHOW TABLES LIKE 'transaction'");
if ($checkTable->rowCount() == 0) {
    // Maak de 'transaction' tabel als deze nog niet bestaat
    $pdo->exec("CREATE TABLE `transaction` (
        `id` int NOT NULL AUTO_INCREMENT,
        `sender` int NOT NULL,
        `receiver` int NOT NULL,
        `amount` decimal(10,2) NOT NULL,
        `description` varchar(500) NOT NULL,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci");

    // Voeg de gegevens toe
    $pdo->exec("
        INSERT INTO `transaction` (`id`, `sender`, `receiver`, `amount`, `description`) VALUES
        (1, 3, 2, 65.00, 'Auto'),
        (2, 5, 2, 94.00, '<script>alert(\"Je bent gehacked\")</script>'),
        (3, 6, 2, 38.84, 'Avondje stappen'),
        (4, 5, 6, 50.00, 'Boodschappen buurtsuper'),
        (5, 6, 5, 50.00, 'Vakantie'),
        (6, 2, 5, 30.00, 'Zakgeld'),
        (7, 5, 6, -47.68, 'Boodschappen');
    ");
}
?>
