<?php
session_start();
include 'db.php';
include 'lang.php';

if (!isset($_SESSION['admin'])) { header("Location: login.php"); exit(); }

$lang = isset($_SESSION['lang']) ? $_SESSION['lang'] : 'en';
$dir = ($lang == 'ar') ? 'rtl' : 'ltr';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name_en = mysqli_real_escape_string($conn, $_POST['name_en']);
    $name_ar = mysqli_real_escape_string($conn, $_POST['name_ar']); 
    // تم تصحيح المفتاح ليتوافق تماماً مع حقل السلكت في الأسفل دون تغيير المتغير
    $wilaya_id = $_POST['wilaya_code'];
    $lat = $_POST['lat'];
    $lng = $_POST['lng'];

    $image_path = ""; 
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        // التعديل الأساسي: المجلد الفعلي المخصص للفنادق
        $target_dir = "img/hotels/"; 
        if (!is_dir($target_dir)) { mkdir($target_dir, 0777, true); }
        
        $file_name = "hotel_" . time() . "_" . basename($_FILES["image"]["name"]);
        $target_file = $target_dir . $file_name;
        
        if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
            // التعديل السحري: تخزين اسم الملف النظيف والصافي فقط في الـ Database
            $image_path = $file_name; 
        }
    }

    $sql = "INSERT INTO hotels (name_en, name_ar, wilaya_id, image, lat, lng) 
            VALUES ('$name_en', '$name_ar', '$wilaya_id', '$image_path', '$lat', '$lng')";

    if (mysqli_query($conn, $sql)) {
        echo "<script>alert('".$texts[$lang]['success_add_hotel']."'); window.location='manage_hotels.php';</script>";
    } else { 
        echo "Error: " . mysqli_error($conn); 
    }
}
?>
<!DOCTYPE html>
<html lang="<?php echo $lang; ?>" dir="<?php echo $dir; ?>">
<head>
    <meta charset="UTF-8">
    <title><?php echo $texts[$lang]['admin_panel']; ?> | <?php echo $texts[$lang]['add_hotel_title']; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600&display=swap" rel="stylesheet">
    <style>
        :root { --gold: #c5a059; --dark: #1e293b; --light: #f8fafc; }
        * { margin:0; padding:0; box-sizing:border-box; font-family:'Cairo',sans-serif; }
        body { display: flex; background: var(--light); min-height: 100vh; font-size: 13px; }
        
        .sidebar { 
            width: 240px; height: 100vh; background: var(--dark); color: white; padding: 20px; position: fixed;
            <?php echo ($lang == 'ar') ? 'right: 0;' : 'left: 0;'; ?> 
        }
        .sidebar h2 { text-align: center; color: var(--gold); margin-bottom: 25px; font-size: 16px; border-bottom: 1px solid #334155; padding-bottom: 10px; }
        .sidebar ul li { padding: 10px; list-style: none; }
        .sidebar ul li a { color: white; text-decoration: none; display: flex; align-items: center; }
        .sidebar ul li i { <?php echo ($lang == 'ar') ? 'margin-left: 10px;' : 'margin-right: 10px;'; ?> color: var(--gold); width: 20px; text-align: center; }
        
        .main-content { 
            <?php echo ($lang == 'ar') ? 'margin-right: 240px;' : 'margin-left: 240px;'; ?> 
            width: calc(100% - 240px); padding: 40px; 
        }
        .form-container { background: white; padding: 30px; border-radius: 15px; box-shadow: 0 5px 15px rgba(0,0,0,0.05); max-width: 700px; margin: auto; }
        .form-header h2 { color: var(--dark); font-size: 20px; text-align: center; margin-bottom: 25px; border-bottom: 2px solid var(--gold); display: inline-block; padding-bottom: 5px; }
        .form-header { text-align: center; }

        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 6px; font-weight: bold; color: var(--dark); }
        .form-group input, .form-group select { 
            width: 100%; padding: 10px; border: 1px solid #e2e8f0; border-radius: 8px; outline: none; font-size: 13px;
        }

        .image-preview-container { 
            border: 2px dashed #e2e8f0; padding: 20px; border-radius: 10px; text-align: center; 
            cursor: pointer; position: relative; min-height: 120px; display: flex; flex-direction: column; align-items: center; justify-content: center; background: #fafafa;
        }
        #preview-img { max-width: 200px; max-height: 120px; margin-top: 10px; display: none; border-radius: 8px; border: 1px solid var(--gold); }
        .image-preview-container i { font-size: 30px; color: var(--gold); }
        .file-input { position: absolute; opacity: 0; width: 100%; height: 100%; cursor: pointer; top:0; left:0; }
        
        .form-actions { display: flex; gap: 15px; }
        .submit-btn { background: var(--gold); color: white; padding: 12px; border: none; border-radius: 8px; cursor: pointer; width: 100%; font-weight: bold; font-size: 15px; transition: 0.3s; flex: 1; }
        .btn-reset { background: #e2e8f0; color: #475569; padding: 12px; border: none; border-radius: 8px; cursor: pointer; font-weight: bold; font-size: 15px; transition: 0.3s; flex: 1; }
        .submit-btn:hover { background: #b08d4a; }
        
        .back-link { margin-bottom: 15px; text-align: <?php echo ($lang == 'ar' ? 'right' : 'left'); ?>; }
        .back-link a { color: #64748b; text-decoration: none; font-weight: bold; font-size: 13px; }

        .flex-row { display: flex; gap: 15px; }
        .flex-row > div { flex: 1; }
    </style>
</head>
<body>
    <div class="sidebar">
        <h2><?php echo $texts[$lang]['tourism_mgmt']; ?></h2>
        <ul>
            <li><a href="admin_dashboard.php"><i class="fas fa-home"></i> <?php echo $texts[$lang]['home']; ?></a></li>
            <li><a href="manage_hotels.php"><i class="fas fa-hotel"></i> <?php echo $texts[$lang]['hotel_mgmt']; ?></a></li>
        </ul>
    </div>

    <div class="main-content">
        <div class="form-container">
            <div class="back-link">
                <a href="manage_hotels.php">
                    <i class="fas <?php echo ($lang == 'ar' ? 'fa-arrow-right' : 'fa-arrow-left'); ?>"></i> 
                    <?php echo $texts[$lang]['back_to_mgmt_hotel']; ?>
                </a> </div>
            <div class="form-header">
                <h2><?php echo $texts[$lang]['add_hotel_title']; ?></h2>
            </div>
            
            <form action="" method="POST" enctype="multipart/form-data">
                <div class="flex-row">
                    <div class="form-group">
                        <label><?php echo $texts[$lang]['hotel_name_en']; ?></label>
                        <input type="text" name="name_en" required placeholder="Hotel Name">
                    </div>
                    <div class="form-group">
                        <label><?php echo $texts[$lang]['hotel_name_ar']; ?></label>
                        <input type="text" name="name_ar" required placeholder="اسم الفندق">
                    </div>
                </div>

                <div class="form-group">
                    <label><?php echo $texts[$lang]['col_wilaya']; ?></label>
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
                    <label><?php echo $texts[$lang]['hotel_img']; ?></label>
                    <div class="image-preview-container">
                        <i class="fas fa-hotel" id="upload-icon"></i>
                        <span id="upload-text"><?php echo $texts[$lang]['click_select_img']; ?></span>
                        <img id="preview-img" src="#" alt="Preview">
                        <input type="file" name="image" class="file-input" id="image-input" accept="image/*" required>
                    </div>
                </div>

                <div class="flex-row">
                    <div class="form-group">
                        <label><?php echo $texts[$lang]['latitude']; ?></label>
                        <input type="text" name="lat" placeholder="36.1234">
                    </div>
                    <div class="form-group">
                        <label><?php echo $texts[$lang]['longitude']; ?></label>
                        <input type="text" name="lng" placeholder="3.5678">
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="submit-btn"><?php echo $texts[$lang]['btn_update']; ?></button>
                    <button type="reset" class="btn-reset"><?php echo $texts[$lang]['btn_reset']; ?></button>
                </div>
            </form>
        </div>
    </div>

    <script>
        const imageInput = document.getElementById('image-input');
        const previewImg = document.getElementById('preview-img');
        const uploadIcon = document.getElementById('upload-icon');
        const uploadText = document.getElementById('upload-text');

        imageInput.onchange = evt => {
            const [file] = imageInput.files;
            if (file) {
                previewImg.src = URL.createObjectURL(file);
                previewImg.style.display = 'block';
                uploadIcon.style.display = 'none';
                uploadText.style.display = 'none';
            }
        }
    </script>
</body>
</html>