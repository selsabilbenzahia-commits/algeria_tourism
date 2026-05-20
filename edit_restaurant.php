<?php
session_start();
include 'db.php';
include 'lang.php'; // تضمين ملف اللغة

if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit();
}

// تحديد اللغة والاتجاه
$lang = isset($_SESSION['lang']) ? $_SESSION['lang'] : 'en';
$dir = ($lang == 'ar') ? 'rtl' : 'ltr';

$id = intval($_GET['id']);
// جلب بيانات المطعم الحالية (كودك الأصلي كما هو)
$res = mysqli_query($conn, "SELECT * FROM restaurants WHERE id = $id");
$data = mysqli_fetch_assoc($res);

// جلب الولايات للقائمة المنسدلة
$wilayas = mysqli_query($conn, "SELECT * FROM wilayas");

if (isset($_POST['update'])) {
    $name = mysqli_real_escape_string($conn, $_POST['name_en']);
    $name_ar = mysqli_real_escape_string($conn, $_POST['name_ar']); 
    $wilaya_id = $_POST['wilaya_id'];
    $lat = $_POST['lat'];
    $lng = $_POST['lng'];
    
    // منطق الصورة باستخدام مجلد images (كودك الأصلي كما هو)
    if (!empty($_FILES['image']['name'])) {
        $image_name = time() . '_' . $_FILES['image']['name'];
        $target_path = "images/" . $image_name;
        
        if (move_uploaded_file($_FILES['image']['tmp_name'], $target_path)) {
            $final_image = $target_path;
        } else {
            $final_image = $data['image'];
        }
    } else {
        $final_image = $data['image'];
    }

    $sql = "UPDATE restaurants SET
            name_en='$name',
            name_ar='$name_ar',
            wilaya_id='$wilaya_id',
            lat='$lat',
            lng='$lng',
            image='$final_image'
            WHERE id=$id";

    if (mysqli_query($conn, $sql)) {
        header("Location: manage_restaurants.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="<?php echo $lang; ?>" dir="<?php echo $dir; ?>">
<head>
    <meta charset="UTF-8">
    <title><?php echo $texts[$lang]['edit_restaurant_title']; ?> | Control Panel</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;700&display=swap" rel="stylesheet">
    <style>
        :root { --gold: #c5a059; --dark: #1e293b; --light: #f8fafc; }
        * { margin:0; padding:0; box-sizing:border-box; font-family:'Cairo',sans-serif; }
        body { display: flex; background: var(--light); }
        
        /* السايدبار حسب اللغة (كودك الأصلي) */
        .sidebar { width: 260px; height: 100vh; background: var(--dark); color: white; padding: 20px; position: fixed; <?php echo ($lang == 'ar' ? 'right: 0;' : 'left: 0;'); ?> }
        .sidebar h2 { text-align: center; color: var(--gold); margin-bottom: 30px; border-bottom: 1px solid #334155; padding-bottom: 10px; }
        .sidebar ul { list-style: none; }
        .sidebar ul li { padding: 12px; border-radius: 8px; margin-bottom: 5px; }
        .sidebar ul li a { color: white; text-decoration: none; display: flex; align-items: center; }
        .sidebar ul li i { <?php echo ($lang == 'ar' ? 'margin-left: 10px;' : 'margin-right: 10px;'); ?> color: var(--gold); }

        /* المحتوى حسب اللغة */
        .main-content { <?php echo ($lang == 'ar' ? 'margin-right: 260px;' : 'margin-left: 260px;'); ?> width: calc(100% - 260px); padding: 40px; }
        
        .form-container { background: white; padding: 40px; border-radius: 15px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); max-width: 800px; margin: auto; }
        
        /* زر الرجوع المضاف */
        .back-nav { margin-bottom: 20px; text-align: <?php echo ($lang == 'ar' ? 'right' : 'left'); ?>; }
        .btn-back { color: #64748b; text-decoration: none; font-weight: bold; display: flex; align-items: center; gap: 8px; font-size: 15px; }
        .btn-back:hover { color: var(--gold); }

        .form-container h1 { font-size: 24px; color: var(--dark); margin-bottom: 30px; text-align: center; border-bottom: 2px solid var(--gold); display: inline-block; padding-bottom: 5px; width: 100%; }
        
        .form-group { margin-bottom: 20px; text-align: <?php echo ($lang == 'ar' ? 'right' : 'left'); ?>; }
        label { display: block; margin-bottom: 8px; font-weight: bold; color: #475569; }
        input[type="text"], select, input[type="file"] { width: 100%; padding: 12px; border: 1px solid #e2e8f0; border-radius: 8px; font-size: 14px; }
        
        .current-img-preview { margin: 10px 0; border: 2px solid var(--gold); border-radius: 8px; width: 120px; height: 120px; object-fit: cover; }
        
        /* تنسيق الأزرار لتكون متساوية (قدقد) */
        .form-actions { display: flex; gap: 15px; margin-top: 30px; }
        .btn-update { flex: 1; background: var(--gold); color: white; border: none; padding: 15px; border-radius: 8px; cursor: pointer; font-weight: bold; font-size: 16px; transition: 0.3s; }
        .btn-reset { flex: 1; background: #e2e8f0; color: #475569; border: none; padding: 15px; border-radius: 8px; cursor: pointer; font-weight: bold; font-size: 16px; transition: 0.3s; }
        .btn-update:hover { background: #b08d4a; }
        .btn-reset:hover { background: #cbd5e1; }
    </style>
</head>
<body>
    <div class="sidebar">
       <h2><?= $texts[$lang]['tourism_mgmt'] ?></h2>
        <ul>
            <li><a href="admin_dashboard.php"><i class="fas fa-home"></i> <?= $texts[$lang]['home'] ?></a></li>
            <li><a href="manage_wilayas.php"><i class="fas fa-map"></i> <?= $texts[$lang]['wilaya_mgmt'] ?></a></li>
            <li><a href="manage_attractions.php"><i class="fas fa-camera"></i> <?= $texts[$lang]['attraction_mgmt'] ?></a></li>
            <li><a href="manage_restaurants.php"><i class="fas fa-utensils"></i> <?= $texts[$lang]['restaurant_mgmt'] ?></a></li>
            <li><a href="manage_hotels.php"><i class="fas fa-bed"></i> <?= $texts[$lang]['hotel_mgmt'] ?></a></li>
            <li style="margin-top: 20px; border-top: 1px solid #334155; padding-top: 20px;">
                <a href="logout.php"><i class="fas fa-sign-out-alt"></i> <?= $texts[$lang]['logout'] ?></a>
            </li>
        </ul>
    </div>
    <div class="main-content">
        <div class="form-container">
            <!-- زر الرجوع -->
            <div class="back-nav">
                <a href="manage_restaurants.php" class="btn-back">
                    <i class="fas <?php echo ($lang == 'ar' ? 'fa-arrow-right' : 'fa-arrow-left'); ?>"></i>
                    <?php echo $texts[$lang]['back_to_mgmt_res']; ?>
                </a>
            </div>

            <center><h1><?php echo $texts[$lang]['edit_restaurant_title']; ?></h1></center>
            
            <form method="POST" enctype="multipart/form-data">
                <div style="display: flex; gap: 20px;">
                    <div class="form-group" style="flex: 1;">
                        <label>Restaurant Name (English)</label>
                        <input type="text" name="name_en" value="<?php echo $data['name_en']; ?>" required>
                    </div>
                    <div class="form-group" style="flex: 1;">
                        <label><?php echo $texts[$lang]['restaurant_name_ar_label']; ?></label>
                        <input type="text" name="name_ar" value="<?php echo $data['name_ar']; ?>" required>
                    </div>
                </div>

                <div class="form-group">
                    <label><?php echo $texts[$lang]['col_wilaya']; ?></label>
                    <select name="wilaya_id" required>
                        <?php while($w = mysqli_fetch_assoc($wilayas)): ?>
                            <?php $w_name = ($lang == 'ar') ? $w['name_ar'] : $w['name_en']; ?>
                            <option value="<?php echo $w['id']; ?>" <?php if($w['id'] == $data['wilaya_id']) echo 'selected'; ?>>
                                <?php echo $w_name; ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label><?php echo $texts[$lang]['current_photo_label']; ?></label>
                    <!-- حافظت على نفس مسار الصورة في كودك -->
                    <img src="img/restaurants/<?php echo $data['image']; ?>" class="current-img-preview" onerror="this.src='img/default.jpg'">
                    <br>
                    <label><?php echo $texts[$lang]['change_photo_label']; ?></label>
                    <input type="file" name="image" accept="image/*">
                </div>

                <div style="display: flex; gap: 20px;">
                    <div class="form-group" style="flex: 1;">
                        <label><?php echo $texts[$lang]['latitude']; ?></label>
                        <input type="text" name="lat" value="<?php echo $data['lat']; ?>">
                    </div>
                    <div class="form-group" style="flex: 1;">
                        <label><?php echo $texts[$lang]['longitude']; ?></label>
                        <input type="text" name="lng" value="<?php echo $data['lng']; ?>">
                    </div>
                </div>

                <!-- الأزرار السفلية بتنسيق متساوي (قدقد) -->
                <div class="form-actions">
                    <button type="submit" name="update" class="btn-update">
                        <i class="fas fa-save"></i> <?php echo $texts[$lang]['btn_save_restaurant']; ?>
                    </button>
                    <button type="reset" class="btn-reset">
                        <i class="fas fa-undo"></i> <?php echo $texts[$lang]['btn_reset']; ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>