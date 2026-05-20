<?php 
session_start();

include('lang.php');

if (isset($_GET['lang'])) {
    $lang = $_GET['lang'];
    $_SESSION['lang'] = $lang; 
} elseif (isset($_SESSION['lang'])) {
    $lang = $_SESSION['lang'];
} else {
    $lang = 'ar';
}
include 'db.php'; 

if (isset($_GET['id'])) {
    $attr_id = $_GET['id'];
}
$user_favs = []; 
if(isset($_SESSION['user_id'])) {
    $uid = $_SESSION['user_id'];
    $get_favs = mysqli_query($conn, "SELECT item_id, item_type FROM favorites WHERE user_id = '$uid'");
    while($f = mysqli_fetch_assoc($get_favs)) {
        $user_favs[] = $f['item_type'] . "_" . $f['item_id'];
    }
}

$id = isset($_GET['id']) ? $_GET['id'] : 1; 

$w_sql = "SELECT * FROM wilayas WHERE id = $id";
$w_res = mysqli_query($conn, $w_sql);
$wilaya = mysqli_fetch_assoc($w_res);
?>
<?php
if (isset($_POST['submit_comment']) && isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $wilaya_id = $id; 
    $comment_text = mysqli_real_escape_string($conn, $_POST['comment_text']);

    if (!empty($comment_text)) {
        $sql = "INSERT INTO comments (user_id, wilaya_id, comment) VALUES ('$user_id', '$wilaya_id', '$comment_text')";
        mysqli_query($conn, $sql);
        header("Location: wilaya.php?id=" . $wilaya_id);
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="<?php echo $lang; ?>" dir="<?php echo ($lang == 'ar') ? 'rtl' : 'ltr'; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo ($lang == 'ar') ? $wilaya['name_ar'] : $wilaya['name_en']; ?>  A luxurious tourist experience  </title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
<?php include 'header.php'; ?>


<section class="hero-premium-v2" style="background-image: url('img/wilayas/<?php echo $wilaya['image']; ?>');">
    <div class="overlay-v2">
        <div class="hero-content-v2">
            <span class="welcome-tag"><?php echo $texts[$lang]['welcome_to']; ?></span>
            <h1><?php echo ($lang == 'ar') ? $wilaya['name_ar'] : $wilaya['name_en']; ?></h1>
            <div class="weather-box-v2">
                <i class="fas fa-cloud-sun"></i>
                <span> Partly sunny - 24°C </span>
            </div>
        </div>
    </div>
</section>

<section class="info-section-v2">
    <div class="container">
        <div class="info-right-box">
            <h2 class="section-title-v2"><?php echo $texts[$lang]['about_wilaya']; ?></h2>
            <div class="gold-line-right"></div>
            
            <div class="description-wrapper" id="descWrapper">
                <p class="desc-text-v2" id="descText">
                    <?php echo ($lang == 'ar') ? $wilaya['description_ar']: $wilaya['description_en']; ?>
                </p>
            </div>
            
            <button onclick="toggleDescription()" id="readMoreBtn" class="read-more-btn">
                <?php echo ($lang == 'ar') ? 'عرض المزيد' : 'Read More'; ?>
            </button>
        </div>
    </div>
</section>

<div class="filter-bar-v2">
    <div class="container">
        <div class="filter-flex-right">
            <button class="filter-btn-v2 active" data-filter="all"><?= $texts[$lang]['all'] ?></button>
            <?php 
            $cat_res = mysqli_query($conn, "SELECT * FROM categories");
            while($cat = mysqli_fetch_assoc($cat_res)) {
                $name = ($lang == 'ar') ? $cat['name_ar'] : $cat['name_en'];
                echo '<button class="filter-btn-v2" data-filter="cat-'.$cat['id'].'">'.$name.'</button>';
            }
            ?>
            <button class="filter-btn-v2" data-filter="hotels"><?php echo $texts[$lang]['hotel']; ?></button>
            <button class="filter-btn-v2" data-filter="restaurants"><?php echo $texts[$lang]['restaurant']; ?></button>
        </div>
    </div>
</div>

<section class="content-section-v2">
    <div class="container">
        
        <div class="section-group-v2 all-sections" id="attr-group">
            <h2 class="group-title-v2"><?php echo $texts[$lang]['tourist_attractions']; ?> </h2>
            <div class="luxury-grid-v2">
                <?php 
  $attr_sql = "SELECT a.*, (SELECT image FROM attraction_images WHERE attraction_id = a.id LIMIT 1) as img FROM attractions a WHERE a.wilaya_id = $id";
   $attr_res = mysqli_query($conn, $attr_sql);
  while($row = mysqli_fetch_assoc($attr_res)) { 
      $is_fav = in_array('attraction_'.$row['id'], $user_favs); ?>
                    <div class="card-full-img all cat-<?php echo $row['categorie_id']; ?>">
                        <img src="img/attractions/<?php echo $row['img']; ?>"alt="" class="bg-img">
                        <div class="card-overlay-v3">
                            <div class="card-top-icons">
                                <button class="icon-circle map" onclick="openPopupMap('<?php echo $row['lat']; ?>', '<?php echo $row['lng']; ?>', '<?php echo ($lang == 'ar') ? $row['name_ar'] : $row['name_en']; ?>')"><i class="fas fa-location-dot"></i></button>
                                <button class="icon-circle heart w-fav-btn" data-id="<?php echo $row['id']; ?>" data-type="attraction">
                                    <i class="<?php echo $is_fav ? 'fas fa-heart' : 'far fa-heart'; ?>"></i>
                                </button>
                            </div>
                            <div class="card-content-v3">
                                <h3><?php echo ($lang == 'ar') ? $row['name_ar'] : $row['name_en']; ?></h3>
                                <a href="attraction_details.php?id=<?php echo $row['id']; ?>" class="btn-explore-v3"><?php echo $texts[$lang]['explore_thet_attraction']; ?></a>
                            </div>
                        </div>
                    </div>
                <?php } ?>
            </div>
        </div>

        <div class="section-group-v2 all-sections" id="rest-group">
    <h2 class="group-title-v2"><?php echo $texts[$lang]['best_restaurants']; ?> </h2>
    <div class="luxury-grid-v2">
<?php 
$r_res = mysqli_query($conn, "SELECT * FROM restaurants WHERE wilaya_id = $id");
while($r = mysqli_fetch_assoc($r_res)) { 
    $is_fav = in_array('restaurant_'.$r['id'], $user_favs); 
?>
    <div class="card-full-img all restaurants">
        <img src="img/restaurants/<?php echo $r['image']; ?>" alt="" class="bg-img">
        <div class="card-overlay-v3">
            <div class="card-top-icons">
                <button class="icon-circle map" onclick="openPopupMap('<?php echo $r['lat']; ?>', '<?php echo $r['lng']; ?>', '<?php echo $r['name_en']; ?>')">
                    <i class="fas fa-location-dot"></i>
                </button>
                <button class="icon-circle heart w-fav-btn" data-id="<?php echo $r['id']; ?>" data-type="restaurant">
                    <i class="<?php echo $is_fav ? 'fas fa-heart' : 'far fa-heart'; ?>"></i>
                </button>
            </div>
            <div class="card-content-v3">
                <h3><?php echo $r['name_en']; ?></h3>
                <span class="badge-v3"><?php echo $texts[$lang]['restaurant']; ?></span>
            </div>
        </div>
    </div>
<?php } ?>
    </div>
</div>

        <div class="section-group-v2 all-sections" id="hotel-group">
            <h2 class="group-title-v2"><?php echo $texts[$lang]['recommended_hotels']; ?></h2>
            <div class="luxury-grid-v2">
                <?php 
                $h_res = mysqli_query($conn, "SELECT * FROM hotels WHERE wilaya_id = $id");
                while($h = mysqli_fetch_assoc($h_res)) { 
                    $is_fav = in_array('hotel_'.$h['id'], $user_favs); ?>
                    <div class="card-full-img all hotels">
                        <img src="img/hotels/<?php echo $h['image']; ?>" alt="" class="bg-img">
                        <div class="card-overlay-v3">
                            <div class="card-top-icons">
                                <button class="icon-circle map" onclick="openPopupMap('<?php echo $h['lat']; ?>', '<?php echo $h['lng']; ?>', '<?php echo $h['name_en']; ?>')"><i class="fas fa-location-dot"></i></button>
                                <button class="icon-circle heart w-fav-btn" data-id="<?php echo $h['id']; ?>" data-type="hotel">
                                    <i class="<?php echo $is_fav ? 'fas fa-heart' : 'far fa-heart'; ?>"></i>
                                </button>
                            </div>
                            <div class="card-content-v3">
                                <h3><?php echo $h['name_en']; ?></h3>
                                <span class="badge-v3"><?php echo $texts[$lang]['hotel']; ?></span>
                            </div>
                        </div>
                    </div>
                <?php } ?>
            </div>
        </div>

    </div>
</section>

<section class="reviews-section-v2">
    <div class="container">
        <div class="reviews-right-wrapper">
            <h3 class="section-title-v2"><?php echo $texts[$lang]['opinions']; ?></h3>
            <div class="gold-line-right"></div>

            <?php if (isset($_SESSION['user_id'])): ?>
                <form id="wilayaCommentForm" class="comment-form-v2">
                    <input type="hidden" id="w_id" value="<?php echo $id; ?>">
                    <textarea id="w_comment_text" required name="comment_text" placeholder="...Add your comment here" required></textarea>
                    <button type="submit" class="submit-comment-v2"> <?php echo $texts[$lang]['publish']; ?></button>
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
                              WHERE c.wilaya_id = '$id' ORDER BY c.created_at DESC";
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
<?php include 'footer.php'; ?>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script> 
document.querySelectorAll('.filter-btn-v2').forEach(btn => {
    btn.addEventListener('click', function() {
        document.querySelector('.filter-btn-v2.active').classList.remove('active');
        this.classList.add('active');
        let filter = this.getAttribute('data-filter');
        document.querySelectorAll('.card-full-img').forEach(card => {
            if (filter === 'all' || card.classList.contains(filter)) { card.style.display = 'block'; }
            else { card.style.display = 'none'; }
        });
        document.querySelectorAll('.section-group-v2').forEach(group => {
            if (filter === 'all') { group.style.display = 'block'; }
            else {
                const hasVisible = group.querySelectorAll('.' + filter).length > 0;
                group.style.display = hasVisible ? 'block' : 'none';
            }
        });
    });
});

function openPopupMap(lat, lng, title) {
    if(!lat || !lng) { alert("The site is not available"); return; }
    const url = `https://maps.google.com/maps?q=${lat},${lng}&hl=ar&z=15&output=embed`;
    const mapHtml = `
        <div id="mapModal" style="position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.8);z-index:9999;display:flex;align-items:center;justify-content:center;">
            <div style="background:#fff;width:90%;max-width:800px;border-radius:25px;overflow:hidden;position:relative;">
                <button onclick="document.getElementById('mapModal').remove()" style="position:absolute;top:15px;left:15px;background:#fff;border:none;width:35px;height:35px;border-radius:50%;cursor:pointer;">✕</button>
                <iframe src="${url}" width="100%" height="450" frameborder="0"></iframe>
            </div>
        </div>`;
    document.body.insertAdjacentHTML('beforeend', mapHtml);
}

$(document).on('click', '.w-fav-btn', function() {
    <?php if(!isset($_SESSION['user_id'])): ?>
        alert('You must log in first to add the item to your favorites.');
    <?php else: ?>
        var btn = $(this);
        var icon = btn.find('i');
        $.ajax({
            url: 'favorite.php',
            method: 'POST',
            data: { item_id: btn.data('id'), item_type: btn.data('type') },
            success: function(res) {
                if(res.trim() == 'added') {
                    icon.removeClass('far').addClass('fas').css('color', '#ff4757');
                } else {
                    icon.removeClass('fas').addClass('far').css('color', '');
                }
            }
        });
    <?php endif; ?>
});
</script>
<script>
$(document).ready(function() {
    $('#wilayaCommentForm').on('submit', function(e) {
        e.preventDefault(); 

        var w_id = $('#w_id').val();
        var comment = $('#w_comment_text').val();

        $.ajax({
            url: 'add_comment_ajax.php', 
            method: 'POST',
            data: { 
                wilaya_id: w_id, 
                comment_text: comment 
            },
            success: function(response) {
                $('.comments-list-v2').prepend(response);
                $('#w_comment_text').val(''); 
            }
        });
    });
});
</script>
<script>
function toggleDescription() {
    var wrapper = document.getElementById("descWrapper");
    var btn = document.getElementById("readMoreBtn");
    var isAr = "<?php echo $lang; ?>" === "ar";

    if (wrapper.classList.contains("expanded")) {
        wrapper.classList.remove("expanded");
        btn.innerHTML = isAr ? "عرض المزيد" : "Read More";
    } else {
        wrapper.classList.add("expanded");
        btn.innerHTML = isAr ? "عرض أقل" : "Show Less";
    }
}
</script>
</body>
</html>