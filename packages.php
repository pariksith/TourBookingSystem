<?php
session_start();
require_once 'database.php';
$usd_rate = 83.00; // 1 USD = 83 INR

// ===== FETCH ALL PACKAGES FROM DB =====
$packages_result = $conn->query("SELECT * FROM packages WHERE available = 1 ORDER BY id ASC");
$all_packages = [];
while ($row = $packages_result->fetch_assoc()) {
    $all_packages[] = $row;
}

// ===== EXTRA STATIC PACKAGES (to enrich the page) =====
$extra_packages = [
    [
        'id'          => 101,
        'destination' => 'Singapore',
        'description' => 'Experience the ultra-modern city-state of Singapore. Visit Marina Bay Sands, Gardens by the Bay, Sentosa Island, and enjoy world-class cuisine from Hawker Centers.',
        'price'       => 72000.00,
        'duration'    => '5 Days / 4 Nights',
        'image'       => 'https://picsum.photos/seed/singapore/800/520',
        'region'      => 'Asia',
        'rating'      => 4.8,
        'reviews'     => 312,
        'badge'       => 'Trending',
        'badge_color' => '#0077b6',
        'includes'    => ['Hotel','Transfer','City Tour','Bay Cruise'],
        'discount'    => 8
    ],
    [
        'id'          => 102,
        'destination' => 'Santorini, Greece',
        'description' => 'Discover the breathtaking beauty of Santorini with its iconic white-washed buildings, blue-domed churches, volcanic beaches, and spectacular Aegean sunsets.',
        'price'       => 110000.00,
        'duration'    => '7 Days / 6 Nights',
        'image'       => 'https://picsum.photos/seed/santorini/800/520',
        'region'      => 'Europe',
        'rating'      => 4.9,
        'reviews'     => 540,
        'badge'       => 'Luxury',
        'badge_color' => '#f77f00',
        'includes'    => ['Villa Stay','Transfer','Sunset Cruise','Wine Tour'],
        'discount'    => 0
    ],
    [
        'id'          => 103,
        'destination' => 'Maldives',
        'description' => 'Escape to the pristine paradise of the Maldives. Enjoy overwater bungalows, crystal-clear lagoons, world-class snorkeling, and breathtaking coral reefs.',
        'price'       => 145000.00,
        'duration'    => '6 Days / 5 Nights',
        'image'       => 'https://picsum.photos/seed/maldives/800/520',
        'region'      => 'Asia',
        'rating'      => 5.0,
        'reviews'     => 890,
        'badge'       => '⭐ Best Seller',
        'badge_color' => '#dc3545',
        'includes'    => ['Overwater Villa','Seaplane','Diving','All Meals'],
        'discount'    => 12
    ],
    [
        'id'          => 104,
        'destination' => 'London, UK',
        'description' => 'Explore the iconic British capital. Visit Big Ben, Buckingham Palace, the Tower of London, world-class museums, and enjoy the vibrant theatre and pub culture.',
        'price'       => 98000.00,
        'duration'    => '7 Days / 6 Nights',
        'image'       => 'https://picsum.photos/seed/london/800/520',
        'region'      => 'Europe',
        'rating'      => 4.7,
        'reviews'     => 420,
        'badge'       => 'Popular',
        'badge_color' => '#0077b6',
        'includes'    => ['Hotel','Transfer','City Pass','Thames Cruise'],
        'discount'    => 5
    ],
    [
        'id'          => 105,
        'destination' => 'New York, USA',
        'description' => 'The city that never sleeps! Explore Times Square, Central Park, the Statue of Liberty, Brooklyn Bridge, Broadway shows, and endless world-class dining options.',
        'price'       => 125000.00,
        'duration'    => '8 Days / 7 Nights',
        'image'       => 'https://picsum.photos/seed/newyork/800/520',
        'region'      => 'Americas',
        'rating'      => 4.8,
        'reviews'     => 675,
        'badge'       => 'Popular',
        'badge_color' => '#0077b6',
        'includes'    => ['Hotel','Transfer','City Pass','Broadway Ticket'],
        'discount'    => 0
    ],
    [
        'id'          => 106,
        'destination' => 'Phuket, Thailand',
        'description' => 'Thailand\'s largest island offers stunning beaches, vibrant nightlife, ancient temples, elephant sanctuaries, and the iconic Phi Phi Islands boat tour.',
        'price'       => 55000.00,
        'duration'    => '6 Days / 5 Nights',
        'image'       => 'https://picsum.photos/seed/phuket/800/520',
        'region'      => 'Asia',
        'rating'      => 4.6,
        'reviews'     => 380,
        'badge'       => 'Budget Pick',
        'badge_color' => '#28a745',
        'includes'    => ['Hotel','Transfer','Island Tour','Thai Cooking Class'],
        'discount'    => 10
    ],
    [
        'id'          => 107,
        'destination' => 'Rome, Italy',
        'description' => 'Walk through centuries of history in the Eternal City. Visit the Colosseum, Vatican City, Trevi Fountain, and indulge in authentic Italian pasta and gelato.',
        'price'       => 88000.00,
        'duration'    => '7 Days / 6 Nights',
        'image'       => 'https://picsum.photos/seed/rome/800/520',
        'region'      => 'Europe',
        'rating'      => 4.8,
        'reviews'     => 510,
        'badge'       => 'Heritage',
        'badge_color' => '#6f42c1',
        'includes'    => ['Hotel','Transfer','Colosseum Pass','Vatican Tour'],
        'discount'    => 0
    ],
    [
        'id'          => 108,
        'destination' => 'Sydney, Australia',
        'description' => 'Discover Australia\'s stunning harbour city. Visit the Opera House, Harbour Bridge, Bondi Beach, Blue Mountains, and enjoy the vibrant café culture.',
        'price'       => 115000.00,
        'duration'    => '8 Days / 7 Nights',
        'image'       => 'https://picsum.photos/seed/sydney/800/520',
        'region'      => 'Oceania',
        'rating'      => 4.7,
        'reviews'     => 295,
        'badge'       => 'Adventure',
        'badge_color' => '#f77f00',
        'includes'    => ['Hotel','Transfer','Harbour Cruise','Blue Mountains Tour'],
        'discount'    => 7
    ],
];

