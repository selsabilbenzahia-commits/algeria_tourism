<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$lang = $_GET['lang'] ?? ($_SESSION['lang'] ?? 'en');
$_SESSION['lang'] = $lang;

include 'lang.php';
include 'db.php';

// بناء روابط اللغة الذكية
$current_params = $_GET;
$current_params['lang'] = 'ar';
$ar_link = "?" . http_build_query($current_params);
$current_params['lang'] = 'en';
$en_link = "?" . http_build_query($current_params);

// جلب مسار الصورة من عمود profile_image كما هو في قاعدة البيانات تماماً
$header_user_image = "";
if (isset($_SESSION['user_id'])) {
    $current_u_id = $_SESSION['user_id'];
    $img_check_query = "SELECT profile_image FROM users WHERE id = '$current_u_id'";
    $img_check_res = mysqli_query($conn, $img_check_query);
    if ($img_check_res && mysqli_num_rows($img_check_res) > 0) {
        $img_row = mysqli_fetch_assoc($img_check_res);
        $header_user_image = trim($img_row['profile_image']); 
    }
}
?>
<!DOCTYPE html>
<html lang="<?= $_SESSION['lang'] ?>" dir="<?= $_SESSION['lang'] == 'ar' ? 'rtl' : 'ltr' ?>">

<head>
    <meta charset="UTF-8">
    <title>Explore Algeria | استكشف الجزائر</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>

<nav class="main-header-v3">
    <div class="header-container">
        
        <div class="header-left-side">
            <div class="logo-v3">
                <a href="index.php">
                    <span class="logo-main"><?= $texts[$lang]['logo_main'] ?> </span>
                    <span class="logo-sub"><?= $texts[$lang]['logo_sub'] ?></span>
                </a>
            </div>
        </div>

        <div class="header-nav">
            <ul class="nav-links-v3">
                <li><a href="index.php"><?= $texts[$lang]['home'] ?></a></li>
                <li><a href="index.php#wilayas-section"><?= $texts[$lang]['wilayas'] ?></a></li>
                <li><a href="index.php#attractions-v4"><?= $texts[$lang]['attractions'] ?></a></li>
                <li><a href="index.php#map"><?= $texts[$lang]['map'] ?></a></li>
                <div class="ai-trigger-v3" onclick="startAiDiscovery()" title="<?= $texts[$lang]['ai_assistant'] ?>">
                    <i class="fas fa-wand-magic-sparkles"></i>
                </div>
                <div class="search-section-v4">
                    <form action="search.php" method="GET" class="search-box-v4">
                        <input type="text" name="query" placeholder="<?= $texts[$lang]['search'] ?>" required>
                        <button type="submit"><i class="fas fa-search"></i></button>
                    </form>
                </div>
            </ul>
        </div>

        <div class="header-left-side">
            <div class="language-selector-v3">
                <div class="current-lang">
                    <img src="https://flagcdn.com/w20/<?= $flags[$lang] ?>.png">
                    <span><?= $_SESSION['lang'] == 'ar' ? 'AR' : 'EN' ?></span>
                    <i class="fas fa-chevron-down"></i>
                </div>
                <ul class="lang-dropdown-v3">
                    <li><a href="<?= $ar_link ?>"><img src="https://flagcdn.com/w20/dz.png"> العربية</a></li>
                    <li><a href="<?= $en_link ?>"><img src="https://flagcdn.com/w20/us.png"> English</a></li>
                </ul>
            </div>

            <div class="header-auth-section">
                <?php if(isset($_SESSION['user_id'])): ?>
                    <div class="user-profile-wrapper" onclick="toggleUserMenu(event)">
                        
                        <div class="user-avatar-circle">
                            <?php if(!empty($header_user_image)): ?>
                                <img src="<?php echo $header_user_image; ?>" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">
                            <?php else: ?>
                                <i class="fas fa-user"></i>
                            <?php endif; ?>
                        </div>

                        <div id="userDropdown" class="user-dropdown-menu">
                            <a href="profile.php"><i class="fas fa-id-card"></i><?= $texts[$lang]['profile'] ?></a>
                            <a href="logout.php" class="logout-item"><i class="fas fa-sign-out-alt"></i> <?= $texts[$lang]['logout'] ?></a>
                        </div>
                    </div>
                <?php else: ?>
                    <a href="login.php" class="auth-btn-v3"><?= $texts[$lang]['login'] ?></a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>

