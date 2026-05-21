<?php
$host = 'localhost';
$db   = 'algeria_tourism';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    echo json_encode(['reply' => 'خطأ في الاتصال بقاعدة البيانات']);
    exit;
}

header('Content-Type: application/json');

$query = $_GET['query'] ?? '';
$query = trim($query);
$lang = $_GET['lang'] ?? 'ar';
$response = ["reply" => "", "suggestions" => []];

if (empty($query)) {
    $response['reply'] = ($lang == 'ar') ? "مرحباً بك! يمكنك كتابة اسم أي ولاية أو معلم سياحي في الجزائر لأعطيك تفاصيل كاملة عنه." : "Hello! You can type the name of any Wilaya or attraction in Algeria to get full details.";
    echo json_encode($response);
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM wilayas WHERE name_ar LIKE ? OR name_en LIKE ? LIMIT 1");
$stmt->execute(["%$query%", "%$query%"]);
$wilaya = $stmt->fetch();

if ($wilaya) {
    $w_id = $wilaya['id'];
    $w_name = ($lang == 'ar') ? $wilaya['name_ar'] : $wilaya['name_en'];
    $w_desc = ($lang == 'ar') ? ($wilaya['description_ar'] ?? "معلومات قريباً") : ($wilaya['description_en'] ?? "Info coming soon");
    
    if ($lang == 'ar') {
        $reply = "📍 <strong>ولاية $w_name:</strong><br>$w_desc<br><br>";
    } else {
        $reply = "📍 <strong>Wilaya of $w_name:</strong><br>$w_desc<br><br>";
    }

    $st_attr = $pdo->prepare("SELECT id, name_ar, name_en FROM attractions WHERE wilaya_id = ? LIMIT 3");
    $st_attr->execute([$w_id]);
    $attrs = $st_attr->fetchAll();
    if (!empty($attrs)) {
        $reply .= ($lang == 'ar') ? "🏛️ <strong>أشهر المعالم السياحية هنا:</strong><br>" : "🏛️ <strong>Famous Attractions here:</strong><br>";
        foreach ($attrs as $attr) {
            $attr_name = ($lang == 'ar') ? $attr['name_ar'] : $attr['name_en'];
            $reply .= "• <a href='attraction_details.php?id={$attr['id']}' style='color:#c5a059; font-weight:bold;'>$attr_name</a><br>";
            $response['suggestions'][] = $attr_name;
        }
        $reply .= "<br>";
    }

    $st_hotels = $pdo->prepare("SELECT id, name_en FROM hotels WHERE wilaya_id = ? LIMIT 2");
    $st_hotels->execute([$w_id]);
    $hotels = $st_hotels->fetchAll();
    if (!empty($hotels)) {
        $reply .= ($lang == 'ar') ? "🏨 <strong>فنادق مقترحة للإقامة:</strong><br>" : "🏨 <strong>Recommended Hotels:</strong><br>";
        foreach ($hotels as $hotel) {
            $reply .= "• " . htmlspecialchars($hotel['name_en']) . "<br>";
        }
        $reply .= "<br>";
    }

    $st_rest = $pdo->prepare("SELECT id, name_en FROM restaurants WHERE wilaya_id = ? LIMIT 2");
    $st_rest->execute([$w_id]);
    $rests = $st_rest->fetchAll();
    if (!empty($rests)) {
        $reply .= ($lang == 'ar') ? "🍽️ <strong>أماكن مميزة لتناول الطعام:</strong><br>" : "🍽️ <strong>Top Restaurants:</strong><br>";
        foreach ($rests as $rest) {
            $reply .= "• " . htmlspecialchars($rest['name_en']) . "<br>";
        }
    }

    $response['reply'] = $reply;

} else {
    $stmt = $pdo->prepare("SELECT a.*, w.name_ar as w_name_ar, w.name_en as w_name_en FROM attractions a JOIN wilayas w ON a.wilaya_id = w.id WHERE a.name_ar LIKE ? OR a.name_en LIKE ? LIMIT 1");
    $stmt->execute(["%$query%", "%$query%"]);
    $attr = $stmt->fetch();

    if ($attr) {
        $attr_name = ($lang == 'ar') ? $attr['name_ar'] : $attr['name_en'];
        $attr_desc = ($lang == 'ar') ? ($attr['description_ar'] ?? "لا يوجد وصف حالياً") : ($attr['description_en'] ?? "No description available");
        $wilaya_belong = ($lang == 'ar') ? $attr['w_name_ar'] : $attr['w_name_en'];

        if ($lang == 'ar') {
            $reply = "🏛️ <strong>المعلم: $attr_name</strong> (يقع في ولاية $wilaya_belong)<br><br>";
            $reply .= "📝 <strong>نبذة عنه:</strong><br>$attr_desc<br><br>";
            $reply .= "🔗 <a href='attraction_details.php?id={$attr['id']}' style='color:#c5a059; font-weight:bold;'>اضغط هنا لزيارة صفحة المعلم بالكامل والتعليق عليه</a>";
        } else {
            $reply = "🏛️ <strong>Attraction: $attr_name</strong> (Located in $wilaya_belong)<br><br>";
            $reply .= "📝 <strong>About:</strong><br>$attr_desc<br><br>";
            $reply .= "🔗 <a href='attraction_details.php?id={$attr['id']}' style='color:#c5a059; font-weight:bold;'>Click here to view full details & comments</a>";
        }

        $response['suggestions'][] = $wilaya_belong;
        $response['reply'] = $reply;

    } else {
        if ($lang == 'ar') {
            $response['reply'] = "🔍 لم أستطع العثور على تفاصيل دقيقة لـ \"<strong>$query</strong>\". حاول كتابة اسم ولاية جزائرية (مثل: بجاية، تيميمون، وهران) أو معلم شهير!";
            $response['suggestions'] = ["بجاية", "الجزائر", "تلمسان", "قسنطينة"];
        } else {
            $response['reply'] = "🔍 Sorry, I couldn't find specific details for \"<strong>$query</strong>\". Try typing an Algerian Wilaya (e.g., Bejaia, Timimoun, Oran) or a famous landmark!";
            $response['suggestions'] = ["Bejaia", "Algiers", "Tlemcen", "Constantine"];
        }
    }
}

echo json_encode($response);
exit;