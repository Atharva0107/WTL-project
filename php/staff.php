
<?php
// PURPOSE: STAFF DESK: changes vehicle availability and shows saved bookings and customer enquiries.
require_once __DIR__ . '/config.local.php';

// Update vehicle availability.
if (
    $_SERVER['REQUEST_METHOD'] === 'POST'
    && isset($_POST['vehicle_id'])
) {
    $vehicleId = (int)$_POST['vehicle_id'];
    $available = isset($_POST['available']) ? 1 : 0;

    $stmt = $conn->prepare(
        'UPDATE vehicles SET available = ? WHERE id = ?'
    );

    $stmt->bind_param('ii', $available, $vehicleId);
    $stmt->execute();
    $stmt->close();

    header('Location: staff.php');
    exit;
}

// Get all vehicles.
$vehicles = [];
$result = $conn->query(
    'SELECT id, name, available FROM vehicles ORDER BY id'
);

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $vehicles[] = $row;
    }
}

// Get contact enquiries.
$enquiries = [];
$enquiryError = false;

$result = $conn->query(
    'SELECT * FROM enquiries ORDER BY id DESC'
);

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $enquiries[] = $row;
    }
} else {
    $enquiryError = true;
}

// Get bookings with vehicle names and pickup/return times.
$bookings = [];
$bookingError = false;

$result = $conn->query(
    "SELECT
        bookings.id,
        bookings.customer_name,
        bookings.phone,
        vehicles.name AS vehicle_name,
        bookings.pickup_datetime,
        bookings.return_datetime,
        bookings.days,
        bookings.total,
        bookings.created_at
     FROM bookings
     LEFT JOIN vehicles ON bookings.vehicle_id = vehicles.id
     ORDER BY bookings.id DESC"
);

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $bookings[] = $row;
    }
} else {
    $bookingError = true;
}
?>
<!-- HTML OUTPUT: the page layout shown in the browser starts here -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Staff Desk | EasyRide</title>

    <link rel="stylesheet" href="../public/css/style.css">

    <style>
        main {
            max-width: 1200px;
            margin: 25px auto;
            padding: 20px;
        }

        .staff-section {
            margin: 30px 0;
            padding: 22px;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
        }

        .table-wrapper {
            width: 100%;
            overflow-x: auto;
            margin-top: 18px;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 650px;
        }

        th, td {
            padding: 13px 16px;
            text-align: left;
            border-bottom: 1px solid #e5e5e5;
            vertical-align: middle;
            line-height: 1.5;
        }

        th {
            background: #f1f4f8;
            font-weight: 600;
            white-space: nowrap;
        }

        tbody tr:nth-child(even) {
            background: #fafafa;
        }

        tbody tr:hover {
            background: #f0f6ff;
        }

        td form {
            margin: 0;
        }

        input[type="checkbox"] {
            width: 18px;
            height: 18px;
            vertical-align: middle;
            cursor: pointer;
        }

        .empty-message {
            padding: 12px 0;
        }

        @media (max-width: 600px) {
            main {
                padding: 10px;
            }

            .staff-section {
                padding: 14px;
            }

            th, td {
                padding: 10px 12px;
            }
        }
    </style>
</head>

<body>

<header>
    <div class="brand">EasyRide Staff Desk</div>
    <nav>
        <a href="../public/index.html">Home</a>
        <a href="vehicles.php">Vehicles</a>
        <a href="booking.php">Booking</a>
        <a href="contact.php">Contact form</a>
    </nav>
</header>

<main>
    <h1>Staff Desk</h1>
    <p>Manage vehicle availability, bookings and customer enquiries.</p>

    <section class="staff-section">
        <h2>Vehicle Availability</h2>
        <p>Tick to make a vehicle available. Untick to make it unavailable.</p>

        <?php if (count($vehicles) === 0): ?>
            <p class="empty-message">No vehicles found.</p>
        <?php else: ?>
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>Vehicle</th>
                            <th>Available for Booking</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($vehicles as $vehicle): ?>
                            <tr>
                                <td>
                                    <?= htmlspecialchars(
                                        $vehicle['name'],
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>
                                </td>
                                <td>
                                    <form method="POST" action="staff.php">
                                        <input
                                            type="hidden"
                                            name="vehicle_id"
                                            value="<?= (int)$vehicle['id'] ?>"
                                        >

                                        <input
                                            type="checkbox"
                                            name="available"
                                            value="1"
                                            aria-label="Toggle availability for <?= htmlspecialchars($vehicle['name'], ENT_QUOTES, 'UTF-8') ?>"
                                            <?= (int)$vehicle['available'] === 1 ? 'checked' : '' ?>
                                            onchange="this.form.submit()"
                                        >

                                        <?= (int)$vehicle['available'] === 1
                                            ? 'Available'
                                            : 'Unavailable' ?>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </section>

    <section class="staff-section">
        <h2>Booking Details</h2>

        <?php if ($bookingError): ?>
            <p>Unable to load bookings. Please check your database columns.</p>
        <?php elseif (count($bookings) === 0): ?>
            <p class="empty-message">No bookings submitted yet.</p>
        <?php else: ?>
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>Booking ID</th>
                            <th>Customer Name</th>
                            <th>Phone</th>
                            <th>Vehicle</th>
                            <th>Pickup Date &amp; Time</th>
                            <th>Return Date &amp; Time</th>
                            <th>Days Charged</th>
                            <th>Total (₹)</th>
                            <th>Booking Created</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($bookings as $booking): ?>
                            <tr>
                                <td><?= (int)$booking['id'] ?></td>

                                <td>
                                    <?= htmlspecialchars(
                                        $booking['customer_name'] ?? '',
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars(
                                        $booking['phone'] ?? '',
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars(
                                        $booking['vehicle_name'] ?? 'Vehicle unavailable',
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>
                                </td>

                                <td>
                                    <?= $booking['pickup_datetime']
                                        ? htmlspecialchars(
                                            date('d M Y, h:i A', strtotime($booking['pickup_datetime'])),
                                            ENT_QUOTES,
                                            'UTF-8'
                                        )
                                        : 'Older booking' ?>
                                </td>

                                <td>
                                    <?= $booking['return_datetime']
                                        ? htmlspecialchars(
                                            date('d M Y, h:i A', strtotime($booking['return_datetime'])),
                                            ENT_QUOTES,
                                            'UTF-8'
                                        )
                                        : 'Older booking' ?>
                                </td>

                                <td><?= (int)$booking['days'] ?></td>

                                <td>
                                    ₹<?= number_format((float)$booking['total'], 2) ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars(
                                        $booking['created_at'] ?? '',
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </section>

    <section class="staff-section">
        <h2>Contact Enquiries</h2>

        <?php if ($enquiryError): ?>
            <p>Unable to load contact enquiries. Please check the enquiries table.</p>
        <?php elseif (count($enquiries) === 0): ?>
            <p class="empty-message">No enquiries submitted yet.</p>
        <?php else: ?>
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <?php foreach (array_keys($enquiries[0]) as $column): ?>
                                <th>
                                    <?= htmlspecialchars(
                                        ucwords(str_replace('_', ' ', $column)),
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>
                                </th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($enquiries as $enquiry): ?>
                            <tr>
                                <?php foreach ($enquiry as $value): ?>
                                    <td>
                                        <?= htmlspecialchars(
                                            (string)($value ?? ''),
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?>
                                    </td>
                                <?php endforeach; ?>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </section>
</main>

</body>
</html>
