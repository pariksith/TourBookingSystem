<?php
// ============================================================
//  INTELE TOUR – AI TRAVEL ASSISTANT
//  Full-featured: Chat, Session History, Trip Planner,
//  Budget Calculator, Destination Quiz, Weather Widget,
//  Popular Queries, Typing Simulation, Context Awareness
// ============================================================
session_start();
require_once 'database.php';

// ================================================================
//  SECTION 1 — CHAT HISTORY TABLE (auto-create if not exists)
// ================================================================
$conn->query("
    CREATE TABLE IF NOT EXISTS chat_history (
        id           INT AUTO_INCREMENT PRIMARY KEY,
        session_key  VARCHAR(100) NOT NULL,
        user_id      INT DEFAULT NULL,
        sender       ENUM('user','bot') NOT NULL,
        message      TEXT NOT NULL,
        intent       VARCHAR(80) DEFAULT NULL,
        created_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_session (session_key),
        INDEX idx_user    (user_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
");

// ================================================================
//  SECTION 2 — SESSION KEY MANAGEMENT
// ================================================================
if (empty($_SESSION['chat_session_key'])) {
    $_SESSION['chat_session_key'] = 'chat_' . uniqid('', true);
}
$chat_key = $_SESSION['chat_session_key'];
$uid      = $_SESSION['user_id'] ?? null;

// ================================================================
//  SECTION 3 — KNOWLEDGE BASE (Packages, Destinations, FAQs)
// ================================================================
$packages_kb = [
    'paris'     => [
        'name'      => 'Paris, France',
        'emoji'     => '🗼',
        'price'     => 85000,
        'duration'  => '7 Days / 6 Nights',
        'id'        => 1,
        'region'    => 'Europe',
        'best_for'  => 'Romance, Culture, Art',
        'best_time' => 'April – June, September – November',
        'highlights'=> ['Eiffel Tower','Louvre Museum','Seine River Cruise','Versailles Palace','Montmartre'],
        'tips'      => 'Book Eiffel Tower tickets in advance. Best croissants at Du Pain et des Idées.',
        'climate'   => 'Mild summers (20°C), cold winters (5°C)',
        'currency'  => 'Euro (€)',
        'visa'      => 'Schengen Visa required for Indian passport holders',
    ],
    'tokyo'     => [
        'name'      => 'Tokyo, Japan',
        'emoji'     => '🏯',
        'price'     => 95000,
        'duration'  => '8 Days / 7 Nights',
        'id'        => 2,
        'region'    => 'Asia',
        'best_for'  => 'Adventure, Technology, Culture',
        'best_time' => 'March – May (Cherry Blossom), October – November',
        'highlights'=> ['Mount Fuji','Shibuya Crossing','Senso-ji Temple','Akihabara','Shinjuku'],
        'tips'      => 'Get an IC card (Suica) for easy transit. Try ramen at Ichiran.',
        'climate'   => 'Hot summers (30°C), cold winters (5°C)',
        'currency'  => 'Japanese Yen (¥)',
        'visa'      => 'Japan Visa required for Indian passport holders',
    ],
    'bali'      => [
        'name'      => 'Bali, Indonesia',
        'emoji'     => '🏝️',
        'price'     => 65000,
        'duration'  => '6 Days / 5 Nights',
        'id'        => 3,
        'region'    => 'Asia',
        'best_for'  => 'Beach, Wellness, Culture',
        'best_time' => 'April – October (Dry Season)',
        'highlights'=> ['Tanah Lot Temple','Ubud Rice Terraces','Seminyak Beach','Kecak Dance','Mount Batur'],
        'tips'      => 'Rent a scooter in Ubud. Bargain at Kuta Market.',
        'climate'   => 'Tropical (28°C average). Rainy Nov–Mar.',
        'currency'  => 'Indonesian Rupiah (IDR)',
        'visa'      => 'Visa on arrival for Indian passport holders (30 days)',
    ],
    'dubai'     => [
        'name'      => 'Dubai, UAE',
        'emoji'     => '🏙️',
        'price'     => 75000,
        'duration'  => '5 Days / 4 Nights',
        'id'        => 4,
        'region'    => 'Middle East',
        'best_for'  => 'Luxury, Shopping, Adventure',
        'best_time' => 'November – March',
        'highlights'=> ['Burj Khalifa','Desert Safari','Dubai Mall','Palm Jumeirah','Dubai Frame'],
        'tips'      => 'Dress modestly in older areas. Book desert safari in advance.',
        'climate'   => 'Very hot summers (45°C), pleasant winters (22°C)',
        'currency'  => 'UAE Dirham (AED)',
        'visa'      => 'Visa on arrival for Indian passport holders (30 days)',
    ],
    'maldives'  => [
        'name'      => 'Maldives',
        'emoji'     => '🌊',
        'price'     => 145000,
        'duration'  => '6 Days / 5 Nights',
        'id'        => 103,
        'region'    => 'Asia',
        'best_for'  => 'Luxury, Diving, Honeymoon',
        'best_time' => 'November – April',
        'highlights'=> ['Overwater Bungalows','Snorkeling','Coral Reefs','Dolphin Cruise','Sunset Fishing'],
        'tips'      => 'Book overwater villa early. Bring reef-safe sunscreen.',
        'climate'   => 'Tropical (29°C). Wet season May–October.',
        'currency'  => 'Maldivian Rufiyaa (MVR)',
        'visa'      => 'Free visa on arrival for all nationalities (30 days)',
    ],
    'singapore' => [
        'name'      => 'Singapore',
        'emoji'     => '🌆',
        'price'     => 72000,
        'duration'  => '5 Days / 4 Nights',
        'id'        => 101,
        'region'    => 'Asia',
        'best_for'  => 'Family, Food, City Life',
        'best_time' => 'February – April',
        'highlights'=> ['Marina Bay Sands','Gardens by the Bay','Sentosa Island','Universal Studios','Hawker Centers'],
        'tips'      => 'Use MRT for getting around. Try Chicken Rice at Tian Tian.',
        'climate'   => 'Tropical (30°C all year). Rain anytime.',
        'currency'  => 'Singapore Dollar (SGD)',
        'visa'      => 'Visa required for Indian passport holders',
    ],
    'london'    => [
        'name'      => 'London, UK',
        'emoji'     => '🎡',
        'price'     => 98000,
        'duration'  => '7 Days / 6 Nights',
        'id'        => 104,
        'region'    => 'Europe',
        'best_for'  => 'History, Culture, Shopping',
        'best_time' => 'June – August',
        'highlights'=> ['Big Ben','Buckingham Palace','Tower of London','British Museum','Hyde Park'],
        'tips'      => 'Get Oyster card for tube travel. Visit free museums.',
        'climate'   => 'Mild (18°C summer), cold and rainy winters',
        'currency'  => 'British Pound (£)',
        'visa'      => 'UK Visa required for Indian passport holders',
    ],
    'rome'      => [
        'name'      => 'Rome, Italy',
        'emoji'     => '🏛️',
        'price'     => 88000,
        'duration'  => '7 Days / 6 Nights',
        'id'        => 107,
        'region'    => 'Europe',
        'best_for'  => 'History, Food, Art',
        'best_time' => 'April – June, September – October',
        'highlights'=> ['Colosseum','Vatican City','Trevi Fountain','Roman Forum','Borghese Gallery'],
        'tips'      => 'Book Vatican tour in advance. Try authentic cacio e pepe.',
        'climate'   => 'Hot summers (32°C), mild winters (10°C)',
        'currency'  => 'Euro (€)',
        'visa'      => 'Schengen Visa required for Indian passport holders',
    ],
    'phuket'    => [
        'name'      => 'Phuket, Thailand',
        'emoji'     => '⛵',
        'price'     => 55000,
        'duration'  => '6 Days / 5 Nights',
        'id'        => 106,
        'region'    => 'Asia',
        'best_for'  => 'Beach, Party, Budget Travel',
        'best_time' => 'November – April',
        'highlights'=> ['Phi Phi Islands','Patong Beach','Big Buddha','Elephant Sanctuary','Old Town'],
        'tips'      => 'Haggle at Chatuchak-style markets. Try Pad Thai on the street.',
        'climate'   => 'Tropical (33°C). Monsoon June–October.',
        'currency'  => 'Thai Baht (THB)',
        'visa'      => 'Visa on arrival for Indian passport holders (30 days)',
    ],
    'newyork'   => [
        'name'      => 'New York, USA',
        'emoji'     => '🗽',
        'price'     => 125000,
        'duration'  => '8 Days / 7 Nights',
        'id'        => 105,
        'region'    => 'Americas',
        'best_for'  => 'City Life, Entertainment, Shopping',
        'best_time' => 'April – June, September – November',
        'highlights'=> ['Times Square','Central Park','Statue of Liberty','Brooklyn Bridge','Broadway'],
        'tips'      => 'Buy NYC Pass for attractions. Walk the Brooklyn Bridge at sunset.',
        'climate'   => 'Hot summers (28°C), cold snowy winters (-2°C)',
        'currency'  => 'US Dollar ($)',
        'visa'      => 'B1/B2 Visa required for Indian passport holders',
    ],
    'sydney'    => [
        'name'      => 'Sydney, Australia',
        'emoji'     => '🦘',
        'price'     => 115000,
        'duration'  => '8 Days / 7 Nights',
        'id'        => 108,
        'region'    => 'Oceania',
        'best_for'  => 'Adventure, Nature, Beach',
        'best_time' => 'September – November, March – May',
        'highlights'=> ['Opera House','Harbour Bridge','Bondi Beach','Blue Mountains','Wildlife Park'],
        'tips'      => 'Opal card for trains. Try Tim Tams! Sunscreen essential.',
        'climate'   => 'Warm summers (26°C), mild winters (14°C)',
        'currency'  => 'Australian Dollar (AUD)',
        'visa'      => 'ETA Visa required for Indian passport holders',
    ],
    'santorini' => [
        'name'      => 'Santorini, Greece',
        'emoji'     => '🏺',
        'price'     => 110000,
        'duration'  => '7 Days / 6 Nights',
        'id'        => 102,
        'region'    => 'Europe',
        'best_for'  => 'Romance, Photography, Wine',
        'best_time' => 'May – October',
        'highlights'=> ['Oia Sunset','Caldera Views','Volcanic Beach','Wine Tasting','Akrotiri Ruins'],
        'tips'      => 'Stay in Oia for the best sunset views. Book accommodation early.',
        'climate'   => 'Warm summers (28°C), mild winters (12°C)',
        'currency'  => 'Euro (€)',
        'visa'      => 'Schengen Visa required for Indian passport holders',
    ],
];

// ================================================================
//  SECTION 4 — INTENT DETECTION ENGINE
// ================================================================
function detectIntent(string $msg): string {
    $msg = strtolower($msg);

    $intents = [
        'greeting'     => ['hi','hello','hey','good morning','good evening','good afternoon','howdy','namaste','hola','what\'s up','sup'],
        'farewell'     => ['bye','goodbye','see you','take care','thanks bye','ciao','au revoir'],
        'thanks'       => ['thank','thanks','thank you','thx','appreciate','awesome','great','perfect','wonderful','excellent','brilliant'],
        'booking'      => ['book','reserve','booking','how to book','want to book','buy package','purchase','confirm','reservation'],
        'payment'      => ['pay','payment','upi','gpay','credit card','debit card','net banking','cost','how much','price','charge','fee'],
        'cancel'       => ['cancel','cancellation','refund','money back','reschedule','change booking'],
        'recommend'    => ['recommend','suggest','best','which','where should','good place','where to go','help me choose','advice','advise','which package'],
        'paris'        => ['paris','france','eiffel','louvre','seine','versailles','montmartre'],
        'tokyo'        => ['tokyo','japan','fuji','shibuya','kyoto','osaka','anime','sushi','ramen'],
        'bali'         => ['bali','indonesia','ubud','seminyak','kuta','tanah lot','uluwatu','rice terrace'],
        'dubai'        => ['dubai','uae','burj','desert safari','palm','creek','emirates'],
        'maldives'     => ['maldives','overwater','atoll','coral','snorkel','dive','island resort'],
        'singapore'    => ['singapore','marina bay','sentosa','gardens by the bay','hawker','lion city'],
        'london'       => ['london','uk','england','big ben','buckingham','thames','tube','underground'],
        'rome'         => ['rome','italy','colosseum','vatican','trevi','pizza','pasta','roman'],
        'phuket'       => ['phuket','thailand','phi phi','patong','thai','bangkok','chiang mai'],
        'newyork'      => ['new york','nyc','manhattan','times square','broadway','central park','statue of liberty'],
        'sydney'       => ['sydney','australia','opera house','bondi','harbour bridge','kangaroo','koala'],
        'santorini'    => ['santorini','greece','oia','aegean','caldera','cyclades'],
        'budget'       => ['budget','cheap','affordable','low cost','economical','value','best deal','inexpensive','less money'],
        'luxury'       => ['luxury','premium','5 star','five star','lavish','high end','expensive','splurge'],
        'visa'         => ['visa','passport','travel document','entry','immigration','permit','customs'],
        'weather'      => ['weather','climate','temperature','when to visit','best time','season','monsoon','rain','cold','hot'],
        'packing'      => ['pack','packing','luggage','what to bring','clothes','carry','bag','essentials'],
        'food'         => ['food','eat','restaurant','cuisine','local food','dish','meal','street food','must try'],
        'family'       => ['family','kids','children','parents','grandparents','family trip','kid friendly'],
        'honeymoon'    => ['honeymoon','couple','romantic','anniversary','wedding trip','romance'],
        'adventure'    => ['adventure','trek','hiking','scuba','diving','extreme','outdoor','sports','bungee','rafting'],
        'solo'         => ['solo','alone','single traveler','by myself','solo trip','travelling alone'],
        'group'        => ['group','team','friends','gang','crew','large group','corporate','batch'],
        'help'         => ['help','support','contact','customer service','complaint','issue','problem','query'],
        'packages_list'=> ['show packages','all packages','list packages','available packages','what packages','see all'],
        'compare'      => ['compare','difference','vs','versus','better','which is better'],
        'trip_planner' => ['plan','itinerary','schedule','trip plan','day by day','day 1','plan my trip'],
        'quiz'         => ['quiz','suggest me','not sure','can\'t decide','help me pick','surprise me'],
        'calculator'   => ['calculator','calculate','total cost','how much will','estimate','total price'],
    ];

    foreach ($intents as $intent => $keywords) {
        foreach ($keywords as $kw) {
            if (strpos($msg, $kw) !== false) return $intent;
        }
    }
    return 'general';
}

// ================================================================
//  SECTION 5 — AI RESPONSE ENGINE
// ================================================================
function generateBotResponse(string $msg, array $packages_kb, $conn, ?int $uid, string $chat_key): array {
    $intent = detectIntent($msg);
    $reply  = '';
    $extras = [];

    switch ($intent) {

        // ---- GREETINGS ----
        case 'greeting':
            $time = date('H');
            $greet = $time < 12 ? 'Good morning' : ($time < 17 ? 'Good afternoon' : 'Good evening');
            $reply = "👋 <strong>$greet!</strong> Welcome to <strong>InteleTour AI Assistant</strong>! 🌍<br><br>
                I'm your personal travel guide powered by AI. I can help you:<br>
                ✈️ Discover dream destinations<br>
                📦 Find the perfect travel package<br>
                💰 Calculate your trip budget<br>
                📋 Plan a day-by-day itinerary<br>
                🌤️ Check best travel seasons<br>
                🎯 Take a destination quiz<br><br>
                <em>What kind of adventure are you dreaming of today?</em> 😊";
            break;

        // ---- FAREWELL ----
        case 'farewell':
            $reply = "👋 <strong>Safe travels!</strong> It was wonderful helping you plan your trip!<br><br>
                Whenever you're ready to book or need more travel inspiration, I'm here 24/7. <br>
                Have an amazing journey! ✈️🌏<br><br>
                <em>Don't forget to check out our <a href='packages.php'>latest packages</a> before you go!</em>";
            break;

        // ---- THANKS ----
        case 'thanks':
            $replies = [
                "😊 You're so welcome! Is there anything else I can help you explore? ✈️",
                "🙏 Happy to help! Your dream trip is just a few clicks away. Need anything else?",
                "🌟 Glad I could assist! Feel free to ask me anything about your travel plans!",
                "✈️ Anytime! That's what I'm here for. Ready to book your adventure? 😊"
            ];
            $reply = $replies[array_rand($replies)];
            break;

        // ---- BOOKING ----
        case 'booking':
            $reply = "📋 <strong>How to Book Your Dream Trip in 4 Steps:</strong><br><br>
                <strong>Step 1 – Browse Packages</strong><br>
                👉 Visit our <a href='packages.php'>Packages Page</a> and explore all destinations.<br><br>
                <strong>Step 2 – Select & Click 'Book Now'</strong><br>
                👉 Choose your preferred package and click the Book Now button.<br><br>
                <strong>Step 3 – Fill Travel Details</strong><br>
                👉 Enter your travel date and number of travelers. Total price auto-calculates.<br><br>
                <strong>Step 4 – Complete Payment</strong><br>
                👉 Pay securely via Credit Card, Debit Card, UPI, or Net Banking.<br><br>
                ✅ You'll receive an instant <strong>Transaction ID</strong> and booking confirmation!<br><br>
                <em>Need help with a specific destination? Just ask! 😊</em>";
            break;

        // ---- PAYMENT ----
        case 'payment':
            $reply = "💳 <strong>Payment Methods We Accept:</strong><br><br>
                💳 <strong>Credit Card</strong> – Visa, Mastercard, Amex<br>
                🏧 <strong>Debit Card</strong> – All major Indian banks<br>
                📱 <strong>UPI</strong> – GPay, PhonePe, Paytm, BHIM<br>
                🏦 <strong>Net Banking</strong> – HDFC, SBI, ICICI, Axis, and 50+ banks<br><br>
                🔒 <strong>Security Promise:</strong><br>
                • SSL-encrypted transactions<br>
                • PCI-DSS compliant payment gateway<br>
                • Instant confirmation after payment<br>
                • Unique Transaction ID for every booking<br><br>
                💡 <em>Tip: UPI payments are fastest for confirmation!</em>";
            break;

        // ---- CANCELLATION ----
        case 'cancel':
            $reply = "🔄 <strong>Cancellation & Refund Policy:</strong><br><br>
                📅 <strong>More than 30 days before travel:</strong><br>
                → Full refund minus 5% processing fee<br><br>
                📅 <strong>15–30 days before travel:</strong><br>
                → 75% refund of total amount paid<br><br>
                📅 <strong>7–14 days before travel:</strong><br>
                → 50% refund of total amount paid<br><br>
                📅 <strong>Less than 7 days:</strong><br>
                → No refund (non-refundable)<br><br>
                📋 <strong>How to Cancel:</strong><br>
                → Go to <a href='my_bookings.php'>My Bookings</a> → Select Booking → Cancel<br><br>
                💬 <em>Need help with a cancellation? Contact us at support@inteletour.com</em>";
            break;

        // ---- RECOMMEND ----
        case 'recommend':
            $reply = "🌟 <strong>My Top Destination Recommendations:</strong><br><br>
                🥇 <strong>Best Value</strong> → 🏝️ <a href='booking.php?id=3'>Bali</a> (₹65,000) – Beach, culture, temples<br>
                🥈 <strong>Best for Romance</strong> → 🗼 <a href='booking.php?id=1'>Paris</a> (₹85,000) – The city of love<br>
                🥉 <strong>Best for Luxury</strong> → 🌊 <a href='packages.php'>Maldives</a> (₹1,45,000) – Overwater paradise<br>
                🏅 <strong>Best for Adventure</strong> → 🏯 <a href='booking.php?id=2'>Tokyo</a> (₹95,000) – Fuji & culture<br>
                🎖️ <strong>Best for Families</strong> → 🌆 <a href='packages.php'>Singapore</a> (₹72,000) – Universal Studios<br>
                ⭐ <strong>Best for Shopping</strong> → 🏙️ <a href='booking.php?id=4'>Dubai</a> (₹75,000) – World-class malls<br><br>
                🎯 <em>Want a personalised pick? Take our <strong>Destination Quiz</strong> below!</em>";
            $extras['show_quiz_btn'] = true;
            break;

        // ---- DESTINATION SPECIFIC ----
        case 'paris':
        case 'tokyo':
        case 'bali':
        case 'dubai':
        case 'maldives':
        case 'singapore':
        case 'london':
        case 'rome':
        case 'phuket':
        case 'newyork':
        case 'sydney':
        case 'santorini':
            $dest = $packages_kb[$intent];
            $highlights = implode(', ', $dest['highlights']);
            $tips_text  = $dest['tips'];
            $reply = "{$dest['emoji']} <strong>{$dest['name']}</strong><br><br>
                📦 <strong>Package:</strong> {$dest['duration']} starting from <strong>₹" . number_format($dest['price']) . "</strong> per person<br>
                🌍 <strong>Region:</strong> {$dest['region']}<br>
                💡 <strong>Best For:</strong> {$dest['best_for']}<br>
                📅 <strong>Best Time to Visit:</strong> {$dest['best_time']}<br>
                🌤️ <strong>Climate:</strong> {$dest['climate']}<br>
                💵 <strong>Currency:</strong> {$dest['currency']}<br>
                🛂 <strong>Visa:</strong> {$dest['visa']}<br><br>
                🗺️ <strong>Top Highlights:</strong><br>
                " . implode('<br>', array_map(fn($h) => "• $h", $dest['highlights'])) . "<br><br>
                💡 <strong>Travel Tip:</strong> {$tips_text}<br><br>
                " . ($dest['id'] < 200 ? "<a href='booking.php?id={$dest['id']}' class='chat-book-btn'>📅 Book {$dest['name']} Now →</a>" : "<a href='packages.php' class='chat-book-btn'>📅 View Package →</a>");
            break;

        // ---- BUDGET ----
        case 'budget':
            $reply = "💰 <strong>Best Budget-Friendly Packages:</strong><br><br>
                🏝️ <strong>Bali, Indonesia</strong> – ₹65,000 / person (6D/5N)<br>
                &nbsp;&nbsp;&nbsp;→ Rice terraces, temples, beaches<br><br>
                ⛵ <strong>Phuket, Thailand</strong> – ₹55,000 / person (6D/5N)<br>
                &nbsp;&nbsp;&nbsp;→ Phi Phi Islands, Patong Beach, nightlife<br><br>
                🌆 <strong>Singapore</strong> – ₹72,000 / person (5D/4N)<br>
                &nbsp;&nbsp;&nbsp;→ Marina Bay, Gardens, Universal Studios<br><br>
                🏙️ <strong>Dubai</strong> – ₹75,000 / person (5D/4N)<br>
                &nbsp;&nbsp;&nbsp;→ Desert Safari, Burj Khalifa, shopping<br><br>
                💡 <em>All prices are per person, inclusive of hotel, transfers, and guided tours. No hidden charges!</em><br><br>
                Want me to <strong>calculate the total cost</strong> for your group? Just tell me the number of travelers! 😊";
            break;

        // ---- LUXURY ----
        case 'luxury':
            $reply = "👑 <strong>Our Premium Luxury Packages:</strong><br><br>
                🌊 <strong>Maldives</strong> – ₹1,45,000 / person (6D/5N)<br>
                &nbsp;&nbsp;&nbsp;→ Overwater villa, seaplane transfer, all-inclusive<br><br>
                🗽 <strong>New York</strong> – ₹1,25,000 / person (8D/7N)<br>
                &nbsp;&nbsp;&nbsp;→ 5-star hotel, Broadway show, helicopter tour<br><br>
                🦘 <strong>Sydney</strong> – ₹1,15,000 / person (8D/7N)<br>
                &nbsp;&nbsp;&nbsp;→ Harbour view hotel, private tours<br><br>
                🏺 <strong>Santorini</strong> – ₹1,10,000 / person (7D/6N)<br>
                &nbsp;&nbsp;&nbsp;→ Clifftop villa, sunset cruise, wine tour<br><br>
                🎡 <strong>London</strong> – ₹98,000 / person (7D/6N)<br>
                &nbsp;&nbsp;&nbsp;→ West End show, Thames cruise, tea ceremony<br><br>
                ✨ <em>Premium packages include 5-star stays, private transfers, and personal tour guides.</em>";
            break;

        // ---- VISA ----
        case 'visa':
            $reply = "🛂 <strong>Visa Information for Indian Travelers:</strong><br><br>
                ✅ <strong>Visa on Arrival (Easy Entry):</strong><br>
                • 🏝️ Bali, Indonesia – 30 days<br>
                • 🏙️ Dubai, UAE – 30 days<br>
                • ⛵ Phuket, Thailand – 30 days<br>
                • 🌊 Maldives – All nationalities free<br><br>
                📋 <strong>Visa Required (Apply in Advance):</strong><br>
                • 🗼 Paris – Schengen Visa (15–30 days processing)<br>
                • 🏛️ Rome – Schengen Visa<br>
                • 🏺 Santorini – Schengen Visa<br>
                • 🎡 London – UK Visa<br>
                • 🗽 New York – B1/B2 US Visa<br>
                • 🏯 Tokyo – Japan Visa<br>
                • 🌆 Singapore – Singapore Visa<br>
                • 🦘 Sydney – ETA Visa (online)<br><br>
                📞 <em>Our team can assist with visa guidance. Contact support@inteletour.com</em>";
            break;

        // ---- WEATHER / BEST TIME ----
        case 'weather':
            $reply = "🌤️ <strong>Best Time to Visit – Destination Guide:</strong><br><br>
                🗼 <strong>Paris</strong> – April–June &amp; Sep–Nov (Pleasant, ~20°C)<br>
                🏯 <strong>Tokyo</strong> – March–May (Cherry Blossom!) &amp; Oct–Nov<br>
                🏝️ <strong>Bali</strong> – April–October (Dry season, sunny beaches)<br>
                🏙️ <strong>Dubai</strong> – November–March (Best weather, ~22°C)<br>
                🌊 <strong>Maldives</strong> – November–April (Crystal clear water)<br>
                🌆 <strong>Singapore</strong> – February–April (Driest months)<br>
                🎡 <strong>London</strong> – June–August (Longest days, 18°C)<br>
                🏛️ <strong>Rome</strong> – April–June &amp; Sep–Oct (Best sightseeing)<br>
                ⛵ <strong>Phuket</strong> – November–April (No monsoon)<br>
                🗽 <strong>New York</strong> – April–June &amp; Sep–Nov (Perfect temp)<br>
                🦘 <strong>Sydney</strong> – September–November (Spring, ~24°C)<br>
                🏺 <strong>Santorini</strong> – May–October (Warm, scenic)<br><br>
                ❓ <em>Ask me about a specific destination for detailed weather info!</em>";
            break;

        // ---- PACKING ----
        case 'packing':
            $reply = "🎒 <strong>Universal Travel Packing Checklist:</strong><br><br>
                📄 <strong>Documents:</strong><br>
                • Passport + Visa + Flight tickets<br>
                • Hotel vouchers + Travel insurance<br>
                • Emergency contacts + InteleTour booking ID<br><br>
                👕 <strong>Clothing (based on destination):</strong><br>
                • Light cottons for Bali, Dubai, Singapore, Phuket<br>
                • Layers and jackets for Paris, London, Tokyo<br>
                • Modest clothing for religious sites<br>
                • Swimwear for Maldives, Bali, Phuket<br><br>
                💊 <strong>Health & Safety:</strong><br>
                • Basic medicines + prescription drugs<br>
                • Sunscreen (SPF 50+) for tropical destinations<br>
                • Hand sanitizer, face masks, insect repellent<br><br>
                🔌 <strong>Tech Essentials:</strong><br>
                • Universal power adapter<br>
                • Power bank (10,000 mAh+)<br>
                • Offline maps downloaded<br>
                • InteleTour app bookmarked<br><br>
                💡 <em>Need a destination-specific packing list? Just ask!</em>";
            break;

        // ---- FOOD ----
        case 'food':
            $reply = "🍽️ <strong>Must-Try Foods by Destination:</strong><br><br>
                🗼 <strong>Paris:</strong> Croissants, Crêpes, French Onion Soup, Escargot<br>
                🏯 <strong>Tokyo:</strong> Ramen, Sushi, Tempura, Takoyaki, Matcha desserts<br>
                🏝️ <strong>Bali:</strong> Nasi Goreng, Babi Guling, Mie Goreng, Satay<br>
                🏙️ <strong>Dubai:</strong> Shawarma, Hummus, Machboos, Camel Burger<br>
                🌆 <strong>Singapore:</strong> Chicken Rice, Laksa, Char Kway Teow, Chilli Crab<br>
                ⛵ <strong>Phuket:</strong> Pad Thai, Tom Yum, Mango Sticky Rice, Green Curry<br>
                🏛️ <strong>Rome:</strong> Cacio e Pepe, Gelato, Tiramisu, Pizza Margherita<br>
                🎡 <strong>London:</strong> Fish &amp; Chips, Full English, Afternoon Tea, Pie<br>
                🗽 <strong>New York:</strong> NY Pizza, Bagels, Hot Dogs, Cheesecake<br><br>
                🌟 <em>Pro Tip: Always explore local street food — it's authentic and affordable!</em>";
            break;

        // ---- FAMILY ----
        case 'family':
            $reply = "👨‍👩‍👧‍👦 <strong>Top Family-Friendly Destinations:</strong><br><br>
                🥇 <strong>Singapore</strong> (₹72,000/person)<br>
                → Universal Studios, SEA Aquarium, Night Safari, cable car rides<br><br>
                🥈 <strong>Bali</strong> (₹65,000/person)<br>
                → Water parks, Elephant Riding, cultural shows, safe beaches<br><br>
                🥉 <strong>Dubai</strong> (₹75,000/person)<br>
                → Legoland, Dubai Aquarium, IMG Worlds of Adventure, waterparks<br><br>
                🏅 <strong>Phuket</strong> (₹55,000/person)<br>
                → Elephant sanctuary, cooking class, calm beaches, island boat trips<br><br>
                💡 <strong>Family Travel Tips:</strong><br>
                • Book non-stop flights for kids<br>
                • Keep an extra day unplanned for rest<br>
                • Pack medical kit &amp; kids' snacks<br>
                • Request connecting rooms when booking<br><br>
                📞 <em>Contact us to customise a family package within your budget!</em>";
            break;

        // ---- HONEYMOON ----
        case 'honeymoon':
            $reply = "💑 <strong>Most Romantic Destinations for Couples:</strong><br><br>
                🥇 <strong>Maldives</strong> – The Ultimate Honeymoon Destination<br>
                → Overwater bungalows, private beach dinners, snorkeling, sunset cruises<br>
                💰 From ₹1,45,000/person<br><br>
                🥈 <strong>Santorini, Greece</strong> – Europe's Most Romantic Island<br>
                → White-washed villas, caldera sunsets, wine tasting, volcanic beaches<br>
                💰 From ₹1,10,000/person<br><br>
                🥉 <strong>Paris, France</strong> – The City of Love<br>
                → Eiffel Tower by night, Seine dinner cruise, wine &amp; cheese, art<br>
                💰 From ₹85,000/person<br><br>
                🏅 <strong>Bali, Indonesia</strong> – Tropical Romance<br>
                → Rice terrace walks, spa retreats, sunset at Tanah Lot, villas<br>
                💰 From ₹65,000/person<br><br>
                💝 <em>We offer special honeymoon add-ons: rose petals, private dinners, couples spa!</em>";
            break;

        // ---- ADVENTURE ----
        case 'adventure':
            $reply = "🧗 <strong>Top Adventure Destinations:</strong><br><br>
                🏯 <strong>Tokyo/Japan</strong> – Mount Fuji hike, skiing in Hokkaido, zip-lining<br>
                🏝️ <strong>Bali</strong> – Mount Batur sunrise trek, white water rafting, surfing<br>
                ⛵ <strong>Phuket</strong> – Scuba diving, rock climbing, ATV rides, deep sea fishing<br>
                🦘 <strong>Sydney</strong> – BridgeClimb, shark diving, Blue Mountains hiking<br>
                🌊 <strong>Maldives</strong> – Scuba diving, parasailing, jet-ski, submarine tour<br>
                🗽 <strong>New York</strong> – Skydiving over Catskills, kayaking, urban exploration<br><br>
                🎯 <strong>Adventure Add-Ons Available:</strong><br>
                • Scuba diving certification courses<br>
                • Guided mountain treks<br>
                • Paragliding experiences<br>
                • Safari day trips<br><br>
                ⚡ <em>Adventure packages can be customised — ask us for details!</em>";
            break;

        // ---- SOLO ----
        case 'solo':
            $reply = "🧳 <strong>Best Destinations for Solo Travelers:</strong><br><br>
                🥇 <strong>Bali</strong> – Super safe, budget-friendly, vibrant hostel scene, yoga retreats<br>
                🥈 <strong>Singapore</strong> – Extremely safe, English-speaking, easy to navigate alone<br>
                🥉 <strong>Tokyo</strong> – Safe, efficient transport, amazing solo food spots<br>
                🏅 <strong>Phuket</strong> – Fun nightlife, easy island hopping, social hostels<br>
                ⭐ <strong>London</strong> – English-speaking, excellent public transport, solo-friendly<br><br>
                🛡️ <strong>Solo Travel Safety Tips:</strong><br>
                • Share your itinerary with family<br>
                • Register at local embassy portal<br>
                • Keep digital copies of all documents<br>
                • Buy comprehensive travel insurance<br>
                • Download offline maps before going<br>
                • Stay in well-reviewed hotels/hostels<br><br>
                💪 <em>Solo travel is the most transformative experience. Go for it! ✈️</em>";
            break;

        // ---- GROUP ----
        case 'group':
            $reply = "👥 <strong>Planning a Group Trip?</strong><br><br>
                We offer special <strong>group discounts</strong> for 10+ travelers!<br><br>
                🎯 <strong>Best Group Destinations:</strong><br>
                • 🏝️ Bali – Budget-friendly, fun activities for everyone<br>
                • 🏙️ Dubai – Luxury group experience, desert safari, BBQ<br>
                • 🌆 Singapore – Theme parks, city tours, diverse food<br>
                • ⛵ Phuket – Island parties, beach activities, affordable<br><br>
                💰 <strong>Group Discounts:</strong><br>
                • 10–15 people → 8% off total price<br>
                • 16–25 people → 12% off total price<br>
                • 26+ people → 15% off + free group coordinator<br><br>
                🎁 <strong>Group Add-Ons:</strong><br>
                • Dedicated tour manager<br>
                • Private coach transfers<br>
                • Group meals & team activities<br><br>
                📞 <em>Call +91 98765 43210 for custom group quotes!</em>";
            break;

        // ---- PACKAGES LIST ----
        case 'packages_list':
            $reply = "📦 <strong>All Available InteleTour Packages:</strong><br><br>";
            $i = 1;
            foreach ($packages_kb as $key => $p) {
                $reply .= "{$p['emoji']} <strong>{$p['name']}</strong> – ₹" . number_format($p['price']) . " | {$p['duration']}<br>";
                $i++;
            }
            $reply .= "<br>👉 <a href='packages.php'>View All Packages with Details →</a><br><br>
                <em>Which destination would you like to know more about? 😊</em>";
            break;

        // ---- COMPARE ----
        case 'compare':
            $reply = "⚖️ <strong>Quick Package Comparison:</strong><br><br>
                <table style='width:100%;font-size:12px;border-collapse:collapse;'>
                <tr style='background:var(--primary);color:white;'>
                    <th style='padding:7px;text-align:left;'>Destination</th>
                    <th style='padding:7px;'>Price</th>
                    <th style='padding:7px;'>Duration</th>
                    <th style='padding:7px;'>Best For</th>
                </tr>";
            $alt = false;
            foreach ($packages_kb as $p) {
                $bg = $alt ? '#f8f9fa' : 'white';
                $reply .= "<tr style='background:{$bg};'>
                    <td style='padding:7px;'>{$p['emoji']} {$p['name']}</td>
                    <td style='padding:7px;text-align:center;font-weight:700;color:var(--accent);'>₹" . number_format($p['price']) . "</td>
                    <td style='padding:7px;text-align:center;'>{$p['duration']}</td>
                    <td style='padding:7px;'>{$p['best_for']}</td>
                </tr>";
                $alt = !$alt;
            }
            $reply .= "</table><br>
                💡 <em>Ask me about any specific destination for detailed info!</em>";
            break;

        // ---- TRIP PLANNER ----
        case 'trip_planner':
            // Check which destination from the message
            $dest_found = null;
            foreach ($packages_kb as $key => $p) {
                if (strpos(strtolower($msg), $key) !== false) {
                    $dest_found = $key;
                    break;
                }
            }
            if ($dest_found) {
                $p = $packages_kb[$dest_found];
                $reply = "{$p['emoji']} <strong>Sample Itinerary – {$p['name']}</strong><br><br>";
                $days = (int)explode(' ', $p['duration'])[0];
                for ($d = 1; $d <= min($days, count($p['highlights']) + 1); $d++) {
                    $highlight = $p['highlights'][$d - 1] ?? 'Leisure & shopping';
                    $reply .= "📅 <strong>Day $d:</strong> Explore {$highlight}<br>";
                }
                $reply .= "<br>💡 <em>This is a sample itinerary. We can customise it just for you!</em><br>
                    <a href='booking.php?id={$p['id']}' class='chat-book-btn'>Book {$p['name']} →</a>";
            } else {
                $reply = "📅 <strong>I'd love to plan your trip!</strong> Which destination are you planning for?<br><br>
                    Try asking: <em>'Plan a trip to Bali'</em> or <em>'Plan Tokyo itinerary'</em><br><br>
                    Available destinations: " . implode(', ', array_map(fn($p) => "{$p['emoji']} {$p['name']}", $packages_kb));
            }
            break;

        // ---- BUDGET CALCULATOR ----
        case 'calculator':
            // Try to extract number from message
            preg_match('/\d+/', $msg, $matches);
            $travelers = !empty($matches) ? (int)$matches[0] : null;
            if ($travelers && $travelers >= 1 && $travelers <= 50) {
                $reply = "🧮 <strong>Price Estimate for $travelers Traveler(s):</strong><br><br>";
                foreach ($packages_kb as $p) {
                    $total = $p['price'] * $travelers;
                    $reply .= "{$p['emoji']} <strong>{$p['name']}</strong> – ₹" . number_format($total) . " total<br>";
                }
                $reply .= "<br>💡 <em>Prices are per person x $travelers travelers. Includes hotel, transfer, and guided tours.</em>";
            } else {
                $reply = "🧮 <strong>Trip Budget Calculator</strong><br><br>
                    Tell me: <strong>How many travelers?</strong><br><br>
                    <em>Example: 'Calculate for 2 travelers' or 'Total cost for 4 people'</em><br><br>
                    I'll instantly show you the total cost for every destination! 💰";
            }
            break;

        // ---- DESTINATION QUIZ ----
        case 'quiz':
            $reply = "🎯 <strong>Destination Quiz – Let Me Find Your Perfect Trip!</strong><br><br>
                Answer these 3 quick questions and I'll match you with your ideal destination:<br><br>
                <strong>Q1: What's your travel style?</strong><br>
                Type: <em>a) Beach &amp; Relax  b) Adventure  c) Culture &amp; History  d) Luxury  e) City Life</em><br><br>
                <strong>Q2: What's your budget per person?</strong><br>
                Type: <em>a) Under ₹70K  b) ₹70K–₹1L  c) Above ₹1L</em><br><br>
                <strong>Q3: Who are you traveling with?</strong><br>
                Type: <em>a) Solo  b) Couple  c) Family  d) Friends</em><br><br>
                <em>🎲 Or just type 'Surprise me!' and I'll pick randomly!</em>";
            $extras['show_quiz'] = true;
            break;

        // ---- HELP / SUPPORT ----
        case 'help':
            $reply = "🎧 <strong>InteleTour Customer Support</strong><br><br>
                📧 <strong>Email:</strong> support@inteletour.com<br>
                📱 <strong>Phone:</strong> +91 98765 43210<br>
                🕐 <strong>Hours:</strong> Mon–Sat, 9 AM – 6 PM IST<br>
                💬 <strong>AI Chat:</strong> Available 24/7 (that's me! 🤖)<br><br>
                <strong>Quick Links:</strong><br>
                📦 <a href='packages.php'>Browse Packages</a><br>
                📋 <a href='my_bookings.php'>View My Bookings</a><br>
                📝 <a href='register.php'>Create Account</a><br>
                🔐 <a href='login.php'>Login</a><br><br>
                <em>What issue can I help resolve for you today? 😊</em>";
            break;

        // ---- GENERAL FALLBACK ----
        default:
            // Check if the message contains a destination name as partial match
            foreach ($packages_kb as $key => $p) {
                if (stripos($msg, $key) !== false || stripos($msg, explode(',', $p['name'])[0]) !== false) {
                    return generateBotResponse($key, $packages_kb, $conn, $uid, $chat_key);
                }
            }
            // Check for numbers (could be calculator query)
            if (preg_match('/\b([2-9]|[1-9][0-9])\s*(people|persons?|travelers?|pax|adults?)\b/i', $msg)) {
                return generateBotResponse("calculate $msg", $packages_kb, $conn, $uid, $chat_key);
            }
            $replies = [
                "🤔 Interesting question! Let me help you better — could you be more specific?<br><br>
                    Here's what I can help with:<br>
                    🌍 Destination info & recommendations<br>
                    📦 Package details & pricing<br>
                    📋 Booking & payment guidance<br>
                    🌤️ Best travel seasons<br>
                    🧮 Trip budget calculator<br>
                    🎯 Destination quiz<br><br>
                    <em>Try asking: 'Tell me about Bali' or 'Best budget destination'</em>",
                "💭 I'm not sure I understood that completely. Try rephrasing?<br><br>
                    Some things you can ask me:<br>
                    • <em>'What's the best time to visit Paris?'</em><br>
                    • <em>'Compare Bali and Dubai'</em><br>
                    • <em>'Plan a 5 day trip to Tokyo'</em><br>
                    • <em>'Calculate cost for 3 travelers'</em>",
                "🌍 I'm your travel expert! Ask me anything about our destinations, packages, visa requirements, packing tips, or local food recommendations.<br><br>
                    <em>Or take our quick Destination Quiz to find your perfect match! 🎯</em>"
            ];
            $reply = $replies[array_rand($replies)];
            break;
    }

    return [
        'reply'  => $reply,
        'intent' => $intent,
        'extras' => $extras,
    ];
}

