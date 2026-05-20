<?php 
session_start(); 
include('lang.php'); 
include 'db.php'; 

if(isset($_GET['lang'])){
    $lang = ($_GET['lang'] == 'en') ? 'en' : 'ar';
    $_SESSION['lang'] = $lang;
} else {
    $lang = isset($_SESSION['lang']) ? $_SESSION['lang'] : 'ar';
}

$dir = ($lang == 'ar') ? 'rtl' : 'ltr';

$user_favs = [];
if(isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $fav_query = "SELECT item_id, item_type FROM favorites WHERE user_id = '$user_id'";
    $fav_res = mysqli_query($conn, $fav_query);
    while($fav_row = mysqli_fetch_assoc($fav_res)) {
        $user_favs[] = $fav_row['item_type'] . '_' . $fav_row['item_id'];
    }
}
?>
<!DOCTYPE html>
<html lang="<?php echo $lang; ?>" dir="<?php echo $dir; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $texts[$lang]['unesco_title']; ?></title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;700&display=swap" rel="stylesheet">
</head>
<body>
<?php include 'header.php'; ?>

<section class="all-wilayas-page">
    <div class="container">
        
        <header class="page-header" style="margin-bottom: 40px; text-align: center;">
            <h1 class="page-title"><?php echo $texts[$lang]['unesco_title']; ?></h1>
            <div class="title-line" style="width: 80px; height: 3px; background: #c5a059; margin: 10px auto;"></div>
        </header>

        <div class="wilayas-grid">
            <?php
            // استعلام تصفية معالم اليونسكو بالاعتماد على الحقل الحقيقي في جدولك is_unesco = 1
            $sql = "SELECT a.*, i.image AS attraction_image 
                    FROM attractions a 
                    LEFT JOIN attraction_images i ON a.id = i.attraction_id 
                    WHERE a.is_unesco = 1 
                    GROUP BY a.id 
                    ORDER BY a.id DESC";
            
            $res = mysqli_query($conn, $sql);
            
            if($res && mysqli_num_rows($res) > 0) {
                while($row = mysqli_fetch_assoc($res)) {
                    $card_size = 'wilaya-card-standard'; 
                    $imageUrl = $row['attraction_image'] ? $row['attraction_image'] : 'default.jpg'; 
            ?>
                <div class="wilaya-card <?php echo $card_size; ?>">
                    <div class="wilaya-img-wrapper">
                        <img src="img/attractions/<?php echo $imageUrl; ?>" alt="<?php echo ($lang == 'ar') ? $row['name_ar'] : $row['name_en'];?>" class="wilaya-main-img">
                        
                        <button class="card-icon-btn wishlist-btn main-fav-btn" data-id="<?php echo $row['id']; ?>" data-type="attraction">
                            <i class="<?php echo in_array('attraction_'.$row['id'], $user_favs) ? 'fas fa-heart' : 'far fa-heart'; ?>" 
                               style="<?php echo in_array('attraction_'.$row['id'], $user_favs) ? 'color: #ff4757;' : ''; ?>"></i>
                        </button>
                        
                        <button class="card-icon-btn loc-btn-v4" title="Attraction location" 
                                onclick="showMapModal('<?php echo $row['lat']; ?>', '<?php echo $row['lng']; ?>', '<?php echo addslashes(($lang == 'ar') ? $row['name_ar'] : $row['name_en']); ?>')">
                            <i class="fas fa-map-marker-alt"></i>
                        </button>

                        <div class="wilaya-overlay">
                            <div class="wilaya-content">
                                <h3><?php echo ($lang == 'ar') ? $row['name_ar'] : $row['name_en']; ?></h3>
                                <p><?php echo mb_substr(($lang == 'ar') ? $row['description_ar'] : $row['description_en'], 0, 95, 'utf-8') . '...'; ?></p>
                                <a href="attraction_details.php?id=<?php echo $row['id']; ?>&lang=<?= $lang ?>" class="explore-btn"> <?php echo $texts[$lang]['explore_att']; ?></a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php 
                } 
            } 
            ?>
        </div>
    </div>
</section>

<div id="mapModal" style="display:none; position:fixed; z-index:9999; left:0; top:0; width:100%; height:100%; background:rgba(0,0,0,0.7);">
    <div style="background:#fff; width:80%; max-width:800px; margin:5% auto; padding:20px; border-radius:15px; position:relative; height:70vh;">
        <span onclick="closeMapModal()" style="position:absolute; top:10px; right:20px; font-size:30px; cursor:pointer; font-weight:bold;">×</span>
        <h3 id="modalTitle" style="margin-bottom:15px; color:#333;">Location</h3>
        <div id="mapContainer" style="width:100%; height:85%; border-radius:10px; overflow:hidden;">
            <iframe id="googleMapFrame" width="100%" height="100%" frameborder="0" style="border:0;" allowfullscreen="" loading="lazy"></iframe>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    $('.main-fav-btn').click(function(e) {
        e.preventDefault();
        var btn = $(this);
        var id = btn.data('id');
        var type = btn.data('type');
        var icon = btn.find('i');

        $.ajax({
            url: 'favorite.php', 
            method: 'POST',
            data: { item_id: id, item_type: type },
            success: function(response) {
                var res = response.trim();
                if(res == 'added') {
                    icon.removeClass('far').addClass('fas').css('color', '#ff4757');
                } else if(res == 'removed') {
                    icon.removeClass('fas').addClass('far').css('color', '');
                } else if(res == 'login_required') {
                    alert('Sorry! You must log in first to add this item to your favorites');
                }
            }
        });
    });
});

function showMapModal(lat, lng, name) {
    if (!lat || lat === '0' || !lng || lng === '0') {
        alert('Location coordinates are currently unavailable');
        return;
    }
    document.getElementById('modalTitle').innerText = name;
    const mapUrl = `https://maps.google.com/maps?q=${lat},${lng}&hl=<?php echo $lang; ?>&z=15&output=embed`;
    document.getElementById('googleMapFrame').src = mapUrl;
    document.getElementById('mapModal').style.display = 'block';
}

function closeMapModal() {
    document.getElementById('mapModal').style.display = 'none';
    document.getElementById('googleMapFrame').src = ''; 
}
</script>
</body>
</html>