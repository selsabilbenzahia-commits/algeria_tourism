<?php
session_start();
include 'db.php';
include 'lang.php';

if (!isset($_SESSION['admin'])) { header("Location: login.php"); exit(); }

$lang = isset($_SESSION['lang']) ? $_SESSION['lang'] : 'en';
$dir = ($lang == 'ar') ? 'rtl' : 'ltr';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name_ar = mysqli_real_escape_string($conn, $_POST['name_ar']);
    $name_en = mysqli_real_escape_string($conn, $_POST['name_en']);
    
    $wilaya_id = intval($_POST['wilaya_id']); 
    $cat_id = $_POST['category_id'];
    $lat = $_POST['lat'];
    $lng = $_POST['lng'];
    $desc_ar = mysqli_real_escape_string($conn, $_POST['description_ar']);
    $desc_en = mysqli_real_escape_string($conn, $_POST['description_en']);

    $sql = "INSERT INTO attractions (name_ar, name_en, wilaya_id, categorie_id, lat, lng, description_ar, description_en) 
            VALUES ('$name_ar', '$name_en', '$wilaya_id', '$cat_id', '$lat', '$lng', '$desc_ar', '$desc_en')";
    
    if (mysqli_query($conn, $sql)) {
        $attraction_id = mysqli_insert_id($conn); 

        if (isset($_FILES['images'])) {
            $target_dir = "img/attractions/"; 
            
            foreach ($_FILES['images']['tmp_name'] as $key => $tmp_name) {
                if ($_FILES['images']['error'][$key] == 0) {
                    
                    $file_name = "attr_" . $attraction_id . "_" . time() . "_" . $key . "_" . basename($_FILES["images"]["name"][$key]);
                    $target_file = $target_dir . $file_name;
                    
                    if (move_uploaded_file($tmp_name, $target_file)) {
                        
                        $db_path = $file_name; 
                        
                        mysqli_query($conn, "INSERT INTO attraction_images (attraction_id, image) VALUES ('$attraction_id', '$db_path')");
                    }
                }
            }
        }
        echo "<script>alert('".$texts[$lang]['success_add']."'); window.location='manage_attractions.php';</script>";
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}
?>
<!DOCTYPE html>
<html lang="<?php echo $lang; ?>" dir="<?php echo $dir; ?>">
<head>
    <meta charset="UTF-8">
    <title><?php echo $texts[$lang]['admin_panel']; ?> | <?php echo $texts[$lang]['add_attraction_title']; ?></title>
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
        .form-container { background: white; padding: 30px; border-radius: 15px; box-shadow: 0 5px 15px rgba(0,0,0,0.05); max-width: 800px; margin: auto; }
        .form-header h2 { color: var(--dark); font-size: 20px; text-align: center; margin-bottom: 25px; border-bottom: 2px solid var(--gold); display: inline-block; padding-bottom: 5px; }
        .form-header { text-align: center; }

        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: bold; color: var(--dark); }
        .form-group input, .form-group textarea, .form-group select { 
            width: 100%; padding: 10px; border: 1px solid #e2e8f0; border-radius: 8px; outline: none; font-size: 13px;
        }
        
        .image-preview-container { 
            border: 2px dashed #e2e8f0; padding: 20px; border-radius: 10px; text-align: center; 
            cursor: pointer; position: relative; background: #fafafa;
        }
        .file-input { position: absolute; opacity: 0; width: 100%; height: 100%; cursor: pointer; top:0; left:0; }
        
        #preview-area { display: flex; flex-wrap: wrap; gap: 10px; margin-top: 15px; justify-content: center; }
        .thumb-preview { width: 80px; height: 80px; object-fit: cover; border-radius: 8px; border: 2px solid var(--gold); position: relative; }
        .main-badge { position: absolute; top: -8px; <?php echo ($lang == 'ar') ? 'right: -8px;' : 'left: -8px;'; ?> background: var(--gold); color: white; font-size: 10px; padding: 2px 6px; border-radius: 4px; font-weight: bold; }

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
            <li><a href="manage_attractions.php"><i class="fas fa-camera"></i> <?php echo $texts[$lang]['attraction_mgmt']; ?></a></li>
        </ul>
    </div>

    <div class="main-content">
        <div class="form-container">
            <div class="back-link">
                <a href="manage_attractions.php">
                    <i class="fas <?php echo ($lang == 'ar' ? 'fa-arrow-right' : 'fa-arrow-left'); ?>"></i> 
                    <?php echo $texts[$lang]['back_to_mgmt_att']; ?>
                </a> 
            </div>
            <div class="form-header">
                <h2><?php echo $texts[$lang]['add_attraction_title']; ?></h2>
            </div>
            
            <form action="" method="POST" enctype="multipart/form-data">
                <div class="flex-row">
                    <div class="form-group">
                        <label><?php echo $texts[$lang]['name_ar']; ?></label>
                        <input type="text" name="name_ar" required>
                    </div>
                    <div class="form-group">
                        <label><?php echo $texts[$lang]['name_en']; ?></label>
                        <input type="text" name="name_en" required>
                    </div>
                </div>

                <div class="flex-row">
                    <div class="form-group">
                        <label><?php echo $texts[$lang]['col_wilaya']; ?></label>
                        <select name="wilaya_id" required>
                            <option value=""><?php echo ($lang == 'ar') ? '-- اختر --' : '-- Choose --'; ?></option>
                            <?php
                            // تصحيح الاستعلام: يجلب فقط الولايات التي تحتوي على بيانات وصورة معتمدة في موقعك
                            $res = mysqli_query($conn, "SELECT id, code, name_en, name_ar FROM wilayas WHERE image IS NOT NULL AND image != '' ORDER BY code ASC");
                            while($row = mysqli_fetch_assoc($res)) {
                                $name = ($lang == 'ar') ? $row['name_ar'] : $row['name_en'];
                                // تمرير الـ id في الـ value لكي ينجح الربط البرمجي
                                echo "<option value='".$row['id']."'>".$row['code']." - ".$name."</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label><?php echo $texts[$lang]['col_category']; ?></label>
                        <select name="category_id" required>
                            <option value=""><?php echo ($lang == 'ar') ? '-- اختر --' : '-- Choose --'; ?></option>
                            <?php
                            $cs = mysqli_query($conn, "SELECT id, name_en, name_ar FROM categories ORDER BY name_en ASC");
                            while($c = mysqli_fetch_assoc($cs)) {
                                $c_name = ($lang == 'ar') ? $c['name_ar'] : $c['name_en'];
                                echo "<option value='".$c['id']."'>".$c_name."</option>";
                            }
                            ?>
                        </select>
                    </div>
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
                    <label><?php echo $texts[$lang]['img_multi_hint']; ?></label>
                    <div class="image-preview-container">
                        <i class="fas fa-images" id="upload-icon" style="font-size: 30px; color: var(--gold);"></i><br>
                        <span id="upload-text"><?php echo $texts[$lang]['select_multi']; ?></span>
                        <div id="preview-area"></div>
                        <input type="file" name="images[]" class="file-input" id="image-input" multiple accept="image/*" required>
                    </div>
                </div>

                <div class="flex-row">
                    <div class="form-group">
                        <label><?php echo $texts[$lang]['latitude']; ?></label>
                        <input type="text" name="lat" placeholder="36.75">
                    </div>
                    <div class="form-group">
                        <label><?php echo $texts[$lang]['longitude']; ?></label>
                        <input type="text" name="lng" placeholder="3.05">
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
        const previewArea = document.getElementById('preview-area');
        const uploadIcon = document.getElementById('upload-icon');
        const uploadText = document.getElementById('upload-text');

        imageInput.onchange = evt => {
            previewArea.innerHTML = '';
            const files = imageInput.files;
            if (files.length > 0) {
                uploadIcon.style.display = 'none';
                uploadText.style.display = 'none';
                Array.from(files).forEach((file, index) => {
                    const reader = new FileReader();
                    reader.onload = e => {
                        const div = document.createElement('div');
                        div.className = 'thumb-preview';
                        div.style.backgroundImage = `url(${e.target.result})`;
                        div.style.backgroundSize = 'cover';
                        div.style.backgroundPosition = 'center';
                        if(index === 0) {
                            div.innerHTML = '<span class="main-badge"><?php echo $texts[$lang]['main_label']; ?></span>';
                        }
                        previewArea.appendChild(div);
                    }
                    reader.readAsDataURL(file);
                });
            } else {
                uploadIcon.style.display = 'inline-block';
                uploadText.style.display = 'inline-block';
            }
        }
    </script>
</body>
</html>