// ================================================================
//  SECTION 6 — AJAX HANDLER (POST Request from JS)
// ================================================================
if (isset($_POST['ajax']) && $_POST['ajax'] === '1') {
    header('Content-Type: application/json; charset=utf-8');

    $user_msg = trim($_POST['message'] ?? '');
    if (empty($user_msg)) {
        echo json_encode(['reply' => 'Please type a message.', 'intent' => 'empty', 'extras' => []], JSON_UNESCAPED_UNICODE);
        exit();
    }

    try {
        $safe_msg = htmlspecialchars($user_msg, ENT_QUOTES, 'UTF-8');
        $result = generateBotResponse($user_msg, $packages_kb, $conn, $uid, $chat_key);

        $intent = (string)($result['intent'] ?? 'general');
        $reply = (string)($result['reply'] ?? "I can help with destinations, pricing, bookings, and travel planning. Tell me what you want to plan.");
        $extras = is_array($result['extras'] ?? null) ? $result['extras'] : [];

        // Save chat history if DB insert is possible; do not fail response on write errors.
        $senderUser = 'user';
        if ($stmt = $conn->prepare("INSERT INTO chat_history (session_key, user_id, sender, message, intent) VALUES (?,?,?,?,?)")) {
            $stmt->bind_param("sisss", $chat_key, $uid, $senderUser, $safe_msg, $intent);
            $stmt->execute();
            $stmt->close();
        }

        $senderBot = 'bot';
        if ($stmt2 = $conn->prepare("INSERT INTO chat_history (session_key, user_id, sender, message, intent) VALUES (?,?,?,?,?)")) {
            $stmt2->bind_param("sisss", $chat_key, $uid, $senderBot, $reply, $intent);
            $stmt2->execute();
            $stmt2->close();
        }

        echo json_encode([
            'reply'  => $reply,
            'intent' => $intent,
            'extras' => $extras,
        ], JSON_UNESCAPED_UNICODE);
        exit();
    } catch (Throwable $e) {
        error_log('AI assistant AJAX error: ' . $e->getMessage());
        echo json_encode([
            'reply' => "I am still here. Ask me about destinations, budget, visa, itinerary, bookings, or payments and I will help right away.",
            'intent' => 'general',
            'extras' => [],
        ], JSON_UNESCAPED_UNICODE);
        exit();
    }
}

