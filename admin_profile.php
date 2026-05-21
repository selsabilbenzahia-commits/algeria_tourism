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

$admin_user = $_SESSION['admin'];
$res = mysqli_query($conn, "SELECT * FROM admins WHERE username = '$admin_user'");
$data = mysqli_fetch_assoc($res);
$id = $data['id'];

if (isset($_POST['update_profile'])) {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = $_POST['password'];
    
    if (!empty($_FILES['image']['name'])) {
        $image_name = "admin_" . time() . "_" . basename($_FILES['image']['name']);
        $target_path = "img/" . $image_name; 
        
        if (!is_dir("img/")) { mkdir("img/", 0777, true); }

        if (move_uploaded_file($_FILES['image']['tmp_name'], $target_path)) {
            $final_image = $image_name; 
        } else { 
            $final_image = $data['image']; 
        }
    } else { 
        $final_image = $data['image']; 
    }

    if (!empty($password)) {
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);
        $sql = "UPDATE admins SET username='$username', password='$hashed_password', image='$final_image' WHERE id=$id";
    } else {
        $sql = "UPDATE admins SET username='$username', image='$final_image' WHERE id=$id";
    }

    if (mysqli_query($conn, $sql)) {
        $_SESSION['admin'] = $username;
        echo "<script>alert('".$texts[$lang]['success_update_profile']."'); window.location='admin_dashboard.php';</script>";
        exit();
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html lang="<?php echo $lang; ?>" dir="<?php echo $dir; ?>">
<head>
    <meta charset="UTF-8">
    <title><?php echo $texts[$lang]['edit_profile']; ?> | Control Panel</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;700&display=swap" rel="stylesheet">
    <style>
        :root { --gold: #c5a059; --dark: #1e293b; --light: #f8fafc; }
        * { margin:0; padding:0; box-sizing:border-box; font-family:'Cairo',sans-serif; }
        body { display: flex; background: var(--light); min-height: 100vh; font-size: 13px; }

        .sidebar { width: 260px; height: 100vh; background: var(--dark); color: white; padding: 20px; position: fixed; <?php echo ($lang == 'ar' ? 'right: 0;' : 'left: 0;'); ?> }
        .sidebar h2 { text-align: center; color: var(--gold); margin-bottom: 30px; border-bottom: 1px solid #334155; padding-bottom: 10px; font-size: 16px; }
        .sidebar ul { list-style: none; }
        .sidebar ul li { padding: 12px; border-radius: 8px; margin-bottom: 5px; }
        .sidebar ul li a { color: white; text-decoration: none; display: flex; align-items: center; }
        .sidebar ul li i { <?php echo ($lang == 'ar' ? 'margin-left: 10px;' : 'margin-right: 10px;'); ?> color: var(--gold); }

        .main-content { <?php echo ($lang == 'ar' ? 'margin-right: 260px;' : 'margin-left: 260px;'); ?> width: calc(100% - 260px); padding: 40px; }
        .form-container { background: white; padding: 40px; border-radius: 15px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); max-width: 600px; margin: auto; }
        
        .back-nav { margin-bottom: 20px; text-align: <?php echo ($lang == 'ar' ? 'right' : 'left'); ?>; }
        .btn-back { color: #64748b; text-decoration: none; font-weight: bold; display: flex; align-items: center; gap: 8px; font-size: 14px; }
        .btn-back:hover { color: var(--gold); }

        .form-container h1 { font-size: 22px; color: var(--dark); margin-bottom: 30px; text-align: center; border-bottom: 2px solid var(--gold); display: inline-block; padding-bottom: 5px; width: 100%; }
        
        .form-group { margin-bottom: 20px; text-align: <?php echo ($lang == 'ar' ? 'right' : 'left'); ?>; }
        label { display: block; margin-bottom: 8px; font-weight: bold; color: #475569; }
        input[type="text"], input[type="password"], input[type="file"] { width: 100%; padding: 12px; border: 1px solid #e2e8f0; border-radius: 8px; font-size: 13px; outline: none; }
        
        .current-admin-img { margin: 10px 0; border: 3px solid var(--gold); border-radius: 50%; width: 100px; height: 100px; object-fit: cover; display: block; }
        
        .form-actions { display: flex; gap: 15px; margin-top: 30px; }
        .btn-update { flex: 1; background: var(--gold); color: white; border: none; padding: 14px; border-radius: 8px; cursor: pointer; font-weight: bold; font-size: 15px; transition: 0.3s; }
        .btn-reset { flex: 1; background: #e2e8f0; color: #475569; border: none; padding: 14px; border-radius: 8px; cursor: pointer; font-weight: bold; font-size: 15px; transition: 0.3s; }
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
                <a href="admin_dashboard.php" class="btn-back">
                    <i class="fas <?php echo ($lang == 'ar' ? 'fa-arrow-right' : 'fa-arrow-left'); ?>"></i>
                    <?php echo $texts[$lang]['back_to_dashboard']; ?>
                </a>
            </div>

            <h1><?php echo $texts[$lang]['edit_profile']; ?></h1>
            
            <form method="POST" enctype="multipart/form-data">
                
                <div class="form-group">
                    <label><?php echo $texts[$lang]['current_username']; ?></label>
                    <input type="text" name="username" value="<?php echo htmlspecialchars($data['username']); ?>" required>
                </div>

                <div class="form-group">
                    <label><?php echo $texts[$lang]['new_password_hint']; ?></label>
                    <input type="password" name="password" placeholder="********">
                </div>

                <div class="form-group">
                    <label><?php echo $texts[$lang]['current_profile_photo']; ?></label>
                    <img src="img/<?php echo $data['image']; ?>" class="current-admin-img" onerror="this.src='<?php echo $data['image']; ?>'">
                    <br>
                    <label><?php echo $texts[$lang]['change_profile_photo']; ?></label>
                    <input type="file" name="image" accept="image/*">
                </div>

                <div class="form-actions">
                    <button type="submit" name="update_profile" class="btn-update">
                        <i class="fas fa-save"></i> <?php echo ($lang == 'ar' ? 'حفظ التغييرات' : 'Save Changes'); ?>
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