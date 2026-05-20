<?php
include "db.php"; 
session_start();

if(!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$lang = $_GET['lang'] ?? ($_SESSION['lang'] ?? 'en');

// 1. جلب بيانات المستخدم الحالية (تأكدي من اسم العمود name أو username في قاعدة بياناتك)
// قمت بوضع "name" بناءً على الخطأ السابق، إذا كان في قاعدتك اسم آخر غيريه هنا
$user_query = "SELECT name, email, profile_image FROM users WHERE id = '$user_id'";
$user_result = mysqli_query($conn, $user_query);
$user_data = mysqli_fetch_assoc($user_result);

// 2. معالجة تحديث البيانات عند الضغط على زر الحفظ (Save Changes)
$update_msg = "";
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_profile'])) {
    $new_username = mysqli_real_escape_string($conn, $_POST['username']);
    $new_email = mysqli_real_escape_string($conn, $_POST['email']);
    $image_name = $user_data['profile_image'];

    if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] == 0) {
        $target_dir = "img/users/"; // مجلد حفظ صور المستخدمين
        
        // إنشاء المجلد إذا لم يكن موجوداً تلقائياً
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }

        $file_extension = pathinfo($_FILES['profile_pic']['name'], PATHINFO_EXTENSION);
        // تسمية الصورة برقم الآيدي الخاص بالمستخدم لكي لا تتكرر الأسماء
        $image_name = "user_" . $user_id . "." . $file_extension; 
        $target_file = $target_dir . $image_name;

        // رفع الملف إلى المجلد
        move_uploaded_file($_FILES['profile_pic']['tmp_name'], $target_file);
    }

    $update_query = "UPDATE users SET name = '$new_username', email = '$new_email', profile_image = '$image_name' WHERE id = '$user_id'";
    if (mysqli_query($conn, $update_query)) {
        $user_data['name'] = $new_username;
        $user_data['email'] = $new_email;
        $user_data['profile_image'] = $image_name;
        $_SESSION['username'] = $new_username; 
        $update_msg = ($lang == 'ar') ? "تم تحديث البيانات بنجاح!" : "Profile updated successfully!";
    } else {
        $update_msg = ($lang == 'ar') ? "حدث خطأ أثناء التحديث." : "Error updating profile.";
    }
}

// 3. استعلام المفضلات المشترك (موزون ومصحح بـ 6 أعمدة لكل جدول لكي لا يظهر خطأ SELECT)
$query = "
    SELECT f.id as fav_id, a.id as item_id, 
        (CASE WHEN '$lang' = 'ar' THEN a.name_ar ELSE a.name_en END) as name, 
        (SELECT image FROM attraction_images WHERE attraction_id = a.id LIMIT 1) as image, 
        f.item_type,
        (CASE WHEN '$lang' = 'ar' THEN 'معلم سياحي' ELSE 'Attraction' END) as display_type
    FROM favorites f 
    JOIN attractions a ON f.item_id = a.id 
    WHERE f.user_id = '$user_id' AND f.item_type = 'attraction'

    UNION ALL
    
    SELECT f.id as fav_id, w.id as item_id, 
        (CASE WHEN '$lang' = 'ar' THEN w.name_ar ELSE w.name_en END) as name, 
        w.image, 
        f.item_type,
        (CASE WHEN '$lang' = 'ar' THEN 'ولاية' ELSE 'Wilaya' END) as display_type
    FROM favorites f 
    JOIN wilayas w ON f.item_id = w.id 
    WHERE f.user_id = '$user_id' AND f.item_type = 'wilaya'