// ================================================================
//  SECTION 7 — CLEAR CHAT HISTORY
// ================================================================
if (isset($_POST['clear_chat'])) {
    if ($clearStmt = $conn->prepare("DELETE FROM chat_history WHERE session_key = ?")) {
        $clearStmt->bind_param("s", $chat_key);
        $clearStmt->execute();
        $clearStmt->close();
    }
    $_SESSION['chat_session_key'] = 'chat_' . uniqid('', true);
    header('Location: ai_assistant.php');
    exit();
}

// ================================================================
//  SECTION 8 — LOAD PREVIOUS CHAT HISTORY (last 30 messages)
// ================================================================
$history_stmt = $conn->prepare("
    SELECT sender, message, created_at
    FROM chat_history
    WHERE session_key = ?
    ORDER BY created_at ASC
    LIMIT 30
");
$history_stmt->bind_param("s", $chat_key);
$history_stmt->execute();
$chat_history_result = $history_stmt->get_result();
$previous_chats = $chat_history_result->fetch_all(MYSQLI_ASSOC);

// ================================================================
//  SECTION 9 — STATS (messages count, session age)
// ================================================================
$msg_count = count($previous_chats);
$session_start_time = !empty($previous_chats) ? $previous_chats[0]['created_at'] : date('Y-m-d H:i:s');

// ================================================================
//  SECTION 10 — POPULAR QUICK TOPICS
// ================================================================
$quick_topics = [
    ['emoji' => '🌍', 'label' => 'Show all packages',     'msg' => 'Show all available packages'],
    ['emoji' => '🏝️', 'label' => 'Tell me about Bali',    'msg' => 'Tell me about Bali'],
    ['emoji' => '🗼', 'label' => 'Tell me about Paris',   'msg' => 'Tell me about Paris'],
    ['emoji' => '🏙️', 'label' => 'Tell me about Dubai',   'msg' => 'Tell me about Dubai'],
    ['emoji' => '💰', 'label' => 'Budget destinations',   'msg' => 'What are the best budget destinations?'],
    ['emoji' => '👑', 'label' => 'Luxury packages',       'msg' => 'Show me luxury travel packages'],
    ['emoji' => '💑', 'label' => 'Honeymoon ideas',       'msg' => 'Best honeymoon destinations'],
    ['emoji' => '👨‍👩‍👧', 'label' => 'Family trips',         'msg' => 'Best family-friendly destinations'],
    ['emoji' => '🧗', 'label' => 'Adventure travel',      'msg' => 'Best adventure travel destinations'],
    ['emoji' => '🌤️', 'label' => 'Best travel seasons',   'msg' => 'When is the best time to visit each destination?'],
    ['emoji' => '🛂', 'label' => 'Visa information',      'msg' => 'Tell me about visa requirements'],
    ['emoji' => '🎒', 'label' => 'Packing tips',          'msg' => 'What should I pack for my trip?'],
    ['emoji' => '🍽️', 'label' => 'Local food guide',      'msg' => 'What local food should I try?'],
    ['emoji' => '📋', 'label' => 'How to book',           'msg' => 'How do I book a package?'],
    ['emoji' => '💳', 'label' => 'Payment options',       'msg' => 'What payment methods do you accept?'],
    ['emoji' => '🔄', 'label' => 'Cancellation policy',   'msg' => 'What is the cancellation policy?'],
    ['emoji' => '🎯', 'label' => 'Destination quiz',      'msg' => 'Take me through the destination quiz'],
    ['emoji' => '🧮', 'label' => 'Calculate trip cost',   'msg' => 'Calculate total cost for 2 travelers'],
    ['emoji' => '🧳', 'label' => 'Solo travel tips',      'msg' => 'Best destinations for solo travelers'],
    ['emoji' => '👥', 'label' => 'Group travel deals',    'msg' => 'Tell me about group travel discounts'],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="InteleTour AI Travel Assistant – Your intelligent 24/7 travel planning companion. Get personalised destination recommendations, itinerary planning, and booking guidance.">
    <title>AI Travel Assistant – InteleTour</title>
    <link rel="stylesheet" href="style.css">
    <style>
    /* ============================================================
       AI ASSISTANT PAGE – FULL STYLESHEET
    ============================================================ */

    /* Page Layout */
    .ai-page-wrapper {
        max-width: 1400px;
        margin: 0 auto;
        padding: 40px 24px 80px;
        display: grid;
        grid-template-columns: 300px 1fr 280px;
        gap: 28px;
        align-items: start;
    }

    /* ---- LEFT SIDEBAR ---- */
    .ai-sidebar-left {
        position: sticky;
        top: 90px;
    }

    .ai-sidebar-card {
        background: white;
        border-radius: 14px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.09);
        overflow: hidden;
        margin-bottom: 20px;
    }

    .ai-sidebar-card-header {
        background: linear-gradient(135deg, var(--dark), var(--primary));
        color: white;
        padding: 14px 18px;
        font-size: 14px;
        font-weight: 700;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .ai-sidebar-card-body {
        padding: 16px;
    }

    /* Quick Topics */
    .quick-topics-list {
        display: flex;
        flex-direction: column;
        gap: 6px;
    }

    .quick-topic-btn {
        background: var(--light);
        border: 1.5px solid rgba(0,119,182,0.15);
        border-radius: 8px;
        padding: 9px 12px;
        font-size: 13px;
        font-weight: 500;
        color: var(--dark);
        cursor: pointer;
        text-align: left;
        transition: all 0.25s;
        display: flex;
        align-items: center;
        gap: 8px;
        width: 100%;
    }

    .quick-topic-btn:hover {
        background: var(--primary);
        color: white;
        border-color: var(--primary);
        transform: translateX(4px);
    }

    /* Session Info */
    .session-info {
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    .session-stat {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 8px 12px;
        background: var(--light);
        border-radius: 8px;
        font-size: 13px;
    }

    .session-stat span:first-child { color: var(--gray); }
    .session-stat span:last-child  { font-weight: 700; color: var(--dark); }

    /* Destination Pills */
    .dest-pills {
        display: flex;
        flex-wrap: wrap;
        gap: 7px;
    }

    .dest-pill {
        background: white;
        border: 1.5px solid var(--primary);
        color: var(--primary);
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.25s;
    }

    .dest-pill:hover {
        background: var(--primary);
        color: white;
    }

    /* ---- MAIN CHAT BOX ---- */
    .chat-main {
        display: flex;
        flex-direction: column;
    }

    /* Chat Header */
    .chat-header-main {
        background: linear-gradient(135deg, var(--dark), var(--primary));
        border-radius: 14px 14px 0 0;
        padding: 18px 24px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        color: white;
    }

    .chat-header-left {
        display: flex;
        align-items: center;
        gap: 14px;
    }

    .ai-big-avatar {
        width: 52px;
        height: 52px;
        border-radius: 50%;
        background: linear-gradient(135deg, var(--accent), #e06b00);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        box-shadow: 0 4px 14px rgba(0,0,0,0.2);
        position: relative;
    }

    .ai-online-dot {
        width: 12px;
        height: 12px;
        background: #28a745;
        border-radius: 50%;
        position: absolute;
        bottom: 2px;
        right: 2px;
        border: 2px solid white;
        animation: pulse-dot 2s infinite;
    }

    @keyframes pulse-dot {
        0%, 100% { transform: scale(1); }
        50%       { transform: scale(1.3); }
    }

    .chat-header-info h3 { font-size: 17px; font-weight: 700; margin-bottom: 2px; }
    .chat-header-info span { font-size: 12px; opacity: 0.8; }

    .chat-header-actions {
        display: flex;
        gap: 8px;
    }

    .chat-action-btn {
        background: rgba(255,255,255,0.15);
        border: 1px solid rgba(255,255,255,0.25);
        color: white;
        padding: 7px 14px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s;
    }

    .chat-action-btn:hover { background: rgba(255,255,255,0.28); }

    /* Mood/Mode Selector Bar */
    .mode-selector-bar {
        background: white;
        border-bottom: 1px solid #eee;
        padding: 12px 20px;
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
        align-items: center;
    }

    .mode-selector-bar span {
        font-size: 12px;
        font-weight: 700;
        color: var(--gray);
        margin-right: 4px;
    }

    .mode-btn {
        padding: 6px 14px;
        border-radius: 20px;
        border: 1.5px solid #e0e0e0;
        background: white;
        font-size: 12px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.25s;
        color: var(--dark);
    }

    .mode-btn.active,
    .mode-btn:hover {
        background: var(--primary);
        color: white;
        border-color: var(--primary);
    }

    /* Chat Messages Area */
    .chat-messages-area {
        height: 520px;
        overflow-y: auto;
        padding: 24px 20px;
        background: linear-gradient(180deg, #f4f8ff 0%, #edf3fc 100%);
        display: flex;
        flex-direction: column;
        gap: 16px;
        scroll-behavior: smooth;
    }

    /* Welcome Message */
    .welcome-msg {
        background: white;
        border-radius: 16px 16px 16px 4px;
        padding: 18px 20px;
        box-shadow: 0 3px 14px rgba(0,0,0,0.08);
        font-size: 14px;
        line-height: 1.7;
        color: #333;
        max-width: 82%;
        align-self: flex-start;
        animation: slideInLeft 0.4s ease;
    }

    /* Messages */
    .msg-wrap {
        display: flex;
        flex-direction: column;
        gap: 4px;
        animation: fadeIn 0.35s ease;
    }

    .msg-wrap.user-wrap { align-items: flex-end; }
    .msg-wrap.bot-wrap  { align-items: flex-start; }

    .msg-bubble {
        max-width: 80%;
        padding: 12px 16px;
        border-radius: 16px;
        font-size: 14px;
        line-height: 1.68;
        word-wrap: break-word;
    }

    .msg-bubble.user-bubble {
        background: linear-gradient(135deg, var(--primary), var(--secondary));
        color: white;
        border-bottom-right-radius: 4px;
    }

    .msg-bubble.bot-bubble {
        background: white;
        color: #333;
        box-shadow: 0 3px 12px rgba(0,0,0,0.08);
        border-bottom-left-radius: 4px;
    }

    .msg-bubble a {
        color: var(--primary);
        text-decoration: underline;
        font-weight: 600;
    }

    .msg-bubble.user-bubble a { color: #fff; }

    .msg-time {
        font-size: 10px;
        color: var(--gray);
        padding: 0 4px;
    }

    .bot-avatar-mini {
        width: 26px;
        height: 26px;
        border-radius: 50%;
        background: linear-gradient(135deg, var(--primary), var(--secondary));
        color: white;
        font-size: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .msg-row {
        display: flex;
        align-items: flex-end;
        gap: 8px;
    }

    /* Typing Indicator */
    .typing-indicator {
        display: none;
        align-items: center;
        gap: 8px;
        animation: fadeIn 0.3s;
    }

    .typing-indicator.show { display: flex; }

    .typing-dots {
        background: white;
        border-radius: 16px 16px 16px 4px;
        padding: 12px 16px;
        box-shadow: 0 3px 12px rgba(0,0,0,0.08);
        display: flex;
        gap: 5px;
        align-items: center;
    }

    .typing-dots span {
        width: 7px;
        height: 7px;
        border-radius: 50%;
        background: var(--primary);
        animation: bounce-dot 1.2s infinite ease-in-out;
    }

    .typing-dots span:nth-child(2) { animation-delay: 0.2s; }
    .typing-dots span:nth-child(3) { animation-delay: 0.4s; }

    @keyframes bounce-dot {
        0%, 80%, 100% { transform: scale(0.6); opacity: 0.4; }
        40%            { transform: scale(1);   opacity: 1;   }
    }

    /* Quick Replies Bar */
    .quick-replies-bar {
        background: white;
        border-top: 1px solid #f0f0f0;
        padding: 12px 18px;
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
        overflow-x: auto;
    }

    .quick-reply-chip {
        flex-shrink: 0;
        padding: 7px 14px;
        background: var(--light);
        border: 1.5px solid rgba(0,180,216,0.4);
        color: var(--primary);
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.25s;
        white-space: nowrap;
    }

    .quick-reply-chip:hover {
        background: var(--primary);
        color: white;
        border-color: var(--primary);
        transform: translateY(-2px);
    }

    /* Input Area */
    .chat-input-wrapper {
        background: white;
        border-radius: 0 0 14px 14px;
        padding: 16px 20px;
        border-top: 1px solid #eee;
        box-shadow: 0 4px 20px rgba(0,0,0,0.06);
    }

    .chat-input-row {
        display: flex;
        gap: 10px;
        align-items: center;
    }

    .chat-input-row input {
        flex: 1;
        padding: 13px 20px;
        border: 1.5px solid #dde2ee;
        border-radius: 30px;
        font-size: 14px;
        outline: none;
        transition: all 0.3s;
        background: #fafafa;
        color: #333;
    }

    .chat-input-row input:focus {
        border-color: var(--primary);
        background: white;
        box-shadow: 0 0 0 3px rgba(0,119,182,0.1);
    }

    .send-btn {
        width: 46px;
        height: 46px;
        border-radius: 50%;
        background: linear-gradient(135deg, var(--primary), var(--secondary));
        border: none;
        color: white;
        font-size: 18px;
        cursor: pointer;
        transition: all 0.3s;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 4px 14px rgba(0,119,182,0.35);
        flex-shrink: 0;
    }

    .send-btn:hover {
        background: linear-gradient(135deg, var(--dark), var(--primary));
        transform: scale(1.08);
        box-shadow: 0 6px 20px rgba(0,119,182,0.45);
    }

    .send-btn:active { transform: scale(0.96); }

    .input-hint {
        font-size: 11px;
        color: var(--gray);
        margin-top: 8px;
        padding: 0 4px;
    }

    /* Chat Book Button (inside messages) */
    .chat-book-btn {
        display: inline-block;
        background: var(--accent);
        color: white !important;
        padding: 8px 18px;
        border-radius: 20px;
        font-size: 13px;
        font-weight: 700;
        text-decoration: none !important;
        margin-top: 10px;
        transition: all 0.3s;
    }

    .chat-book-btn:hover { background: #e06b00 !important; transform: translateY(-2px); }

    /* ---- RIGHT SIDEBAR ---- */
    .ai-sidebar-right {
        position: sticky;
        top: 90px;
    }

    /* Destination Cards in sidebar */
    .mini-dest-card {
        background: white;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 3px 14px rgba(0,0,0,0.09);
        margin-bottom: 14px;
        cursor: pointer;
        transition: all 0.3s;
    }

    .mini-dest-card:hover { transform: translateY(-4px); box-shadow: 0 8px 24px rgba(0,0,0,0.14); }

    .mini-dest-card img {
        width: 100%;
        height: 90px;
        object-fit: cover;
        display: block;
    }

    .mini-dest-info {
        padding: 10px 12px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .mini-dest-info h4 { font-size: 13px; color: var(--dark); font-weight: 700; }
    .mini-dest-info span { font-size: 12px; color: var(--accent); font-weight: 800; }

    /* Travel Tips Card */
    .travel-tip-card {
        background: linear-gradient(135deg, var(--primary), var(--secondary));
        border-radius: 14px;
        padding: 18px;
        color: white;
        margin-bottom: 20px;
    }

    .travel-tip-card h4 { font-size: 14px; margin-bottom: 10px; }

    .travel-tip-card p {
        font-size: 13px;
        opacity: 0.9;
        line-height: 1.6;
    }

    .tip-rotate-btn {
        background: rgba(255,255,255,0.2);
        border: 1px solid rgba(255,255,255,0.3);
        color: white;
        padding: 6px 14px;
        border-radius: 15px;
        font-size: 11px;
        cursor: pointer;
        margin-top: 12px;
        transition: all 0.3s;
    }

    .tip-rotate-btn:hover { background: rgba(255,255,255,0.35); }

    /* Live Chat Toggle Banner */
    .live-chat-banner {
        background: white;
        border: 1.5px solid var(--primary);
        border-radius: 12px;
        padding: 16px;
        text-align: center;
        margin-bottom: 20px;
    }

    .live-chat-banner h4 { font-size: 14px; color: var(--dark); margin-bottom: 7px; }
    .live-chat-banner p  { font-size: 12px; color: var(--gray); margin-bottom: 12px; }

    /* Trip Planner Widget */
    .trip-planner-widget {
        background: white;
        border-radius: 14px;
        box-shadow: 0 4px 18px rgba(0,0,0,0.09);
        overflow: hidden;
        margin-bottom: 20px;
    }

    .trip-planner-widget .header {
        background: linear-gradient(135deg, var(--accent), #e06b00);
        color: white;
        padding: 13px 16px;
        font-size: 14px;
        font-weight: 700;
    }

    .trip-planner-widget .body {
        padding: 16px;
    }

    .planner-field {
        margin-bottom: 12px;
    }

    .planner-field label {
        display: block;
        font-size: 12px;
        font-weight: 600;
        color: var(--dark);
        margin-bottom: 5px;
    }

    .planner-field select,
    .planner-field input {
        width: 100%;
        padding: 9px 12px;
        border: 1.5px solid #dde;
        border-radius: 8px;
        font-size: 13px;
        outline: none;
        background: #fafafa;
        transition: all 0.3s;
    }

    .planner-field select:focus,
    .planner-field input:focus {
        border-color: var(--primary);
        background: white;
    }

    #planner-result {
        background: var(--light);
        border-radius: 8px;
        padding: 12px;
        font-size: 13px;
        color: var(--dark);
        margin-top: 10px;
        display: none;
        line-height: 1.7;
    }

    /* Budget Estimator (right sidebar mini) */
    .budget-mini-result {
        font-size: 22px;
        font-weight: 800;
        color: var(--accent);
        text-align: center;
        margin: 10px 0;
    }

    /* Responsive */
    @media (max-width: 1200px) {
        .ai-page-wrapper { grid-template-columns: 260px 1fr; }
        .ai-sidebar-right { display: none; }
    }

    @media (max-width: 900px) {
        .ai-page-wrapper { grid-template-columns: 1fr; padding: 20px 16px 60px; }
        .ai-sidebar-left  { position: static; }
        .chat-messages-area { height: 380px; }
    }

    @media (max-width: 480px) {
        .chat-header-actions { display: none; }
        .mode-selector-bar   { gap: 5px; }
        .mode-btn            { padding: 5px 10px; font-size: 11px; }
        .chat-messages-area  { height: 320px; }
    }
    </style>
</head>
<body>

<!-- ===== NAVBAR ===== -->
<nav class="navbar">
    <div class="logo">✈ Intele<span>Tour</span></div>
    <ul class="nav-links">
        <li><a href="index.php">Home</a></li>
        <li><a href="packages.php">Packages</a></li>
        <li><a href="ai_assistant.php" class="active">AI Assistant</a></li>
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
    <h1>🤖 AI Travel Assistant</h1>
    <p>Your intelligent 24/7 travel planning companion — powered by InteleTour Intelligence</p>
</div>

<!-- ===== MAIN LAYOUT GRID ===== -->
<div class="ai-page-wrapper">

    <!-- =====================================================
         LEFT SIDEBAR
    ====================================================== -->
    <aside class="ai-sidebar-left">

        <!-- Quick Topics -->
        <div class="ai-sidebar-card">
            <div class="ai-sidebar-card-header">
                ⚡ Quick Topics
            </div>
            <div class="ai-sidebar-card-body">
                <div class="quick-topics-list">
                    <?php foreach (array_slice($quick_topics, 0, 10) as $topic): ?>
                    <button class="quick-topic-btn" onclick="sendQuickMsg('<?= htmlspecialchars($topic['msg'], ENT_QUOTES) ?>')">
                        <?= $topic['emoji'] ?> <?= htmlspecialchars($topic['label']) ?>
                    </button>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- More Topics -->
        <div class="ai-sidebar-card">
            <div class="ai-sidebar-card-header">
                🧭 More Topics
            </div>
            <div class="ai-sidebar-card-body">
                <div class="quick-topics-list">
                    <?php foreach (array_slice($quick_topics, 10) as $topic): ?>
                    <button class="quick-topic-btn" onclick="sendQuickMsg('<?= htmlspecialchars($topic['msg'], ENT_QUOTES) ?>')">
                        <?= $topic['emoji'] ?> <?= htmlspecialchars($topic['label']) ?>
                    </button>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Session Info -->
        <div class="ai-sidebar-card">
            <div class="ai-sidebar-card-header">
                📊 Session Info
            </div>
            <div class="ai-sidebar-card-body">
                <div class="session-info">
                    <div class="session-stat">
                        <span>Messages</span>
                        <span id="msg-count-display"><?= $msg_count ?></span>
                    </div>
                    <div class="session-stat">
                        <span>Status</span>
                        <span style="color:#28a745;">● Online</span>
                    </div>
                    <div class="session-stat">
                        <span>User</span>
                        <span><?= isLoggedIn() ? htmlspecialchars($_SESSION['username']) : 'Guest' ?></span>
                    </div>
                    <div class="session-stat">
                        <span>Response</span>
                        <span>Instant ⚡</span>
                    </div>
                </div>
                <form method="POST" style="margin-top:14px;">
                    <button type="submit" name="clear_chat"
                        onclick="return confirm('Clear all chat history?')"
                        style="width:100%;padding:9px;background:var(--light);border:1.5px solid #dde;border-radius:8px;font-size:13px;font-weight:600;cursor:pointer;color:var(--danger);transition:all 0.3s;"
                        onmouseover="this.style.background='var(--danger)';this.style.color='white'"
                        onmouseout="this.style.background='var(--light)';this.style.color='var(--danger)'">
                        🗑️ Clear Chat History
                    </button>
                </form>
            </div>
        </div>

        <!-- Destination Pills -->
        <div class="ai-sidebar-card">
            <div class="ai-sidebar-card-header">
                📍 Destinations
            </div>
            <div class="ai-sidebar-card-body">
                <div class="dest-pills">
                    <?php foreach ($packages_kb as $key => $p): ?>
                    <button class="dest-pill" onclick="sendQuickMsg('Tell me about <?= $p['name'] ?>')">
                        <?= $p['emoji'] ?> <?= htmlspecialchars(explode(',', $p['name'])[0]) ?>
                    </button>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

    </aside>

    <!-- =====================================================
         MAIN CHAT BOX
    ====================================================== -->
    <main class="chat-main">

        <!-- Chat Header -->
        <div class="chat-header-main">
            <div class="chat-header-left">
                <div class="ai-big-avatar">
                    🤖
                    <div class="ai-online-dot"></div>
                </div>
                <div class="chat-header-info">
                    <h3>InteleTour AI Assistant</h3>
                    <span>● Online · Powered by InteleTour Intelligence · <?= $msg_count ?> messages</span>
                </div>
            </div>
            <div class="chat-header-actions">
                <button class="chat-action-btn" onclick="exportChat()">📥 Export</button>
                <button class="chat-action-btn" onclick="toggleFullscreen()">⛶ Fullscreen</button>
            </div>
        </div>

        <!-- Mode Selector Bar -->
        <div class="mode-selector-bar">
            <span>Mode:</span>
            <button class="mode-btn active" data-mode="general"     onclick="setMode(this, 'general')">🌍 General</button>
            <button class="mode-btn"        data-mode="planner"     onclick="setMode(this, 'planner')">📋 Trip Planner</button>
            <button class="mode-btn"        data-mode="budget"      onclick="setMode(this, 'budget')">💰 Budget Calc</button>
            <button class="mode-btn"        data-mode="quiz"        onclick="setMode(this, 'quiz')">🎯 Destination Quiz</button>
            <button class="mode-btn"        data-mode="compare"     onclick="setMode(this, 'compare')">⚖️ Compare</button>
        </div>

        <!-- Chat Messages -->
        <div class="chat-messages-area" id="chat-messages">

            <!-- Welcome Message (always shown) -->
            <div class="welcome-msg">
                👋 <strong>Hello<?= isLoggedIn() ? ', ' . htmlspecialchars($_SESSION['username']) : '' ?>!</strong> I'm your <strong>InteleTour AI Travel Assistant</strong>. 🌍<br><br>
                I can help you:<br>
                ✈️ Discover perfect travel destinations<br>
                📦 Explore and compare packages<br>
                💰 Calculate your total trip budget<br>
                📅 Plan a day-by-day itinerary<br>
                🌤️ Know the best time to travel<br>
                🎯 Take a destination quiz<br><br>
                <em>What dream destination shall we explore today? 😊</em>
            </div>

            <!-- Load Previous Chat History from DB -->
            <?php foreach ($previous_chats as $chat):
                $is_user = $chat['sender'] === 'user';
                $time    = date('h:i A', strtotime($chat['created_at']));
            ?>
            <div class="msg-wrap <?= $is_user ? 'user-wrap' : 'bot-wrap' ?>">
                <?php if (!$is_user): ?>
                <div class="msg-row">
                    <div class="bot-avatar-mini">🤖</div>
                    <div class="msg-bubble bot-bubble">
                        <?= $chat['message'] ?>
                    </div>
                </div>
                <?php else: ?>
                <div class="msg-bubble user-bubble">
                    <?= htmlspecialchars($chat['message']) ?>
                </div>
                <?php endif; ?>
                <span class="msg-time"><?= $time ?></span>
            </div>
            <?php endforeach; ?>

            <!-- Typing Indicator -->
            <div class="typing-indicator" id="typing-indicator">
                <div class="bot-avatar-mini">🤖</div>
                <div class="typing-dots">
                    <span></span><span></span><span></span>
                </div>
            </div>

        </div>

        <!-- Quick Reply Chips -->
        <div class="quick-replies-bar" id="quick-replies-bar">
            <button class="quick-reply-chip" onclick="sendQuickMsg('Show all available packages')">🌍 All Packages</button>
            <button class="quick-reply-chip" onclick="sendQuickMsg('Tell me about Bali')">🏝️ About Bali</button>
            <button class="quick-reply-chip" onclick="sendQuickMsg('Tell me about Paris')">🗼 About Paris</button>
            <button class="quick-reply-chip" onclick="sendQuickMsg('Tell me about Maldives')">🌊 Maldives</button>
            <button class="quick-reply-chip" onclick="sendQuickMsg('Best honeymoon destinations')">💑 Honeymoon</button>
            <button class="quick-reply-chip" onclick="sendQuickMsg('Best budget destinations')">💰 Budget Picks</button>
            <button class="quick-reply-chip" onclick="sendQuickMsg('Calculate cost for 2 travelers')">🧮 Budget Calc</button>
            <button class="quick-reply-chip" onclick="sendQuickMsg('Tell me about visa requirements')">🛂 Visa Info</button>
            <button class="quick-reply-chip" onclick="sendQuickMsg('Take me through the destination quiz')">🎯 Quiz</button>
            <button class="quick-reply-chip" onclick="sendQuickMsg('Best time to visit each destination')">🌤️ Best Season</button>
        </div>

        <!-- Input Area -->
        <div class="chat-input-wrapper">
            <div class="chat-input-row">
                <input
                    type="text"
                    id="chat-input"
                    placeholder="Ask me anything about travel... 🌍"
                    autocomplete="off"
                    maxlength="500"
                >
                <button class="send-btn" id="send-btn" onclick="sendMessage()" title="Send Message">➤</button>
            </div>
            <p class="input-hint">
                💡 Try: <em>"Plan a Bali trip for 2"</em> · <em>"Compare Paris and Tokyo"</em> · <em>"Cost for 4 travelers to Dubai"</em>
            </p>
        </div>

    </main>

    <!-- =====================================================
         RIGHT SIDEBAR
    ====================================================== -->
    <aside class="ai-sidebar-right">

        <!-- Travel Tips Card -->
        <div class="travel-tip-card">
            <h4>💡 Travel Tip of the Day</h4>
            <p id="travel-tip-text">Book flights 6–8 weeks in advance for the best prices. Use Google Flights' price tracking feature to get alerts.</p>
            <button class="tip-rotate-btn" onclick="rotateTip()">↻ Next Tip</button>
        </div>

        <!-- Trip Planner Widget -->
        <div class="trip-planner-widget">
            <div class="header">📅 Quick Trip Planner</div>
            <div class="body">
                <div class="planner-field">
                    <label>Destination</label>
                    <select id="planner-dest">
                        <option value="">-- Select Destination --</option>
                        <?php foreach ($packages_kb as $key => $p): ?>
                            <option value="<?= $key ?>" data-price="<?= $p['price'] ?>" data-dur="<?= htmlspecialchars($p['duration']) ?>">
                                <?= $p['emoji'] ?> <?= htmlspecialchars($p['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="planner-field">
                    <label>Number of Travelers</label>
                    <input type="number" id="planner-travelers" value="2" min="1" max="50">
                </div>
                <div class="planner-field">
                    <label>Travel Date</label>
                    <input type="date" id="planner-date" min="<?= date('Y-m-d') ?>">
                </div>
                <button class="btn btn-primary btn-full btn-sm" onclick="calculateTrip()">
                    Calculate Trip Cost
                </button>
                <div id="planner-result"></div>
            </div>
        </div>

        <!-- Popular Destinations -->
        <div class="ai-sidebar-card">
            <div class="ai-sidebar-card-header">🔥 Popular Packages</div>
            <div class="ai-sidebar-card-body" style="padding: 12px;">
                <?php
                $popular = ['bali', 'paris', 'dubai', 'tokyo', 'maldives'];
                foreach ($popular as $pk):
                    $p = $packages_kb[$pk];
                    $img_seed = strtolower(explode(',', $pk)[0]);
                ?>
                <div class="mini-dest-card" onclick="sendQuickMsg('Tell me about <?= $p['name'] ?>')">
                    <img src="https://picsum.photos/seed/<?= $img_seed ?>/300/120" alt="<?= htmlspecialchars($p['name']) ?>" loading="lazy">
                    <div class="mini-dest-info">
                        <h4><?= $p['emoji'] ?> <?= htmlspecialchars(explode(',', $p['name'])[0]) ?></h4>
                        <span>₹<?= number_format($p['price']) ?></span>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Live Support Banner -->
        <div class="live-chat-banner">
            <h4>👨‍💼 Need Human Help?</h4>
            <p>Our travel experts are available Mon–Sat, 9AM–6PM IST</p>
            <a href="tel:+919876543210" class="btn btn-secondary btn-sm btn-full">📞 Call Now</a>
            <a href="mailto:support@inteletour.com" class="btn btn-outline" style="margin-top:8px; color:var(--primary); border-color:var(--primary); display:block; text-align:center; padding:8px; border-radius:20px; font-size:13px; font-weight:600; border: 1.5px solid var(--primary);">
                📧 Email Support
            </a>
        </div>

    </aside>

</div><!-- end .ai-page-wrapper -->

<!-- ===== CTA SECTION ===== -->
<section class="section" style="background: linear-gradient(135deg, var(--dark), var(--primary)); text-align:center; color:white; padding: 50px 40px;">
    <h2 style="font-size:28px; font-weight:800; margin-bottom:12px;">Ready to Make it Official? ✈️</h2>
    <p style="opacity:0.88; margin-bottom:28px; font-size:15px; max-width:500px; margin-left:auto; margin-right:auto;">
        Browse our full packages and book your dream trip in minutes with secure online payment.
    </p>
    <div style="display:flex; gap:14px; justify-content:center; flex-wrap:wrap;">
        <a href="packages.php" class="btn btn-primary">📦 Browse All Packages</a>
        <?php if (!isLoggedIn()): ?>
            <a href="register.php" class="btn btn-outline">📝 Create Free Account</a>
        <?php else: ?>
            <a href="my_bookings.php" class="btn btn-outline">📋 My Bookings</a>
        <?php endif; ?>
    </div>
</section>

<!-- ===== FOOTER ===== -->
<footer>
    <p>© 2025 <span>InteleTour</span> – AI Assistant powered by InteleTour Intelligence. Built with ❤️</p>
</footer>

<!-- ===== JAVASCRIPT ENGINE ===== -->
<script src="script.js"></script>
<script>
// ============================================================
//  AI ASSISTANT – FULL JAVASCRIPT ENGINE
// ============================================================

// ---- STATE ----
let isTyping   = false;
let msgCount   = <?= $msg_count ?>;
let currentMode = 'general';
let chatHistory = [];

// ---- DOM REFS ----
const     chatBox     = document.getElementById('chat-messages'),
    inputField  = document.getElementById('chat-input'),
    sendBtn     = document.getElementById('send-btn'),
    typingEl    = document.getElementById('typing-indicator'),
    msgCountEl  = document.getElementById('msg-count-display');

// ============================================================
//  SECTION A — SEND MESSAGE (Main Function)
// ============================================================
function fallbackReply(userText) {
    const q = (userText || '').toLowerCase();
    if (q.includes('book') || q.includes('booking')) {
        return "I can help you book quickly. Tell me your destination, travel month, and number of travelers, and I will suggest the best option.";
    }
    if (q.includes('budget') || q.includes('cheap') || q.includes('affordable')) {
        return "For budget trips, good picks are Phuket, Bali, and Singapore. Share your budget per person and I will shortlist the best package.";
    }
    if (q.includes('visa')) {
        return "I can share visa guidance by destination. Tell me your destination and passport country, and I will provide the requirement summary.";
    }
    if (q.includes('hotel') || q.includes('cab') || q.includes('train') || q.includes('flight')) {
        return "I can help with flights, trains, hotels, and cabs. Tell me source, destination, date, and traveler count.";
    }
    return "I can answer destination info, package prices, booking steps, payment options, visa basics, and itinerary planning. What do you want to plan?";
}

async function sendMessage() {
    const msg = inputField.value.trim();
    if (!msg || isTyping) return;

    // Append user bubble
    appendMessage('user', msg);
    inputField.value = '';
    inputField.focus();
    msgCount++;
    updateMsgCount();

    // Show typing indicator
    showTyping();

    try {
        const response = await fetch('ai_assistant.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'ajax=1&message=' + encodeURIComponent(msg)
        });

        const raw = await response.text();
        let data = null;
        try {
            data = JSON.parse(raw);
        } catch (e) {
            data = null;
        }

        if (!response.ok || !data || typeof data.reply !== 'string') {
            throw new Error('invalid_response');
        }

        // Simulate realistic typing delay based on reply length
        const delay = Math.min(Math.max(data.reply.length * 2, 900), 2800);

        setTimeout(() => {
            hideTyping();
            appendMessage('bot', data.reply, data.intent);
            msgCount++;
            updateMsgCount();

            // Handle extras (e.g., show quiz button, booking button)
            if (data.extras && data.extras.show_quiz_btn) {
                showQuizPrompt();
            }

            // Update quick replies based on intent
            updateQuickReplies(data.intent);

        }, delay);

    } catch (err) {
        setTimeout(() => {
            hideTyping();
            appendMessage('bot', fallbackReply(msg), 'general');
        }, 1000);
    }
}

// ============================================================
//  SECTION B — APPEND MESSAGE TO CHAT
// ============================================================
function appendMessage(sender, text, intent = '') {
    const wrap   = document.createElement('div');
    const time   = getCurrentTime();
    const isUser = sender === 'user';

    wrap.className = `msg-wrap ${isUser ? 'user-wrap' : 'bot-wrap'}`;

    if (isUser) {
        wrap.innerHTML = `
            <div class="msg-bubble user-bubble">${escapeHtml(text)}</div>
            <span class="msg-time">${time}</span>
        `;
    } else {
        wrap.innerHTML = `
            <div class="msg-row">
                <div class="bot-avatar-mini">🤖</div>
                <div class="msg-bubble bot-bubble">${text}</div>
            </div>
            <span class="msg-time">${time}</span>
        `;
    }

    // Insert before typing indicator
    chatBox.insertBefore(wrap, typingEl);
    scrollToBottom();

    // Store in local history
    chatHistory.push({ sender, text, time, intent });
}

// ============================================================
//  SECTION C — QUICK MESSAGE (sidebar / chips)
// ============================================================
function sendQuickMsg(msg) {
    if (isTyping) return;
    inputField.value = msg;
    sendMessage();
}

// ============================================================
//  SECTION D — TYPING INDICATOR
// ============================================================
function showTyping() {
    isTyping = true;
    sendBtn.disabled   = true;
    sendBtn.style.opacity = '0.5';
    typingEl.classList.add('show');
    scrollToBottom();
}

function hideTyping() {
    isTyping = false;
    sendBtn.disabled   = false;
    sendBtn.style.opacity = '1';
    typingEl.classList.remove('show');
}

// ============================================================
//  SECTION E — SCROLL TO BOTTOM
// ============================================================
function scrollToBottom() {
    chatBox.scrollTo({ top: chatBox.scrollHeight, behavior: 'smooth' });
}

// ============================================================
//  SECTION F — UPDATE MESSAGE COUNT
// ============================================================
function updateMsgCount() {
    if (msgCountEl) msgCountEl.textContent = msgCount;
}

// ============================================================
//  SECTION G — CURRENT TIME
// ============================================================
function getCurrentTime() {
    const now = new Date();
    let h = now.getHours(), m = now.getMinutes();
    const ampm = h >= 12 ? 'PM' : 'AM';
    h = h % 12 || 12;
    return `${h}:${String(m).padStart(2, '0')} ${ampm}`;
}

// ============================================================
//  SECTION H — ESCAPE HTML (for user messages)
// ============================================================
function escapeHtml(str) {
    return str
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

// ============================================================
//  SECTION I — MODE SELECTOR
// ============================================================
function setMode(btn, mode) {
    document.querySelectorAll('.mode-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    currentMode = mode;

    const modeMessages = {
        general  : '🌍 Switched to General Mode! Ask me anything about travel — destinations, packages, tips and more.',
        planner  : '📋 Trip Planner Mode activated! Tell me a destination and I\'ll build you a day-by-day itinerary.\n\nTry: "Plan a Bali trip" or "Plan 7 days in Tokyo"',
        budget   : '💰 Budget Calculator Mode! Tell me a destination and how many travelers.\n\nTry: "Calculate for 3 travelers to Dubai" or "Cost for 2 people to Maldives"',
        quiz     : '🎯 Destination Quiz Mode! I\'ll ask you 3 quick questions and find your perfect destination.\n\nReady? Type "Start quiz" to begin!',
        compare  : '⚖️ Compare Mode! I\'ll put destinations side-by-side for you.\n\nTry: "Compare Bali and Dubai" or "Compare Paris and Rome"',
    };

    sendQuickMsg(modeMessages[mode] || 'Hello!');
}

// ============================================================
//  SECTION J — UPDATE QUICK REPLIES AFTER EACH RESPONSE
// ============================================================
function updateQuickReplies(intent) {
    const bar = document.getElementById('quick-replies-bar');
    if (!bar) return;

    const contextual = {
        paris      : ['🏯 Now show Tokyo',   '💑 Honeymoon options',   '📋 Plan Paris trip',   '💰 Calculate Paris cost'],
        tokyo      : ['🏝️ Now show Bali',    '📋 Plan Tokyo trip',     '🌤️ Best time for Tokyo','💳 How to book'],
        bali       : ['🏙️ Now show Dubai',   '💑 Honeymoon in Bali',   '📋 Plan Bali trip',    '💰 Bali cost for 2'],
        dubai      : ['🌊 Now show Maldives','📋 Plan Dubai trip',     '💰 Dubai cost for 4',  '🎡 Compare Dubai & Singapore'],
        maldives   : ['🗼 Now show Paris',   '💑 Maldives honeymoon',  '💰 Maldives cost',     '🛂 Maldives visa info'],
        budget     : ['🏝️ Tell me about Bali','⛵ Tell me about Phuket','🌆 Tell me about Singapore','🧮 Calculate for 2 travelers'],
        recommend  : ['🎯 Take destination quiz','⚖️ Compare top 3',   '💑 Honeymoon picks',   '🧗 Adventure travel'],
        booking    : ['💳 Payment options',  '🔄 Cancellation policy', '📦 Show all packages', '📋 My bookings'],
        honeymoon  : ['🌊 Maldives details', '🏺 Santorini details',   '🗼 Paris honeymoon',   '💰 Calculate honeymoon cost'],
        family     : ['🌆 Singapore details','🏝️ Bali for families',   '🏙️ Dubai for kids',    '💰 Family trip cost'],
        visa       : ['🗼 Paris visa',       '🏯 Japan visa',          '🗽 US visa',           '🎡 UK visa'],
        weather    : ['🌤️ Best time Bali',   '❄️ Best time Tokyo',     '☀️ Best time Dubai',   '🌧️ Monsoon seasons'],
        general    : ['🌍 All packages',     '💰 Budget picks',        '👑 Luxury packages',   '🎯 Destination quiz'],
    };

    const chips = contextual[intent] || contextual['general'];
    bar.innerHTML = chips.map(label => {
        // Extract message from chip label by stripping emoji prefix
        const msg = label.replace(/^[^\s]+\s/, '');
        return `<button class="quick-reply-chip" onclick="sendQuickMsg('${msg.replace(/'/g, "\\\'")}')">${label}</button>`;
    }).join('');
}

// ============================================================
//  SECTION K — QUIZ PROMPT
// ============================================================
function showQuizPrompt() {
    const quizHtml = `
        <div style="background:linear-gradient(135deg,var(--dark),var(--primary));color:white;border-radius:12px;padding:16px;margin-top:14px;font-size:13px;">
            <strong>🎯 Quick Destination Finder</strong><br><br>
            <strong>Q1 – Travel Style:</strong><br>
            <button onclick="quizAnswer(this,'beach')"    style="margin:4px;padding:6px 12px;border-radius:15px;border:1.5px solid rgba(255,255,255,0.4);background:transparent;color:white;cursor:pointer;font-size:12px;" onmouseover="this.style.background='rgba(255,255,255,0.2)'" onmouseout="this.style.background='transparent'">🏖️ Beach & Relax</button>
            <button onclick="quizAnswer(this,'adventure')" style="margin:4px;padding:6px 12px;border-radius:15px;border:1.5px solid rgba(255,255,255,0.4);background:transparent;color:white;cursor:pointer;font-size:12px;" onmouseover="this.style.background='rgba(255,255,255,0.2)'" onmouseout="this.style.background='transparent'">🧗 Adventure</button>
            <button onclick="quizAnswer(this,'culture')"  style="margin:4px;padding:6px 12px;border-radius:15px;border:1.5px solid rgba(255,255,255,0.4);background:transparent;color:white;cursor:pointer;font-size:12px;" onmouseover="this.style.background='rgba(255,255,255,0.2)'" onmouseout="this.style.background='transparent'">🏛️ Culture</button>
            <button onclick="quizAnswer(this,'luxury')"   style="margin:4px;padding:6px 12px;border-radius:15px;border:1.5px solid rgba(255,255,255,0.4);background:transparent;color:white;cursor:pointer;font-size:12px;" onmouseover="this.style.background='rgba(255,255,255,0.2)'" onmouseout="this.style.background='transparent'">👑 Luxury</button>
            <button onclick="quizAnswer(this,'city')"     style="margin:4px;padding:6px 12px;border-radius:15px;border:1.5px solid rgba(255,255,255,0.4);background:transparent;color:white;cursor:pointer;font-size:12px;" onmouseover="this.style.background='rgba(255,255,255,0.2)'" onmouseout="this.style.background='transparent'">🏙️ City Life</button>
        </div>
    `;
    appendMessage('bot', quizHtml, 'quiz');
}

// Quiz answer handler
let quizAnswers = {};

function quizAnswer(btn, answer) {
    // Determine which question based on parent structure
    const quizContainer = btn.parentElement;
    const questionLabel = quizContainer.querySelector('strong:last-of-type')?.textContent || '';

    if (questionLabel.includes('Q1')) {
        quizAnswers.style = answer;
        sendQuickMsg(`Quiz answer: My travel style is ${answer}. Now ask me Q2 about budget.`);
    } else if (questionLabel.includes('Q2')) {
        quizAnswers.budget = answer;
        sendQuickMsg(`Quiz answer: My budget is ${answer}. Now ask me Q3 about travel companions.`);
    } else {
        quizAnswers.companion = answer;
        sendQuickMsg(`Quiz complete! My style: ${quizAnswers.style || 'any'}, budget: ${quizAnswers.budget || 'any'}, with: ${answer}. Recommend my perfect destination!`);
    }
}

// ============================================================
//  SECTION L — TRIP PLANNER WIDGET (Right Sidebar)
// ============================================================
function calculateTrip() {
    const destEl      = document.getElementById('planner-dest');
    const travelersEl = document.getElementById('planner-travelers');
    const dateEl      = document.getElementById('planner-date');
    const resultEl    = document.getElementById('planner-result');

    const destOpt  = destEl.options[destEl.selectedIndex];
    const travelers = parseInt(travelersEl.value) || 1;
    const date     = dateEl.value;

    if (!destEl.value) {
        resultEl.style.display = 'block';
        resultEl.innerHTML = '⚠️ Please select a destination first.';
        return;
    }

    const price    = parseFloat(destOpt.dataset.price);
    const duration = destOpt.dataset.dur;
    const total    = price * travelers;
    const destName = destOpt.textContent.trim();

    let dateStr = '';
    if (date) {
        const d = new Date(date);
        const returnDate = new Date(d);
        const days = parseInt(duration.split(' ')[0]);
        returnDate.setDate(d.getDate() + days);
        dateStr = `<br>📅 <strong>Depart:</strong> ${d.toDateString()}<br>📅 <strong>Return:</strong> ${returnDate.toDateString()}`;
    }

    resultEl.style.display = 'block';
    resultEl.innerHTML = `
        <strong>${destName}</strong><br>
        👥 ${travelers} traveler(s) × ₹${price.toLocaleString('en-IN')}<br>
        ⏱ ${duration}${dateStr}<br>
        <div style="font-size:20px;font-weight:800;color:var(--accent);margin-top:8px;">
            Total: ₹${total.toLocaleString('en-IN')}
        </div>
        <a href="packages.php" style="display:block;margin-top:10px;background:var(--accent);color:white;text-align:center;padding:8px;border-radius:20px;font-size:12px;font-weight:700;text-decoration:none;">
            Book Now →
        </a>
    `;

    // Also send to chat
    sendQuickMsg(`Calculate trip cost for ${travelers} travelers to ${destName.replace(/^[^\s]+\s/,'')}`);
}

// ============================================================
//  SECTION M — TRAVEL TIPS ROTATOR
// ============================================================
const travelTips = [
    'Book flights 6–8 weeks in advance for the best prices. Use price tracking alerts on Google Flights.',
    'Always carry a printed copy of your passport and visa — digital copies can fail when you need them most.',
    'Travel insurance is non-negotiable. A single medical emergency abroad can cost more than your entire trip.',
    'Learn 5 basic phrases in the local language — hello, thank you, please, sorry, and where is...? It opens doors!',
    'Arrive at airports 3 hours early for international flights. Security lines can be unpredictable.',
    'Use a VPN when connecting to hotel or airport WiFi to protect your banking and personal data.',
    'Pack a universal power adapter, extra memory cards, and a portable power bank for every trip.',
    'Notify your bank before international travel to avoid your card being blocked for suspicious activity.',
    'The best street food is always found away from tourist hotspots — follow the locals!',
    'Take photos of your luggage before checking it in. It helps enormously if bags are lost or delayed.',
    'Overwater villas in Maldives are cheaper when booked directly with the resort than through agents.',
    'Cherry blossom season in Tokyo (late March – early April) sells out hotels 6 months in advance!',
    'In Dubai, always carry cash for small shops and taxis — not everywhere accepts cards.',
    'Santorini is best explored by renting a quad bike or ATV — roads are too narrow for taxis.',
    'Singapore\'s Hawker Centers offer some of the world\'s best food for under ₹200 per meal!',
];

let currentTipIndex = 0;

function rotateTip() {
    currentTipIndex = (currentTipIndex + 1) % travelTips.length;
    const tipEl = document.getElementById('travel-tip-text');
    if (tipEl) {
        tipEl.style.opacity = '0';
        tipEl.style.transition = 'opacity 0.3s';
        setTimeout(() => {
            tipEl.textContent = travelTips[currentTipIndex];
            tipEl.style.opacity = '1';
        }, 300);
    }
}

// Auto-rotate tips every 12 seconds
setInterval(rotateTip, 12000);

// ============================================================
//  SECTION N — EXPORT CHAT
// ============================================================
function exportChat() {
    if (chatHistory.length === 0) {
        showToast('No new messages to export in this session.');
        return;
    }

    let content = '=== InteleTour AI Chat Export ===\n';
    content    += `Date: ${new Date().toDateString()}\n`;
    content    += `Total Messages: ${chatHistory.length}\n\n`;

    chatHistory.forEach((m, i) => {
        const label = m.sender === 'user' ? 'You' : 'AI Assistant';
        // Strip HTML tags for clean export
        const cleanText = m.text.replace(/<[^>]+>/g, '').replace(/\s+/g, ' ').trim();
        content += `[${m.time}] ${label}:\n${cleanText}\n\n`;
    });

    content += '=== End of Chat ===\n© InteleTour AI Assistant';

    const blob = new Blob([content], { type: 'text/plain' });
    const url  = URL.createObjectURL(blob);
    const link = document.createElement('a');
    link.href     = url;
    link.download = `InteleTour_Chat_${Date.now()}.txt`;
    link.click();
    URL.revokeObjectURL(url);
    showToast('✅ Chat exported successfully!');
}

// ============================================================
//  SECTION O — FULLSCREEN TOGGLE
// ============================================================
function toggleFullscreen() {
    const el = document.querySelector('.chat-main');
    if (!document.fullscreenElement) {
        el.requestFullscreen().catch(() => {
            showToast('Fullscreen not supported on this browser.');
        });
    } else {
        document.exitFullscreen();
    }
}

// ============================================================
//  SECTION P — TOAST NOTIFICATION
// ============================================================
function showToast(message, type = 'info') {
    const existing = document.getElementById('ai-toast');
    if (existing) existing.remove();

    const colors = {
        info    : 'var(--dark)',
        success : '#155724',
        error   : '#721c24',
        warning : '#856404',
    };

    const toast = document.createElement('div');
    toast.id = 'ai-toast';
    toast.textContent = message;
    toast.style.cssText = `
        position: fixed;
        bottom: 28px;
        left: 50%;
        transform: translateX(-50%);
        background: ${colors[type] || colors.info};
        color: white;
        padding: 13px 28px;
        border-radius: 30px;
        font-size: 14px;
        font-weight: 600;
        box-shadow: 0 8px 28px rgba(0,0,0,0.22);
        z-index: 99999;
        animation: fadeIn 0.3s ease;
        max-width: 90vw;
        text-align: center;
    `;
    document.body.appendChild(toast);
    setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transition = 'opacity 0.4s ease';
        setTimeout(() => toast.remove(), 400);
    }, 3000);
}

// ============================================================
//  SECTION Q — KEYBOARD SHORTCUTS
// ============================================================
document.addEventListener('keydown', function (e) {
    // Enter to send
    if (e.key === 'Enter' && document.activeElement === inputField) {
        e.preventDefault();
        sendMessage();
        return;
    }
    // Escape to clear input
    if (e.key === 'Escape' && document.activeElement === inputField) {
        inputField.value = '';
        return;
    }
    // Ctrl+L or Cmd+L = focus input
    if ((e.ctrlKey || e.metaKey) && e.key === 'l') {
        e.preventDefault();
        inputField.focus();
        return;
    }
    // Ctrl+E = export chat
    if ((e.ctrlKey || e.metaKey) && e.key === 'e') {
        e.preventDefault();
        exportChat();
        return;
    }
});

// ============================================================
//  SECTION R — INPUT CHARACTER COUNTER + VALIDATION
// ============================================================
inputField.addEventListener('input', function () {
    const len   = this.value.length;
    const max   = 500;
    const hint  = document.querySelector('.input-hint');

    if (len > max - 50 && hint) {
        hint.textContent = `${max - len} characters remaining`;
        hint.style.color = len > max - 10 ? 'var(--danger)' : 'var(--warning)';
    } else if (hint) {
        hint.innerHTML = '💡 Try: <em>"Plan a Bali trip for 2"</em> · <em>"Compare Paris and Tokyo"</em> · <em>"Cost for 4 travelers to Dubai"</em>';
        hint.style.color = 'var(--gray)';
    }
});

// ============================================================
//  SECTION S — AUTO SUGGESTIONS (Dropdown while typing)
// ============================================================
const suggestions = [
    'Tell me about Bali',
    'Tell me about Paris',
    'Tell me about Tokyo',
    'Tell me about Dubai',
    'Tell me about Maldives',
    'Tell me about Singapore',
    'Tell me about London',
    'Tell me about Rome',
    'Tell me about Phuket',
    'Tell me about New York',
    'Tell me about Sydney',
    'Tell me about Santorini',
    'Best budget destinations',
    'Best luxury packages',
    'Best honeymoon destinations',
    'Best family destinations',
    'Best adventure destinations',
    'Best time to visit Bali',
    'Best time to visit Japan',
    'Calculate cost for 2 travelers',
    'Calculate cost for 4 travelers',
    'Calculate cost for 6 travelers',
    'Plan a trip to Bali',
    'Plan a trip to Tokyo',
    'Plan a trip to Paris',
    'Compare Bali and Dubai',
    'Compare Paris and Tokyo',
    'Visa requirements for Paris',
    'Visa requirements for Dubai',
    'What to pack for Bali',
    'Local food in Tokyo',
    'Local food in Paris',
    'How to book a package',
    'Cancellation policy',
    'Payment methods',
    'Group travel discounts',
    'Solo travel tips',
    'Show all packages',
];

// Create autocomplete dropdown
const suggDropdown = document.createElement('div');
suggDropdown.id = 'sugg-dropdown';
suggDropdown.style.cssText = `
    position: absolute;
    background: white;
    border: 1.5px solid #dde;
    border-radius: 12px;
    box-shadow: 0 8px 28px rgba(0,0,0,0.14);
    z-index: 9999;
    max-height: 220px;
    overflow-y: auto;
    display: none;
    min-width: 300px;
`;
document.querySelector('.chat-input-row').style.position = 'relative';
document.querySelector('.chat-input-row').appendChild(suggDropdown);

inputField.addEventListener('input', function () {
    const q = this.value.toLowerCase().trim();
    if (q.length < 2) { suggDropdown.style.display = 'none'; return; }

    const matches = suggestions.filter(s => s.toLowerCase().includes(q)).slice(0, 6);
    if (matches.length === 0) { suggDropdown.style.display = 'none'; return; }

    suggDropdown.innerHTML = matches.map(s => `
        <div onclick="selectSuggestion('${s.replace(/'/g, "\\'")}')"
            style="padding:10px 16px;cursor:pointer;font-size:13px;color:var(--dark);transition:background 0.2s;border-bottom:1px solid #f5f5f5;"
            onmouseover="this.style.background='var(--light)'"
            onmouseout="this.style.background='white'">
            🔍 ${s}
        </div>
    `).join('');

    suggDropdown.style.display = 'block';
    suggDropdown.style.bottom  = '56px';
    suggDropdown.style.left    = '0';
});

function selectSuggestion(val) {
    inputField.value = val;
    suggDropdown.style.display = 'none';
    inputField.focus();
}

document.addEventListener('click', function (e) {
    if (e.target !== inputField) {
        suggDropdown.style.display = 'none';
    }
});

// ============================================================
//  SECTION T — MOOD DETECTION (shows emoji reaction)
// ============================================================
function detectUserMood(text) {
    const lower = text.toLowerCase();
    if (/(excited|amazing|can\'t wait|so happy|love|awesome|fantastic)/.test(lower)) return '😄';
    if (/(worried|nervous|anxious|scared|afraid|not sure)/.test(lower))              return '😟';
    if (/(angry|upset|disappointed|bad|terrible|worst)/.test(lower))                return '😤';
    if (/(confused|don\'t understand|not clear|what do you mean)/.test(lower))       return '🤔';
    if (/(sad|unhappy|depressed|miss|lonely)/.test(lower))                           return '😢';
    return null;
}

// ============================================================
//  SECTION U — REAL-TIME CLOCK IN HEADER
// ============================================================
function updateHeaderTime() {
    const headerSpan = document.querySelector('.chat-header-info span');
    if (headerSpan) {
        const now  = new Date();
        const time = now.toLocaleTimeString('en-IN', { hour: '2-digit', minute: '2-digit' });
        const day  = now.toLocaleDateString('en-IN', { weekday: 'short', day: 'numeric', month: 'short' });
        headerSpan.textContent = `● Online · ${day} · ${time} IST`;
    }
}

setInterval(updateHeaderTime, 30000);
updateHeaderTime();

// ============================================================
//  SECTION V — SCROLL-TO-BOTTOM BUTTON (appears on scroll up)
// ============================================================
const scrollBtn = document.createElement('button');
scrollBtn.innerHTML = '⬇️';
scrollBtn.title     = 'Scroll to latest';
scrollBtn.style.cssText = `
    position: absolute;
    bottom: 180px;
    right: 20px;
    width: 38px;
    height: 38px;
    border-radius: 50%;
    background: var(--primary);
    color: white;
    border: none;
    font-size: 14px;
    cursor: pointer;
    box-shadow: 0 4px 14px rgba(0,119,182,0.4);
    display: none;
    z-index: 50;
    transition: all 0.3s;
`;
scrollBtn.onclick = scrollToBottom;
document.querySelector('.chat-main').style.position = 'relative';
document.querySelector('.chat-main').appendChild(scrollBtn);

chatBox.addEventListener('scroll', function () {
    const atBottom = chatBox.scrollHeight - chatBox.scrollTop - chatBox.clientHeight < 60;
    scrollBtn.style.display = atBottom ? 'none' : 'block';
});

// ============================================================
//  SECTION W — WELCOME GREETING + PROACTIVE MESSAGE
// ============================================================
window.addEventListener('load', function () {
    scrollToBottom();

    // Proactive greeting after 4 seconds if no previous chat
    const hasPreviousChat = <?= $msg_count > 0 ? 'true' : 'false' ?>;
    if (!hasPreviousChat) {
        setTimeout(() => {
            appendMessage('bot',
                '💡 <strong>Quick Start Tip!</strong> Here are some popular things to ask me:<br><br>' +
                '• <em>"Tell me about Bali"</em><br>' +
                '• <em>"Best destinations for honeymoon"</em><br>' +
                '• <em>"Calculate trip cost for 2 travelers"</em><br>' +
                '• <em>"Plan a 7-day Tokyo itinerary"</em><br>' +
                '• <em>"Compare Bali and Singapore"</em>',
                'proactive'
            );
        }, 4000);
    }
});

// ============================================================
//  SECTION X — PREVENT DOUBLE SUBMIT & INPUT LOCK
// ============================================================
sendBtn.addEventListener('click', function(e) {
    e.preventDefault();
    sendMessage();
});

// Visual feedback while sending
function lockInput() {
    inputField.disabled = true;
    inputField.placeholder = 'AI is thinking... ✨';
}

function unlockInput() {
    inputField.disabled = false;
    inputField.placeholder = 'Ask me anything about travel... 🌍';
    inputField.focus();
}

// Override showTyping/hideTyping to also lock input
const _showTyping = showTyping;
const _hideTyping = hideTyping;
window.showTyping = function() { _showTyping(); lockInput(); };
window.hideTyping = function() { _hideTyping(); unlockInput(); };

// ============================================================
//  SECTION Y — COPY MESSAGE ON DOUBLE CLICK
// ============================================================
chatBox.addEventListener('dblclick', function(e) {
    const bubble = e.target.closest('.msg-bubble');
    if (!bubble) return;
    const text = bubble.innerText;
    navigator.clipboard.writeText(text).then(() => {
        showToast('📋 Message copied to clipboard!', 'success');
    }).catch(() => {
        showToast('Could not copy — try manually selecting the text.');
    });
});

// ============================================================
//  SECTION Z — ACCESSIBILITY: ARIA LIVE REGION
// ============================================================
const ariaLive = document.createElement('div');
ariaLive.setAttribute('aria-live', 'polite');
ariaLive.setAttribute('aria-atomic', 'true');
ariaLive.style.cssText = 'position:absolute;width:1px;height:1px;overflow:hidden;clip:rect(0,0,0,0);';
document.body.appendChild(ariaLive);

function announceToScreenReader(text) {
    ariaLive.textContent = '';
    setTimeout(() => { ariaLive.textContent = text.replace(/<[^>]+>/g, ''); }, 100);
}

// Override appendMessage to also announce bot replies
const _appendMessage = appendMessage;
window.appendMessage = function(sender, text, intent) {
    _appendMessage(sender, text, intent);
    if (sender === 'bot') {
        announceToScreenReader('AI reply: ' + text.replace(/<[^>]+>/g, '').substring(0, 100));
    }
};

// ============================================================
//  END OF AI ASSISTANT JAVASCRIPT ENGINE
// ============================================================
</script>
</body>
</html>