// Ensure extra static packages exist in DB so booking works for all cards.
foreach ($extra_packages as &$extraPkg) {
    $sel = $conn->prepare("SELECT id FROM packages WHERE destination = ? LIMIT 1");
    $sel->bind_param("s", $extraPkg['destination']);
    $sel->execute();
    $found = $sel->get_result()->fetch_assoc();

    if ($found && isset($found['id'])) {
        $extraPkg['id'] = (int)$found['id'];
    } else {
        $ins = $conn->prepare("INSERT INTO packages (destination, description, price, duration, image, available) VALUES (?, ?, ?, ?, ?, 1)");
        $ins->bind_param(
            "ssdss",
            $extraPkg['destination'],
            $extraPkg['description'],
            $extraPkg['price'],
            $extraPkg['duration'],
            $extraPkg['image']
        );
        if ($ins->execute()) {
            $extraPkg['id'] = (int)$conn->insert_id;
        }
    }
}
unset($extraPkg);

// Merge DB packages with extra static packages
// Add missing keys to DB packages for uniformity
$db_enriched = [];
$region_map = [
    'Paris'  => ['Europe',      4.9, 620, '⭐ Popular', '#f77f00', ['Hotel','Eiffel Tour','Seine Cruise','Louvre Pass'], 0],
    'Tokyo'  => ['Asia',        4.8, 750, 'Trending',   '#0077b6', ['Hotel','Transfer','Mt Fuji Tour','Shibuya Walk'], 5],
    'Bali'   => ['Asia',        4.7, 830, '💚 Best Value','#28a745',['Hotel','Transfer','Temple Tour','Beach Day'], 10],
    'Dubai'  => ['Middle East', 4.9, 910, '⭐ Best Seller','#dc3545',['Hotel','Transfer','Desert Safari','Burj Khalifa'], 0],
];

foreach ($all_packages as $pkg) {
    $dest = explode(',', $pkg['destination'])[0];
    $info = $region_map[$dest] ?? ['Asia', 4.5, 100, 'Popular', '#0077b6', ['Hotel','Transfer','Tour'], 0];
    $pkg['region']      = $info[0];
    $pkg['rating']      = $info[1];
    $pkg['reviews']     = $info[2];
    $pkg['badge']       = $info[3];
    $pkg['badge_color'] = $info[4];
    $pkg['includes']    = $info[5];
    $pkg['discount']    = $info[6];
    $db_enriched[]      = $pkg;
}

$all_display_packages = array_merge($db_enriched, $extra_packages);

// ===== FILTER & SORT (GET params) =====
$filter_region = $_GET['region'] ?? 'all';
$filter_sort   = $_GET['sort']   ?? 'default';
$filter_budget = $_GET['budget'] ?? 'all';
$search_query  = trim($_GET['search'] ?? '');

// Apply filters
$filtered = array_filter($all_display_packages, function ($pkg) use ($filter_region, $filter_budget, $search_query) {
    $pass_region = ($filter_region === 'all' || strtolower($pkg['region']) === strtolower($filter_region));
    $pass_budget = true;
    if ($filter_budget === 'budget')  $pass_budget = $pkg['price'] <= 70000;
    if ($filter_budget === 'mid')     $pass_budget = $pkg['price'] > 70000 && $pkg['price'] <= 110000;
    if ($filter_budget === 'luxury')  $pass_budget = $pkg['price'] > 110000;
    $pass_search = true;
    if ($search_query) {
        $pass_search = stripos($pkg['destination'], $search_query) !== false
                    || stripos($pkg['description'], $search_query) !== false;
    }
    return $pass_region && $pass_budget && $pass_search;
});

// Apply sort
usort($filtered, function ($a, $b) use ($filter_sort) {
    if ($filter_sort === 'price_asc')   return $a['price'] <=> $b['price'];
    if ($filter_sort === 'price_desc')  return $b['price'] <=> $a['price'];
    if ($filter_sort === 'rating')      return $b['rating'] <=> $a['rating'];
    if ($filter_sort === 'reviews')     return $b['reviews'] <=> $a['reviews'];
    return 0;
});

