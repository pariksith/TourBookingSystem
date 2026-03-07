<?php
session_start();
require_once 'database.php';

if (!isLoggedIn()) redirect('login.php');

$booking_id = isset($_GET['booking_id']) ? (int)$_GET['booking_id'] : 0;

// Fetch booking with package info
$stmt = $conn->prepare("
    SELECT b.*, p.destination, p.duration, p.image
    FROM bookings b
    JOIN packages p ON b.package_id = p.id
    WHERE b.id = ? AND b.user_id = ?
");
$stmt->bind_param("ii", $booking_id, $_SESSION['user_id']);
$stmt->execute();
$booking = $stmt->get_result()->fetch_assoc();

if (!$booking) redirect('my_bookings.php');

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $method = clean($conn, $_POST['payment_method']);
    $valid_methods = ['credit_card', 'debit_card', 'upi', 'net_banking'];

    if (!in_array($method, $valid_methods)) {
        $error = "Please select a valid payment method.";
    } else {
        $transaction_id = 'TXN' . strtoupper(uniqid());
        $ins = $conn->prepare("INSERT INTO payments (booking_id, payment_method, payment_status, transaction_id, amount) VALUES (?, ?, 'completed', ?, ?)");
        $ins->bind_param("issd", $booking_id, $method, $transaction_id, $booking['total_price']);

        if ($ins->execute()) {
            // Update booking status
            $conn->query("UPDATE bookings SET status = 'confirmed' WHERE id = $booking_id");
            $success = "Payment successful! Transaction ID: <strong>$transaction_id</strong>";
        } else {
            $error = "Payment failed. Please try again.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment – InteleTour</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<nav class="navbar">
    <div class="logo">✈ Intele<span>Tour</span></div>
    <ul class="nav-links">
        <li><a href="index.php">Home</a></li>
        <li><a href="packages.php">Packages</a></li>
        <li><a href="my_bookings.php">My Bookings</a></li>
        <li><a href="logout.php" class="btn-nav">Logout</a></li>
    </ul>
</nav>

<div class="page-header">
    <h1>💳 Secure Payment</h1>
    <p>Complete your booking payment securely</p>
</div>

<div class="payment-container">
    <?php if ($success): ?>
        <div class="alert alert-success"><?= $success ?></div>
        <div style="text-align:center; margin-top:20px;">
            <a href="my_bookings.php" class="btn btn-primary">View My Bookings</a>
            <a href="packages.php" class="btn btn-secondary" style="margin-left:10px;">Book Another</a>
        </div>
    <?php else: ?>

    <div class="payment-card">
        <div class="payment-card-header">
            <h2>📍 <?= htmlspecialchars($booking['destination']) ?></h2>
            <p><?= htmlspecialchars($booking['duration']) ?> · <?= $booking['travelers'] ?> Traveler(s)</p>
            <p style="font-size:22px; font-weight:800; margin-top:8px;">₹<?= number_format($booking['total_price'], 2) ?></p>
        </div>
        <div class="payment-card-body">
            <?php if ($error): ?><div class="alert alert-danger"><?= $error ?></div><?php endif; ?>
            <form method="POST">
                <input type="hidden" name="payment_method" id="payment_method_input" value="">
                <p style="font-weight:600; margin-bottom:12px; color:var(--dark);">Select Payment Method:</p>
                <div class="payment-method-options">
                    <div class="payment-method-option" data-value="credit_card">💳 Credit Card</div>
                    <div class="payment-method-option" data-value="debit_card">🏧 Debit Card</div>
                    <div class="payment-method-option" data-value="upi">📱 UPI</div>
                    <div class="payment-method-option" data-value="net_banking">🏦 Net Banking</div>
                </div>
                <button type="submit" class="btn btn-primary btn-full" style="margin-top:10px;">
                    Pay ₹<?= number_format($booking['total_price'], 2) ?> Now
                </button>
            </form>
        </div>
    </div>

    <?php endif; ?>
</div>

<footer><p>© 2025 <span>InteleTour</span></p></footer>
<script src="script.js"></script>
</body>
</html>