";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="<?php echo $lang; ?>" dir="<?php echo ($lang == 'ar') ? 'rtl' : 'ltr'; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo ($lang == 'ar') ? 'الملف الشخصي' : 'User Profile'; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <script>
        (function () {
            const savedTheme = localStorage.getItem('theme') || 'dark';
            if (savedTheme === 'light') {
                document.documentElement.classList.add('light-mode');
            }
        })();
    </script>

    <style>
        :root {
            --main-color: #c5a059;
            --bg-color: #1a1a1a;
            --card-bg: #262626;
            --text-color: #ffffff;
            --border-color: #444;
            --input-bg: #333;
        }
        
        /* تفعيل المود المضيء على مستوى جذر الصفحة html و body */
        html.light-mode, html.light-mode body {
            --bg-color: #f5f6fa;
            --card-bg: #ffffff;
            --text-color: #2f3640;
            --border-color: #dcdde1;
            --input-bg: #f1f2f6;
        }

        body {
            background-color: var(--bg-color);
            color: var(--text-color);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
            direction: <?php echo ($lang == 'ar') ? 'rtl' : 'ltr'; ?>;
            transition: background 0.3s ease, color 0.3s ease;
        }
        .container {
            max-width: 1000px;
            margin: 50px auto;
            padding: 20px;
        }
        .profile-card {
            background: var(--card-bg);
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.3);
            margin-bottom: 40px;
            border-top: 5px solid var(--main-color);
        }
        .profile-header {
            display: flex;
            align-items: center;
            gap: 20px;
            margin-bottom: 25px;
        }
        .profile-avatar {
            width: 80px;
            height: 80px;
            background: var(--main-color);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 35px;
            color: #000;
        }
        .profile-form .form-group {
            margin-bottom: 15px;
        }
        .profile-form label {
            display: block;
            margin-bottom: 5px;
            color: #b3b3b3;
        }
        .profile-form input {
            width: 100%;
            padding: 10px;
            border-radius: 8px;
            background: var(--input-bg);
            color: var(--text-color);
            border: 1px solid var(--border-color);
            box-sizing: border-box;
        }
        .btn-save {
            background: var(--main-color);
            color: #000;
            border: none;
            padding: 12px 25px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: bold;
        }
        .nav-favorites-box {
            text-align: center;
            margin: 30px 0;
        }
        .btn-go-fav {
            background: transparent;
            color: var(--main-color);
            border: 2px solid var(--main-color);
            padding: 15px 40px;
            font-size: 18px;
            border-radius: 30px;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            transition: 0.3s ease;
        }
        .btn-go-fav:hover {
            background: var(--main-color);
            color: #000;
        }
        .favorites-section {
            display: none;
            margin-top: 20px;
        }
        .favorites-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        .fav-item {
            background: var(--card-bg);
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 10px rgba(0,0,0,0.2);
        }
        .fav-item img {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }
        .fav-info {
            padding: 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .btn-delete {
            background: #ff4d4d;
            color: white;
            border: none;
            padding: 8px 12px;
            border-radius: 6px;
            cursor: pointer;
        }
        .alert-msg {
            background: #2ebd59;
            color: white;
            padding: 10px;
            border-radius: 8px;
            margin-bottom: 15px;
            text-align: center;
        }
    </style>
</head>
<body>

    <button id="theme-toggle" style="position: fixed; top: 20px; <?php echo ($lang == 'ar') ? 'left: 20px;' : 'right: 20px;'; ?> background: var(--main-color); color: #000; border: none; padding: 12px 15px; border-radius: 50%; cursor: pointer; z-index: 1000; font-size: 18px; box-shadow: 0 4px 10px rgba(0,0,0,0.3);">
        <i class="fas fa-moon" id="theme-icon"></i>
    </button>

    <div class="container">
        
        <div class="profile-card">
    <div class="profile-header" style="display: flex; align-items: center; gap: 20px; margin-bottom: 25px;">
        <div class="profile-avatar" style="width: 90px; height: 90px; border-radius: 50%; overflow: hidden; background: #c5a059; display: flex; align-items: center; justify-content: center; border: 3px solid #c5a059;">
            <?php if(!empty($user_data['profile_image'])): ?>
                <img src="img/users/<?php echo $user_data['profile_image']; ?>" style="width: 100%; height: 100%; object-fit: cover;">
            <?php else: ?>
                <i class="fas fa-user" style="font-size: 40px; color: #000;"></i>
            <?php endif; ?>
        </div>
        <div>
            <h2><?php echo ($lang == 'ar') ? 'إعدادات الحساب' : 'Account Settings'; ?></h2>
            <p style="color: #b3b3b3; margin:0;"><?php echo htmlspecialchars($user_data['name']); ?></p>
        </div>
    </div>

    <?php if(!empty($update_msg)): ?>
        <div class="alert-msg"><?php echo $update_msg; ?></div>
    <?php endif; ?>

    <form method="POST" class="profile-form" enctype="multipart/form-data">
        <div class="form-group">
            <label><?php echo ($lang == 'ar') ? 'اسم المستخدم:' : 'Username:'; ?></label>
            <input type="text" name="username" value="<?php echo htmlspecialchars($user_data['name']); ?>" required>
        </div>
        <div class="form-group">
            <label><?php echo ($lang == 'ar') ? 'البريد الإلكتروني:' : 'Email Address:'; ?></label>
            <input type="email" name="email" value="<?php echo htmlspecialchars($user_data['email']); ?>" required>
        </div>
        
        <div class="form-group" style="margin-bottom: 20px;">
            <label><?php echo ($lang == 'ar') ? 'تغيير الصورة الشخصية:' : 'Change Profile Picture:'; ?></label>
            <input type="file" name="profile_pic" accept="image/*" style="padding: 5px;">
        </div>

        <button type="submit" name="update_profile" class="btn-save">
            <i class="fas fa-save"></i> <?php echo ($lang == 'ar') ? 'حفظ التغييرات' : 'Save Changes'; ?>
        </button>
    </form>
</div>
        <div class="nav-favorites-box">
            <a href="#my-favorites" class="btn-go-fav" id="toggle-fav-btn">
                <i class="fas fa-heart"></i>
                <span><?php echo ($lang == 'ar') ? 'عرض قائمتي المفضلة' : 'View My Favorites'; ?></span>
            </a>
        </div>

        <section class="favorites-section" id="my-favorites">
            <h2 style="border-bottom: 2px solid var(--main-color); padding-bottom: 10px;">
                <?php echo ($lang == 'ar') ? 'المعالم والولايات المفضلة لديك' : 'Your Favorite Places'; ?>
            </h2>
            
            <div class="favorites-grid">
                <?php if(mysqli_num_rows($result) > 0): ?>
                    <?php while($row = mysqli_fetch_assoc($result)): ?>
                        <div class="fav-item" id="fav-item-<?php echo $row['fav_id']; ?>">
                            <?php
                            if ($row['item_type'] == 'wilaya') {
                                $folder = "img/wilayas/";
                            } elseif ($row['item_type'] == 'attraction') {
                                $folder = "img/attractions/";
                            } elseif ($row['item_type'] == 'hotel') {
                                $folder = "img/hotels/";
                            } elseif ($row['item_type'] == 'restaurant') {
                                $folder = "img/restaurants/";
                            } else {
                                $folder = "img/";
                            }

                            if (!empty($row['image'])) {
                                $image_final_path = $folder . $row['image'];
                            } else {
                                $image_final_path = "uploads/default.jpg"; // المسار الافتراضي المعتمد في ملفكم
                            }
                            ?>

                            <img src="<?php echo $image_final_path; ?>" alt="<?php echo htmlspecialchars($row['name']); ?>">
                            <div class="fav-info">
                                <div>
                                    <span style="color: var(--main-color); font-size: 12px; display: block; margin-bottom: 5px;">
                                        <?php echo htmlspecialchars($row['display_type']); ?>
                                    </span>
                                    <h4><?php echo htmlspecialchars($row['name']); ?></h4>
                                </div>
                                <button class="btn-delete" onclick="removeFromProfile(<?php echo $row['item_id']; ?>, '<?php echo $row['item_type']; ?>', <?php echo $row['fav_id']; ?>)">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div style="text-align: center; grid-column: 1/-1; padding: 40px 0;">
                        <p><?php echo ($lang == 'ar') ? 'قائمة مفضلاتك فارغة حالياً.' : 'Your favorites list is currently empty.'; ?></p>
                        <a href="index.php" style="color: var(--main-color); text-decoration: none; font-weight: bold;">
                            <?php echo ($lang == 'ar') ? 'ابدأ باكتشاف الجزائر الآن ←' : 'Start discovering Algeria now →'; ?>
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </section>

    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        // كود الـ JavaScript النظيف والمضمون للتحكم بالوضع المظلم والمضيء وثباته
        const themeToggleBtn = document.getElementById('theme-toggle');
        const themeIcon = document.getElementById('theme-icon');

        // دالة لتحديث الأيقونة بناءً على الوضع الحالي المطبق في الـ HTML
        function updateIcon() {
            if (document.documentElement.classList.contains('light-mode')) {
                themeIcon.classList.replace('fa-moon', 'fa-sun');
            } else {
                themeIcon.classList.replace('fa-sun', 'fa-moon');
            }
        }
        
        // تحديث الأيقونة عند تحميل الصفحة لأول مرة
        updateIcon();

        // عند الضغط على الزر: نقوم بالتبديل والحفظ فوراً في الـ LocalStorage
        themeToggleBtn.addEventListener('click', () => {
            document.documentElement.classList.toggle('light-mode');
            
            if (document.documentElement.classList.contains('light-mode')) {
                localStorage.setItem('theme', 'light');
            } else {
                localStorage.setItem('theme', 'dark');
            }
            updateIcon();
        });

        // حركة فتح وغلق قسم المفضلات
        $('#toggle-fav-btn').on('click', function(e) {
            e.preventDefault();
            $('.favorites-section').slideToggle(600);
            $('html, body').animate({
                scrollTop: $("#my-favorites").offset().top - 20
            }, 800);
        });

        // دالة حذف عنصر من المفضلات بـ AJAX
        function removeFromProfile(itemId, itemType, favId) {
            if(confirm("<?php echo ($lang == 'ar') ? 'هل أنت متأكد من حذف هذا العنصر؟' : 'Are you sure you want to delete this item?'; ?>")) {
                $.ajax({
                    url: 'favorite.php', 
                    method: 'POST',
                    data: { item_id: itemId, item_type: itemType },
                    success: function(response) {
                        if(response.trim() == 'removed') {
                            $('#fav-item-' + favId).fadeOut(500);
                        } else {
                            alert("<?php echo ($lang == 'ar') ? 'حدث خطأ أثناء الحذف.' : 'An error occurred during deletion.'; ?>");
                        }
                    }
                });
            }
        }
    </script>
</body>
</html>