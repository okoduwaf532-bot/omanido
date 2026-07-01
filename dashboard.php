<?php
session_start();
include 'includes/db.php';

if(!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true){
    header("location: index.php");
    exit;
}

// als button is ingedrukt
if($_SERVER["REQUEST_METHOD"] == "POST"){
    $ontvangerNaam = trim($_POST['ontvanger'] ?? '');
    $bedrag = trim($_POST['bedrag'] ?? '');
    $omschrijving = trim($_POST['omschrijving'] ?? '');

    $bedragValue = filter_var($bedrag, FILTER_VALIDATE_FLOAT);

    if (
        $ontvangerNaam === '' ||
        $omschrijving === '' ||
        $bedragValue === false ||
        !is_finite($bedragValue) ||
        $bedragValue <= 0 ||
        !preg_match('/^\d+(?:\.\d{1,2})?$/', $bedrag)
    ) {
        $error = "Vul een geldig positief bedrag in";
    } else {
        // Controleer of de ontvanger bestaat
        $stmt = $pdo->prepare("SELECT id, username FROM user WHERE username = ?");
        $stmt->execute([$ontvangerNaam]);
        $ontvanger = $stmt->fetch();

        if($ontvanger) {
            try {
                $pdo->beginTransaction();

                $stmt = $pdo->prepare("UPDATE user SET balance = balance - ? WHERE id = ? AND balance >= ?");
                $stmt->execute([$bedragValue, $_SESSION['user']['id'], $bedragValue]);

                if ($stmt->rowCount() !== 1) {
                    $pdo->rollBack();
                    $error = "Je hebt niet genoeg saldo om dit bedrag over te maken";
                } else {
                    $stmt = $pdo->prepare("UPDATE user SET balance = balance + ? WHERE id = ?");
                    $stmt->execute([$bedragValue, $ontvanger['id']]);

                    $stmt = $pdo->prepare("INSERT INTO transaction (sender, receiver, amount, description) VALUES (?, ?, ?, ?)");
                    $stmt->execute([$_SESSION['user']['id'], $ontvanger['id'], $bedragValue, $omschrijving]);

                    $pdo->commit();

                    $stmt = $pdo->prepare("SELECT balance FROM user WHERE id = ?");
                    $stmt->execute([$_SESSION['user']['id']]);
                    $saldo = $stmt->fetchColumn();
                    $_SESSION['user']['balance'] = $saldo;

                    $success = "Het bedrag is succesvol overgemaakt";
                }
            } catch (Throwable $exception) {
                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }
                $error = "Overmaken mislukt";
            }
        } else {
            $error = "Deze gebruiker bestaat niet";
        }
    }

}

include 'includes/db.php';

// Haal het saldo van de ingelogde gebruiker op
$stmt = $pdo->prepare("SELECT balance FROM user WHERE id = ?");
$stmt->execute([$_SESSION['user']['id']]);
$saldo = $stmt->fetchColumn();
?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Omanido</title>
    <!-- Voeg Tailwind CSS toe via CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <?php include 'includes/header.php'; ?>

    <div class="container mx-auto p-4">
        <div class="flex flex-wrap -mx-2">
            <!-- Saldo Kaart -->
            <div class="w-full md:w-1/3 px-2 mb-4">
                <div class="bg-white p-6 rounded-lg shadow-md h-full flex flex-col justify-between">
                    <div>
                        <h3 class="font-bold text-xl mb-2">Mijn Saldo</h3>
                        <p class="text-sm text-gray-600 mb-4">Actueel Beschikbaar Saldo</p>
                    </div>
                    <p class="text-2xl md:text-3xl font-bold mb-4 <?php echo $saldo >= 0 ? 'text-green-500' : 'text-red-500'; ?> self-center break-all leading-tight text-center w-full">
                        <?= format_money($saldo) ?>
                    </p>
                    <div class="text-center">
                        <a href="transacties.php" class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded">
                            Transactieoverzicht
                        </a>
                    </div>
                </div>
            </div>


            <!-- Overdrachtsformulier Kaart -->
            <div class="w-full md:w-2/3 px-2 mb-4">
                <div class="bg-white p-6 rounded-lg shadow-md h-full"> <!-- Verhoogde padding van p-4 naar p-6 -->
                    <h3 class="font-bold text-xl mb-4">Geld Overmaken</h3>
                    <form action="<?php echo e($_SERVER["PHP_SELF"]); ?>" method="post">
                        <div class="mb-4">
                            <label for="ontvanger" class="block text-sm font-medium text-gray-700">Ontvanger:</label>
                            <input type="text" id="ontvanger" name="ontvanger" required class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3">
                        </div>
                        <div class="mb-4">
                            <label for="bedrag" class="block text-sm font-medium text-gray-700">Bedrag(€):</label>
                            <input type="text" id="bedrag" name="bedrag" inputmode="decimal" pattern="^\d+(?:\.\d{1,2})?$" required class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3">
                        </div>
                        <div class="mb-4">
                            <label for="omschrijving" class="block text-sm font-medium text-gray-700">Omschrijving:</label>
                            <input type="text" id="omschrijving" name="omschrijving" required class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3">
                        </div>
                        <input type="submit" value="Overmaken" class="w-full bg-blue-600 text-white font-bold py-2 px-4 rounded hover:bg-blue-700 focus:outline-none focus:shadow-outline">
                        <?php
                            if(isset($error)) {
                                echo '<p class="text-red-500 text-sm mt-2">' . e($error) . '</p>';
                            }
                            if(isset($success)) {
                                echo '<p class="text-green-500 text-sm mt-2">' . e($success) . '</p>';
                            }
                        ?>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
