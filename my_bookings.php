<?php
session_start();
require_once 'database.php';

if (!isLoggedIn()) redirect('login.php?msg=Please login to view your bookings.');

$user_id = $_SESSION['user_id'];

$stmt = $conn->prepare("
    SELECT b.*, p.destination, p.duration, p.image,
           pay.payment_status, pay.payment_method, pay.transaction_id
    FROM bookings b
    JOIN packages p ON b.package_id = p.id
    LEFT JOIN payments pay ON pay.booking_id = b.id
    WHERE b.user_id = ?
    ORDER BY b.created_at DESC
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$bookings = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Bookings – InteleTour</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<nav class="navbar">
    <div class="logo">✈ Intele<span>Tour</span></div>
    <ul class="nav-links">
        <li><a href="index.php">Home</a></li>
        <li><a href="packages.php">Packages</a></li>
        <li><a href="my_bookings.php" class="active">My Bookings</a></li>
        <li><a href="logout.php" class="btn-nav">Logout</a></li>
    </ul>
</nav>

<div class="page-header">
    <h1>📋 My Bookings</h1>
    <p>Welcome back, <?= htmlspecialchars($_SESSION['username']) ?>! Here are your trips.</p>
</div>

<div class="bookings-table-container">
    <?php if ($bookings->num_rows === 0): ?>
        <div class="alert alert-info" style="text-align:center; font-size:16px;">
            You have no bookings yet. <a href="packages.php" style="color:var(--primary); font-weight:600;">Browse Packages</a>
        </div>
    <?php else: ?>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Destination</th>
                <th>Travel Date</th>
                <th>Travelers</th>
                <th>Total Price</th>
                <th>Booking Status</th>
                <th>Payment</th>
                <th>Transaction ID</th>
            </tr>
        </thead>
        <tbody>
            <?php $i = 1; while ($b = $bookings->fetch_assoc()): ?>
            <tr>
                <td><?= $i++ ?></td>
                <td>
                    <div style="display:flex; align-items:center; gap:10px;">
                        <img src="<?= htmlspecialchars($b['image']) ?>" style="width:45px; height:35px; object-fit:cover; border-radius:5px;">
                        <strong><?= htmlspecialchars($b['destination']) ?></strong>
                    </div>
                </td>
                <td><?= date('d M Y', strtotime($b['booking_date'])) ?></td>
                <td><?= $b['travelers'] ?></td>
                <td style="font-weight:700; color:var(--accent);">₹<?= number_format($b['total_price'], 2) ?></td>
                <td><span class="badge badge-<?= $b['status'] ?>"><?= ucfirst($b['status']) ?></span></td>
                <td>
                    <?php if ($b['payment_status']): ?>
                        <span class="badge badge-<?= $b['payment_status'] ?>"><?= ucfirst($b['payment_status']) ?></span>
                    <?php else: ?>
                        <a href="payment.php?booking_id=<?= $b['id'] ?>" class="btn btn-primary btn-sm">Pay Now</a>
                    <?php endif; ?>
                </td>
                <td style="font-size:12px; color:var(--gray);"><?= $b['transaction_id'] ?? '—' ?></td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
    <?php endif; ?>
</div>

<footer><p>© 2025 <span>InteleTour</span></p></footer>
<script src="script.js"></script>
</body>
</html>
