<?php
session_start();
include 'db.php';
include 'lang.php';

if (!isset($_SESSION['admin'])) { header("Location: login.php"); exit(); }

$lang = isset($_SESSION['lang']) ? $_SESSION['lang'] : 'en';
$dir = ($lang == 'ar') ? 'rtl' : 'ltr';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $code = $_POST['wilaya_code'];
    $desc_ar = mysqli_real_escape_string($conn, $_POST['description_ar']);
    $desc_en = mysqli_real_escape_string($conn, $_POST['description_en']);
    $lat = $_POST['lat'];
    $lng = $_POST['lng'];

    $image_db_path = ""; 
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $target_dir = "images/"; 
        if (!is_dir($target_dir)) { mkdir($target_dir, 0777, true); }
        $file_name = time() . "_" . basename($_FILES["image"]["name"]);
        $target_file = $target_dir . $file_name;
        if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
            $image_db_path = "images/" . $file_name; 
        }
    }

    if ($image_db_path != "") {
        $sql = "UPDATE wilayas SET description_ar = '$desc_ar', description_en = '$desc_en', 
                image = '$image_db_path', lat = '$lat', lng = '$lng' WHERE code = '$code'";
    } else {
        $sql = "UPDATE wilayas SET description_ar = '$desc_ar', description_en = '$desc_en', 
                lat = '$lat', lng = '$lng' WHERE code = '$code'";
    }

    if (mysqli_query($conn, $sql)) {
        echo "<script>alert('".$texts[$lang]['success_update']."'); window.location='manage_wilayas.php';</script>";
    } else { 
        echo "Error: " . mysqli_error($conn); 
    }
}
?>
<!DOCTYPE html>
<html lang="<?php echo $lang; ?>" dir="<?php echo $dir; ?>">
<head>
    <meta charset="UTF-8">
    <title><?php echo $texts[$lang]['admin_panel']; ?> | <?php echo $texts[$lang]['update_wilaya_title']; ?></title>
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
        .sidebar ul li i { <?php echo ($lang == 'ar') ? 'margin-left: 10px;' : 'margin-right: 10px;'; ?> color: var(--gold); }
        
        .main-content { 
            <?php echo ($lang == 'ar') ? 'margin-right: 240px;' : 'margin-left: 240px;'; ?> 
            width: calc(100% - 240px); padding: 40px; 
        }
        .form-container { background: white; padding: 30px; border-radius: 15px; box-shadow: 0 5px 15px rgba(0,0,0,0.05); max-width: 700px; margin: auto; }
        .form-header h2 { color: var(--dark); font-size: 20px; text-align: center; margin-bottom: 25px; border-bottom: 2px solid var(--gold); display: inline-block; padding-bottom: 5px; }
        .form-header { text-align: center; }

        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: bold; color: var(--dark); }
        .form-group input, .form-group textarea, .form-group select { 
            width: 100%; padding: 10px; border: 1px solid #e2e8f0; border-radius: 8px; outline: none; font-size: 13px;
        }
        
        .image-preview-container { 
            border: 2px dashed #e2e8f0; padding: 15px; border-radius: 10px; text-align: center; 
            cursor: pointer; position: relative; background: #fafafa;
        }
        #preview-img { max-width: 150px; border-radius: 8px; display: none; margin-top: 10px; }
        
        .form-actions { display: flex; gap: 15px; }
        .submit-btn { background: var(--gold); color: white; padding: 12px; border: none; border-radius: 8px; cursor: pointer; width: 100%; font-weight: bold; font-size: 15px; transition: 0.3s; flex: 1; }
        .btn-reset { background: #e2e8f0; color: #475569; padding: 12px; border: none; border-radius: 8px; cursor: pointer; font-weight: bold; font-size: 15px; transition: 0.3s; flex: 1; }
        .submit-btn:hover { background: #b08d4a; }
        
        /* زر الرجوع الجديد بتنسيق بسيط */
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
            <li><a href="manage_wilayas.php"><i class="fas fa-map"></i> <?php echo $texts[$lang]['wilaya_mgmt']; ?></a></li>
        </ul>
    </div>

    <div class="main-content">
        <div class="form-container">
            <div class="back-link">
                <a href="manage_wilayas.php">
                    <i class="fas <?php echo ($lang == 'ar' ? 'fa-arrow-right' : 'fa-arrow-left'); ?>"></i> 
                    <?php echo $texts[$lang]['back_to_mgmt_wilaya']; ?>
                </a>
            </div>
            <div class="form-header">
                <h2><?php echo $texts[$lang]['update_wilaya_title']; ?></h2>
            </div>
            
            <form action="" method="POST" enctype="multipart/form-data">
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

                <div class="flex-row">
                    <div class="form-group">
                        <label><?php echo $texts[$lang]['desc_ar']; ?></label>
                        <textarea name="description_ar" rows="4" required></textarea>
                    </div>
                    <div class="form-group">
                        <label><?php echo $texts[$lang]['desc_en']; ?></label>
                        <textarea name="description_en" rows="4" required></textarea>
                    </div>
                </div>

                <div class="form-group">
                    <label><?php echo $texts[$lang]['upload_img']; ?></label>
                    <div class="image-preview-container" onclick="document.getElementById('image-input').click();">
                        <i class="fas fa-camera" id="upload-icon" style="font-size: 24px; color: var(--gold);"></i><br>
                        <span id="upload-text"><?php echo $texts[$lang]['select_file']; ?></span>
                        <img id="preview-img" src="#" alt="Preview">
                        <input type="file" name="image" id="image-input" style="display:none;" accept="image/*">
                    </div>
                </div>

                <div class="flex-row">
                    <div class="form-group">
                        <label><?php echo $texts[$lang]['latitude']; ?></label>
                        <input type="text" name="lat" placeholder="e.g. 36.75">
                    </div>
                    <div class="form-group">
                        <label><?php echo $texts[$lang]['longitude']; ?></label>
                        <input type="text" name="lng" placeholder="e.g. 3.05">
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="submit-btn"><?php echo $texts[$lang]['btn_update']; ?></button>
                    <button type="reset" class="btn-reset"><?php echo $texts[$lang]['btn_reset']; ?></button>
                </div>
            </form>
        </div>
    </div>
            </form>
        </div>
    </div>

    <script>
        document.getElementById('image-input').onchange = function(evt) {
            const [file] = this.files;
            if (file) {
                const preview = document.getElementById('preview-img');
                preview.src = URL.createObjectURL(file);
                preview.style.display = 'inline-block';
                document.getElementById('upload-icon').style.display = 'none';
                document.getElementById('upload-text').style.display = 'none';
            }
        }
    </script>
</body>
</html>