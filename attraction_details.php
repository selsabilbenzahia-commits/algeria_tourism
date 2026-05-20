<?php
include('lang.php'); 
include 'db.php';
session_start();

// 1. تحديد اللغة وحفظها في السشن لضمان استمراريتها
if (isset($_GET['lang'])) {
    $lang = $_GET['lang'];
    $_SESSION['lang'] = $lang; 
} elseif (isset($_SESSION['lang'])) {
    $lang = $_SESSION['lang'];
} else {
    $lang = 'ar';
}

$main_image = 'default.jpg'; 
$all_images = [];

// 2. التأكد من وجود id المعلم في الرابط
if (isset($_GET['id'])) {
    $attr_id = $_GET['id']; // هذا السطر ضروري جداً
    
    // 3. الاستعلام المحدث لجلب اسم الولاية باللغتين
    $query = "SELECT a.*, w.name_en as wilaya_name_en, w.name_ar as wilaya_name_ar 
              FROM attractions a 
              JOIN wilayas w ON a.wilaya_id = w.id 
              WHERE a.id = '$attr_id'";
    $result = mysqli_query($conn, $query);
    $attr = mysqli_fetch_assoc($result);

    if (!$attr) { 
        die("!the attraction does not exist"); 
    }

    // جلب الصور الإضافية
    $img_query = "SELECT image FROM attraction_images WHERE attraction_id = '$attr_id'";
    $img_result = mysqli_query($conn, $img_query);
    while($row = mysqli_fetch_assoc($img_result)) {
        $all_images[] = trim($row['image']); 
    }

    if (!empty($all_images)) {
        $main_image = $all_images[0];
    }
}
?>


<!DOCTYPE html>
<html lang="<?php echo $lang; ?>" dir="<?php echo ($lang == 'ar') ? 'rtl' : 'ltr'; ?>">
<head>
    <meta charset="UTF-8">
    <title><?php echo ($lang == 'ar') ? $attr['name_ar'] : $attr['name_en']; ?></title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
<?php include 'header.php'; ?>
<section class="attr-hero" id="hero-bg">
    <img src="img/attractions/<?php echo $main_image; ?>" class="hero-img-fallback">
    
    <div class="attr-hero-content">
    <h1><?php echo ($lang == 'ar') ? $attr['name_ar'] : $attr['name_en']; ?></h1>
    <p><i class="fas fa-map-marker-alt"></i><?php echo ($lang == 'ar') ? $attr['wilaya_name_ar'] : $attr['wilaya_name_en']; ?>
    </p>
    </div>
</section>

<div class="container main-content-v2">
    
    <div class="details-section">
        <h2 class="section-title"><?php echo $texts[$lang]['about_att']; ?></h2>
        <p class="description-text"><?php echo ($lang == 'ar') ? $attr['description_ar'] : $attr['description_en']; ?></p>
    </div>

    <div class="gallery-section">
        <h2 class="section-title"><?php echo $texts[$lang]['gallery']; ?></h2>
        <div class="images-grid">
            <?php foreach($all_images as $img): ?>
             <img src="img/attractions/<?php echo $img; ?>" class="thumb-img" onclick="openModal(this.src)">
            <?php endforeach; ?>
        </div>
    </div>

    <hr class="section-divider">

    <section class="reviews-section-v2">
    <div class="container">
        <div class="reviews-right-wrapper">
            <h3 class="section-title-v2"><?php echo $texts[$lang]['opinions']; ?></h3>
            <div class="gold-line-right"></div>

            <?php if (isset($_SESSION['user_id'])): ?>
                <form id="commentForm" class="comment-form-v2">
                    <input type="hidden" id="attr_id" value="<?php echo $_GET['id'];?>">
                    <textarea id="comment_text_area" name="comment_text" placeholder="...Add your comment here" required></textarea>
                    <button type="submit" name="submit_comment" class="submit-comment-v2"> <?php echo $texts[$lang]['publish']; ?></button>
                </form>
            <?php else: ?>
                <div class="login-msg-v2">
                    <p><?php echo $texts[$lang]['sorry']; ?><a href="login.php"> <?php echo $texts[$lang]['login']; ?></a><?php echo $texts[$lang]['add_comment']; ?></p>
                </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['user_id'])): ?>
                <div class="comments-list-v2">
                    <?php
                    $query = "SELECT c.*, u.name FROM comments c 
                              JOIN users u ON c.user_id = u.id 
                              WHERE c.attraction_id = '$attr_id' ORDER BY c.created_at DESC";
                    $result = mysqli_query($conn, $query);

                    while ($row = mysqli_fetch_assoc($result)): ?>
                        <div class="single-comment-v2">
                            <div class="u-avatar-v2"><?php echo mb_substr($row['name'], 0, 1, 'utf-8'); ?></div>
                            <div class="u-text-v2">
                                <strong><?php echo $row['name']; ?></strong>
                                <p><?php echo $row['comment']; ?></p>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>
