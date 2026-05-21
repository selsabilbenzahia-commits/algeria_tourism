<?php
include('lang.php');
include 'db.php'; 
include 'header.php'; 

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$query = isset($_GET['query']) ? mysqli_real_escape_string($conn, $_GET['query']) : '';
$col_name = ($lang == 'ar') ? 'name_ar' : 'name_en';
?>

<!DOCTYPE html>
<html lang="<?= $lang ?>" dir="<?= ($lang == 'ar') ? 'rtl' : 'ltr' ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $texts[$lang]['result_s'] ?> "<?= htmlspecialchars($query) ?>"</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        .results-page-container { max-width: 1200px; margin: 120px auto 60px; padding: 0 20px; }
        .search-info-header { margin-bottom: 40px; border-inline-start: 6px solid #c5a059; padding-inline-start: 20px; }
        .results-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 25px; }
        .wilaya-card { background: #fff; border-radius: 15px; overflow: hidden; box-shadow: 0 5px 15px rgba(0,0,0,0.08); position: relative; transition: 0.3s; }
        .wilaya-card:hover { transform: translateY(-5px); }
        .img-container { position: relative; height: 200px; overflow: hidden; }
        .img-container img { width: 100%; height: 100%; object-fit: cover; }
        .card-actions { position: absolute; top: 10px; right: 10px; display: flex; flex-direction: column; gap: 8px; z-index: 10; }
        .action-btn { background: white; border: none; width: 35px; height: 35px; border-radius: 50%; display: flex; align-items: center; justify-content: center; cursor: pointer; box-shadow: 0 2px 5px rgba(0,0,0,0.2); transition: 0.3s; }
        .wilaya-info { padding: 15px; text-align: center; }
        .wilaya-info h3 { margin: 0; font-size: 18px; color: #333; }
        .explore-link { display: block; margin-top: 10px; color: #c5a059; text-decoration: none; font-weight: bold; font-size: 14px; }
        .empty-state { text-align: center; padding: 100px 0; color: #666; }
        .empty-state i { font-size: 50px; margin-bottom: 20px; color: #ccc; }
    </style>
</head>
<body>

<div class="results-page-container">
    
    <?php if (!empty($query)): ?>
        <div class="search-info-header">
            <h1><?= $texts[$lang]['result_s'] ?> <span style="color: #c5a059;">"<?= htmlspecialchars($query) ?>"</span></h1>
            <p><?= $texts[$lang]['serch_result'] ?></p>
        </div>

        <?php
        $found_anything = false;
        $current_user_id = $_SESSION['user_id'] ?? 0;

        $sql_w = "SELECT * FROM wilayas 
                  WHERE (name_ar LIKE '%$query%' OR name_en LIKE '%$query%') 
                  AND image IS NOT NULL AND image != '' 
                  AND lat != 0 AND lat IS NOT NULL";
        
        $res_w = mysqli_query($conn, $sql_w);
        
        if ($res_w && mysqli_num_rows($res_w) > 0): 
            $found_anything = true;
        ?>
            <section class="results-section">
                <div class="section-title"><h3><i class="fas fa-map-marked-alt"></i> <?= $texts[$lang]['wilayas'] ?></h3></div>
                <div class="results-grid">
                    <?php while($w = mysqli_fetch_assoc($res_w)): 
                        $w_img = "img/wilayas/" . $w['image'];
                        
                        $is_fav_w = false;
                        if($current_user_id > 0) {
                            $check_f = mysqli_query($conn, "SELECT id FROM favorites WHERE user_id = $current_user_id AND item_id = {$w['id']} AND item_type = 'wilaya'");
                            if(mysqli_num_rows($check_f) > 0) $is_fav_w = true;
                        }
                    ?>
                        <div class="wilaya-card">
                            <div class="img-container">
                                <img src="<?= $w_img ?>" alt="<?= $w[$col_name] ?>">
                                <div class="card-actions">
                                    <button class="action-btn main-fav-btn" data-id="<?= $w['id'] ?>" data-type="wilaya">
                                        <i class="<?= $is_fav_w ? 'fas' : 'far' ?> fa-heart" <?= $is_fav_w ? 'style="color: #ff4757;"' : '' ?>></i>
                                    </button>
                                    <button class="action-btn" onclick="showMapModal('<?= $w['lat'] ?>', '<?= $w['lng'] ?>', '<?= $w[$col_name] ?>')">
                                        <i class="fas fa-location-dot"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="wilaya-info">
                                <h3><?= $w[$col_name] ?></h3>
                                <a href="wilaya.php?id=<?= $w['id'] ?>" class="explore-link"><?= $texts[$lang]['explore_btt'] ?></a>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            </section>
        <?php endif; ?>

        <?php
        $sql_a = "SELECT a.*, c.$col_name as cat_name, 
                  (SELECT image FROM attraction_images WHERE attraction_id = a.id LIMIT 1) as attr_img
                  FROM attractions a 
                  LEFT JOIN categories c ON a.categorie_id = c.id 
                  WHERE (a.name_en LIKE '%$query%' OR a.name_ar LIKE '%$query%')
                  GROUP BY a.id";

        $res_a = mysqli_query($conn, $sql_a);
        
        if ($res_a && mysqli_num_rows($res_a) > 0): 
            $found_anything = true;
        ?>
            <section class="results-section" style="margin-top: 50px;">
                <div class="section-title"><h3><i class="fas fa-camera-retro"></i> <?= $texts[$lang]['attractions'] ?></h3></div>
                <div class="results-grid">
                    <?php while($a = mysqli_fetch_assoc($res_a)): 
                        $a_img = !empty($a['attr_img']) ? "img/attractions/" . $a['attr_img'] : "img/default.jpg";
                        
                        $is_fav_a = false;
                        if($current_user_id > 0) {
                            $check_f_a = mysqli_query($conn, "SELECT id FROM favorites WHERE user_id = $current_user_id AND item_id = {$a['id']} AND item_type = 'attraction'");
                            if(mysqli_num_rows($check_f_a) > 0) $is_fav_a = true;
                        }
                    ?>
                        <div class="wilaya-card">
                            <div class="img-container">
                                <img src="<?= $a_img ?>" alt="<?= ($lang == 'ar') ? $a['name_ar'] : $a['name_en'] ?>">
                                <div class="card-actions">
                                    <button class="action-btn main-fav-btn" data-id="<?= $a['id'] ?>" data-type="attraction">
                                        <i class="<?= $is_fav_a ? 'fas' : 'far' ?> fa-heart" <?= $is_fav_a ? 'style="color: #ff4757;"' : '' ?>></i>
                                    </button>
                                    <button class="action-btn" onclick="showMapModal('<?= $a['lat'] ?>', '<?= $a['lng'] ?>', '<?= ($lang == 'ar') ? $a['name_ar'] : $a['name_en'] ?>')">
                                        <i class="fas fa-location-dot"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="wilaya-info">
                                <span style="font-size: 12px; color: #c5a059;"><?= $a['cat_name'] ?></span>
                                <h3><?= ($lang == 'ar') ? $a['name_ar'] : $a['name_en'] ?></h3>
                                <a href="attraction_details.php?id=<?= $a['id'] ?>" class="explore-link"><?= $texts[$lang]['det_att'] ?></a>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            </section>
        <?php endif; ?>

        <?php if (!$found_anything): ?>
            <div class="empty-state">
                <i class="fas fa-search-location"></i>
                <h2><?= $texts[$lang]['excu'] ?></h2>
                <p><?= $texts[$lang]['try_search'] ?></p>
            </div>
        <?php endif; ?>

    <?php endif; ?>
</div>

<div id="mapModal" style="display:none; position:fixed; z-index:9999; left:0; top:0; width:100%; height:100%; background:rgba(0,0,0,0.7);">
    <div style="background:#fff; width:80%; max-width:800px; margin:5% auto; padding:20px; border-radius:15px; position:relative; height:70vh;">
        <span onclick="closeMapModal()" style="position:absolute; top:10px; right:20px; font-size:30px; cursor:pointer; font-weight:bold;">×</span>
        <h3 id="modalTitle" style="margin-bottom:15px;">Location</h3>
        <iframe id="googleMapFrame" width="100%" height="85%" frameborder="0" style="border-radius:10px;"></iframe>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    $('.main-fav-btn').click(function() {
        var btn = $(this);
        var icon = btn.find('i');
        $.post('favorite.php', { item_id: btn.data('id'), item_type: btn.data('type') }, function(res) {
            if(res.trim() == 'added') icon.removeClass('far').addClass('fas').css('color', '#ff4757');
            else if(res.trim() == 'removed') icon.removeClass('fas').addClass('far').css('color', '');
            else alert('<?= ($lang == "ar") ? "يرجى تسجيل الدخول أولاً" : "Please login first" ?>');
        });
    });
});

function showMapModal(lat, lng, name) {
    document.getElementById('modalTitle').innerText = name;
    document.getElementById('googleMapFrame').src = `https://maps.google.com/maps?q=${lat},${lng}&hl=<?= $lang ?>&z=15&output=embed`;
    document.getElementById('mapModal').style.display = 'block';
}
function closeMapModal() { document.getElementById('mapModal').style.display = 'none'; }
</script>

<?php include 'footer.php'; ?>
</body>
</html>