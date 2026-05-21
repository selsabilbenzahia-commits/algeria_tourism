<?php
session_start();
include 'db.php';
include 'lang.php'; 

if (!isset($_SESSION['admin'])) { header("Location: login.php"); exit(); }

$lang = isset($_SESSION['lang']) ? $_SESSION['lang'] : 'en';
$dir = ($lang == 'ar') ? 'rtl' : 'ltr';

$id = intval($_GET['id']);

$query = "SELECT * FROM wilayas WHERE id = $id";
$res = mysqli_query($conn, $query);
$data = mysqli_fetch_assoc($res);

if (isset($_POST['update'])) {
    $name_en = mysqli_real_escape_string($conn, $_POST['name_en']);
    $name_ar = mysqli_real_escape_string($conn, $_POST['name_ar']);
    $desc_en = mysqli_real_escape_string($conn, $_POST['description_en']);
    $desc_ar = mysqli_real_escape_string($conn, $_POST['description_ar']);
    $lat = mysqli_real_escape_string($conn, $_POST['lat']); 
    $lng = mysqli_real_escape_string($conn, $_POST['lng']); 

    $image_sq = "";
    if (!empty($_FILES['image']['name'])) {
        $file_name = time() . "_" . $_FILES['image']['name'];
        $image_path = "img/wilayas/" . $file_name;
        if (move_uploaded_file($_FILES['image']['tmp_name'], $image_path)) {
            $image_sq = ", image='$file_name'";
        }
    }

    $sql = "UPDATE wilayas SET 
            name_en='$name_en', 
            name_ar='$name_ar', 
            description_en='$desc_en',
            description_ar='$desc_ar',
            lat='$lat',
            lng='$lng' 
            $image_sq
            WHERE id=$id";
            
    if (mysqli_query($conn, $sql)) { 
        header("Location: manage_wilayas.php"); 
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="<?= $lang ?>" dir="<?= $dir ?>">
<head>
    <meta charset="UTF-8">
    <title><?= $texts[$lang]['edit_wilaya_header'] ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;700&display=swap" rel="stylesheet">
    <style>
        :root { --gold: #c5a059; --dark: #1e293b; --light: #f8fafc; --gray: #64748b; }
        * { margin:0; padding:0; box-sizing:border-box; font-family:'Cairo',sans-serif; }
        body { display: flex; background: var(--light); min-height: 100vh; }
        
        .sidebar { 
            width: 260px; height: 100vh; background: var(--dark); color: white; padding: 20px; position: fixed; 
            <?= ($lang == 'ar' ? 'right: 0;' : 'left: 0;') ?> 
        }
        .sidebar h2 { text-align: center; color: var(--gold); margin-bottom: 25px; font-size: 18px; border-bottom: 1px solid #334155; padding-bottom: 10px; }
        .sidebar ul li { padding: 12px; list-style: none; border-radius: 8px; transition: 0.3s; }
        .sidebar ul li:hover { background: #334155; }
        .sidebar ul li a { color: white; text-decoration: none; display: flex; align-items: center; gap: 10px; }
        .sidebar ul li i { color: var(--gold); width: 20px; text-align: center; }

        .main-content { 
            <?= ($lang == 'ar' ? 'margin-right: 260px;' : 'margin-left: 260px;') ?> 
            width: calc(100% - 260px); padding: 40px; 
        }
        
        .form-container { background: white; padding: 35px; border-radius: 15px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); max-width: 900px; margin: auto; }
        
        .back-nav { margin-bottom: 20px; display: flex; justify-content: flex-start; }
        .btn-back { color: var(--gray); text-decoration: none; font-weight: bold; display: flex; align-items: center; gap: 8px; transition: 0.3s; font-size: 15px; }
        .btn-back:hover { color: var(--gold); transform: translateX(<?= ($lang == 'ar' ? '5px' : '-5px') ?>); }

        h1 { font-size: 26px; color: var(--dark); margin-bottom: 35px; text-align: center; position: relative; padding-bottom: 12px; }
        h1::after { content: ''; width: 60px; height: 4px; background: var(--gold); position: absolute; bottom: 0; left: 50%; transform: translateX(-50%); border-radius: 2px; }
        
        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 25px; }
        .full-row { grid-column: span 2; }
        
        .form-group { margin-bottom: 5px; text-align: <?= ($lang == 'ar' ? 'right' : 'left') ?>; }
        label { display: block; margin-bottom: 10px; font-weight: 700; color: var(--dark); font-size: 14px; }
        input, textarea { width: 100%; padding: 12px 15px; border: 1px solid #cbd5e1; border-radius: 10px; outline: none; transition: 0.3s; background: #fff; color: #334155; }
        input:focus, textarea:focus { border-color: var(--gold); box-shadow: 0 0 0 3px rgba(197, 160, 89, 0.1); }
        
        .preview-wrapper { display: flex; align-items: center; gap: 20px; background: #f1f5f9; padding: 15px; border-radius: 12px; border: 1px dashed #cbd5e1; }
        .preview-img { width: 130px; height: 85px; border-radius: 8px; object-fit: cover; border: 3px solid white; box-shadow: 0 3px 10px rgba(0,0,0,0.1); }
        
        .form-actions { display: flex; gap: 20px; margin-top: 40px; padding-top: 20px; border-top: 1px solid #f1f5f9; }
        
        .btn-action { 
            flex: 1; display: flex; align-items: center; justify-content: center; gap: 10px;
            padding: 15px; border-radius: 12px; font-weight: bold; font-size: 16px; cursor: pointer; transition: 0.3s; border: none;
        }
        .btn-save { background: var(--gold); color: white; box-shadow: 0 4px 12px rgba(197, 160, 89, 0.2); }
        .btn-save:hover { background: var(--dark); transform: translateY(-2px); }
        
        .btn-undo { background: #e2e8f0; color: #475569; }
        .btn-undo:hover { background: #cbd5e1; transform: translateY(-2px); }
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
            <div class="back-nav">
                <a href="manage_wilayas.php" class="btn-back">
                    <i class="fas <?= ($lang == 'ar' ? 'fa-arrow-right' : 'fa-arrow-left') ?>"></i>
                    <?= $texts[$lang]['back_to_mgmt'] ?>
                </a>
            </div>

            <h1><?= $texts[$lang]['edit_wilaya_header'] ?></h1>
            
            <form method="POST" enctype="multipart/form-data">
                <div class="form-grid">
                    <div class="form-group">
                        <label><?= $texts[$lang]['col_wilaya'] ?> (EN)</label>
                        <input type="text" name="name_en" value="<?= htmlspecialchars($data['name_en']) ?>" required>
                    </div>
                    <div class="form-group">
                        <label><?= $texts[$lang]['col_wilaya'] ?> (AR)</label>
                        <input type="text" name="name_ar" value="<?= htmlspecialchars($data['name_ar']) ?>" required>
                    </div>

                    <div class="form-group full-row">
                        <label><?= $texts[$lang]['current_photo_label'] ?></label>
                        <div class="preview-wrapper">
                            <?php $img = !empty($data['image']) ? $data['image'] : 'default_wilaya.jpg'; ?>
                            <img src="img/wilayas/<?= $img ?>" class="preview-img">
                            <div style="flex:1;">
                                <input type="file" name="image" accept="image/*">
                                <small style="display:block; color:var(--gray); margin-top:8px; font-size:12px;"><?= $texts[$lang]['change_photo_label'] ?></small>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label><?= $texts[$lang]['col_lat'] ?></label>
                        <input type="text" name="lat" value="<?= htmlspecialchars($data['lat']) ?>">
                    </div>
                    <div class="form-group">
                        <label><?= $texts[$lang]['col_lng'] ?></label>
                        <input type="text" name="lng" value="<?= htmlspecialchars($data['lng']) ?>">
                    </div>

                    <div class="form-group">
                        <label><?= $texts[$lang]['col_desc'] ?> (EN)</label>
                        <textarea name="description_en" rows="6"><?= htmlspecialchars($data['description_en']) ?></textarea>
                    </div>
                    <div class="form-group">
                        <label><?= $texts[$lang]['col_desc'] ?> (AR)</label>
                        <textarea name="description_ar" rows="6"><?= htmlspecialchars($data['description_ar']) ?></textarea>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" name="update" class="btn-action btn-save">
                        <i class="fas fa-save"></i> <?= $texts[$lang]['btn_update'] ?>
                    </button>
                    <button type="reset" class="btn-action btn-undo">
                        <i class="fas fa-undo"></i> <?= $texts[$lang]['btn_reset'] ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>