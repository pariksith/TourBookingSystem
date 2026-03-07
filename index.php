<?php
session_start();
require_once 'database.php';
$usd_rate = 83.00; // 1 USD = 83 INR

// Fetch featured packages from DB
$packages_result = $conn->query("SELECT * FROM packages WHERE available = 1 LIMIT 4");

// Gallery locations
$gallery_locations = [
    'Paris, France',
    'Rome, Italy',
    'Santorini, Greece',
    'Barcelona, Spain',
    'Amsterdam, Netherlands',
    'Prague, Czech Republic',
    'Vienna, Austria',
    'Zurich, Switzerland',
    'London, UK',
    'Istanbul, Turkey',
    'Dubai, UAE',
    'Tokyo, Japan',
    'Kyoto, Japan',
    'Seoul, South Korea',
    'Bali, Indonesia',
    'Phuket, Thailand',
    'Singapore',
    'Sydney, Australia',
    'Auckland, New Zealand',
    'New York, USA',
    'San Francisco, USA',
    'Banff, Canada',
    'Cairo, Egypt',
    'Cape Town, South Africa'
];

// Testimonials data
$testimonials = [
    [
        'name'   => 'Aisha Rahman',
        'place'  => 'Dubai Trip',
        'review' => 'InteleTour made our Dubai vacation absolutely seamless. The AI assistant helped us pick the perfect itinerary and the booking process was effortless!',
        'rating' => 5,
        'avatar' => 'AR'
    ],
    [
        'name'   => 'Ravi Kumar',
        'place'  => 'Bali Trip',
        'review' => 'Best travel experience ever! The Bali package was perfectly curated. Hotels, transfers, and guided tours — all handled brilliantly by InteleTour.',
        'rating' => 5,
        'avatar' => 'RK'
    ],
    [
        'name'   => 'Priya Menon',
        'place'  => 'Paris Trip',
        'review' => 'I used the AI chatbot to plan my Paris trip and it recommended exactly what I was looking for. Highly recommend InteleTour to all travel lovers!',
        'rating' => 5,
        'avatar' => 'PM'
    ],
    [
        'name'   => 'James Wilson',
        'place'  => 'Tokyo Trip',
        'review' => 'Tokyo was a dream come true. InteleTour\'s package covered everything — Mount Fuji, Shibuya, temples and more. The team was incredibly helpful.',
        'rating' => 4,
        'avatar' => 'JW'
    ],
    [
        'name'   => 'Meera Nair',
        'place'  => 'Singapore Trip',
        'review' => 'Smooth booking, great price, and the customer support was top-notch. The InteleTour AI assistant is truly a game-changer for travel planning!',
        'rating' => 5,
        'avatar' => 'MN'
    ],
    [
        'name'   => 'Carlos Rivera',
        'place'  => 'Barcelona Trip',
        'review' => 'Visited Barcelona with my family and every detail was perfectly arranged. InteleTour truly delivers what they promise — unforgettable experiences!',
        'rating' => 5,
        'avatar' => 'CR'
    ]
];

// FAQ data
$faqs = [
    [
        'q' => 'How do I book a travel package on InteleTour?',
        'a' => 'Simply browse our Packages page, select your preferred destination, choose your travel date and number of travelers, then proceed to payment. You will receive an instant booking confirmation with a unique transaction ID.'
    ],
    [
        'q' => 'What payment methods are accepted?',
        'a' => 'We accept Credit Card, Debit Card, UPI (GPay, PhonePe, Paytm), and Net Banking. All transactions are secured with end-to-end encryption for your safety.'
    ],
    [
        'q' => 'Can I cancel or modify my booking?',
        'a' => 'Yes, you can cancel or modify your booking from the My Bookings section. Please note that cancellation policies vary depending on the destination and travel date.'
    ],
    [
        'q' => 'What is the AI Travel Assistant?',
        'a' => 'Our AI Travel Assistant is an intelligent chatbot available 24/7. It helps you discover destinations, compare packages, answer travel queries, and guide you through the entire booking process in real-time.'
    ],
    [
        'q' => 'Are the package prices inclusive of all charges?',
        'a' => 'Yes! All our package prices are all-inclusive covering accommodation, guided tours, and local transfers. There are absolutely no hidden charges. What you see is what you pay.'
    ],
    [
        'q' => 'How do I contact customer support?',
        'a' => 'You can reach our support team via email at support@inteletour.com or call +91 98765 43210. Support is available Monday to Saturday, 9 AM to 6 PM IST. You can also chat with our AI assistant anytime!'
    ],
    [
        'q' => 'Is it safe to book and pay online on InteleTour?',
        'a' => 'Absolutely. InteleTour uses secure HTTPS connections, encrypted payment gateways, and follows strict data protection standards to ensure your personal and financial information is completely safe.'
    ],
    [
        'q' => 'Can I book for a group of travelers?',
        'a' => 'Yes! When booking, you can specify up to 20 travelers for any package. The total price is automatically calculated based on the per-person rate multiplied by the number of travelers.'
    ]
];

