
<?php
// PURPOSE: BOOKING PAGE: loads available vehicles, checks customer details and rental dates, calculates the price, and saves a booking.
require_once __DIR__ . '/config.local.php';

$message = '';
$vehicles = [];
$selected_vehicle = (int)($_POST['vehicle_id'] ?? 0);
$pickup_value = $_POST['pickup_datetime'] ?? '';
$return_value = $_POST['return_datetime'] ?? '';

// Load vehicles that the staff has marked as available.
$result = $conn->query(
    "SELECT id, name, daily_rate
     FROM vehicles
     WHERE available = 1
     ORDER BY name"
);

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $vehicles[] = $row;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $customer_name = trim($_POST['customer_name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $vehicle_id = (int)($_POST['vehicle_id'] ?? 0);

    // Validate the date/time values.
    $pickup = DateTime::createFromFormat('Y-m-d\TH:i', $pickup_value);
    $return = DateTime::createFromFormat('Y-m-d\TH:i', $return_value);

    $valid_pickup = $pickup &&
        $pickup->format('Y-m-d\TH:i') === $pickup_value;

    $valid_return = $return &&
        $return->format('Y-m-d\TH:i') === $return_value;

    if (
        $customer_name === '' ||
        !preg_match('/^[0-9]{10}$/', $phone) ||
        $vehicle_id < 1 ||
        !$valid_pickup ||
        !$valid_return
    ) {
        $message = 'Please enter your name, a valid 10-digit phone number, a vehicle, and valid pickup and return dates/times.';
    } elseif (
        $pickup <= new DateTime() ||
        $return <= $pickup
    ) {
        $message = 'Pickup must be in the future, and return must be after pickup.';
    } else {
        $pickup_sql = $pickup->format('Y-m-d H:i:s');
        $return_sql = $return->format('Y-m-d H:i:s');

        // Charge for each started 24-hour rental period.
        $seconds = $return->getTimestamp() - $pickup->getTimestamp();
        $days = (int)ceil($seconds / 86400);

        // Check the vehicle's rate and current availability.
        $stmt = $conn->prepare(
            "SELECT daily_rate
             FROM vehicles
             WHERE id = ? AND available = 1"
        );

        $stmt->bind_param('i', $vehicle_id);
        $stmt->execute();
        $stmt->bind_result($daily_rate);

        if ($stmt->fetch()) {
            $stmt->close();

            // Prevent overlapping reservations for this vehicle.
            $stmt = $conn->prepare(
                "SELECT COUNT(*)
                 FROM bookings
                 WHERE vehicle_id = ?
                   AND pickup_datetime IS NOT NULL
                   AND return_datetime IS NOT NULL
                   AND pickup_datetime < ?
                   AND return_datetime > ?"
            );

            $stmt->bind_param(
                'iss',
                $vehicle_id,
                $return_sql,
                $pickup_sql
            );
            $stmt->execute();
            $stmt->bind_result($overlap_count);
            $stmt->fetch();
            $stmt->close();

            if ($overlap_count > 0) {
                $message = 'This vehicle is already booked for some or all of those dates. Please choose another time.';
            } else {
                $total = $daily_rate * $days;

                // Save the booking and its pickup/return times.
                $stmt = $conn->prepare(
                    "INSERT INTO bookings
                     (customer_name, phone, vehicle_id, days, total,
                      pickup_datetime, return_datetime)
                     VALUES (?, ?, ?, ?, ?, ?, ?)"
                );

                $stmt->bind_param(
                    'ssiidss',
                    $customer_name,
                    $phone,
                    $vehicle_id,
                    $days,
                    $total,
                    $pickup_sql,
                    $return_sql
                );

                if ($stmt->execute()) {
                    $message = 'Booking saved successfully! Rental duration: ' .
                        $days . ' day(s). Total: ₹' .
                        number_format($total, 2);
                } else {
                    $message = 'Booking could not be saved. Please try again.';
                }

                $stmt->close();
            }
        } else {
            $stmt->close();
            $message = 'Please select a vehicle that is currently available.';
        }
    }
}
?>
<!-- HTML OUTPUT: the page layout shown in the browser starts here -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book a Vehicle - EasyRide</title>
    <link rel="stylesheet" href="../public/css/style.css">
</head>
<body>

<header>
    <h1>EasyRide Vehicle Rental</h1>
    <nav>
        <a href="../public/index.html">Home</a>
        <a href="vehicles.php">Our Vehicles</a>
        <a href="contact.php">Contact Us</a>
    </nav>
</header>

<main>
    <h2>Book a Vehicle</h2>

    <?php if ($message !== ''): ?>
        <p role="status">
            <?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?>
        </p>
    <?php endif; ?>

    <?php if (count($vehicles) === 0): ?>
        <p>No vehicles are currently available.</p>
    <?php else: ?>
        <form method="POST" action="booking.php">

            <label for="customer_name">Full Name</label>
            <input
                type="text"
                id="customer_name"
                name="customer_name"
                value="<?= htmlspecialchars($_POST['customer_name'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                required
            >

            <label for="phone">Phone Number</label>
            <input
                type="tel"
                id="phone"
                name="phone"
                pattern="[0-9]{10}"
                minlength="10"
                maxlength="10"
                inputmode="numeric"
                placeholder="Enter 10-digit phone number"
                value="<?= htmlspecialchars($_POST['phone'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                required
            >

            <label for="vehicle_id">Select Available Vehicle</label>
            <select id="vehicle_id" name="vehicle_id" required>
                <option value="">Choose a vehicle</option>

                <?php foreach ($vehicles as $vehicle): ?>
                    <option
                        value="<?= (int)$vehicle['id'] ?>"
                        <?= $selected_vehicle === (int)$vehicle['id'] ? 'selected' : '' ?>
                    >
                        <?= htmlspecialchars($vehicle['name'], ENT_QUOTES, 'UTF-8') ?>
                        — ₹<?= number_format((float)$vehicle['daily_rate'], 2) ?>/day
                    </option>
                <?php endforeach; ?>
            </select>

            <label for="pickup_datetime">Pickup Date and Time</label>
            <input
                type="datetime-local"
                id="pickup_datetime"
                name="pickup_datetime"
                value="<?= htmlspecialchars($pickup_value, ENT_QUOTES, 'UTF-8') ?>"
                min="<?= date('Y-m-d\TH:i', time() + 60) ?>"
                required
            >

            <label for="return_datetime">Return Date and Time</label>
            <input
                type="datetime-local"
                id="return_datetime"
                name="return_datetime"
                value="<?= htmlspecialchars($return_value, ENT_QUOTES, 'UTF-8') ?>"
                required
            >

            <p>
                Rental charges are calculated for each started 24-hour period.
            </p>

            <button type="submit">Confirm Booking</button>
        </form>
    <?php endif; ?>
</main>

<script>
const pickupInput = document.getElementById('pickup_datetime');
const returnInput = document.getElementById('return_datetime');

if (pickupInput && returnInput) {
    pickupInput.addEventListener('change', function () {
        returnInput.min = pickupInput.value;
        if (returnInput.value && returnInput.value <= pickupInput.value) {
            returnInput.value = '';
        }
    });

    returnInput.addEventListener('change', function () {
        if (pickupInput.value && returnInput.value <= pickupInput.value) {
            alert('Return date and time must be after pickup.');
            returnInput.value = '';
        }
    });
}
</script>

</body>
</html>
