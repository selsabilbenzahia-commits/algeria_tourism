<?php
session_start();
include 'db.php';
include 'lang.php'; 

if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit();
}

$lang = isset($_SESSION['lang']) ? $_SESSION['lang'] : 'en';
$dir = ($lang == 'ar') ? 'rtl' : 'ltr';

$id = intval($_GET['id']);
$res = mysqli_query($conn, "SELECT * FROM hotels WHERE id = $id");
$data = mysqli_fetch_assoc($res);

$wilayas = mysqli_query($conn, "SELECT * FROM wilayas");

if (isset($_POST['update'])) {
    $name = mysqli_real_escape_string($conn, $_POST['name_en']);
    $name_ar = mysqli_real_escape_string($conn, $_POST['name_ar']); 
    $wilaya_id = $_POST['wilaya_id'];
    $lat = $_POST['lat'];
    $lng = $_POST['lng'];
    
    if (!empty($_FILES['image']['name'])) {
        $image_name = time() . '_' . basename($_FILES['image']['name']);
        $target_path = "img/hotels/" . $image_name; 
        
        if (move_uploaded_file($_FILES['image']['tmp_name'], $target_path)) {
            $final_image = $image_name; 
        } else { 
            $final_image = $data['image']; 
        }
    } else { 
        $final_image = $data['image']; 
    }

    $sql = "UPDATE hotels SET 
            name_en='$name', 
            name_ar='$name_ar', 
            wilaya_id='$wilaya_id', 
            lat='$lat', 
            lng='$lng', 
            image='$final_image' 
            WHERE id=$id";

    if (mysqli_query($conn, $sql)) {
        header("Location: manage_hotels.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="<?php echo $lang; ?>" dir="<?php echo $dir; ?>">
<head>
    <meta charset="UTF-8">
    <title><?php echo $texts[$lang]['edit_hotel_title']; ?> | Control Panel</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;700&display=swap" rel="stylesheet">
    <style>
        :root { --gold: #c5a059; --dark: #1e293b; --light: #f8fafc; }
        * { margin:0; padding:0; box-sizing:border-box; font-family:'Cairo',sans-serif; }
        body { display: flex; background: var(--light); }

        /* السايدبار حسب اللغة */
        .sidebar { width: 260px; height: 100vh; background: var(--dark); color: white; padding: 20px; position: fixed; <?php echo ($lang == 'ar' ? 'right: 0;' : 'left: 0;'); ?> }
        .sidebar h2 { text-align: center; color: var(--gold); margin-bottom: 30px; border-bottom: 1px solid #334155; padding-bottom: 10px; }
        .sidebar ul { list-style: none; }
        .sidebar ul li { padding: 12px; border-radius: 8px; margin-bottom: 5px; }
        .sidebar ul li a { color: white; text-decoration: none; display: flex; align-items: center; }
        .sidebar ul li i { <?php echo ($lang == 'ar' ? 'margin-left: 10px;' : 'margin-right: 10px;'); ?> color: var(--gold); }

        /* المحتوى حسب اللغة */
        .main-content { <?php echo ($lang == 'ar' ? 'margin-right: 260px;' : 'margin-left: 260px;'); ?> width: calc(100% - 260px); padding: 40px; }
        
        .form-container { background: white; padding: 40px; border-radius: 15px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); max-width: 800px; margin: auto; }
        
        .back-nav { margin-bottom: 20px; text-align: <?php echo ($lang == 'ar' ? 'right' : 'left'); ?>; }
        .btn-back { color: #64748b; text-decoration: none; font-weight: bold; display: flex; align-items: center; gap: 8px; font-size: 15px; }
        .btn-back:hover { color: var(--gold); }

        
        
         .form-container h1 { font-size: 26px; color: var(--dark); margin-bottom: 35px; text-align: center; position: relative; padding-bottom: 12px; }
          .form-container h1::after { content: ''; width: 60px; height: 4px; background: var(--gold); position: absolute; bottom: 0; left: 50%; transform: translateX(-50%); border-radius: 2px; }


        .form-group { margin-bottom: 20px; text-align: <?php echo ($lang == 'ar' ? 'right' : 'left'); ?>; }
        label { display: block; margin-bottom: 8px; font-weight: bold; color: #475569; }
        input[type="text"], select, input[type="file"], input[type="number"] { width: 100%; padding: 12px; border: 1px solid #e2e8f0; border-radius: 8px; font-size: 14px; }
        
        .current-img-preview { margin: 10px 0; border: 2px solid var(--gold); border-radius: 8px; width: 120px; height: 120px; object-fit: cover; }
        
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
            <div class="back-nav">
                <a href="manage_hotels.php" class="btn-back">
                    <i class="fas <?php echo ($lang == 'ar' ? 'fa-arrow-right' : 'fa-arrow-left'); ?>"></i>
                    <?php echo $texts[$lang]['back_to_mgmt_hotel']; ?>
                </a>
            </div>

            <center><h1><?php echo $texts[$lang]['edit_hotel_title']; ?></h1></center>
            
            <form method="POST" enctype="multipart/form-data">
                
                <div style="display: flex; gap: 20px;">
                    <div class="form-group" style="flex: 1;">
                        <label>Hotel Name (English)</label>
                        <input type="text" name="name_en" value="<?php echo $data['name_en']; ?>" required>
                    </div>
                    <div class="form-group" style="flex: 1;">
                        <label><?php echo $texts[$lang]['hotel_name_ar_label']; ?></label>
                        <input type="text" name="name_ar" value="<?php echo $data['name_ar']; ?>" required>
                    </div>
                </div>

                <div class="form-group">
                    <label><?php echo $texts[$lang]['select_wilaya']; ?></label>
                    <select name="wilaya_code" required>
                        <option value=""><?php echo ($lang == 'ar') ? '-- اختر --' : '-- Choose --'; ?></option>
                        <?php
                        $res = mysqli_query($conn, "SELECT code, name_en, name_ar FROM wilayas ORDER BY code ASC");
                        while($row = mysqli_fetch_assoc($res)) {
                            $name = ($lang == 'ar') ? $row['name_ar'] : $row['name_en'];
                            echo "<option value='".$row['code']."'>".$row['code']." - ".$name."</option>";
                        }
                        ?>
                    </select>
                </div>

                <div class="form-group">
                    <label><?php echo $texts[$lang]['current_photo_label']; ?></label>
                    <img src="img/hotels/<?php echo $data['image']; ?>" class="current-img-preview" onerror="this.src='img/default_hotel.jpg'">
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

                <div class="form-actions">
                    <button type="submit" name="update" class="btn-update">
                        <i class="fas fa-save"></i> <?php echo $texts[$lang]['btn_save_hotel']; ?>
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