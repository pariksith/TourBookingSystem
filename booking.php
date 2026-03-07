<?php
session_start();
require_once 'database.php';

if (!isLoggedIn()) redirect('login.php?msg=Please login to book a package.');

$package_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($package_id <= 0) {
    $firstPkgResult = $conn->query("SELECT id FROM packages WHERE available = 1 ORDER BY id ASC LIMIT 1");
    $firstPkg = $firstPkgResult ? $firstPkgResult->fetch_assoc() : null;
    if ($firstPkg && isset($firstPkg['id'])) {
        redirect('booking.php?id=' . (int)$firstPkg['id']);
    }
    redirect('packages.php');
}

$stmt = $conn->prepare("SELECT * FROM packages WHERE id = ? AND available = 1");
$stmt->bind_param("i", $package_id);
$stmt->execute();
$package = $stmt->get_result()->fetch_assoc();

if (!$package) redirect('packages.php');

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $service_type = trim($_POST['service_type'] ?? 'flight');
    $valid_services = ['flight', 'train', 'hotel', 'cab'];
    if (!in_array($service_type, $valid_services, true)) {
        $service_type = 'flight';
    }

    $passenger_name  = trim($_POST['passenger_name'] ?? '');
    $passenger_email = trim($_POST['passenger_email'] ?? '');
    $user_id         = (int)$_SESSION['user_id'];
    $booking_date    = '';
    $travelers       = 0;

    if ($passenger_name === '') {
        $error = 'Please enter passenger name.';
    } elseif (!filter_var($passenger_email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    }

    if ($error === '') {
        if ($service_type === 'flight') {
            $from_city       = trim($_POST['flight_from_city'] ?? '');
            $to_city         = trim($_POST['flight_to_city'] ?? '');
            $travel_class    = trim($_POST['flight_travel_class'] ?? '');
            $booking_date    = clean($conn, $_POST['flight_departure_on'] ?? '');
            $adults          = max(1, (int)($_POST['flight_adults'] ?? 1));
            $children        = max(0, (int)($_POST['flight_children'] ?? 0));
            $travelers       = $adults + $children;

            if ($from_city === '' || $to_city === '' || $travel_class === '') {
                $error = 'Please complete all required flight fields.';
            }
        } elseif ($service_type === 'train') {
            $from_station    = trim($_POST['train_from_station'] ?? '');
            $to_station      = trim($_POST['train_to_station'] ?? '');
            $train_class     = trim($_POST['train_class'] ?? '');
            $booking_date    = clean($conn, $_POST['train_journey_date'] ?? '');
            $adults          = max(1, (int)($_POST['train_adults'] ?? 1));
            $children        = max(0, (int)($_POST['train_children'] ?? 0));
            $travelers       = $adults + $children;

            if ($from_station === '' || $to_station === '' || $train_class === '') {
                $error = 'Please complete all required train fields.';
            }
        } elseif ($service_type === 'hotel') {
            $city            = trim($_POST['hotel_city'] ?? '');
            $booking_date    = clean($conn, $_POST['hotel_check_in'] ?? '');
            $check_out       = clean($conn, $_POST['hotel_check_out'] ?? '');
            $room_type       = trim($_POST['hotel_room_type'] ?? '');
            $adults          = max(1, (int)($_POST['hotel_adults'] ?? 1));
            $children        = max(0, (int)($_POST['hotel_children'] ?? 0));
            $travelers       = $adults + $children;

            if ($city === '' || $room_type === '') {
                $error = 'Please complete all required hotel fields.';
            } elseif ($check_out === '' || strtotime($check_out) <= strtotime($booking_date)) {
                $error = 'Check-out date must be after check-in date.';
            }
        } else {
            $pickup_location = trim($_POST['cab_pickup_location'] ?? '');
            $drop_location   = trim($_POST['cab_drop_location'] ?? '');
            $booking_date    = clean($conn, $_POST['cab_pickup_date'] ?? '');
            $cab_type        = trim($_POST['cab_type'] ?? '');
            $travelers       = max(1, (int)($_POST['cab_passengers'] ?? 1));

            if ($pickup_location === '' || $drop_location === '' || $cab_type === '') {
                $error = 'Please complete all required cab fields.';
            }
        }
    }

    if ($error === '') {
        if (empty($booking_date)) {
            $error = 'Please select a valid date.';
        } elseif (strtotime($booking_date) < strtotime('today')) {
            $error = 'Date must be today or in the future.';
        } elseif ($travelers < 1 || $travelers > 20) {
            $error = 'Travelers must be between 1 and 20.';
        }
    }

    if ($error === '') {
        $total_price = (float)$package['price'] * $travelers;
        $ins = $conn->prepare("INSERT INTO bookings (user_id, package_id, booking_date, travelers, total_price, status) VALUES (?, ?, ?, ?, ?, 'pending')");
        $ins->bind_param("iisid", $user_id, $package_id, $booking_date, $travelers, $total_price);

        if ($ins->execute()) {
            $booking_id = $conn->insert_id;
            redirect("payment.php?booking_id=$booking_id");
        } else {
            $error = 'Booking failed. Please try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Form</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .booking-form-title { text-align: center; padding: 44px 20px 18px; background: #f2f2f2; }
        .booking-form-title h1 { margin: 0; font-size: 64px; line-height: 1.05; font-weight: 800; color: #1f2a3f; }
        .booking-form-title h1 .thin { font-weight: 400; color: #4f4f4f; }
        .booking-form-title .underline { width: 122px; height: 3px; background: #ea2a2a; margin: 14px auto 0; }
        .booking-service-strip-wrap { background: #f2f2f2; padding: 24px 20px 46px; }
        .booking-service-strip { max-width: 980px; margin: 0 auto; display: grid; grid-template-columns: repeat(4, 1fr); gap: 4px; }
        .booking-service-item { text-align: center; color: #fff; padding: 24px 10px; font-size: 24px; font-weight: 500; background: #f61222; }
        .booking-service-item.active { background: #2eb8c0; }
        .booking-container { margin-top: 25px; max-width: 1100px; background: #f2f2f2; box-shadow: none; }
        .service-tabs { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 8px; margin-bottom: 20px; }
        .service-tab { border: 1px solid #c8c8c8; background: #fff; padding: 12px; font-weight: 700; cursor: pointer; }
        .service-tab.active { background: #2eb8c0; color: #fff; border-color: #2eb8c0; }
        .service-form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 18px 24px; }
        .service-form-grid .span-2 { grid-column: 1 / -1; }
        .service-form-grid .form-group label { font-size: 16px; font-weight: 500; margin-bottom: 8px; color: #0d1f35; }
        .service-form-grid input, .service-form-grid select { background: #f2f2f2; border: 1px solid #a8a8a8; border-radius: 0; padding: 16px 18px; font-size: 15px; min-height: 64px; box-shadow: none; }
        .service-panel { display: none; }
        .service-panel.active { display: block; }
        .book-btn { margin-top: 16px; border-radius: 0; font-size: 16px; padding: 14px 20px; background: #2eb8c0; }
        .book-btn:hover { background: #1ea4ac; box-shadow: none; transform: none; }
        .package-summary { background: #fff; border: 1px solid #d8d8d8; border-left: 4px solid #2eb8c0; }
        @media (max-width: 900px) {
            .booking-form-title h1 { font-size: 44px; }
            .booking-service-item { font-size: 18px; padding: 18px 8px; }
            .service-form-grid { grid-template-columns: 1fr; }
            .service-form-grid .span-2 { grid-column: auto; }
            .service-tabs { grid-template-columns: 1fr 1fr; }
        }
        @media (max-width: 600px) {
            .booking-service-strip { grid-template-columns: 1fr 1fr; }
            .booking-form-title h1 { font-size: 34px; }
        }
    </style>
</head>
<body>
<nav class="navbar">
    <div class="logo">&#9992; Intele<span>Tour</span></div>
    <ul class="nav-links">
        <li><a href="index.php">Home</a></li>
        <li><a href="packages.php">Packages</a></li>
        <li><a href="my_bookings.php">My Bookings</a></li>
        <li><a href="logout.php" class="btn-nav">Logout</a></li>
    </ul>
</nav>

<div class="booking-form-title">
    <h1>Booking <span class="thin">Form</span></h1>
    <div class="underline"></div>
</div>

<div class="booking-container">
    <?php if ($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>

    <div class="package-summary">
        <h3><?= htmlspecialchars($package['destination']) ?></h3>
        <p><?= htmlspecialchars($package['description']) ?></p>
        <p style="margin-top:8px;"><strong>Duration:</strong> <?= htmlspecialchars($package['duration']) ?> &nbsp;|&nbsp; <strong>Base price:</strong> ₹ <?= number_format($package['price'], 2) ?></p>
    </div>

    <?php $serviceType = $_POST['service_type'] ?? 'flight'; ?>
    <form method="POST" id="booking_form">
        <input type="hidden" name="service_type" id="service_type" value="<?= htmlspecialchars($serviceType) ?>">
        <input type="hidden" id="package_price" value="<?= htmlspecialchars((string)$package['price']) ?>">

        <div class="service-tabs">
            <button type="button" class="service-tab" data-service="flight">Flight</button>
            <button type="button" class="service-tab" data-service="train">Train</button>
            <button type="button" class="service-tab" data-service="hotel">Hotel</button>
            <button type="button" class="service-tab" data-service="cab">Cab</button>
        </div>

        <div class="service-form-grid">
            <div class="form-group span-2">
                <label>Name *</label>
                <input type="text" name="passenger_name" placeholder="Your Name" value="<?= htmlspecialchars($_POST['passenger_name'] ?? '') ?>" required>
            </div>

            <div class="form-group span-2">
                <label>E-mail *</label>
                <input type="email" name="passenger_email" placeholder="ex : yourmail@gmail.com" value="<?= htmlspecialchars($_POST['passenger_email'] ?? '') ?>" required>
            </div>

            <div class="service-panel" id="panel-flight">
                <div class="service-form-grid">
                    <div class="form-group">
                        <label>From *</label>
                        <?php $flightFrom = $_POST['flight_from_city'] ?? 'India'; ?>
                        <select name="flight_from_city">
                            <option value="India" <?= $flightFrom === 'India' ? 'selected' : '' ?>>India</option>
                            <option value="Australia" <?= $flightFrom === 'Australia' ? 'selected' : '' ?>>Australia</option>
                            <option value="Singapore" <?= $flightFrom === 'Singapore' ? 'selected' : '' ?>>Singapore</option>
                            <option value="UAE" <?= $flightFrom === 'UAE' ? 'selected' : '' ?>>UAE</option>
                            <option value="Japan" <?= $flightFrom === 'Japan' ? 'selected' : '' ?>>Japan</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>To *</label>
                        <?php $flightTo = $_POST['flight_to_city'] ?? $package['destination']; ?>
                        <select name="flight_to_city">
                            <option value="<?= htmlspecialchars($package['destination']) ?>" <?= $flightTo === $package['destination'] ? 'selected' : '' ?>><?= htmlspecialchars($package['destination']) ?></option>
                            <option value="Singapore" <?= $flightTo === 'Singapore' ? 'selected' : '' ?>>Singapore</option>
                            <option value="Sydney" <?= $flightTo === 'Sydney' ? 'selected' : '' ?>>Sydney</option>
                            <option value="Tokyo" <?= $flightTo === 'Tokyo' ? 'selected' : '' ?>>Tokyo</option>
                            <option value="Paris" <?= $flightTo === 'Paris' ? 'selected' : '' ?>>Paris</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Adults *</label>
                        <?php $flightAdults = (int)($_POST['flight_adults'] ?? 1); ?>
                        <select name="flight_adults" id="flight_adults">
                            <?php for ($i = 1; $i <= 10; $i++): ?>
                                <option value="<?= $i ?>" <?= $flightAdults === $i ? 'selected' : '' ?>><?= str_pad((string)$i, 2, '0', STR_PAD_LEFT) ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Children *</label>
                        <?php $flightChildren = (int)($_POST['flight_children'] ?? 0); ?>
                        <select name="flight_children" id="flight_children">
                            <?php for ($i = 0; $i <= 10; $i++): ?>
                                <option value="<?= $i ?>" <?= $flightChildren === $i ? 'selected' : '' ?>><?= str_pad((string)$i, 2, '0', STR_PAD_LEFT) ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <div class="form-group span-2">
                        <label>Travel Class *</label>
                        <?php $flightClass = $_POST['flight_travel_class'] ?? 'Economy Class'; ?>
                        <select name="flight_travel_class">
                            <option value="Economy Class" <?= $flightClass === 'Economy Class' ? 'selected' : '' ?>>Economy Class</option>
                            <option value="Premium Economy" <?= $flightClass === 'Premium Economy' ? 'selected' : '' ?>>Premium Economy</option>
                            <option value="Business Class" <?= $flightClass === 'Business Class' ? 'selected' : '' ?>>Business Class</option>
                            <option value="First Class" <?= $flightClass === 'First Class' ? 'selected' : '' ?>>First Class</option>
                        </select>
                    </div>
                    <div class="form-group span-2">
                        <label>Departure On *</label>
                        <input type="date" name="flight_departure_on" min="<?= date('Y-m-d') ?>" value="<?= htmlspecialchars($_POST['flight_departure_on'] ?? '') ?>">
                    </div>
                </div>
            </div>

            <div class="service-panel" id="panel-train">
                <div class="service-form-grid">
                    <div class="form-group">
                        <label>From Station *</label>
                        <input type="text" name="train_from_station" value="<?= htmlspecialchars($_POST['train_from_station'] ?? '') ?>" placeholder="Ex: New Delhi">
                    </div>
                    <div class="form-group">
                        <label>To Station *</label>
                        <input type="text" name="train_to_station" value="<?= htmlspecialchars($_POST['train_to_station'] ?? '') ?>" placeholder="Ex: Mumbai Central">
                    </div>
                    <div class="form-group">
                        <label>Adults *</label>
                        <?php $trainAdults = (int)($_POST['train_adults'] ?? 1); ?>
                        <select name="train_adults" id="train_adults">
                            <?php for ($i = 1; $i <= 10; $i++): ?>
                                <option value="<?= $i ?>" <?= $trainAdults === $i ? 'selected' : '' ?>><?= str_pad((string)$i, 2, '0', STR_PAD_LEFT) ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Children *</label>
                        <?php $trainChildren = (int)($_POST['train_children'] ?? 0); ?>
                        <select name="train_children" id="train_children">
                            <?php for ($i = 0; $i <= 10; $i++): ?>
                                <option value="<?= $i ?>" <?= $trainChildren === $i ? 'selected' : '' ?>><?= str_pad((string)$i, 2, '0', STR_PAD_LEFT) ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Class *</label>
                        <?php $trainClass = $_POST['train_class'] ?? 'Sleeper'; ?>
                        <select name="train_class">
                            <option value="Sleeper" <?= $trainClass === 'Sleeper' ? 'selected' : '' ?>>Sleeper</option>
                            <option value="3A" <?= $trainClass === '3A' ? 'selected' : '' ?>>AC 3 Tier (3A)</option>
                            <option value="2A" <?= $trainClass === '2A' ? 'selected' : '' ?>>AC 2 Tier (2A)</option>
                            <option value="1A" <?= $trainClass === '1A' ? 'selected' : '' ?>>First AC (1A)</option>
                            <option value="CC" <?= $trainClass === 'CC' ? 'selected' : '' ?>>Chair Car (CC)</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Journey Date *</label>
                        <input type="date" name="train_journey_date" min="<?= date('Y-m-d') ?>" value="<?= htmlspecialchars($_POST['train_journey_date'] ?? '') ?>">
                    </div>
                </div>
            </div>

            <div class="service-panel" id="panel-hotel">
                <div class="service-form-grid">
                    <div class="form-group span-2">
                        <label>City / Hotel Area *</label>
                        <input type="text" name="hotel_city" value="<?= htmlspecialchars($_POST['hotel_city'] ?? $package['destination']) ?>" placeholder="Ex: Connaught Place, New Delhi">
                    </div>
                    <div class="form-group">
                        <label>Check-In *</label>
                        <input type="date" name="hotel_check_in" min="<?= date('Y-m-d') ?>" value="<?= htmlspecialchars($_POST['hotel_check_in'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label>Check-Out *</label>
                        <input type="date" name="hotel_check_out" min="<?= date('Y-m-d') ?>" value="<?= htmlspecialchars($_POST['hotel_check_out'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label>Adults *</label>
                        <?php $hotelAdults = (int)($_POST['hotel_adults'] ?? 2); ?>
                        <select name="hotel_adults" id="hotel_adults">
                            <?php for ($i = 1; $i <= 10; $i++): ?>
                                <option value="<?= $i ?>" <?= $hotelAdults === $i ? 'selected' : '' ?>><?= str_pad((string)$i, 2, '0', STR_PAD_LEFT) ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Children *</label>
                        <?php $hotelChildren = (int)($_POST['hotel_children'] ?? 0); ?>
                        <select name="hotel_children" id="hotel_children">
                            <?php for ($i = 0; $i <= 10; $i++): ?>
                                <option value="<?= $i ?>" <?= $hotelChildren === $i ? 'selected' : '' ?>><?= str_pad((string)$i, 2, '0', STR_PAD_LEFT) ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <div class="form-group span-2">
                        <label>Room Type *</label>
                        <?php $hotelRoom = $_POST['hotel_room_type'] ?? 'Deluxe'; ?>
                        <select name="hotel_room_type">
                            <option value="Standard" <?= $hotelRoom === 'Standard' ? 'selected' : '' ?>>Standard</option>
                            <option value="Deluxe" <?= $hotelRoom === 'Deluxe' ? 'selected' : '' ?>>Deluxe</option>
                            <option value="Suite" <?= $hotelRoom === 'Suite' ? 'selected' : '' ?>>Suite</option>
                            <option value="Family Room" <?= $hotelRoom === 'Family Room' ? 'selected' : '' ?>>Family Room</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="service-panel" id="panel-cab">
                <div class="service-form-grid">
                    <div class="form-group">
                        <label>Pickup Location *</label>
                        <input type="text" name="cab_pickup_location" value="<?= htmlspecialchars($_POST['cab_pickup_location'] ?? '') ?>" placeholder="Ex: Airport Terminal 3">
                    </div>
                    <div class="form-group">
                        <label>Drop Location *</label>
                        <input type="text" name="cab_drop_location" value="<?= htmlspecialchars($_POST['cab_drop_location'] ?? '') ?>" placeholder="Ex: Downtown Hotel">
                    </div>
                    <div class="form-group">
                        <label>Pickup Date *</label>
                        <input type="date" name="cab_pickup_date" min="<?= date('Y-m-d') ?>" value="<?= htmlspecialchars($_POST['cab_pickup_date'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label>Pickup Time</label>
                        <input type="time" name="cab_pickup_time" value="<?= htmlspecialchars($_POST['cab_pickup_time'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label>Cab Type *</label>
                        <?php $cabType = $_POST['cab_type'] ?? 'Sedan'; ?>
                        <select name="cab_type">
                            <option value="Hatchback" <?= $cabType === 'Hatchback' ? 'selected' : '' ?>>Hatchback</option>
                            <option value="Sedan" <?= $cabType === 'Sedan' ? 'selected' : '' ?>>Sedan</option>
                            <option value="SUV" <?= $cabType === 'SUV' ? 'selected' : '' ?>>SUV</option>
                            <option value="Premium" <?= $cabType === 'Premium' ? 'selected' : '' ?>>Premium</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Passengers *</label>
                        <?php $cabPassengers = (int)($_POST['cab_passengers'] ?? 1); ?>
                        <select name="cab_passengers" id="cab_passengers">
                            <?php for ($i = 1; $i <= 10; $i++): ?>
                                <option value="<?= $i ?>" <?= $cabPassengers === $i ? 'selected' : '' ?>><?= str_pad((string)$i, 2, '0', STR_PAD_LEFT) ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <div class="form-group">
            <label>Total Price</label>
            <div style="font-size:24px; font-weight:800; color:var(--accent);" id="total_price_display">₹ 0.00</div>
        </div>
        <button type="submit" class="btn btn-primary btn-full book-btn" id="submit_btn">Search Flight & Proceed to Payment</button>
    </form>
</div>

<footer><p>2026 <span>InteleTour</span></p></footer>
<script src="script.js"></script>
<script>
(() => {
    const serviceInput = document.getElementById('service_type');
    const tabs = document.querySelectorAll('.service-tab');
    const panels = {
        flight: document.getElementById('panel-flight'),
        train: document.getElementById('panel-train'),
        hotel: document.getElementById('panel-hotel'),
        cab: document.getElementById('panel-cab')
    };

    const totalDisplay = document.getElementById('total_price_display');
    const priceInput = document.getElementById('package_price');
    const submitBtn = document.getElementById('submit_btn');

    const labels = {
        flight: 'Search Flight & Proceed to Payment',
        train: 'Search Train & Proceed to Payment',
        hotel: 'Search Hotel & Proceed to Payment',
        cab: 'Search Cab & Proceed to Payment'
    };

    function selectedService() {
        const value = (serviceInput.value || 'flight').toLowerCase();
        return panels[value] ? value : 'flight';
    }

    function travelerCountFor(service) {
        if (service === 'flight') {
            const a = parseInt(document.getElementById('flight_adults')?.value || '1', 10);
            const c = parseInt(document.getElementById('flight_children')?.value || '0', 10);
            return Math.max(1, a + c);
        }
        if (service === 'train') {
            const a = parseInt(document.getElementById('train_adults')?.value || '1', 10);
            const c = parseInt(document.getElementById('train_children')?.value || '0', 10);
            return Math.max(1, a + c);
        }
        if (service === 'hotel') {
            const a = parseInt(document.getElementById('hotel_adults')?.value || '1', 10);
            const c = parseInt(document.getElementById('hotel_children')?.value || '0', 10);
            return Math.max(1, a + c);
        }
        return Math.max(1, parseInt(document.getElementById('cab_passengers')?.value || '1', 10));
    }

    function updateTotal() {
        const service = selectedService();
        const travelers = travelerCountFor(service);
        const price = parseFloat(priceInput?.value || '0');
        totalDisplay.textContent = '₹ ' + (travelers * price).toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function applyService(service) {
        serviceInput.value = service;

        tabs.forEach((tab) => {
            tab.classList.toggle('active', tab.dataset.service === service);
        });

        Object.entries(panels).forEach(([key, panel]) => {
            if (!panel) return;
            panel.classList.toggle('active', key === service);
        });

        submitBtn.textContent = labels[service] || labels.flight;
        updateTotal();
    }

    tabs.forEach((tab) => {
        tab.addEventListener('click', () => {
            applyService(tab.dataset.service || 'flight');
        });
    });

    ['flight_adults','flight_children','train_adults','train_children','hotel_adults','hotel_children','cab_passengers'].forEach((id) => {
        const el = document.getElementById(id);
        if (el) el.addEventListener('change', updateTotal);
    });

    applyService(selectedService());
})();
</script>
</body>
</html>