// Handle newsletter subscription
$newsletter_msg = '';
$newsletter_type = 'success';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['newsletter_email'])) {
    $email = filter_var(trim($_POST['newsletter_email']), FILTER_VALIDATE_EMAIL);
    if ($email) {
        $newsletter_msg = "Thank you for subscribing! We'll keep you updated with the best travel deals. ✈️";
    } else {
        $newsletter_msg = "Please enter a valid email address.";
        $newsletter_type = 'danger';
    }
}

// Handle contact form
$contact_msg = '';
$contact_type = 'success';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['contact_submit'])) {
    $c_name    = htmlspecialchars(trim($_POST['contact_name'] ?? ''));
    $c_email   = filter_var(trim($_POST['contact_email'] ?? ''), FILTER_VALIDATE_EMAIL);
    $c_subject = htmlspecialchars(trim($_POST['contact_subject'] ?? ''));
    $c_message = htmlspecialchars(trim($_POST['contact_message'] ?? ''));

    if (!$c_name || !$c_email || !$c_subject || !$c_message) {
        $contact_msg  = "Please fill in all fields before submitting.";
        $contact_type = 'danger';
    } else {
        $contact_msg = "Thank you, <strong>$c_name</strong>! Your message has been received. We'll get back to you within 24 hours. 😊";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="InteleTour – Discover handcrafted travel packages to the world's most beautiful destinations. AI-powered booking, best prices, secure payments.">
    <meta name="keywords" content="travel, tour booking, travel packages, Paris, Tokyo, Bali, Dubai, AI travel assistant">
    <title>InteleTour – Explore the World</title>
    <link rel="stylesheet" href="style.css">
    <style>
        /* ===== ANNOUNCEMENT BAR ===== */
        .announcement-bar {
            background: linear-gradient(90deg, var(--accent), #e06b00);
            color: white;
            text-align: center;
            padding: 9px 20px;
            font-size: 13px;
            font-weight: 600;
            letter-spacing: 0.3px;
        }
        .announcement-bar a {
            color: white;
            text-decoration: underline;
            margin-left: 8px;
        }

        /* Keep navbar links one fixed color (no active/hover color switch) */
        .nav-links > li > a:not(.btn-nav) {
            color: #111 !important;
        }
        .nav-links > li > a:not(.btn-nav):hover,
        .nav-links > li > a:not(.btn-nav).active {
            color: #111 !important;
            background: transparent !important;
        }

        /* ===== DROPDOWN ===== */
        .dropdown { position: relative; }
        .dropdown-menu {
            display: none;
            position: absolute;
            top: 38px;
            left: 0;
            background: white;
            border-radius: 10px;
            box-shadow: 0 8px 30px rgba(0,0,0,0.15);
            min-width: 180px;
            z-index: 999;
            overflow: hidden;
        }
        .dropdown-menu li { list-style: none; }
        .dropdown-menu li a {
            display: block;
            padding: 11px 18px;
            color: var(--dark);
            font-size: 14px;
            font-weight: 500;
            transition: var(--transition);
        }
        .dropdown-menu li a:hover {
            background: var(--light);
            color: var(--primary);
        }
        .dropdown:hover .dropdown-menu { display: block; }
        .caret { font-size: 11px; }

        /* ===== HERO VIDEO OVERLAY ===== */
        .hero-badge {
            display: inline-block;
            background: rgba(255,255,255,0.2);
            border: 1px solid rgba(255,255,255,0.4);
            color: white;
            padding: 6px 16px;
            border-radius: 25px;
            font-size: 13px;
            font-weight: 600;
            margin-bottom: 18px;
            backdrop-filter: blur(4px);
        }
        .hero-stats {
            display: flex;
            gap: 40px;
            margin-top: 40px;
            justify-content: center;
            flex-wrap: wrap;
        }
        .hero-stat {
            text-align: center;
            color: white;
        }
        .hero-stat h3 {
            font-size: 28px;
            font-weight: 800;
            color: var(--accent);
        }
        .hero-stat p {
            font-size: 13px;
            opacity: 0.85;
            margin: 0;
        }

        /* ===== TRAVEL GALLERY GRID ===== */
        .travel-gallery-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
            gap: 20px;
            max-width: 1300px;
            margin: 0 auto;
        }
        .travel-gallery-card {
            position: relative;
            border-radius: 14px;
            overflow: hidden;
            box-shadow: 0 4px 18px rgba(0,0,0,0.12);
            cursor: pointer;
            aspect-ratio: 4/3;
        }
        .travel-gallery-card img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
            display: block;
        }
        .travel-gallery-card:hover img { transform: scale(1.1); }
        .travel-gallery-info {
            position: absolute;
            bottom: 0; left: 0; right: 0;
            background: linear-gradient(transparent, rgba(3,4,94,0.85));
            color: white;
            padding: 30px 16px 14px;
            transform: translateY(100%);
            transition: transform 0.35s ease;
        }
        .travel-gallery-card:hover .travel-gallery-info { transform: translateY(0); }
        .travel-gallery-info h3 { font-size: 16px; font-weight: 700; margin: 0; }

        /* ===== GALLERY FILTER TABS ===== */
        .gallery-filter-tabs {
            display: flex;
            justify-content: center;
            gap: 10px;
            flex-wrap: wrap;
            margin-bottom: 35px;
        }
        .filter-tab {
            padding: 8px 20px;
            border: 2px solid var(--primary);
            color: var(--primary);
            background: transparent;
            border-radius: 25px;
            cursor: pointer;
            font-size: 13px;
            font-weight: 600;
            transition: var(--transition);
        }
        .filter-tab.active,
        .filter-tab:hover {
            background: var(--primary);
            color: white;
        }

        /* ===== STATS COUNTER SECTION ===== */
        .stats-section {
            background: linear-gradient(135deg, var(--dark), var(--primary));
            padding: 60px 40px;
        }
        .stats-counter-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 30px;
            max-width: 1000px;
            margin: 0 auto;
            text-align: center;
        }
        .counter-item { color: white; }
        .counter-item .counter-number {
            font-size: 48px;
            font-weight: 800;
            color: var(--accent);
            display: block;
            line-height: 1;
        }
        .counter-item .counter-label {
            font-size: 15px;
            opacity: 0.88;
            margin-top: 8px;
            font-weight: 500;
        }
        .counter-item .counter-icon {
            font-size: 36px;
            margin-bottom: 10px;
            display: block;
        }

        /* ===== PACKAGES SECTION ===== */
        .package-badge {
            background: var(--accent);
            color: white;
            font-size: 11px;
            font-weight: 700;
            padding: 3px 10px;
            border-radius: 20px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .card-rating { color: #f7b731; font-size: 13px; margin-bottom: 8px; }
        .card-includes {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
            margin-bottom: 14px;
        }
        .include-tag {
            background: var(--light);
            color: var(--primary);
            font-size: 11px;
            padding: 3px 9px;
            border-radius: 12px;
            font-weight: 600;
        }

        /* ===== PROCESS SECTION ===== */
        .process-section { background: var(--white); }
        .process-steps {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 0;
            max-width: 1000px;
            margin: 0 auto;
            position: relative;
        }
        .process-steps::before {
            content: '';
            position: absolute;
            top: 40px;
            left: 15%;
            right: 15%;
            height: 3px;
            background: linear-gradient(90deg, var(--primary), var(--secondary));
            z-index: 0;
        }
        .process-step {
            text-align: center;
            padding: 20px 15px;
            position: relative;
            z-index: 1;
        }
        .step-circle {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            font-size: 28px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 16px;
            box-shadow: 0 6px 20px rgba(0,119,182,0.35);
            font-weight: 800;
        }
        .process-step h3 { font-size: 16px; color: var(--dark); margin-bottom: 8px; }
        .process-step p { font-size: 13px; color: var(--gray); }

        /* ===== TESTIMONIALS ===== */
        .testimonials-section { background: var(--light); }
        .testimonials-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 25px;
            max-width: 1200px;
            margin: 0 auto;
        }
        .testimonial-card {
            background: white;
            padding: 28px;
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            position: relative;
            transition: var(--transition);
        }
        .testimonial-card:hover { transform: translateY(-5px); }
        .testimonial-card::before {
            content: '"';
            font-size: 80px;
            color: var(--primary);
            opacity: 0.12;
            position: absolute;
            top: 10px; left: 20px;
            font-family: Georgia, serif;
            line-height: 1;
        }
        .testimonial-text {
            font-size: 14px;
            color: #555;
            line-height: 1.7;
            margin-bottom: 20px;
            font-style: italic;
        }
        .testimonial-author {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .author-avatar {
            width: 46px;
            height: 46px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            font-size: 15px;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .author-info h4 { font-size: 15px; color: var(--dark); margin-bottom: 2px; }
        .author-info span { font-size: 12px; color: var(--gray); }
        .star-rating { color: #f7b731; font-size: 13px; margin-bottom: 12px; }

        /* ===== NEWSLETTER ===== */
        .newsletter-section {
            background: linear-gradient(135deg, #00b4d8, var(--primary));
            padding: 70px 40px;
            text-align: center;
            color: white;
        }
        .newsletter-section h2 { font-size: 32px; font-weight: 800; margin-bottom: 12px; }
        .newsletter-section p { opacity: 0.9; margin-bottom: 30px; font-size: 16px; }
        .newsletter-form {
            display: flex;
            justify-content: center;
            gap: 12px;
            max-width: 520px;
            margin: 0 auto;
            flex-wrap: wrap;
        }
        .newsletter-form input {
            flex: 1;
            min-width: 240px;
            padding: 13px 20px;
            border: none;
            border-radius: 30px;
            font-size: 14px;
            outline: none;
        }
        .newsletter-form button {
            background: var(--accent);
            color: white;
            border: none;
            padding: 13px 28px;
            border-radius: 30px;
            font-size: 14px;
            font-weight: 700;
            cursor: pointer;
            transition: var(--transition);
        }
        .newsletter-form button:hover { background: #e06b00; transform: scale(1.04); }

        /* ===== DESTINATIONS HIGHLIGHT ===== */
        .destinations-row {
            display: flex;
            gap: 15px;
            overflow-x: auto;
            padding-bottom: 10px;
            max-width: 1300px;
            margin: 0 auto;
            scrollbar-width: thin;
        }
        .dest-chip {
            flex-shrink: 0;
            background: white;
            border: 2px solid var(--light-gray);
            border-radius: 50px;
            padding: 10px 20px;
            font-size: 14px;
            font-weight: 600;
            color: var(--dark);
            cursor: pointer;
            transition: var(--transition);
            white-space: nowrap;
        }
        .dest-chip:hover {
            background: var(--primary);
            color: white;
            border-color: var(--primary);
        }

        /* ===== FAQ SECTION ===== */
        .faq-section { background: var(--white); }
        .faq-container {
            max-width: 800px;
            margin: 0 auto;
        }
        .faq-item {
            background: white;
            border: 1px solid #eee;
            border-radius: 10px;
            margin-bottom: 12px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        .faq-question {
            padding: 18px 22px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            cursor: pointer;
            font-weight: 600;
            color: var(--dark);
            font-size: 15px;
            transition: var(--transition);
        }
        .faq-question:hover { background: var(--light); color: var(--primary); }
        .faq-question.open { background: var(--primary); color: white; }
        .faq-icon {
            font-size: 20px;
            font-weight: 400;
            transition: transform 0.3s;
            flex-shrink: 0;
        }
        .faq-question.open .faq-icon { transform: rotate(45deg); }
        .faq-answer {
            display: none;
            padding: 16px 22px;
            font-size: 14px;
            color: #555;
            line-height: 1.7;
            border-top: 1px solid #eee;
        }
        .faq-answer.open { display: block; }

        /* ===== CONTACT SECTION ===== */
        .contact-section { background: var(--light); }
        .contact-grid {
            display: grid;
            grid-template-columns: 1fr 1.5fr;
            gap: 40px;
            max-width: 1050px;
            margin: 0 auto;
            align-items: start;
        }
        .contact-info h3 { font-size: 22px; color: var(--dark); margin-bottom: 16px; }
        .contact-info p { color: var(--gray); font-size: 14px; margin-bottom: 24px; line-height: 1.7; }
        .contact-detail {
            display: flex;
            align-items: flex-start;
            gap: 14px;
            margin-bottom: 18px;
        }
        .contact-icon {
            width: 44px;
            height: 44px;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            flex-shrink: 0;
        }
        .contact-detail-text h4 { font-size: 14px; color: var(--dark); margin-bottom: 3px; }
        .contact-detail-text p { color: var(--gray); font-size: 13px; margin: 0; }
        .contact-form-card {
            background: white;
            padding: 35px;
            border-radius: var(--radius);
            box-shadow: var(--shadow);
        }
        .contact-form-card h3 { font-size: 20px; color: var(--dark); margin-bottom: 20px; }

        /* ===== SOCIAL LINKS ===== */
        .social-links {
            display: flex;
            gap: 12px;
            margin-top: 20px;
        }
        .social-link {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--primary);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            transition: var(--transition);
            text-decoration: none;
        }
        .social-link:hover { background: var(--accent); transform: translateY(-3px); }

        /* ===== FOOTER ENHANCED ===== */
        .footer-main {
            background: var(--dark);
            color: rgba(255,255,255,0.8);
            padding: 60px 40px 30px;
        }
        .footer-grid {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr 1fr;
            gap: 40px;
            max-width: 1200px;
            margin: 0 auto 40px;
        }
        .footer-brand .logo { font-size: 22px; font-weight: 800; color: white; margin-bottom: 12px; }
        .footer-brand p { font-size: 13px; line-height: 1.7; opacity: 0.75; }
        .footer-col h4 { color: white; font-size: 15px; margin-bottom: 16px; font-weight: 700; }
        .footer-col ul { list-style: none; }
        .footer-col ul li { margin-bottom: 9px; }
        .footer-col ul li a { color: rgba(255,255,255,0.65); font-size: 13px; transition: var(--transition); }
        .footer-col ul li a:hover { color: var(--accent); padding-left: 5px; }
        footer {
            background: var(--dark);
            padding: 0;
            text-align: initial;
        }
        .footer-bottom {
            border-top: 1px solid rgba(255,255,255,0.1);
            padding: 22px 40px 26px;
            display: grid;
            grid-template-columns: minmax(0, 1fr) minmax(0, 1fr);
            align-items: center;
            max-width: 1300px;
            margin: 0 auto;
            gap: 24px;
            box-sizing: border-box;
        }
        .footer-bottom p {
            font-size: 13px;
            opacity: 0.65;
            margin: 0;
            line-height: 1.5;
        }
        .footer-bottom p:first-child { text-align: left; }
        .footer-bottom p:last-child { text-align: right; }

        /* ===== BACK TO TOP ===== */
        .back-to-top {
            position: fixed;
            bottom: 88px;
            right: 28px;
            width: 46px;
            height: 46px;
            background: var(--primary);
            color: white;
            border-radius: 50%;
            display: none;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            cursor: pointer;
            box-shadow: 0 4px 15px rgba(0,119,182,0.4);
            z-index: 9999;
            transition: var(--transition);
            border: none;
            text-decoration: none;
        }
        .back-to-top:hover { background: var(--accent); transform: translateY(-3px); }
        .back-to-top.show { display: flex; }

        /* ===== RESPONSIVE EXTRAS ===== */
        @media (max-width: 900px) {
            .contact-grid { grid-template-columns: 1fr; }
            .footer-grid { grid-template-columns: 1fr 1fr; }
            .process-steps::before { display: none; }
            .footer-bottom {
                grid-template-columns: 1fr;
                text-align: center;
                gap: 8px;
                padding: 18px 20px 22px;
            }
            .footer-bottom p:first-child { text-align: center; }
            .footer-bottom p:last-child { text-align: center; }
        }
        @media (max-width: 600px) {
            .footer-grid { grid-template-columns: 1fr; }
            .hero-stats { gap: 20px; }
            .counter-item .counter-number { font-size: 36px; }
        }
    </style>
</head>
<body>

<!-- ===== ANNOUNCEMENT BAR ===== -->
<div class="announcement-bar">
    🎉 Special Offer: Get 10% OFF on all bookings this month! Use code <strong>INTELE10</strong>
    <a href="packages.php">Book Now →</a>
</div>

<!-- ===== NAVBAR ===== -->
<nav class="navbar">
    <div class="logo">✈ Intele<span>Tour</span></div>
    <ul class="nav-links">
        <li><a href="index.php" class="active">Home</a></li>
        <li><a href="#about">About</a></li>
        <li><a href="booking.php">Booking</a></li>
        <li><a href="#gallery">Gallery</a></li>
        <li class="dropdown">
            <a href="#">Pages <span class="caret">&#9662;</span></a>
            <ul class="dropdown-menu">
                <li><a href="packages.php">📦 All Packages</a></li>
                <li><a href="ai_assistant.php">🤖 AI Assistant</a></li>
                
            </ul>
        </li>
        <?php if (!isLoggedIn()): ?>
            <li><a href="register.php" class="btn-nav">Get Started</a></li>
        <?php else: ?>
            <li><a href="logout.php" class="btn-nav">Logout</a></li>
        <?php endif; ?>
    </ul>
</nav>

<!-- ===== HERO SECTION ===== -->
<section class="hero">
    <span class="hero-badge">✈ AI-Powered Travel Platform </span>
    <h1>Discover the World with <span>InteleTour</span></h1>
    <p>Handcrafted travel packages to the world's most beautiful destinations, powered by AI intelligence</p>
    <div class="hero-btns">
        <a href="packages.php" class="btn btn-primary">🌍 Explore Packages</a>
        <a href="ai_assistant.php" class="btn btn-outline">🤖 AI Travel Assistant</a>
    </div>
    <div class="hero-stats">
        <div class="hero-stat">
            <h3>10K+</h3>
            <p>Happy Travelers</p>
        </div>
        <div class="hero-stat">
            <h3>50+</h3>
            <p>Destinations</p>
        </div>
        <div class="hero-stat">
            <h3>4.9★</h3>
            <p>Average Rating</p>
        </div>
        <div class="hero-stat">
            <h3>24/7</h3>
            <p>AI Support</p>
        </div>
    </div>
</section>

<!-- ===== POPULAR DESTINATION CHIPS ===== -->
<section class="section" style="padding: 30px 40px; background: var(--light-gray);">
    <div class="destinations-row">
        <?php
        $chips = ['🗼 Paris','🏯 Tokyo','🏝️ Bali','🏙️ Dubai','🗽 New York','🎭 London','🌉 Singapore','🏔️ Banff','🌊 Sydney','🕌 Istanbul','🎪 Barcelona','🏛️ Rome'];
        foreach ($chips as $chip): ?>
            <a href="packages.php" class="dest-chip"><?= $chip ?></a>
        <?php endforeach; ?>
    </div>
</section>

<!-- ===== FEATURED PACKAGES ===== -->
<section class="section" style="background: var(--white);">
    <div class="section-title">
        <h2>Featured <span>Packages</span></h2>
        <div class="divider"></div>
        <p>Our most loved travel packages — curated just for you</p>
    </div>
    <div class="packages-grid">
        <?php if ($packages_result && $packages_result->num_rows > 0):
            while ($pkg = $packages_result->fetch_assoc()):
                $price_usd = ((float)$pkg['price']) / $usd_rate;
            ?>
        <div class="package-card">
            <div style="position:relative;">
                <img src="<?= htmlspecialchars(packageImageUrl($pkg['destination'], $pkg['image'] ?? '')) ?>" alt="<?= htmlspecialchars($pkg['destination']) ?>">
                <span class="package-badge" style="position:absolute; top:14px; left:14px;">⭐ Popular</span>
            </div>
            <div class="card-body">
                <div class="card-rating">★★★★★ <span style="color:var(--gray); font-size:12px;">(4.9/5)</span></div>
                <h3>📍 <?= htmlspecialchars($pkg['destination']) ?></h3>
                <p><?= htmlspecialchars(substr($pkg['description'], 0, 90)) ?>...</p>
                <div class="card-includes">
                    <span class="include-tag">🏨 Hotel</span>
                    <span class="include-tag">🚌 Transfer</span>
                    <span class="include-tag">🎟️ Tours</span>
                </div>
                <div class="card-meta">
                    <span class="price">&#8377;<?= number_format($pkg['price'], 2) ?> / $<?= number_format($price_usd, 2) ?></span>
                    <span class="duration">⏱ <?= htmlspecialchars($pkg['duration']) ?></span>
                </div>
                <?php if (isLoggedIn()): ?>
                    <a href="booking.php?id=<?= $pkg['id'] ?>" class="btn btn-primary btn-full">Book Now →</a>
                <?php else: ?>
                    <a href="login.php" class="btn btn-secondary btn-full">Login to Book</a>
                <?php endif; ?>
            </div>
        </div>
        <?php endwhile;
        else: ?>
            <p style="text-align:center; color:var(--gray);">No packages available at the moment.</p>
        <?php endif; ?>
    </div>
    <div style="text-align:center; margin-top:35px;">
        <a href="packages.php" class="btn btn-secondary">View All Packages →</a>
    </div>
</section>

<!-- ===== HOW IT WORKS ===== -->
<section class="section process-section" id="about">
    <div class="section-title">
        <h2>How It <span>Works</span></h2>
        <div class="divider"></div>
        <p>Book your dream vacation in just 4 simple steps</p>
    </div>
    <div class="process-steps">
        <div class="process-step">
            <div class="step-circle">1</div>
            <h3>Browse Packages</h3>
            <p>Explore our curated collection of global travel packages sorted by destination and budget.</p>
        </div>
        <div class="process-step">
            <div class="step-circle">2</div>
            <h3>Create Account</h3>
            <p>Register for free and unlock full access to booking, tracking, and AI-powered recommendations.</p>
        </div>
        <div class="process-step">
            <div class="step-circle">3</div>
            <h3>Book Your Trip</h3>
            <p>Select your travel date, number of travelers, and confirm your booking in just a few clicks.</p>
        </div>
        <div class="process-step">
            <div class="step-circle">4</div>
            <h3>Pay & Travel</h3>
            <p>Complete secure payment via UPI, card, or net banking and get ready for your adventure!</p>
        </div>
    </div>
</section>

<!-- ===== TRAVEL GALLERY ===== -->
<section id="gallery" class="section" style="background: var(--light);">
    <div class="section-title">
        <h2>Travel <span>Gallery</span></h2>
        <div class="divider"></div>
        <p>Explore breathtaking destinations from around the world</p>
    </div>

    <!-- Filter Tabs -->
    <div class="gallery-filter-tabs">
        <button class="filter-tab active" data-filter="all">🌐 All</button>
        <button class="filter-tab" data-filter="europe">🏰 Europe</button>
        <button class="filter-tab" data-filter="asia">🏯 Asia</button>
        <button class="filter-tab" data-filter="middleeast">🕌 Middle East</button>
        <button class="filter-tab" data-filter="americas">🗽 Americas</button>
        <button class="filter-tab" data-filter="oceania">🦘 Oceania</button>
        <button class="filter-tab" data-filter="africa">🦁 Africa</button>
    </div>

    <?php
    // Map each location to a region for filtering
    $location_regions = [
        'Paris, France'             => 'europe',
        'Rome, Italy'               => 'europe',
        'Santorini, Greece'         => 'europe',
        'Barcelona, Spain'          => 'europe',
        'Amsterdam, Netherlands'    => 'europe',
        'Prague, Czech Republic'    => 'europe',
        'Vienna, Austria'           => 'europe',
        'Zurich, Switzerland'       => 'europe',
        'London, UK'                => 'europe',
        'Istanbul, Turkey'          => 'europe',
        'Dubai, UAE'                => 'middleeast',
        'Tokyo, Japan'              => 'asia',
        'Kyoto, Japan'              => 'asia',
        'Seoul, South Korea'        => 'asia',
        'Bali, Indonesia'           => 'asia',
        'Phuket, Thailand'          => 'asia',
        'Singapore'                 => 'asia',
        'Sydney, Australia'         => 'oceania',
        'Auckland, New Zealand'     => 'oceania',
        'New York, USA'             => 'americas',
        'San Francisco, USA'        => 'americas',
        'Banff, Canada'             => 'americas',
        'Cairo, Egypt'              => 'africa',
        'Cape Town, South Africa'   => 'africa'
    ];
    ?>

    <div class="travel-gallery-grid" id="gallery-grid">
        <?php foreach ($gallery_locations as $idx => $location):
            $seed   = urlencode(strtolower(str_replace([',', ' '], ['', '-'], $location)));
            $region = $location_regions[$location] ?? 'all';
        ?>
        <div class="travel-gallery-card" data-region="<?= $region ?>">
            <img
                src="https://picsum.photos/seed/<?= $seed ?>-<?= $idx + 1 ?>/800/520"
                alt="<?= htmlspecialchars($location) ?>"
                loading="lazy"
            >
            <div class="travel-gallery-info">
                <h3>📍 <?= htmlspecialchars($location) ?></h3>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</section>

<!-- ===== WHY CHOOSE US ===== -->
<section class="section" style="background: var(--white);">
    <div class="section-title">
        <h2>Why Choose <span>Us</span></h2>
        <div class="divider"></div>
        <p>Everything you need for a perfect travel experience</p>
    </div>
    <div class="features-grid">
        <div class="feature-card">
            <div class="feature-icon">🌍</div>
            <h3>Global Destinations</h3>
            <p>Explore 50+ top destinations across Europe, Asia, Middle East, Americas, and more.</p>
        </div>
        <div class="feature-card">
            <div class="feature-icon">💰</div>
            <h3>Best Prices</h3>
            <p>Competitive pricing with transparent costs and absolutely no hidden charges guaranteed.</p>
        </div>
        <div class="feature-card">
            <div class="feature-icon">🤖</div>
            <h3>AI-Powered</h3>
            <p>Get personalized destination recommendations and 24/7 AI travel assistant support.</p>
        </div>
        <div class="feature-card">
            <div class="feature-icon">🔒</div>
            <h3>Secure Booking</h3>
            <p>End-to-end encrypted payments and robust data protection for peace of mind.</p>
        </div>
        <div class="feature-card">
            <div class="feature-icon">📱</div>
            <h3>Easy Management</h3>
            <p>Manage, track, and update all your bookings from a clean, user-friendly dashboard.</p>
        </div>
        <div class="feature-card">
            <div class="feature-icon">🎧</div>
            <h3>24/7 Support</h3>
            <p>Round-the-clock human and AI support to assist you at every step of your journey.</p>
        </div>
        <div class="feature-card">
            <div class="feature-icon">✅</div>
            <h3>Verified Packages</h3>
            <p>All packages are verified and quality-checked to ensure the best travel experience.</p>
        </div>
        <div class="feature-card">
            <div class="feature-icon">🔄</div>
            <h3>Flexible Cancellation</h3>
            <p>Worry-free booking with flexible cancellation and modification policies available.</p>
        </div>
    </div>
</section>

<!-- ===== TESTIMONIALS ===== -->
<section class="section testimonials-section">
    <div class="section-title">
        <h2>What Travelers <span>Say</span></h2>
        <div class="divider"></div>
        <p>Real reviews from real InteleTour travelers</p>
    </div>
    <div class="testimonials-grid">
        <?php foreach ($testimonials as $t): ?>
        <div class="testimonial-card">
            <div class="star-rating">
                <?= str_repeat('★', $t['rating']) . str_repeat('☆', 5 - $t['rating']) ?>
            </div>
            <p class="testimonial-text"><?= htmlspecialchars($t['review']) ?></p>
            <div class="testimonial-author">
                <div class="author-avatar"><?= $t['avatar'] ?></div>
                <div class="author-info">
                    <h4><?= htmlspecialchars($t['name']) ?></h4>
                    <span>📍 <?= htmlspecialchars($t['place']) ?></span>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</section>

<!-- ===== FAQ SECTION ===== -->
<section class="section faq-section">
    <div class="section-title">
        <h2>Frequently Asked <span>Questions</span></h2>
        <div class="divider"></div>
        <p>Everything you need to know about booking with InteleTour</p>
    </div>
    <div class="faq-container">
        <?php foreach ($faqs as $i => $faq): ?>
        <div class="faq-item">
            <div class="faq-question" onclick="toggleFaq(<?= $i ?>)" id="faq-q-<?= $i ?>">
                <span><?= htmlspecialchars($faq['q']) ?></span>
                <span class="faq-icon">+</span>
            </div>
            <div class="faq-answer" id="faq-a-<?= $i ?>">
                <?= htmlspecialchars($faq['a']) ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</section>

<!-- ===== CTA SECTION ===== -->
<section class="section" style="background: linear-gradient(135deg, var(--dark), var(--primary)); text-align:center; color: white;">
    <h2 style="font-size:36px; font-weight:800; margin-bottom:15px;">Ready to Start Your Journey? 🌏</h2>
    <p style="opacity:0.88; margin-bottom:35px; font-size:17px; max-width:550px; margin-left:auto; margin-right:auto;">
        Join 10,000+ happy travelers who have already booked their dream vacations with InteleTour.
    </p>
    <div style="display:flex; gap:15px; justify-content:center; flex-wrap:wrap;">
        <?php if (!isLoggedIn()): ?>
            <a href="register.php" class="btn btn-primary">🚀 Get Started Free</a>
        <?php endif; ?>
        <a href="packages.php" class="btn btn-outline">📦 Browse Packages</a>
        <a href="ai_assistant.php" class="btn btn-outline">🤖 Chat with AI</a>
    </div>
</section>



<!-- ===== FOOTER ===== -->
<footer>
    <div class="footer-bottom">
        <p>© 2026 <span style="color:var(--accent);">InteleTour</span> — All rights reserved. Built with ❤️ </p>
        <p>Powered by AI · Secured by Trust · Inspired by Travel</p>
    </div>
</footer>

<!-- ===== BACK TO TOP BUTTON ===== -->
<a href="#" class="back-to-top" id="backToTop" title="Back to top">↑</a>

<!-- ===== SCRIPTS ===== -->
<script src="script.js"></script>
<script>
// ===== GALLERY FILTER =====
document.querySelectorAll('.filter-tab').forEach(tab => {
    tab.addEventListener('click', function () {
        document.querySelectorAll('.filter-tab').forEach(t => t.classList.remove('active'));
        this.classList.add('active');
        const filter = this.getAttribute('data-filter');
        document.querySelectorAll('.travel-gallery-card').forEach(card => {
            if (filter === 'all' || card.getAttribute('data-region') === filter) {
                card.style.display = 'block';
                card.style.animation = 'fadeIn 0.4s ease';
            } else {
                card.style.display = 'none';
            }
        });
    });
});

// ===== FAQ TOGGLE =====
function toggleFaq(index) {
    const question = document.getElementById('faq-q-' + index);
    const answer   = document.getElementById('faq-a-' + index);
    const isOpen   = answer.classList.contains('open');

    // Close all
    document.querySelectorAll('.faq-answer').forEach(a => a.classList.remove('open'));
    document.querySelectorAll('.faq-question').forEach(q => q.classList.remove('open'));

    // Open clicked if it was closed
    if (!isOpen) {
        answer.classList.add('open');
        question.classList.add('open');
    }
}

// ===== COUNTER ANIMATION =====
function animateCounters() {
    document.querySelectorAll('.counter-number').forEach(counter => {
        const target = parseInt(counter.getAttribute('data-target'));
        const duration = 2000;
        const step = target / (duration / 16);
        let current = 0;
        const timer = setInterval(() => {
            current += step;
            if (current >= target) {
                counter.textContent = target.toLocaleString('en-IN') + (target >= 1000 ? '' : '');
                clearInterval(timer);
            } else {
                counter.textContent = Math.floor(current).toLocaleString('en-IN');
            }
        }, 16);
    });
}

// Trigger counter when stats section is visible
const statsSection = document.querySelector('.stats-section');
let counterAnimated = false;
if (statsSection) {
    const observer = new IntersectionObserver(entries => {
        entries.forEach(entry => {
            if (entry.isIntersecting && !counterAnimated) {
                counterAnimated = true;
                animateCounters();
            }
        });
    }, { threshold: 0.3 });
    observer.observe(statsSection);
}

// ===== BACK TO TOP =====
const backToTopBtn = document.getElementById('backToTop');
window.addEventListener('scroll', () => {
    if (window.scrollY > 400) {
        backToTopBtn.classList.add('show');
    } else {
        backToTopBtn.classList.remove('show');
    }
});
backToTopBtn.addEventListener('click', function (e) {
    e.preventDefault();
    window.scrollTo({ top: 0, behavior: 'smooth' });
});

// ===== SMOOTH SCROLL FOR ANCHOR LINKS =====
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
            e.preventDefault();
            target.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    });
});

// ===== NAVBAR SCROLL EFFECT =====
window.addEventListener('scroll', () => {
    const navbar = document.querySelector('.navbar');
    if (window.scrollY > 60) {
        navbar.style.boxShadow = '0 4px 25px rgba(0,0,0,0.25)';
    } else {
        navbar.style.boxShadow = 'none';
    }
});

// ===== ANIMATE CARDS ON SCROLL =====
const cards = document.querySelectorAll('.package-card, .feature-card, .testimonial-card');
const cardObserver = new IntersectionObserver(entries => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            entry.target.style.opacity = '1';
            entry.target.style.transform = 'translateY(0)';
        }
    });
}, { threshold: 0.1 });

cards.forEach(card => {
    card.style.opacity = '0';
    card.style.transform = 'translateY(30px)';
    card.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
    cardObserver.observe(card);
});
</script>

</body>
</html>
