<?php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'intele_tour');

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$conn->set_charset("utf8");

// Helper: sanitize input
function clean($conn, $data) {
    return mysqli_real_escape_string($conn, trim($data));
}

// Helper: redirect
function redirect($url) {
    header("Location: $url");
    exit();
}

// Helper: check user login
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Helper: check admin login
function isAdmin() {
    return isset($_SESSION['admin_id']);
}

// Helper: ensure every package has a place-based image URL (non-random).
function packageImageUrl($destination, $image = '') {
    $image = trim((string)$image);
    $destinationText = strtolower((string)$destination);

    // Known random placeholder hosts we don't want to show.
    $isRandomPlaceholder = stripos($image, 'picsum.photos') !== false;

    // Keep valid remote URLs as-is.
    if ($image !== '' && preg_match('/^https?:\/\//i', $image) && !$isRandomPlaceholder) {
        return $image;
    }

    // Keep valid local files if they exist and are non-empty.
    if ($image !== '') {
        $normalized = ltrim(str_replace(['\\', '/'], DIRECTORY_SEPARATOR, $image), DIRECTORY_SEPARATOR);
        $absolute = __DIR__ . DIRECTORY_SEPARATOR . $normalized;
        if (is_file($absolute) && filesize($absolute) > 0) {
            return $image;
        }
    }

    // Fixed place-based image map (no random generation).
    $placeImageMap = [
        'paris' => 'https://images.unsplash.com/photo-1431274172761-fca41d930114?auto=format&fit=crop&w=1400&q=80',
        'tokyo' => 'https://images.unsplash.com/photo-1540959733332-eab4deabeeaf?auto=format&fit=crop&w=1400&q=80',
        'bali' => 'https://images.unsplash.com/photo-1537953773345-d172ccf13cf1?auto=format&fit=crop&w=1400&q=80',
        'dubai' => 'https://images.unsplash.com/photo-1512453979798-5ea266f8880c?auto=format&fit=crop&w=1400&q=80',
        'singapore' => 'https://images.unsplash.com/photo-1525625293386-3f8f99389edd?auto=format&fit=crop&w=1400&q=80',
        'santorini' => 'https://images.unsplash.com/photo-1570077188670-e3a8d69ac5ff?auto=format&fit=crop&w=1400&q=80',
        'maldives' => 'https://images.unsplash.com/photo-1573843981267-be1999ff37cd?auto=format&fit=crop&w=1400&q=80',
        'london' => 'https://images.unsplash.com/photo-1513635269975-59663e0ac1ad?auto=format&fit=crop&w=1400&q=80',
        'new york' => 'https://images.unsplash.com/photo-1496588152823-86ff7695e68f?auto=format&fit=crop&w=1400&q=80',
        'phuket' => 'https://images.unsplash.com/photo-1468413253725-0d5181091126?auto=format&fit=crop&w=1400&q=80',
        'rome' => 'https://images.unsplash.com/photo-1525874684015-58379d421a52?auto=format&fit=crop&w=1400&q=80',
        'sydney' => 'https://images.unsplash.com/photo-1528072164453-f4e8ef0d475a?auto=format&fit=crop&w=1400&q=80',
        'kyoto' => 'https://images.unsplash.com/photo-1492571350019-22de08371fd3?auto=format&fit=crop&w=1400&q=80',
        'seoul' => 'https://images.unsplash.com/photo-1538485399081-7c897f7f8e2b?auto=format&fit=crop&w=1400&q=80',
        'amsterdam' => 'https://images.unsplash.com/photo-1512470876302-972faa2aa9a4?auto=format&fit=crop&w=1400&q=80',
        'barcelona' => 'https://images.unsplash.com/photo-1583422409516-2895a77efded?auto=format&fit=crop&w=1400&q=80',
        'vienna' => 'https://images.unsplash.com/photo-1516557070061-c3d1653fa646?auto=format&fit=crop&w=1400&q=80',
        'zurich' => 'https://images.unsplash.com/photo-1534422298391-e4f8c172dddb?auto=format&fit=crop&w=1400&q=80',
        'istanbul' => 'https://images.unsplash.com/photo-1524231757912-21f4fe3a7200?auto=format&fit=crop&w=1400&q=80',
        'cairo' => 'https://images.unsplash.com/photo-1572252009286-268acec5ca0a?auto=format&fit=crop&w=1400&q=80',
        'cape town' => 'https://images.unsplash.com/photo-1580060839134-75a5edca2e99?auto=format&fit=crop&w=1400&q=80',
        'banff' => 'https://images.unsplash.com/photo-1601758123927-196f49fdb478?auto=format&fit=crop&w=1400&q=80',
        'auckland' => 'https://images.unsplash.com/photo-1523482580672-f109ba8cb9be?auto=format&fit=crop&w=1400&q=80',
        'prague' => 'https://images.unsplash.com/photo-1519677100203-a0e668c92439?auto=format&fit=crop&w=1400&q=80',
    ];

    foreach ($placeImageMap as $keyword => $url) {
        if (stripos($destinationText, $keyword) !== false) {
            return $url;
        }
    }

    // Final non-random fallback.
    return 'https://images.unsplash.com/photo-1488646953014-85cb44e25828?auto=format&fit=crop&w=1400&q=80';
}
?>
