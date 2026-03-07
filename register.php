<?php
session_start();
require_once 'database.php';

if (isLoggedIn()) redirect('index.php');

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = clean($conn, $_POST['username']);
    $email    = clean($conn, $_POST['email']);
    $password = $_POST['password'];
    $mobile   = clean($conn, $_POST['mobile']);
    $address  = clean($conn, $_POST['address']);

    if (empty($username) || empty($email) || empty($password)) {
        $error = "Please fill in all required fields.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email address.";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters.";
    } else {
        // Check duplicate
        $check = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $check->bind_param("ss", $username, $email);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            $error = "Username or email already exists.";
        } else {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (username, email, password, mobile, address) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("sssss", $username, $email, $hashed, $mobile, $address);
            if ($stmt->execute()) {
                $success = "Registration successful! You can now login.";
            } else {
                $error = "Registration failed. Please try again.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register </title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<nav class="navbar">
    <a href="index.php" class="logo">&#9992; Intele<span>Tour</span></a>
    <ul class="nav-links">
        <li><a href="login.php">Login</a></li>
        <li><a href="register.php">Register</a></li>
    </ul>
</nav>

<div class="form-container">
    <h2>&#9992; Create Account</h2>
    <p class="subtitle">Join InteleTour and start exploring the world</p>

    <?php if ($error): ?><div class="alert alert-danger"><?= $error ?></div><?php endif; ?>
    <?php if ($success): ?><div class="alert alert-success"><?= $success ?> <a href="login.php">Login here</a></div><?php endif; ?>

    <form method="POST" id="register-form">
        <div class="form-group">
            <label>Username *</label>
            <input type="text" name="username" placeholder="Enter username" required>
        </div>
        <div class="form-group">
            <label>Email Address *</label>
            <input type="email" name="email" placeholder="Enter email" required>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label>Password *</label>
                <input type="password" name="password" id="password" placeholder="Min 6 chars" required>
            </div>
            <div class="form-group">
                <label>Confirm Password *</label>
                <input type="password" name="confirm_password" id="confirm_password" placeholder="Repeat password" required>
            </div>
        </div>
        <div class="form-group">
            <label>Mobile Number</label>
            <input type="tel" name="mobile" placeholder="+91 XXXXXXXXXX">
        </div>
        <div class="form-group">
            <label>Address</label>
            <textarea name="address" rows="3" placeholder="Your address"></textarea>
        </div>
        <button type="submit" class="btn btn-primary btn-full">Create Account</button>
    </form>
    <p style="text-align:center; margin-top:18px; font-size:14px; color:var(--gray);">
        Already have an account? <a href="login.php" style="color:var(--primary); font-weight:600;">Login</a>
    </p>
</div>

<footer><p>© 2026 <span>InteleTour</span></p></footer>
<script src="script.js"></script>
</body>
</html>



