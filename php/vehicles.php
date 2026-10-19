
<?php
// PURPOSE: LIVE VEHICLE LIST: reads available vehicle details from the database and displays them.
require_once __DIR__ . '/config.local.php';

$vehicles = [];

$result = $conn->query(
    "SELECT id, name, category, daily_rate, available
     FROM vehicles
     WHERE available = 1
     ORDER BY name"
);

$imageByCategory = [
    'Hatchback' => 'https://images.unsplash.com/photo-1552519507-da3b142c6e3d?auto=format&fit=crop&w=900&q=80',
    'Sedan' => 'https://images.unsplash.com/photo-1492144534655-ae79c964c9d7?auto=format&fit=crop&w=900&q=80',
    'SUV' => 'https://images.unsplash.com/photo-1519641471654-76ce0107ad1b?auto=format&fit=crop&w=900&q=80',
    'Premium' => 'https://images.unsplash.com/photo-1503376780353-7e6692767b70?auto=format&fit=crop&w=900&q=80',
    'MPV' => 'https://images.unsplash.com/photo-1549317661-bd32c8ce0db2?auto=format&fit=crop&w=900&q=80',
    'Crossover' => 'https://images.unsplash.com/photo-1533473359331-0135ef1b58bf?auto=format&fit=crop&w=900&q=80'
];

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $vehicles[] = $row;
    }
}
?>
<!-- HTML OUTPUT: the page layout shown in the browser starts here -->
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Available Vehicles - EasyRide</title>
    <link rel="stylesheet" href="../public/css/style.css">
</head>
<body>

<header>
    <div class="brand">EasyRide Vehicle Rentals</div>
    <nav>
        <a href="../public/index.html">Home</a>
        <a href="../public/fleet.html">Fleet information</a>
        <a href="booking.php">Book a Vehicle</a>
        <a href="contact.php">Contact Us</a>
    </nav>
</header>

<main>
    <h1>Available Vehicles</h1>
    <p>This list shows vehicles currently available for booking.</p>

    <?php if (count($vehicles) === 0): ?>
        <p class="notice">No vehicles are currently available.</p>
    <?php else: ?>
        <div class="grid vehicle-grid">
            <?php foreach ($vehicles as $vehicle): ?>
                <article class="card vehicle-card">

                    <img
                        class="vehicle-image"
                        src="<?= htmlspecialchars(
                            $imageByCategory[$vehicle['category']]
                            ?? $imageByCategory['Hatchback'],
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?>"
                        alt="<?= htmlspecialchars(
                            $vehicle['name'],
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?>"
                    >

                    <h2><?= htmlspecialchars(
                        $vehicle['name'],
                        ENT_QUOTES,
                        'UTF-8'
                    ) ?></h2>

                    <p>Category: <?= htmlspecialchars(
                        $vehicle['category'],
                        ENT_QUOTES,
                        'UTF-8'
                    ) ?></p>

                    <p>
                        <strong>₹<?= number_format(
                            (float)$vehicle['daily_rate'],
                            2
                        ) ?>/day</strong>
                    </p>

                    <p>Available</p>

                    <a class="button" href="booking.php">
                        Book this vehicle
                    </a>

                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</main>

</body>
</html>
