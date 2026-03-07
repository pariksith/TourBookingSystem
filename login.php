<?php
session_start();
require_once 'database.php';

if (isLoggedIn()) redirect('index.php');
if (isAdmin()) redirect('admin.php');

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = clean($conn, $_POST['username'] ?? '');
    $password = $_POST['password'];
    $role     = $_POST['role'] ?? 'user';

    if (empty($password)) {
        $error = "Please enter password.";
    } elseif ($role === 'admin') {
        if ($password === 'admin123') {
            $_SESSION['admin_id'] = 1;
            $_SESSION['admin_username'] = 'admin';
            redirect('admin.php');
        } else {
            $error = "Invalid admin password.";
        }
    } else {
        if (empty($username)) {
            $error = "Please enter username or email.";
        } else {
            $stmt = $conn->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
            $stmt->bind_param("ss", $username, $username);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();

            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['email'] = $user['email'];
                redirect('index.php');
            } else {
                $error = "Invalid username/email or password.";
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
    <title>Login </title>
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
    <h2>Welcome Back</h2>
    <p class="subtitle">Login to manage your bookings</p>

    <?php if ($error): ?><div class="alert alert-danger"><?= $error ?></div><?php endif; ?>
    <?php if (isset($_GET['msg'])): ?>
        <div class="alert alert-info"><?= htmlspecialchars($_GET['msg']) ?></div>
    <?php endif; ?>

    <form method="POST">
        <?php $selectedRole = $_POST['role'] ?? 'user'; ?>
        <div class="form-group">
            <label>Role</label>
            <select name="role">
                <option value="user" <?= $selectedRole === 'user' ? 'selected' : '' ?>>User</option>
                <option value="admin" <?= $selectedRole === 'admin' ? 'selected' : '' ?>>Admin</option>
            </select>
        </div>
        <div class="form-group" id="username_group">
            <label>Username or Email *</label>
            <input type="text" id="username_input" name="username" placeholder="Enter username or email" value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" required>
        </div>
        <div class="form-group">
            <label id="password_label">Password *</label>
            <input type="password" name="password" placeholder="Enter password" required>
        </div>
        <button type="submit" class="btn btn-primary btn-full">Login</button>
    </form>
    <p style="text-align:center; margin-top:18px; font-size:14px; color:var(--gray);">
        Don't have an account? <a href="register.php" style="color:var(--primary); font-weight:600;">Register</a>
    </p>
</div>

<footer><p>© 2026 <span>InteleTour</span></p></footer>
<script src="script.js"></script>
<script>
(() => {
    const roleSelect = document.querySelector('select[name="role"]');
    const usernameGroup = document.getElementById('username_group');
    const usernameInput = document.getElementById('username_input');
    const passwordLabel = document.getElementById('password_label');

    function updateRoleFields() {
        const isAdminRole = roleSelect && roleSelect.value === 'admin';
        if (!usernameGroup || !usernameInput || !passwordLabel) return;

        usernameGroup.style.display = isAdminRole ? 'none' : 'block';
        usernameInput.required = !isAdminRole;
        if (isAdminRole) usernameInput.value = '';

        passwordLabel.textContent = isAdminRole ? 'Admin Password *' : 'Password *';
    }

    if (roleSelect) {
        roleSelect.addEventListener('change', updateRoleFields);
    }
    updateRoleFields();
})();
</script>
</body>
</html>