<style>
    .ai-trigger-v3 {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 38px;
        height: 38px;
        background: transparent;
        color: #c5a059;
        border: 1px solid rgba(197, 160, 89, 0.4);
        border-radius: 50%;
        margin-right: 12px;
        cursor: pointer;
        transition: all 0.3s ease;
        font-size: 16px;
    }
    .ai-trigger-v3:hover {
        background: #c5a059;
        color: #fff;
        box-shadow: 0 0 15px rgba(197, 160, 89, 0.4);
        transform: translateY(-2px);
    }
    .search-section-v4 { display: flex !important; align-items: center !important; justify-content: center !important; flex: 1.5 !important; margin: 0 30px !important; }
    .search-box-v4 { display: flex !important; align-items: center !important; background: rgba(255, 255, 255, 0.9) !important; border: 1px solid #ffffff !important; border-radius: 50px !important; width: 100% !important; max-width: 450px !important; height: 42px !important; padding: 0 20px !important; box-shadow: 0 4px 15px rgba(0,0,0,0.1) !important; transition: all 0.3s ease !important; }
    .search-box-v4 input { background: transparent !important; border: none !important; outline: none !important; color: #333 !important; width: 100% !important; font-family: 'Cairo', sans-serif !important; font-size: 14px !important; }
    .search-box-v4 button { background: transparent !important; border: none !important; color: #c5a059 !important; cursor: pointer !important; }
    .user-dropdown-menu.show { display: block !important; opacity: 1 !important; visibility: visible !important; }
</style>

<script>
function startAiDiscovery() {
    Swal.fire({
        title: '<?= $texts[$lang]['ai_assistant'] ?>',
        text: '<?= $texts[$lang]['searching_location'] ?>',
        icon: 'info',
        showConfirmButton: false,
        timer: 1500,
        didOpen: () => { Swal.showLoading() }
    });

    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition((position) => {
            const userLat = position.coords.latitude;
            const userLng = position.coords.longitude;

            const mapSection = document.getElementById('map');
            if (mapSection) {
                mapSection.scrollIntoView({ behavior: 'smooth' });
                setTimeout(() => {
                    if (window.map) {
                        window.map.flyTo([userLat, userLng], 12, { animate: true, duration: 2 });
                        L.marker([userLat, userLng]).addTo(window.map)
                            .bindPopup('<b>أنت هنا حالياً</b>').openPopup();
                    }
                    Swal.fire({
                        title: '<?= $texts[$lang]['nearby_places'] ?>',
                        text: '<?= $texts[$lang]['location_success'] ?>',
                        icon: 'success',
                        confirmButtonText: 'OK'
                    });
                }, 1000);
            } else {
                window.location.href = "index.php?lat=" + userLat + "&lng=" + userLng;
            }
        }, () => {
            Swal.fire('Error', '<?= $texts[$lang]['location_denied'] ?>', 'error');
        });
    }
}

// دالة الضغط الأصلية مع منع انتشار الحدث لضمان عمل الاختفاء التلقائي بسلاسة
function toggleUserMenu(event) {
    if (event) event.stopPropagation();
    document.getElementById("userDropdown").classList.toggle("show");
}

// كود إغلاق النافذة المنسدلة تلقائياً عند الضغط في أي مكان فارغ بالواجهة
window.addEventListener('click', function(e) {
    const dropdown = document.getElementById('userDropdown');
    if (dropdown && dropdown.classList.contains('show')) {
        dropdown.classList.remove('show');
    }
});
</script>