</div>

<script>
function updateHero(imgSrc) {
    document.getElementById('hero-bg').style.backgroundImage = "linear-gradient(rgba(0,0,0,0.6), rgba(0,0,0,0.6)), url('" + imgSrc + "')";
}
</script>







<div id="imageModal" class="modal">
  <span class="close" onclick="closeModal()">&times;</span>
  
  <a class="prev" onclick="changeImage(-1)">&#10094;</a>
  
  <img class="modal-content" id="img01">
  
  <a class="next" onclick="changeImage(1)">&#10095;</a>
</div>

<script>
// مصفوفة الصور الأصلية القادمة من السيرفر
var rawImages = <?php echo json_encode($all_images); ?>;

// الحل الذكي: بناء مصفوفة جديدة تحتوي على المسار الكامل الصحيح لكل الصور تلقائياً
var imagesArray = rawImages.map(function(img) {
    // إذا كان الاسم يحتوي مسبقاً على المجلد لا نكرره، وإلا نضيفه
    if (img.startsWith('img/attractions/')) {
        return img;
    } else {
        return 'img/attractions/' + img;
    }
});

var currentIndex = 0;

// دالة فتح الـ Modal
function openModal(imgSrc) {
    var modal = document.getElementById("imageModal");
    var modalImg = document.getElementById("img01");
    
    // الآن البحث سينجح 100% لأن المسارات متطابقة في المصفوفة
    currentIndex = imagesArray.indexOf(imgSrc);
    
    // إذا لم يجدها لأي سبب، نجعل المؤشر يبدأ من 0 كحماية للكود
    if (currentIndex === -1) {
        currentIndex = 0;
    }

    modal.style.display = "flex";
    modalImg.src = imgSrc;
}

// دالة تقليب الصور (يمين ويسار) المضمونة
function changeImage(n) {
    currentIndex += n;
    
    // حلقة دائرية تمنع خروج المؤشر عن النطاق
    if (currentIndex >= imagesArray.length) { 
        currentIndex = 0; 
    }
    if (currentIndex < 0) { 
        currentIndex = imagesArray.length - 1; 
    }
    
    // تمرير المسار الكامل المصلح مباشرة لمنع ظهور الأيقونة المكسورة
    document.getElementById("img01").src = imagesArray[currentIndex];
}

// دالة غلق الـ Modal
function closeModal() {
    document.getElementById("imageModal").style.display = "none";
}
</script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
$(document).ready(function() {
    $('#commentForm').on('submit', function(e) {
        e.preventDefault(); // هذا هو السر اللي يخلي الصفحة ما تطلعش للفوق

        var attr_id = $('#attr_id').val();
        var comment = $('#comment_text_area').val();

        $.ajax({
            url: 'add_comment_ajax.php', 
            method: 'POST',
            data: { id: attr_id, comment_text: comment },
            success: function(response) {
                $('.comments-list-v2').prepend(response);
                $('#comment_text_area').val(''); // تفريغ الخانة بعد الإرسال[cite: 3]
            }
        });
    });
});
</script>
</body>
</html>