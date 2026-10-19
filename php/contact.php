<?php
// PURPOSE: Processes the contact form and saves customer enquiries in the database, then displays a result message.
require_once __DIR__ . '/config.local.php';

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $text = trim($_POST['message'] ?? '');

    if ($name === '' || !preg_match('/^[0-9]{10}$/', $phone) || $text === '') {
        $message = 'Enter your name, a valid 10-digit phone number and a message.';
    } else {
        $stmt = $conn->prepare('INSERT INTO enquiries (customer_name, phone, message) VALUES (?, ?, ?)');
        $stmt->bind_param('sss', $name, $phone, $text);
        if ($stmt->execute()) {
            $message = 'Enquiry saved successfully.';
        } else {
            $message = 'Could not save enquiry. Please try again.';
        }
        $stmt->close();
    }
}
?>
<!-- HTML OUTPUT: the page shown in the browser starts below -->
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Contact EasyRide</title>
<link rel="stylesheet" href="../public/css/style.css">
</head>
<body>
<header><div class="brand">EasyRide Vehicle Rentals</div><nav>
<a href="../public/index.html">Home</a>
<a href="vehicles.php">Our Vehicles</a>
<a href="booking.php">Book a Vehicle</a>
<a href="staff.php">Staff Desk</a>
</nav></header>
<main>
<h1>Contact Us</h1>
<p>Send us your question about vehicle rentals.</p>
<?php if ($message !== ''): ?><p class="notice"><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></p><?php endif; ?>
<form method="post" action="contact.php">
<label for="name">Your Name</label><input type="text" id="name" name="name" required>
<label for="phone">Phone Number</label><input type="tel" id="phone" name="phone" pattern="[0-9]{10}" minlength="10" maxlength="10" inputmode="numeric" placeholder="Enter 10-digit phone number" required>
<label for="message">Your Message</label><textarea id="message" name="message" required></textarea>
<button type="submit">Send Message</button>
</form>
</main>
</body></html>