$total_count   = count($all_display_packages);
$filtered_count = count($filtered);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Browse all InteleTour travel packages – Paris, Tokyo, Bali, Dubai, Maldives, Singapore and more.">
    <title>Travel Packages – InteleTour</title>
    <link rel="stylesheet" href="style.css">
    <style>
        /* ===== PACKAGES PAGE SPECIFIC CSS ===== */

        /* Deals Banner */
        .deals-banner {
            background: linear-gradient(90deg, #dc3545, #f77f00, #dc3545);
            background-size: 200% auto;
            animation: shimmer 3s linear infinite;
            color: white;
            text-align: center;
            padding: 11px 20px;
            font-size: 13px;
            font-weight: 700;
            letter-spacing: 0.3px;
        }
        .deals-banner a { color: white; text-decoration: underline; margin-left: 8px; }
        @keyframes shimmer {
            0%   { background-position: 0% center; }
            100% { background-position: 200% center; }
        }

        /* Search + Filter Bar */
        .filter-bar {
            background: white;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            padding: 20px 40px;
            position: sticky;
            top: 65px;
            z-index: 100;
        }
        .filter-bar-inner {
            max-width: 1300px;
            margin: 0 auto;
            display: flex;
            gap: 14px;
            align-items: center;
            flex-wrap: wrap;
        }
        .search-box {
            flex: 1;
            min-width: 220px;
            display: flex;
            align-items: center;
            border: 1.5px solid #dde;
            border-radius: 30px;
            overflow: hidden;
            background: #fafafa;
            transition: all 0.3s;
        }
        .search-box:focus-within {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(0,119,182,0.1);
        }
        .search-box span {
            padding: 0 14px;
            color: var(--gray);
            font-size: 16px;
        }
        .search-box input {
            flex: 1;
            padding: 11px 0;
            border: none;
            outline: none;
            font-size: 14px;
            background: transparent;
            color: #333;
        }
        .filter-select {
            padding: 11px 16px;
            border: 1.5px solid #dde;
            border-radius: 25px;
            font-size: 13px;
            font-weight: 600;
            color: var(--dark);
            background: #fafafa;
            cursor: pointer;
            outline: none;
            transition: all 0.3s;
        }
        .filter-select:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(0,119,182,0.1);
        }
        .filter-count {
            font-size: 13px;
            color: var(--gray);
            white-space: nowrap;
            font-weight: 500;
            margin-left: auto;
        }
        .filter-count strong { color: var(--primary); }

        /* Region Tab Filters */
        .region-tabs {
            max-width: 1300px;
            margin: 0 auto 35px;
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        .region-tab {
            padding: 9px 20px;
            border: 2px solid #e0e0e0;
            border-radius: 25px;
            background: white;
            font-size: 13px;
            font-weight: 600;
            color: var(--dark);
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-block;
        }
        .region-tab:hover,
        .region-tab.active {
            background: var(--primary);
            color: white;
            border-color: var(--primary);
            box-shadow: 0 4px 14px rgba(0,119,182,0.3);
        }

        /* Package Cards Enhanced */
        .packages-section {
            max-width: 1300px;
            margin: 0 auto;
        }
        .pkg-card-wrap {
            background: white;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            transition: all 0.35s ease;
            position: relative;
            display: flex;
            flex-direction: column;
        }
        .pkg-card-wrap:hover {
            transform: translateY(-10px);
            box-shadow: 0 16px 45px rgba(0,0,0,0.18);
        }
        .pkg-img-wrap {
            position: relative;
            overflow: hidden;
            height: 220px;
        }
        .pkg-img-wrap img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.55s ease;
        }
        .pkg-card-wrap:hover .pkg-img-wrap img {
            transform: scale(1.1);
        }
        .pkg-badge {
            position: absolute;
            top: 14px;
            left: 14px;
            color: white;
            font-size: 11px;
            font-weight: 700;
            padding: 5px 13px;
            border-radius: 20px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            backdrop-filter: blur(4px);
        }
        .pkg-discount-tag {
            position: absolute;
            top: 14px;
            right: 14px;
            background: #dc3545;
            color: white;
            font-size: 12px;
            font-weight: 800;
            padding: 5px 11px;
            border-radius: 20px;
        }
        .pkg-region-tag {
            position: absolute;
            bottom: 12px;
            right: 12px;
            background: rgba(0,0,0,0.55);
            color: white;
            font-size: 11px;
            padding: 4px 10px;
            border-radius: 12px;
            backdrop-filter: blur(4px);
        }
        .pkg-body {
            padding: 20px;
            flex: 1;
            display: flex;
            flex-direction: column;
        }
        .pkg-rating-row {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 8px;
        }
        .pkg-stars { color: #f7b731; font-size: 13px; }
        .pkg-rating-num {
            font-size: 13px;
            font-weight: 700;
            color: var(--dark);
        }
        .pkg-reviews {
            font-size: 12px;
            color: var(--gray);
        }
        .pkg-body h3 {
            font-size: 19px;
            color: var(--dark);
            font-weight: 700;
            margin-bottom: 8px;
        }
        .pkg-body p {
            font-size: 13px;
            color: var(--gray);
            line-height: 1.65;
            margin-bottom: 14px;
            flex: 1;
        }
        .pkg-includes {
            display: flex;
            gap: 6px;
            flex-wrap: wrap;
            margin-bottom: 15px;
        }
        .pkg-tag {
            background: var(--light);
            color: var(--primary);
            font-size: 11px;
            padding: 3px 10px;
            border-radius: 12px;
            font-weight: 600;
            border: 1px solid rgba(0,119,182,0.15);
        }
        .pkg-price-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            flex-wrap: wrap;
            gap: 8px;
        }
        .pkg-price-block {}
        .pkg-price-original {
            font-size: 12px;
            color: var(--gray);
            text-decoration: line-through;
        }
        .pkg-price {
            font-size: 22px;
            font-weight: 800;
            color: var(--accent);
            line-height: 1;
        }
        .pkg-price-per {
            font-size: 11px;
            color: var(--gray);
        }
        .pkg-duration {
            background: var(--light-gray);
            padding: 5px 13px;
            border-radius: 20px;
            font-size: 12px;
            color: var(--gray);
            font-weight: 600;
        }
        .pkg-btn-row {
            display: flex;
            gap: 10px;
        }
        .pkg-btn-row .btn {
            flex: 1;
            text-align: center;
            font-size: 13px;
            padding: 11px 14px;
        }
        .btn-wish {
            background: white;
            border: 2px solid var(--primary);
            color: var(--primary);
            border-radius: 30px;
            padding: 10px 15px;
            cursor: pointer;
            font-size: 16px;
            transition: all 0.3s;
        }
        .btn-wish:hover {
            background: var(--primary);
            color: white;
        }

        /* No Results */
        .no-results {
            text-align: center;
            padding: 60px 20px;
            color: var(--gray);
        }
        .no-results h3 { font-size: 22px; margin-bottom: 10px; color: var(--dark); }
        .no-results p  { font-size: 15px; margin-bottom: 22px; }

        /* Top Destinations Horizontal Scroll */
        .top-destinations {
            background: var(--light-gray);
            padding: 35px 40px;
        }
        .top-destinations h3 {
            text-align: center;
            font-size: 20px;
            color: var(--dark);
            margin-bottom: 22px;
            font-weight: 700;
        }
        .dest-scroll-row {
            display: flex;
            gap: 16px;
            overflow-x: auto;
            padding-bottom: 8px;
            max-width: 1300px;
            margin: 0 auto;
            scrollbar-width: thin;
        }
        .dest-scroll-card {
            flex-shrink: 0;
            width: 160px;
            border-radius: 14px;
            overflow: hidden;
            cursor: pointer;
            position: relative;
            box-shadow: 0 4px 14px rgba(0,0,0,0.12);
            transition: all 0.3s;
        }
        .dest-scroll-card:hover { transform: translateY(-5px); }
        .dest-scroll-card img {
            width: 100%;
            height: 110px;
            object-fit: cover;
            display: block;
        }
        .dest-scroll-card .dest-name {
            background: linear-gradient(transparent, rgba(3,4,94,0.88));
            color: white;
            font-size: 12px;
            font-weight: 700;
            padding: 18px 10px 8px;
            position: absolute;
            bottom: 0; left: 0; right: 0;
        }

        /* AI Suggestion Box */
        .ai-suggest-box {
            background: linear-gradient(135deg, var(--dark), var(--primary));
            border-radius: 16px;
            padding: 30px 35px;
            color: white;
            display: flex;
            align-items: center;
            gap: 24px;
            max-width: 1300px;
            margin: 0 auto 45px;
            flex-wrap: wrap;
        }
        .ai-suggest-icon { font-size: 50px; flex-shrink: 0; }
        .ai-suggest-text { flex: 1; }
        .ai-suggest-text h3 { font-size: 20px; font-weight: 700; margin-bottom: 7px; }
        .ai-suggest-text p { opacity: 0.88; font-size: 14px; }
        .ai-suggest-box .btn {
            background: var(--accent);
            color: white;
            white-space: nowrap;
        }
        .ai-suggest-box .btn:hover { background: #e06b00; }

        /* Package Comparison Strip */
        .compare-strip {
            background: white;
            border-top: 3px solid var(--primary);
            padding: 16px 40px;
            position: fixed;
            bottom: 0; left: 0; right: 0;
            z-index: 999;
            display: none;
            align-items: center;
            gap: 20px;
            box-shadow: 0 -4px 20px rgba(0,0,0,0.12);
            flex-wrap: wrap;
        }
        .compare-strip.show { display: flex; }
        .compare-strip p { font-size: 14px; font-weight: 600; color: var(--dark); }
        .compare-items { display: flex; gap: 10px; flex: 1; flex-wrap: wrap; }
        .compare-chip {
            background: var(--light);
            border: 1.5px solid var(--primary);
            color: var(--primary);
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 7px;
        }
        .compare-chip button {
            background: none;
            border: none;
            color: var(--danger);
            cursor: pointer;
            font-size: 14px;
            padding: 0;
            line-height: 1;
        }
        .compare-strip .btn { white-space: nowrap; }

        /* Why Book Section */
        .why-book-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 22px;
            max-width: 1300px;
            margin: 0 auto;
        }
        .why-book-card {
            background: white;
            border-radius: 12px;
            padding: 26px 20px;
            text-align: center;
            box-shadow: 0 3px 14px rgba(0,0,0,0.07);
            transition: all 0.3s;
        }
        .why-book-card:hover { transform: translateY(-5px); box-shadow: var(--shadow-lg); }
        .why-book-card .icon { font-size: 38px; margin-bottom: 12px; }
        .why-book-card h4 { font-size: 15px; color: var(--dark); font-weight: 700; margin-bottom: 7px; }
        .why-book-card p { font-size: 13px; color: var(--gray); line-height: 1.6; }

        /* Mini Testimonials */
        .mini-testimonials {
            background: var(--light);
            padding: 60px 40px;
        }
        .mini-testimonials-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(270px, 1fr));
            gap: 20px;
            max-width: 1300px;
            margin: 0 auto;
        }
        .mini-review-card {
            background: white;
            padding: 22px;
            border-radius: 12px;
            box-shadow: 0 3px 14px rgba(0,0,0,0.07);
        }
        .mini-review-card .stars { color: #f7b731; font-size: 13px; margin-bottom: 9px; }
        .mini-review-card p {
            font-size: 13px;
            color: #555;
            font-style: italic;
            line-height: 1.65;
            margin-bottom: 14px;
        }
        .mini-review-author {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 13px;
        }
        .mini-avatar {
            width: 38px;
            height: 38px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            font-size: 13px;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .mini-review-author strong { color: var(--dark); font-size: 14px; }
        .mini-review-author span { color: var(--gray); font-size: 12px; }

        /* Responsive */
        @media (max-width: 768px) {
            .filter-bar { padding: 14px 16px; top: 60px; }
            .filter-bar-inner { gap: 10px; }
            .search-box { min-width: 100%; }
            .top-destinations { padding: 28px 16px; }
            .ai-suggest-box { padding: 22px 20px; }
            .compare-strip { padding: 14px 16px; }
            .mini-testimonials { padding: 45px 20px; }
        }
        @media (max-width: 480px) {
            .pkg-btn-row { flex-direction: column; }
            .ai-suggest-box { flex-direction: column; text-align: center; }
        }
    </style>
</head>
<body>

<!-- ===== DEALS BANNER ===== -->
<div class="deals-banner">
    🔥 Limited Time: Up to 12% OFF on select packages!
    <a href="#packages-grid">View Deals →</a>
</div>

<!-- ===== NAVBAR ===== -->
<nav class="navbar">
    <div class="logo">✈ Intele<span>Tour</span></div>
    <ul class="nav-links">
        <li><a href="index.php">Home</a></li>
        <li><a href="packages.php" class="active">Packages</a></li>
        <li><a href="ai_assistant.php">AI Assistant</a></li>
        <?php if (isLoggedIn()): ?>
            <li><a href="my_bookings.php">My Bookings</a></li>
            <li><a href="logout.php" class="btn-nav">Logout</a></li>
        <?php else: ?>
            <li><a href="login.php">Login</a></li>
            <li><a href="register.php" class="btn-nav">Register</a></li>
        <?php endif; ?>
    </ul>
</nav>

<!-- ===== PAGE HEADER ===== -->
<div class="page-header">
    <h1>✈ Travel Packages</h1>
    <p>Explore <?= $total_count ?>+ handcrafted packages to the world's most beautiful destinations</p>
</div>

<!-- ===== TOP DESTINATIONS SCROLL ===== -->
<div class="top-destinations">
    <h3>🌍 Popular Destinations</h3>
    <div class="dest-scroll-row">
        <?php
        $scroll_dests = [
            ['Paris',     'https://picsum.photos/seed/paris/300/200'],
            ['Tokyo',     'https://picsum.photos/seed/tokyo/300/200'],
            ['Bali',      'https://picsum.photos/seed/bali/300/200'],
            ['Dubai',     'https://picsum.photos/seed/dubai/300/200'],
            ['Maldives',  'https://picsum.photos/seed/maldives/300/200'],
            ['Singapore', 'https://picsum.photos/seed/singapore/300/200'],
            ['London',    'https://picsum.photos/seed/london/300/200'],
            ['Rome',      'https://picsum.photos/seed/rome/300/200'],
            ['New York',  'https://picsum.photos/seed/newyork/300/200'],
            ['Santorini', 'https://picsum.photos/seed/santorini/300/200'],
            ['Sydney',    'https://picsum.photos/seed/sydney/300/200'],
            ['Phuket',    'https://picsum.photos/seed/phuket/300/200'],
        ];
        foreach ($scroll_dests as $d): ?>
        <a href="?search=<?= urlencode($d[0]) ?>" class="dest-scroll-card">
            <img src="<?= $d[1] ?>" alt="<?= $d[0] ?>" loading="lazy">
            <div class="dest-name">📍 <?= $d[0] ?></div>
        </a>
        <?php endforeach; ?>
    </div>
</div>

<!-- ===== FILTER BAR ===== -->
<div class="filter-bar">
    <form method="GET" id="filter-form">
        <div class="filter-bar-inner">
            <!-- Search -->
            <div class="search-box">
                <span>🔍</span>
                <input
                    type="text"
                    name="search"
                    placeholder="Search destinations..."
                    value="<?= htmlspecialchars($search_query) ?>"
                    id="search-input"
                    autocomplete="off"
                >
            </div>

            <!-- Region -->
            <select name="region" class="filter-select" onchange="this.form.submit()">
                <option value="all"        <?= $filter_region==='all'        ?'selected':'' ?>>🌐 All Regions</option>
                <option value="Europe"     <?= $filter_region==='Europe'     ?'selected':'' ?>>🏰 Europe</option>
                <option value="Asia"       <?= $filter_region==='Asia'       ?'selected':'' ?>>🏯 Asia</option>
                <option value="Middle East"<?= $filter_region==='Middle East'?'selected':'' ?>>🕌 Middle East</option>
                <option value="Americas"   <?= $filter_region==='Americas'   ?'selected':'' ?>>🗽 Americas</option>
                <option value="Oceania"    <?= $filter_region==='Oceania'    ?'selected':'' ?>>🦘 Oceania</option>
            </select>

            <!-- Budget -->
            <select name="budget" class="filter-select" onchange="this.form.submit()">
                <option value="all"    <?= $filter_budget==='all'    ?'selected':'' ?>>💰 All Budgets</option>
                <option value="budget" <?= $filter_budget==='budget' ?'selected':'' ?>>🟢 Under ₹70,000</option>
                <option value="mid"    <?= $filter_budget==='mid'    ?'selected':'' ?>>🟡 ₹70K – ₹1.1L</option>
                <option value="luxury" <?= $filter_budget==='luxury' ?'selected':'' ?>>🔴 Above ₹1.1L</option>
            </select>

            <!-- Sort -->
            <select name="sort" class="filter-select" onchange="this.form.submit()">
                <option value="default"    <?= $filter_sort==='default'    ?'selected':'' ?>>📌 Default</option>
                <option value="price_asc"  <?= $filter_sort==='price_asc'  ?'selected':'' ?>>⬆ Price: Low to High</option>
                <option value="price_desc" <?= $filter_sort==='price_desc' ?'selected':'' ?>>⬇ Price: High to Low</option>
                <option value="rating"     <?= $filter_sort==='rating'     ?'selected':'' ?>>⭐ Top Rated</option>
                <option value="reviews"    <?= $filter_sort==='reviews'    ?'selected':'' ?>>💬 Most Reviewed</option>
            </select>

            <!-- Search Button -->
            <button type="submit" class="btn btn-primary btn-sm">Search</button>

            <?php if ($search_query || $filter_region !== 'all' || $filter_budget !== 'all' || $filter_sort !== 'default'): ?>
                <a href="packages.php" class="btn btn-danger btn-sm">✕ Clear</a>
            <?php endif; ?>

            <span class="filter-count">
                Showing <strong><?= $filtered_count ?></strong> of <strong><?= $total_count ?></strong> packages
            </span>
        </div>
    </form>
</div>

<!-- ===== MAIN PACKAGES SECTION ===== -->
<section class="section" id="packages-grid" style="background: var(--light-gray);">
    <div class="packages-section">

        <!-- Region Tabs -->
        <div class="region-tabs">
            <?php
            $region_tabs = [
                'all'        => '🌐 All',
                'Europe'     => '🏰 Europe',
                'Asia'       => '🏯 Asia',
                'Middle East'=> '🕌 Middle East',
                'Americas'   => '🗽 Americas',
                'Oceania'    => '🦘 Oceania',
            ];
            foreach ($region_tabs as $val => $label):
                $active = ($filter_region === $val) ? 'active' : '';
                $params = http_build_query(array_merge($_GET, ['region' => $val, 'sort' => $filter_sort, 'budget' => $filter_budget]));
            ?>
            <a href="?<?= $params ?>" class="region-tab <?= $active ?>"><?= $label ?></a>
            <?php endforeach; ?>
        </div>

        <!-- AI Suggestion Box -->
        <div class="ai-suggest-box">
            <div class="ai-suggest-icon">🤖</div>
            <div class="ai-suggest-text">
                <h3>Not sure where to go? Let AI decide for you!</h3>
                <p>Tell our AI assistant your budget, interests, and travel dates — and get personalised destination recommendations instantly.</p>
            </div>
            <a href="ai_assistant.php" class="btn">Chat with AI →</a>
        </div>

        <!-- Package Cards Grid -->
        <?php if (empty($filtered)): ?>
        <div class="no-results">
            <div style="font-size:60px; margin-bottom:16px;">🔍</div>
            <h3>No packages found</h3>
            <p>Try adjusting your search or filters to find your perfect destination.</p>
            <a href="packages.php" class="btn btn-primary">Clear Filters</a>
        </div>
        <?php else: ?>
        <div class="packages-grid" id="packages-grid-items">
            <?php foreach ($filtered as $pkg):
                $discounted_price = $pkg['discount'] > 0
                    ? $pkg['price'] * (1 - $pkg['discount'] / 100)
                    : $pkg['price'];
                $original_price = $pkg['price'];
                $discounted_usd = $discounted_price / $usd_rate;
                $original_usd = $original_price / $usd_rate;
                $stars = str_repeat('★', floor($pkg['rating'])) . ($pkg['rating'] - floor($pkg['rating']) >= 0.5 ? '½' : '');
                $img_src = packageImageUrl($pkg['destination'], $pkg['image'] ?? '');
                $book_url = "booking.php?id={$pkg['id']}";
            ?>
            <div class="pkg-card-wrap" data-id="<?= $pkg['id'] ?>" data-name="<?= htmlspecialchars($pkg['destination']) ?>">

                <!-- Image -->
                <div class="pkg-img-wrap">
                    <img src="<?= $img_src ?>" alt="<?= htmlspecialchars($pkg['destination']) ?>" loading="lazy">
                    <!-- Badge -->
                    <span class="pkg-badge" style="background:<?= $pkg['badge_color'] ?>;">
                        <?= htmlspecialchars($pkg['badge']) ?>
                    </span>
                    <!-- Discount Tag -->
                    <?php if ($pkg['discount'] > 0): ?>
                        <span class="pkg-discount-tag">-<?= $pkg['discount'] ?>% OFF</span>
                    <?php endif; ?>
                    <!-- Region Tag -->
                    <span class="pkg-region-tag"><?= htmlspecialchars($pkg['region']) ?></span>
                </div>

                <!-- Body -->
                <div class="pkg-body">
                    <!-- Rating -->
                    <div class="pkg-rating-row">
                        <span class="pkg-stars"><?= $stars ?></span>
                        <span class="pkg-rating-num"><?= $pkg['rating'] ?></span>
                        <span class="pkg-reviews">(<?= number_format($pkg['reviews']) ?> reviews)</span>
                    </div>

                    <h3>📍 <?= htmlspecialchars($pkg['destination']) ?></h3>
                    <p><?= htmlspecialchars(substr($pkg['description'], 0, 110)) ?>...</p>

                    <!-- Includes -->
                    <div class="pkg-includes">
                        <?php foreach ($pkg['includes'] as $inc): ?>
                            <span class="pkg-tag">✔ <?= htmlspecialchars($inc) ?></span>
                        <?php endforeach; ?>
                    </div>

                    <!-- Price Row -->
                    <div class="pkg-price-row">
                        <div class="pkg-price-block">
                            <?php if ($pkg['discount'] > 0): ?>
                                <div class="pkg-price-original">&#8377;<?= number_format($original_price, 0) ?> / $<?= number_format($original_usd, 0) ?></div>
                            <?php endif; ?>
                            <div class="pkg-price">&#8377;<?= number_format($discounted_price, 0) ?> / $<?= number_format($discounted_usd, 0) ?></div>
                            <div class="pkg-price-per">per person</div>
                        </div>
                        <span class="pkg-duration">⏱ <?= htmlspecialchars($pkg['duration']) ?></span>
                    </div>

                    <!-- Buttons -->
                    <div class="pkg-btn-row">
                        <?php if (isLoggedIn()): ?>
                            <a href="<?= $book_url ?>" class="btn btn-primary">Book Now</a>
                        <?php else: ?>
                            <a href="login.php" class="btn btn-secondary">Login to Book</a>
                        <?php endif; ?>
                        <button class="btn-wish" onclick="toggleWishlist(this, <?= $pkg['id'] ?>)" title="Add to Wishlist">♡</button>
                        <button
                            class="btn btn-outline-compare"
                            style="background:white;border:2px solid var(--primary);color:var(--primary);border-radius:30px;padding:10px 14px;cursor:pointer;font-size:13px;font-weight:600;transition:all 0.3s;"
                            onclick="addToCompare(<?= $pkg['id'] ?>, '<?= htmlspecialchars($pkg['destination']) ?>')"
                            title="Compare"
                        >⚖️</button>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

    </div>
</section>

<!-- ===== WHY BOOK WITH US ===== -->
<section class="section" style="background: white;">
    <div class="section-title">
        <h2>Why Book with <span>InteleTour</span></h2>
        <div class="divider"></div>
        <p>Everything you need for a stress-free booking experience</p>
    </div>
    <div class="why-book-grid">
        <div class="why-book-card">
            <div class="icon">🔒</div>
            <h4>100% Secure Payments</h4>
            <p>All transactions are encrypted and protected with multi-layer security systems.</p>
        </div>
        <div class="why-book-card">
            <div class="icon">✅</div>
            <h4>Instant Confirmation</h4>
            <p>Receive your booking confirmation and unique transaction ID immediately after payment.</p>
        </div>
        <div class="why-book-card">
            <div class="icon">💰</div>
            <h4>Best Price Guarantee</h4>
            <p>We promise the best rates. Found cheaper? We'll match it no questions asked.</p>
        </div>
        <div class="why-book-card">
            <div class="icon">🔄</div>
            <h4>Free Cancellation</h4>
            <p>Plans changed? Cancel or reschedule your booking up to 48 hours before departure.</p>
        </div>
        <div class="why-book-card">
            <div class="icon">🤖</div>
            <h4>AI Travel Support</h4>
            <p>Our AI assistant is available 24/7 to guide you through every step of your journey.</p>
        </div>
        <div class="why-book-card">
            <div class="icon">🎧</div>
            <h4>Expert Team</h4>
            <p>Our travel experts are available Mon–Sat, 9AM–6PM IST to answer all your questions.</p>
        </div>
    </div>
</section>

<!-- ===== MINI TESTIMONIALS ===== -->
<div class="mini-testimonials">
    <div class="section-title">
        <h2>Travelers Love <span>InteleTour</span></h2>
        <div class="divider"></div>
        <p>Real experiences from our happy customers</p>
    </div>
    <div class="mini-testimonials-grid">
        <?php
        $mini_reviews = [
            ['AR', 'Aisha R.', 'Dubai', 5, 'Absolutely seamless experience! The Dubai desert safari was a highlight of my life. Highly recommend InteleTour for anyone looking for a premium travel service.'],
            ['RK', 'Ravi K.', 'Bali', 5, 'Bali package was incredible — perfectly curated with hotel, beach tours, and temple visits. The AI chatbot helped me plan everything perfectly!'],
            ['PM', 'Priya M.', 'Paris', 5, 'Paris was magical! The Seine River cruise and Eiffel Tower visit were perfectly scheduled. InteleTour made our honeymoon absolutely unforgettable.'],
            ['JW', 'James W.', 'Maldives', 5, 'The Maldives overwater villa was beyond our expectations. Best money ever spent. InteleTour delivered everything they promised and more!'],
            ['MN', 'Meera N.', 'Singapore', 4, 'Singapore was a delight — Gardens by the Bay, Marina Bay Sands, and the food tours were all amazing. Great value for money with InteleTour.'],
            ['CR', 'Carlos R.', 'Tokyo', 5, 'Mount Fuji at sunrise was breathtaking! The Tokyo package covered everything perfectly. Booking was super easy and the team was very responsive.'],
        ];
        foreach ($mini_reviews as $r): ?>
        <div class="mini-review-card">
            <div class="stars"><?= str_repeat('★', $r[3]) ?></div>
            <p>"<?= htmlspecialchars($r[4]) ?>"</p>
            <div class="mini-review-author">
                <div class="mini-avatar"><?= $r[0] ?></div>
                <div>
                    <strong><?= $r[1] ?></strong><br>
                    <span>📍 <?= $r[2] ?> Trip</span>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- ===== CTA SECTION ===== -->
<section class="section" style="background: linear-gradient(135deg, var(--dark), var(--primary)); text-align:center; color:white;">
    <h2 style="font-size:32px; font-weight:800; margin-bottom:14px;">Can't Find What You're Looking For?</h2>
    <p style="opacity:0.88; margin-bottom:32px; font-size:16px; max-width:520px; margin-left:auto; margin-right:auto;">
        Let our AI Travel Assistant create a personalised trip plan just for you — based on your budget, preferences, and travel style.
    </p>
    <div style="display:flex; gap:15px; justify-content:center; flex-wrap:wrap;">
        <a href="ai_assistant.php" class="btn btn-primary">🤖 Ask AI Assistant</a>
        <?php if (!isLoggedIn()): ?>
            <a href="register.php" class="btn btn-outline">📝 Create Account</a>
        <?php else: ?>
            <a href="my_bookings.php" class="btn btn-outline">📋 My Bookings</a>
        <?php endif; ?>
    </div>
</section>

<!-- ===== FOOTER ===== -->
<footer>
    <p>© 2025 <span>InteleTour</span> – All rights reserved. Built with ❤️ for travelers.</p>
</footer>

<!-- ===== COMPARE STRIP (Fixed Bottom) ===== -->
<div class="compare-strip" id="compare-strip">
    <p>⚖️ Compare:</p>
    <div class="compare-items" id="compare-items"></div>
    <button class="btn btn-primary btn-sm" onclick="doCompare()">Compare Now</button>
    <button class="btn btn-danger btn-sm" onclick="clearCompare()">Clear All</button>
</div>

<!-- ===== SCRIPTS ===== -->
<script src="script.js"></script>
<script>
// ===== WISHLIST TOGGLE =====
const wishlist = JSON.parse(localStorage.getItem('intele_wishlist') || '[]');

function toggleWishlist(btn, id) {
    const idx = wishlist.indexOf(id);
    if (idx === -1) {
        wishlist.push(id);
        btn.textContent = '♥';
        btn.style.color = '#dc3545';
        btn.style.borderColor = '#dc3545';
        showToast('Added to wishlist ♥');
    } else {
        wishlist.splice(idx, 1);
        btn.textContent = '♡';
        btn.style.color = '';
        btn.style.borderColor = '';
        showToast('Removed from wishlist');
    }
    localStorage.setItem('intele_wishlist', JSON.stringify(wishlist));
}

// Restore wishlist state on load
document.querySelectorAll('.btn-wish').forEach(btn => {
    const id = parseInt(btn.closest('.pkg-card-wrap').getAttribute('data-id'));
    if (wishlist.includes(id)) {
        btn.textContent = '♥';
        btn.style.color = '#dc3545';
        btn.style.borderColor = '#dc3545';
    }
});

// ===== COMPARE PACKAGES =====
let compareList = [];

function addToCompare(id, name) {
    if (compareList.find(p => p.id === id)) {
        showToast('Already in comparison list!');
        return;
    }
    if (compareList.length >= 3) {
        showToast('You can compare up to 3 packages only.');
        return;
    }
    compareList.push({ id, name });
    renderCompareStrip();
    showToast(`"${name}" added to compare`);
}

function removeFromCompare(id) {
    compareList = compareList.filter(p => p.id !== id);
    renderCompareStrip();
}

function clearCompare() {
    compareList = [];
    renderCompareStrip();
}

function renderCompareStrip() {
    const strip = document.getElementById('compare-strip');
    const items = document.getElementById('compare-items');
    if (compareList.length === 0) {
        strip.classList.remove('show');
        return;
    }
    strip.classList.add('show');
    items.innerHTML = compareList.map(p => `
        <div class="compare-chip">
            ${p.name}
            <button onclick="removeFromCompare(${p.id})">✕</button>
        </div>
    `).join('');
}

function doCompare() {
    if (compareList.length < 2) {
        showToast('Please select at least 2 packages to compare.');
        return;
    }
    const ids = compareList.map(p => p.id).join(',');
    alert(`Comparing packages: ${compareList.map(p => p.name).join(', ')}\n\nFull comparison page coming soon!`);
}

// ===== TOAST NOTIFICATION =====
function showToast(message) {
    const existing = document.getElementById('toast-msg');
    if (existing) existing.remove();
    const toast = document.createElement('div');
    toast.id = 'toast-msg';
    toast.textContent = message;
    toast.style.cssText = `
        position: fixed; bottom: 80px; left: 50%; transform: translateX(-50%);
        background: var(--dark); color: white; padding: 12px 24px;
        border-radius: 30px; font-size: 14px; font-weight: 600;
        box-shadow: 0 6px 22px rgba(0,0,0,0.25); z-index: 99999;
        animation: fadeIn 0.3s ease;
    `;
    document.body.appendChild(toast);
    setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transition = 'opacity 0.4s';
        setTimeout(() => toast.remove(), 400);
    }, 2500);
}

