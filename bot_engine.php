<?php
// إعدادات الاتصال بقاعدة البيانات (تأكدي من مطابقتها لجهازك)
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

$query = $_GET['query'] ?? '';
$lang = $_GET['lang'] ?? 'ar';
$response = ["reply" => "", "suggestions" => []];

// 1. البحث في جدول الولايات (Wilayas)
$stmt = $pdo->prepare("SELECT * FROM wilayas WHERE name_ar LIKE ? OR name_en LIKE ? LIMIT 1");
$stmt->execute(["%$query%", "%$query%"]);
$wilaya = $stmt->fetch();

if ($wilaya) {
    $w_id = $wilaya['id'];
    $name = ($lang == 'ar') ? $wilaya['name_ar'] : $wilaya['name_en'];
    $desc = ($lang == 'ar') ? ($wilaya['description_ar'] ?? "معلومات قريباً") : ($wilaya['description_en'] ?? "Info coming soon");
    
    $response['reply'] = "<strong>$name:</strong> $desc";

    // جلب أهم المعالم (Attractions) لهذه الولاية
    $st2 = $pdo->prepare("SELECT name_ar, name_en FROM attractions WHERE wilaya_id = ? LIMIT 3");
    $st2->execute([$w_id]);
    while($row = $st2->fetch()) {
        $response['suggestions'][] = ($lang == 'ar') ? $row['name_ar'] : $row['name_en'];
    }
} else {
    // 2. البحث في جدول المعالم (Attractions)
    $stmt = $pdo->prepare("SELECT * FROM attractions WHERE name_ar LIKE ? OR name_en LIKE ? LIMIT 1");
    $stmt->execute(["%$query%", "%$query%"]);
    $attr = $stmt->fetch();

    if ($attr) {
        $name = ($lang == 'ar') ? $attr['name_ar'] : $attr['name_en'];
        $desc = ($lang == 'ar') ? $attr['description_ar'] : $attr['description_en'];
        $response['reply'] = "<strong>$name:</strong> $desc";
    } else {
        // إذا لم يجد شيئاً
        $response['reply'] = ($lang == 'ar') ? "لم أجد معلومات دقيقة حول '$query'. جرب كتابة اسم ولاية (مثل وهران أو باتنة)." : "I couldn't find specific info for '$query'. Try a wilaya name.";
        $response['suggestions'] = ["وهران", "باتنة", "بجاية"];
    }
}

header('Content-Type: application/json');
echo json_encode($response);