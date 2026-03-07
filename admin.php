<?php
session_start();
require_once 'database.php';
$usd_rate = 83.00; // 1 USD = 83 INR

if (!isAdmin()) redirect('login.php?msg=Admin access required.');

$message = '';
$msg_type = 'success';

// ===== ADD PACKAGE =====
if (isset($_POST['add_package'])) {
    $dest  = clean($conn, $_POST['destination']);
    $desc  = clean($conn, $_POST['description']);
    $price = (float)$_POST['price'];
    $dur   = clean($conn, $_POST['duration']);
    $img   = clean($conn, $_POST['image']);

    $stmt = $conn->prepare("INSERT INTO packages (destination, description, price, duration, image) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("ssdss", $dest, $desc, $price, $dur, $img);
    if ($stmt->execute()) {
        $message = "Package added successfully!";
    } else {
        $message = "Failed to add package."; $msg_type = 'danger';
    }
}

// ===== DELETE PACKAGE =====
if (isset($_POST['delete_package'])) {
    $pid = (int)$_POST['package_id'];
    $conn->query("DELETE FROM packages WHERE id = $pid");
    $message = "Package deleted.";
}

// ===== UPDATE BOOKING STATUS =====
if (isset($_POST['update_booking'])) {
    $bid    = (int)$_POST['booking_id'];
    $status = clean($conn, $_POST['status']);
    $conn->query("UPDATE bookings SET status = '$status' WHERE id = $bid");
    $message = "Booking status updated.";
}

// ===== FETCH DATA =====
$packages = $conn->query("SELECT * FROM packages ORDER BY id DESC");
$bookings = $conn->query("
    SELECT b.*, u.username, u.email, p.destination
    FROM bookings b
    JOIN users u ON b.user_id = u.id
    JOIN packages p ON b.package_id = p.id
    ORDER BY b.created_at DESC
");
$payments = $conn->query("
    SELECT pay.*, b.id as bid, p.destination, u.username
    FROM payments pay
    JOIN bookings b ON pay.booking_id = b.id
    JOIN packages p ON b.package_id = p.id
    JOIN users u ON b.user_id = u.id
    ORDER BY pay.paid_at DESC
");

// Stats
$total_users    = $conn->query("SELECT COUNT(*) as c FROM users")->fetch_assoc()['c'];
$total_bookings = $conn->query("SELECT COUNT(*) as c FROM bookings")->fetch_assoc()['c'];
$total_revenue  = $conn->query("SELECT SUM(amount) as s FROM payments WHERE payment_status='completed'")->fetch_assoc()['s'] ?? 0;
$total_packages = $conn->query("SELECT COUNT(*) as c FROM packages")->fetch_assoc()['c'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel – InteleTour</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<nav class="navbar">
    <div class="logo">✈ Intele<span>Tour</span> Admin</div>
    <ul class="nav-links">
        <li><a href="index.php">View Site</a></li>
        <li style="color:rgba(255,255,255,0.7); font-size:14px;">👤 <?= htmlspecialchars($_SESSION['admin_username']) ?></li>
        <li><a href="logout.php" class="btn-nav">Logout</a></li>
    </ul>
</nav>

<div class="page-header">
    <h1>⚙️ Admin Dashboard</h1>
    <p>Manage packages, bookings, and payments</p>
</div>

<div class="admin-container">

    <?php if ($message): ?>
        <div class="alert alert-<?= $msg_type ?>"><?= $message ?></div>
    <?php endif; ?>

    <!-- STATS -->
    <div class="stats-grid">
        <div class="stat-card">
            <h3><?= $total_users ?></h3>
            <p>Registered Users</p>
        </div>
        <div class="stat-card">
            <h3><?= $total_packages ?></h3>
            <p>Tour Packages</p>
        </div>
        <div class="stat-card">
            <h3><?= $total_bookings ?></h3>
            <p>Total Bookings</p>
        </div>
        <div class="stat-card" style="border-top-color: var(--accent);">
            <h3 style="color:var(--accent);">₹<?= number_format($total_revenue, 0) ?></h3>
            <p>Total Revenue</p>
        </div>
    </div>

    <!-- TABS -->
    <div class="admin-tabs">
        <button class="tab-btn active" data-tab="tab-packages">📦 Packages</button>
        <button class="tab-btn" data-tab="tab-add">➕ Add Package</button>
        <button class="tab-btn" data-tab="tab-bookings">📋 Bookings</button>
        <button class="tab-btn" data-tab="tab-payments">💳 Payments</button>
    </div>

    <!-- TAB: PACKAGES -->
    <div id="tab-packages" class="tab-content active">
        <table>
            <thead>
                <tr><th>#</th><th>Destination</th><th>Price</th><th>Duration</th><th>Status</th><th>Action</th></tr>
            </thead>
            <tbody>
                <?php $packages->data_seek(0); while ($p = $packages->fetch_assoc()): ?>
                <tr>
                    <td><?= $p['id'] ?></td>
                    <td><strong><?= htmlspecialchars($p['destination']) ?></strong></td>
                    <td>&#8377;<?= number_format($p['price'], 2) ?><br><small>$<?= number_format(((float)$p['price']) / $usd_rate, 2) ?></small></td>
                    <td><?= htmlspecialchars($p['duration']) ?></td>
                    <td><span class="badge <?= $p['available'] ? 'badge-confirmed' : 'badge-cancelled' ?>"><?= $p['available'] ? 'Active' : 'Inactive' ?></span></td>
                    <td>
                        <form method="POST" class="delete-form" style="display:inline;">
                            <input type="hidden" name="package_id" value="<?= $p['id'] ?>">
                            <button type="submit" name="delete_package" class="btn btn-danger btn-sm">Delete</button>
                        </form>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <!-- TAB: ADD PACKAGE -->
    <div id="tab-add" class="tab-content">
        <div style="max-width:600px;">
            <form method="POST">
                <div class="form-group">
                    <label>Destination Name *</label>
                    <input type="text" name="destination" placeholder="e.g. Maldives" required>
                </div>
                <div class="form-group">
                    <label>Description *</label>
                    <textarea name="description" rows="4" placeholder="Describe the package..." required></textarea>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Price (₹) *</label>
                        <input type="number" name="price" placeholder="e.g. 75000" step="0.01" required>
                    </div>
                    <div class="form-group">
                        <label>Duration *</label>
                        <input type="text" name="duration" placeholder="e.g. 5 Days / 4 Nights" required>
                    </div>
                </div>
                <div class="form-group">
                    <label>Image Path</label>
                    <input type="text" name="image" placeholder="images/maldives.jpg">
                </div>
                <button type="submit" name="add_package" class="btn btn-primary">Add Package</button>
            </form>
        </div>
    </div>

    <!-- TAB: BOOKINGS -->
    <div id="tab-bookings" class="tab-content">
        <table>
            <thead>
                <tr><th>#</th><th>User</th><th>Destination</th><th>Date</th><th>Travelers</th><th>Total</th><th>Status</th><th>Update</th></tr>
            </thead>
            <tbody>
                <?php while ($b = $bookings->fetch_assoc()): ?>
                <tr>
                    <td><?= $b['id'] ?></td>
                    <td><?= htmlspecialchars($b['username']) ?><br><small><?= htmlspecialchars($b['email']) ?></small></td>
                    <td><?= htmlspecialchars($b['destination']) ?></td>
                    <td><?= date('d M Y', strtotime($b['booking_date'])) ?></td>
                    <td><?= $b['travelers'] ?></td>
                    <td>₹<?= number_format($b['total_price'], 2) ?></td>
                    <td><span class="badge badge-<?= $b['status'] ?>"><?= ucfirst($b['status']) ?></span></td>
                    <td>
                        <form method="POST" style="display:flex; gap:6px; align-items:center;">
                            <input type="hidden" name="booking_id" value="<?= $b['id'] ?>">
                            <select name="status" style="padding:5px; border-radius:5px; border:1px solid #ccc; font-size:13px;">
                                <option value="pending" <?= $b['status']=='pending'?'selected':'' ?>>Pending</option>
                                <option value="confirmed" <?= $b['status']=='confirmed'?'selected':'' ?>>Confirmed</option>
                                <option value="cancelled" <?= $b['status']=='cancelled'?'selected':'' ?>>Cancelled</option>
                            </select>
                            <button type="submit" name="update_booking" class="btn btn-secondary btn-sm">Update</button>
                        </form>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <!-- TAB: PAYMENTS -->
    <div id="tab-payments" class="tab-content">
        <table>
            <thead>
                <tr><th>#</th><th>User</th><th>Destination</th><th>Method</th><th>Amount</th><th>Status</th><th>Transaction ID</th><th>Date</th></tr>
            </thead>
            <tbody>
                <?php while ($pay = $payments->fetch_assoc()): ?>
                <tr>
                    <td><?= $pay['id'] ?></td>
                    <td><?= htmlspecialchars($pay['username']) ?></td>
                    <td><?= htmlspecialchars($pay['destination']) ?></td>
                    <td><?= ucwords(str_replace('_', ' ', $pay['payment_method'])) ?></td>
                    <td>₹<?= number_format($pay['amount'], 2) ?></td>
                    <td><span class="badge badge-<?= $pay['payment_status'] ?>"><?= ucfirst($pay['payment_status']) ?></span></td>
                    <td style="font-size:12px;"><?= htmlspecialchars($pay['transaction_id']) ?></td>
                    <td><?= date('d M Y, H:i', strtotime($pay['paid_at'])) ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

</div>

<footer><p>© 2025 <span>InteleTour</span> Admin Panel</p></footer>
<script src="script.js"></script>
</body>
</html>