// ===== LIVE SEARCH (client-side filter as you type) =====
const searchInput = document.getElementById('search-input');
if (searchInput && <?= json_encode(empty($search_query)) ?>) {
    searchInput.addEventListener('input', function () {
        const q = this.value.toLowerCase();
        document.querySelectorAll('.pkg-card-wrap').forEach(card => {
            const name = card.getAttribute('data-name').toLowerCase();
            card.style.display = name.includes(q) ? 'flex' : 'none';
        });
    });
}

// ===== SCROLL REVEAL FOR CARDS =====
const pkgCards = document.querySelectorAll('.pkg-card-wrap');
const revealObserver = new IntersectionObserver(entries => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            entry.target.style.opacity = '1';
            entry.target.style.transform = 'translateY(0)';
        }
    });
}, { threshold: 0.08 });

pkgCards.forEach((card, i) => {
    card.style.opacity = '0';
    card.style.transform = 'translateY(30px)';
    card.style.transition = `opacity 0.5s ease ${i * 0.07}s, transform 0.5s ease ${i * 0.07}s`;
    revealObserver.observe(card);
});

// ===== AUTO SUBMIT SEARCH ON ENTER =====
if (searchInput) {
    searchInput.addEventListener('keydown', function (e) {
        if (e.key === 'Enter') {
            document.getElementById('filter-form').submit();
        }
    });
}
</script>
</body>
</html>
