<?php include 'header.php'; ?>
<?php

include "db.php";
$user_favs = []; 
if(isset($_SESSION['user_id'])) {
    $uid = $_SESSION['user_id'];
    $get_favs = mysqli_query($conn, "SELECT item_id, item_type FROM favorites WHERE user_id = '$uid'");
    while($f = mysqli_fetch_assoc($get_favs)) {
        $user_favs[] = $f['item_type'] . "_" . $f['item_id'];
    }
}

// جلب الإحصائيات بدقة
$res_wilayas = mysqli_query($conn, "SELECT COUNT(*) as total FROM wilayas  WHERE image IS NOT NULL AND image != '' ORDER BY code ASC");
$count_wilayas = mysqli_fetch_assoc($res_wilayas)['total'];

$res_attractions = mysqli_query($conn, "SELECT COUNT(*) as total FROM attractions");
$count_attractions = mysqli_fetch_assoc($res_attractions)['total'];

$res_unesco = mysqli_query($conn, "SELECT COUNT(*) as total FROM attractions WHERE is_unesco = 1");
$count_unesco = mysqli_fetch_assoc($res_unesco)['total'];
?>

<div class="swiper mySwiper">
    <div class="swiper-wrapper">
        <?php
        $slides = [
            [
                "title" => $texts[$lang]['slide1_title'],
                "desc"  => $texts[$lang]['slide1_desc'],
                "img"   => "https://www.algerie360.com/wp-content/uploads/2024/04/sahara-algerie-1-scaled.jpg"
    ],
    [
                 "title" => $texts[$lang]['slide2_title'],
                  "desc"  => $texts[$lang]['slide2_desc'],
                 "img"   => "https://www.elbilad.net/storage/images/article/d_6c9e09f622f17282f5eeade9c944c52e.jpg"
    ],
    [
                 "title" => $texts[$lang]['slide3_title'],
                  "desc"  => $texts[$lang]['slide3_desc'],
                 "img"   => "https://timenews.nl/wp-content/uploads/2023/09/%D9%82%D8%B5%D8%A8%D8%A9-%D8%A7%D9%84%D8%AC%D8%B2%D8%A7%D8%A6%D8%B1.jpg"
    ],
    [       
                 "title" => $texts[$lang]['slide4_title'],
                  "desc"  => $texts[$lang]['slide4_desc'],
                 "img"   => "https://i.pinimg.com/1200x/9a/22/f7/9a22f7e7b0f4cfbbec3fde9e8d8f8d6a.jpg"
    ]
        ];

        foreach($slides as $slide) { ?>
            <div class="swiper-slide" style="background-image: url('<?php echo $slide['img']; ?>'); background-size: cover; background-position: center;">
                <div class="hero-overlay">
                    <h1 style="font-family: 'Cairo', sans-serif; font-weight: 800; text-transform: none;"><?php echo $slide['title']; ?></h1>
                    <p style="font-size: 24px; color: var(--gold); margin-top: 10px;"><?php echo $slide['desc']; ?></p>
                
                        <div style="margin-top: 35px;">
    <a href="#wilayas-section" class="hero-cta-btn"><?= $texts[$lang]['hero_butt'] ?><i class="fas fa-arrow-down"></i></a>
</div>
                
                </div>
            </div>
        <?php } ?>
    </div>
    
    <div class="swiper-button-next" style="color: white;"></div>
    <div class="swiper-button-prev" style="color: white;"></div>
    <div class="swiper-pagination"></div>
</div>




<section class="wilayas-section" id="wilayas-section">
    <div class="container">
        <div class="section-header-flex">
            <h2 class="main-title"> <?= $texts[$lang]['main_title'] ?></h2>
            <a href="all_wilayas.php" class="all-wilayas-link"> <?= $texts[$lang]['all_wilayas'] ?><i class="fas <?= ($lang == 'ar') ? 'fa-arrow-left' : 'fa-arrow-right' ?>"></i></i></a>
        </div>


        <div class="wilayas-grid">
            <?php
            
            $specific_ids = "16, 31, 13, 25, 42";
            $sql = "SELECT * FROM wilayas WHERE id IN ($specific_ids) ORDER BY FIELD(id, $specific_ids)";
            $res = mysqli_query($conn, $sql);
            
            $count = 0;
            while($row = mysqli_fetch_assoc($res)) {
                $count++;
                $card_size = ($count == 1) ? 'wilaya-card-large' : 'wilaya-card-standard';
            
                $imageUrl = $row['image']; 

                
            ?>
                <div class="wilaya-card <?php echo $card_size; ?>">
                    <div class="wilaya-img-wrapper">
                        <img src="img/wilayas/<?php echo $imageUrl; ?>" alt="<?php echo ($lang == 'ar') ? $row['name_ar'] : $row['name_en'];?>" class="wilaya-main-img">
                        
                        <button class="card-icon-btn wishlist-btn main-fav-btn" data-id="<?php echo $row['id']; ?>" data-type="wilaya">
    <i class="<?php echo in_array('wilaya_'.$row['id'], $user_favs) ? 'fas fa-heart' : 'far fa-heart'; ?>" 
       style="<?php echo in_array('wilaya_'.$row['id'], $user_favs) ? 'color: #ff4757;' : ''; ?>"></i>
</button>
                        
                        <button class="card-icon-btn loc-btn-v4" title="Wilaya location" 
       onclick="showMapModal('<?php echo $row['lat']; ?>', '<?php echo $row['lng']; ?>', '<?php echo addslashes(($lang == 'ar') ? $row['name_ar'] : $row['name_en']); ?>')">
    <i class="fas fa-map-marker-alt"></i>
</button>

                        <div class="wilaya-overlay">
                            <div class="wilaya-content">
                                <h3><?php echo ($lang == 'ar') ? $row['name_ar'] : $row['name_en']; ?></h3>
                                <p><?php echo mb_substr(($lang == 'ar') ? $row['description_ar'] : $row['description_en'], 0, 95, 'utf-8') . '...'; ?></p>
                                <a href="wilaya.php?id=<?php echo $row['id']; ?>&lang=<?= $lang ?>" class="explore-btn"> <?= $texts[$lang]['explore_btt'] ?></a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php } ?>
        </div>
    </div>
</section>

<section id="attractions-v4" class="attractions-lux-section">
    <div class="container-fluid">
        <h2 class="section-title-v4"><?= $texts[$lang]['section_tit'] ?></h2>

        <div class="category-filter-v4">
            <button class="filter-btn-v4 active" data-id="all"><?= $texts[$lang]['all'] ?></button>
            <?php
            $cat_query = "SELECT * FROM categories"; 
            $cat_res = mysqli_query($conn, $cat_query);
            while($cat = mysqli_fetch_assoc($cat_res)) {
                $name = ($lang == 'ar') ? $cat['name_ar'] : $cat['name_en'];

                echo '<button class="filter-btn-v4" data-id="'.$cat['id'].'">'.$name.'</button>';
            }
            ?>
        </div>

        <div class="slider-wrapper-v4">
            <button class="nav-arrow prev" onclick="scrollSlider(-1)">❮</button>
            
            <div class="horizontal-slider-v4" id="monumentSlider">
                <?php
               $query = "SELECT a.*, 
          (SELECT image FROM attraction_images WHERE attraction_id = a.id LIMIT 1) as main_image 
          FROM attractions a";
$res = mysqli_query($conn, $query);

while($attr = mysqli_fetch_assoc($res)) {
?>
    <div class="attraction-card-v4" data-category="<?php echo $attr['categorie_id']; ?>">
        <div class="img-container-v4">
            <img src="img/attractions/<?php echo $attr['main_image']; ?>" alt="<?php echo ($lang == 'ar') ? $attr['name_ar'] : $attr['name_en']; ?>">
            
            <button class="icon-btn fav-btn-v4 main-fav-btn" data-id="<?php echo $attr['id']; ?>" data-type="attraction">
                <i class="<?php echo in_array('attraction_'.$attr['id'], $user_favs) ? 'fas fa-heart' : 'far fa-heart'; ?>" 
                   style="<?php echo in_array('attraction_'.$attr['id'], $user_favs) ? 'color: #ff4757;' : ''; ?>"></i>
            </button>

               <button class="icon-btn loc-btn-v4" title=" attraction location"
                    onclick="showMapModal('<?php echo $attr['lat']; ?>', '<?php echo $attr['lng']; ?>', '<?php echo addslashes($attr['name_en']); ?>')">
                <i class="fas fa-map-marker-alt"></i>
            </button> 
           
            
            <div class="card-overlay-v4">
                <h3><?php echo ($lang == 'ar') ? $attr['name_ar'] : $attr['name_en'];?></h3>
                
                <a href="attraction_details.php?id=<?php echo $attr['id']; ?>" class="btn-explore-v3"> <?= $texts[$lang]['btn_explore'] ?></a>
            </div>
                        </div>
                    </div>
                <?php } ?>
            </div>
            <button class="nav-arrow next" onclick="scrollSlider(1)">❯</button>
        </div>
    </div>
</section>



<section class="haws-stats-v4">
    <div class="container">
        <h2 class="main-title-v4"><?= $texts[$lang]['stats_title'] ?></h2>
        <div class="stats-grid-v4">
            
            <a href="all_wilayas.php" class="stat-card-v4 card-blue">
                <div class="stat-icon-v4"><i class="fas fa-city"></i></div>
                <div class="stat-info-v4">
                    <h3><?= $count_wilayas ?>+</h3>
                    <p><?= $texts[$lang]['stats_wilayas'] ?></p>
                </div>
                <div class="card-shape"></div>
            </a>

            <a href="all_attractions.php" class="stat-card-v4 card-gold">
                <div class="stat-icon-v4"><i class="fas fa-camera-retro"></i></div>
                <div class="stat-info-v4">
                    <h3><?= $count_attractions ?>+</h3>
                    <p><?= $texts[$lang]['stats_attractions'] ?></p>
                </div>
                <div class="card-shape"></div>
            </a>

            <a href="unesco_sites.php" class="stat-card-v4 card-green">
                <div class="stat-icon-v4"><i class="fas fa-jelly fa-regular fa-landmark"></i></div>
                <div class="stat-info-v4">
                    <h3><?= $count_unesco ?>+</h3>
                    <p><?= $texts[$lang]['stats_unesco'] ?></p>
                </div>
                <div class="card-shape"></div>
            </a>

        </div>
    </div>
</section>

<style>
.haws-stats-v4 { padding: 90px 0; background: #fdfdfd; position: relative; overflow: hidden; }
.main-title-v4 { text-align: center; margin-bottom: 50px; font-family: 'Cairo', sans-serif; font-weight: 800; font-size: 32px; color:#c5a059; }

.stats-grid-v4 { display: flex; gap: 30px; justify-content: center; flex-wrap: wrap; padding: 0 20px; }

.stat-card-v4 { 
    position: relative; background: #fff; padding: 50px 30px; border-radius: 35px; width: 340px;
    text-align: center; transition: 0.5s all ease; text-decoration: none;
    box-shadow: 0 10px 30px rgba(0,0,0,0.05); overflow: hidden; border: none;
}

/* إضافة ألوان لكسر المود */
.card-blue { border-bottom: 5px solid #0984e3; }
.card-blue .stat-icon-v4 { color: #0984e3; }
.card-blue h3 { color: #0984e3; }

.card-gold { border-bottom: 5px solid #c5a059; }
.card-gold .stat-icon-v4 { color: #c5a059; }
.card-gold h3 { color: #c5a059; }

.card-green { border-bottom: 5px solid #27ae60; }
.card-green .stat-icon-v4 { color: #27ae60; }
.card-green h3 { color: #27ae60; }

.stat-card-v4:hover { transform: translateY(-15px); box-shadow: 0 20px 40px rgba(0,0,0,0.1); }

/* خلفية خفيفة تظهر عند التحويم */
.card-shape { 
    position: absolute; width: 150px; height: 150px; background: rgba(0,0,0,0.02); 
    border-radius: 50%; top: -50px; right: -50px; transition: 0.5s; 
}
.stat-card-v4:hover .card-shape { width: 300px; height: 300px; background: rgba(0,0,0,0.04); }

.stat-icon-v4 { font-size: 50px; margin-bottom: 25px; display: block; }
.stat-info-v4 h3 { font-size: 48px; font-weight: 900; margin-bottom: 10px; font-family: 'Cairo', sans-serif; }
.stat-info-v4 p { color: #636e72; font-size: 16px; font-weight: 700; line-height: 1.5; }

@media (max-width: 768px) { .stat-card-v4 { width: 100%; } }
</style>
<?php
$w_res = mysqli_query($conn, "SELECT id, name_en, image, lat, lng FROM wilayas");
$wilayas_data = mysqli_fetch_all($w_res, MYSQLI_ASSOC);

$a_query = "SELECT a.id, a.name_en, a.lat, a.lng, a.categorie_id, c.name_en as cat_name, 
            (SELECT image FROM attraction_images WHERE attraction_id = a.id LIMIT 1) as img 
            FROM attractions a JOIN categories c ON a.categorie_id = c.id";
$a_res = mysqli_query($conn, $a_query);
$attractions_data = mysqli_fetch_all($a_res, MYSQLI_ASSOC);
?>

<section id="map_a" class="premium-map-section">
    <div class="map-title-container">
    <h2 class="main-title"><?= $texts[$lang]['map_title'] ?></h2>
</div>
    
    <div class="map_frame">
        <div id="map"></div>
    </div>
</section>
<script>
document.addEventListener('DOMContentLoaded', function() {
   
    window.map = L.map('map').setView([28.0339, 1.6596], 6);
    L.tileLayer('http://{s}.google.com/vt/lyrs=m&x={x}&y={y}&z={z}',{
        maxZoom: 20, subdomains:['mt0','mt1','mt2','mt3']
    }).addTo(map);

    function createIcon(catId, isWilaya = false) {
        let color = isWilaya ? '#d63031' : '#218c74'; 
        let shape = isWilaya ? 'drop-shape' : 'rect-shape';
        let icon = isWilaya ? 'fa-location-dot' : 'fa-star';

        if(!isWilaya) {
            switch(parseInt(catId)) {
                case 1: icon = 'fa-landmark'; break;
                case 2: icon = 'fa-tree'; break;
                case 3: icon = 'fa-sun'; break;
                case 4: icon = 'fa-water'; break;
            }
        }

        return L.divIcon({
            className: 'marker-container',
            html: `<div class="${shape}" style="background:${color}"><i class="fas ${icon}"></i></div>`,
            iconSize: [30, 38],
            iconAnchor: [15, 38],
            popupAnchor: [0, -35]
        });
    }

    var wilayas = <?php echo json_encode($wilayas_data); ?>;
    wilayas.forEach(w => {
        if(w.lat && w.lng) {
            L.marker([parseFloat(w.lat), parseFloat(w.lng)], {
                icon: createIcon(null, true),
                zIndexOffset: 1000 
            })
            .bindPopup(`
                <div class="pop-premium">
                    <img src="img/wilayas/${w.image}">
                    <h3>wilaya of ${w.name_en}</h3>
                    <a href="wilaya.php?id=${w.id}" class="pop-btn w"> Explore the wilaya</a>
                </div>
            `).addTo(map);
        }
    });

    var attrs = <?php echo json_encode($attractions_data); ?>;
    attrs.forEach(a => {
        if(a.lat && a.lng) {
            let lat = parseFloat(a.lat) + 0.005; 
            let lng = parseFloat(a.lng) + 0.005;

            L.marker([lat, lng], {icon: createIcon(a.categorie_id)})
            .bindPopup(`
                <div class="pop-premium attr">
                    <img src="img/attractions/${a.img}">
                    <h3>${a.name_en}</h3>
                    <p>${a.cat_name}</p>
                    <a href="attraction_details.php?id=${a.id}" class="pop-btn a">Show the attraction </a>
                </div>
            `).addTo(map);
        }
    });
});
</script>

<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
<script>
    var swiper = new Swiper(".mySwiper", { 
    loop: true, 
    effect: "fade", 
    autoplay: { delay: 5000 },
    navigation: { nextEl: ".swiper-button-next", prevEl: ".swiper-button-prev" },
    pagination: { el: ".swiper-pagination", clickable: true }
});
</script>

<script>
function scrollSlider(direction) {
    const slider = document.getElementById('monumentSlider');
    const scrollAmount = 320; 
    
    const isRTL = window.getComputedStyle(slider).direction === 'rtl';
    const move = isRTL ? (direction * -scrollAmount) : (direction * scrollAmount);

    const currentScroll = Math.abs(slider.scrollLeft);
    const maxScroll = slider.scrollWidth - slider.clientWidth;

    if (direction === 1 && currentScroll >= maxScroll - 5) {
        slider.scrollTo({ left: 0, behavior: 'smooth' });
    } else if (direction === -1 && currentScroll <= 5) {
        slider.scrollTo({ left: isRTL ? -maxScroll : maxScroll, behavior: 'smooth' });
    } else {
        slider.scrollBy({ left: move, behavior: 'smooth' });
    }
}

document.querySelectorAll('.filter-btn-v4').forEach(btn => {
    btn.addEventListener('click', () => {
        document.querySelector('.filter-btn-v4.active').classList.remove('active');
        btn.classList.add('active');

        const categoryId = btn.getAttribute('data-id');
        const cards = document.querySelectorAll('.attraction-card-v4');

        cards.forEach(card => {
            if (categoryId === 'all' || card.getAttribute('data-category') === categoryId) {
                card.style.display = 'block';
                card.style.opacity = '0';
                setTimeout(() => card.style.opacity = '1', 50);
            } else {
                card.style.display = 'none';
            }
        });
    });
});
</script>
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
</script>





<!-- ================= REVIEW SECTION ================= -->

<section class="review-section">

    <div class="review-container">

        <h2><?= $texts[$lang]['review_title'] ?></h2>

        <p class="review-subtitle">
            <?= $texts[$lang]['review_subtitle'] ?>
        </p>

        <?php if(isset($_SESSION['user_id'])) { ?>

        <div class="stars-box">

            <i class="fas fa-star star" data-rate="1"></i>
            <i class="fas fa-star star" data-rate="2"></i>
            <i class="fas fa-star star" data-rate="3"></i>
            <i class="fas fa-star star" data-rate="4"></i>
            <i class="fas fa-star star" data-rate="5"></i>

        </div>

        <div id="feedbackArea" style="display:none;">

            <h3 class="suggest-title">
                <?= $texts[$lang]['improve_question'] ?>
            </h3>

            <div class="suggestions-grid">

                <label>
                    <input type="checkbox" value="Design Improvement">
                    <?= $texts[$lang]['suggest_design'] ?>
                </label>

                <label>
                    <input type="checkbox" value="Add More Places">
                    <?= $texts[$lang]['suggest_places'] ?>
                </label>

                <label>
                    <input type="checkbox" value="Better AI">
                    <?= $texts[$lang]['suggest_ai'] ?>
                </label>

                <label>
                    <input type="checkbox" value="Improve Speed">
                    <?= $texts[$lang]['suggest_speed'] ?>
                </label>

                <label>
                    <input type="checkbox" value="Better Translation">
                    <?= $texts[$lang]['suggest_translation'] ?>
                </label>

                <label>
                    <input type="checkbox" value="More Hotels">
                    <?= $texts[$lang]['suggest_hotels'] ?>
                </label>

            </div>

            <textarea  
                id="customSuggestion"
                name="Suggestion"
                placeholder="<?= $texts[$lang]['write_suggestion'] ?>"
            ></textarea>

            <button id="sendReviewBtn">
                <?= $texts[$lang]['send_review'] ?>
            </button>

        </div>

        <div id="successMessage"></div>

        <?php } else { ?>

            <div class="login-alert-review">

                <i class="fas fa-lock"></i>

                <p><?= $texts[$lang]['login_to_review'] ?></p>

            </div>

        <?php } ?>

    </div>

</section>





<style>

.review-section{

    padding:80px 20px;

    background:#f8fafc;
}

.review-container{

    max-width:700px;

    margin:auto;

    background:rgba(255,255,255,0.75);

    backdrop-filter:blur(15px);

    padding:50px;

    border-radius:35px;

    box-shadow:0 15px 40px rgba(0,0,0,0.1);

    text-align:center;
}

.review-container h2{

    font-size:38px;

    color:#c5a059;

    margin-bottom:15px;

    font-family:'Cairo',sans-serif;
}

.review-subtitle{

    color:#64748b;

    margin-bottom:35px;

    font-size:17px;
}

.stars-box{

    display:flex;

    justify-content:center;

    gap:15px;

    margin-bottom:40px;
}

.star{

    font-size:45px;

    cursor:pointer;

    color:#d1d5db;

    transition:0.3s;
}

.star:hover,
.star.active{

    color:#facc15;

    transform:scale(1.2);
}

.suggest-title{

    margin-bottom:25px;

    color:#1e293b;
}

.suggestions-grid{

    display:grid;

    grid-template-columns:repeat(auto-fit,minmax(220px,1fr));

    gap:15px;

    margin-bottom:30px;

    text-align:<?php echo ($lang == 'ar') ? 'right' : 'left'; ?>;
}

.suggestions-grid label{

    background:#f8fafc;

    padding:15px;

    border-radius:15px;

    cursor:pointer;

    transition:0.3s;
}

.suggestions-grid label:hover{

    background:#e2e8f0;
}

textarea{

    width:100%;

    height:120px;

    border:none;

    background:#f8fafc;

    border-radius:20px;

    padding:20px;

    resize:none;

    outline:none;

    font-family:'Cairo',sans-serif;

    margin-bottom:25px;
}

#sendReviewBtn{

    background:linear-gradient(135deg,#c5a059,#facc15);

    border:none;

    color:white;

    padding:15px 35px;

    border-radius:50px;

    font-size:17px;

    cursor:pointer;

    transition:0.3s;
}

#sendReviewBtn:hover{

    transform:translateY(-3px);
}

.login-alert-review{

    padding:40px;
}

.login-alert-review i{

    font-size:50px;

    color:#c5a059;

    margin-bottom:20px;
}

#successMessage{

    margin-top:25px;

    font-weight:bold;

    color:#16a34a;
}

</style>


<script>

let selectedRating = 0;

const stars = document.querySelectorAll('.star');

stars.forEach((star,index)=>{

    star.addEventListener('click',()=>{

        selectedRating = star.dataset.rate;

        stars.forEach(s=>s.classList.remove('active'));

        for(let i=0;i<=index;i++){

            stars[i].classList.add('active');
        }

        document.getElementById('feedbackArea').style.display='block';
    });
});





document.getElementById('sendReviewBtn').addEventListener('click',()=>{

    let selected = [];

    document.querySelectorAll('.suggestions-grid input:checked').forEach(el=>{

        selected.push(el.value);
    });

    let customSuggestion = document.getElementById('customSuggestion').value;

    fetch('save_review.php',{

        method:'POST',

        headers:{
            'Content-Type':'application/x-www-form-urlencoded'
        },

        body:
        'rating=' + selectedRating +
        '&suggestion=' + encodeURIComponent(customSuggestion) +
        '&selected_options=' + encodeURIComponent(selected.join(','))

    })

    .then(res=>res.text())

    .then(data=>{

        document.getElementById('successMessage').innerHTML =
        "<?= $texts[$lang]['review_success'] ?>";

        document.getElementById('feedbackArea').style.display='none';
    });

});
// دالة تشغيل وعرض الخريطة التفاعلية الآمنة بالتصميم الأبيض الفخم
function showMapModal(lat, lng, itemName) {
    if(!lat || !lng) {
        alert("إحداثيات هذا الموقع غير متوفرة حالياً.");
        return;
    }
    
    // تحديث العنوان المكتوب بالأسود داخل الـ Modal الجديد باسم الموقع الممرر
    document.getElementById('modalTitle').innerText = itemName;
    
    // تصحيح بناء الرابط التفاعلي لخرائط جوجل لتفادي خطأ الـ Syntax
    const embedUrl = "https://maps.google.com/maps?q=" + lat + "," + lng + "&t=&z=14&ie=UTF8&iwloc=&output=embed";
    
    // تمرير الرابط المصلح وعرض النافذة بمرونة تامة عبر jQuery
    document.getElementById('mapIframe').src = embedUrl;
    $('#mapModal').css('display', 'flex').fadeIn(300);
}

// دالة إغلاق الخريطة وتفريغ الإطار تماماً لمنع ثقل السيرفر
function closeMapModal() {
    $('#mapModal').fadeOut(200, function() {
        document.getElementById('mapIframe').src = "";
    });
}

// غلق نافذة الخريطة تلقائياً إذا نقر المستخدم في المساحة الرمادية المحيطة
window.addEventListener('click', function(event) {
    const modal = document.getElementById('mapModal');
    if (event.target == modal) {
        closeMapModal();
    }
});

</script>

<!-- ================= END REVIEW SECTION ================= -->

<!-- ================= AI ASSISTANT ================= -->

<style>

.ai-bot-btn{

    position: fixed;

    <?php echo ($lang == 'ar') ? 'right:25px;' : 'left:25px;'; ?>

    bottom: 25px;

    width: 68px;
    height: 68px;

    border-radius: 50%;

    background: linear-gradient(135deg,#c5a059,#f4d03f);

    display: flex;
    align-items: center;
    justify-content: center;

    color: white;
    font-size: 28px;

    cursor: pointer;

    box-shadow: 0 10px 25px rgba(0,0,0,0.25);

    z-index: 999999;

    transition: 0.4s;

    animation: floatBot 2s infinite ease-in-out;
}

.ai-bot-btn:hover{
    transform: scale(1.1);
}

@keyframes floatBot{

    0%{
        transform: translateY(0);
    }

    50%{
        transform: translateY(-8px);
    }

    100%{
        transform: translateY(0);
    }
}

.ai-chat-container{

    position: fixed;

    bottom: 105px;

    <?php echo ($lang == 'ar') ? 'right:25px;' : 'left:25px;'; ?>

    width: 340px;
    height: 500px;

    background: white;

    border-radius: 25px;

    overflow: hidden;

    display: none;

    flex-direction: column;

    z-index: 999999;

    box-shadow: 0 15px 40px rgba(0,0,0,0.2);
}

.ai-header{

    background: #1e293b;

    color: white;

    padding: 18px;

    display: flex;

    justify-content: space-between;

    align-items: center;

    font-weight: bold;

    font-size: 16px;
}

.ai-header button{

    background: none;

    border: none;

    color: white;

    font-size: 18px;

    cursor: pointer;
}

.ai-chat-body{

    flex: 1;

    padding: 15px;

    overflow-y: auto;

    background: #f8fafc;
}

.bot-message,
.user-message{

    padding: 12px 15px;

    margin-bottom: 12px;

    border-radius: 15px;

    max-width: 80%;

    font-size: 14px;

    line-height: 1.5;
}

.bot-message{

    background: #e2e8f0;

    color: #1e293b;
}

.user-message{

    background: #c5a059;

    color: white;

    margin-left: auto;
}

.ai-chat-input{

    display: flex;

    border-top: 1px solid #eee;
}

.ai-chat-input input{

    flex: 1;

    border: none;

    padding: 15px;

    outline: none;

    font-family: 'Cairo', sans-serif;
}

.ai-chat-input button{

    width: 60px;

    border: none;

    background: #c5a059;

    color: white;

    font-size: 18px;

    cursor: pointer;
}


.bot-suggest-btn {
    background: #fff;
    border: 1px solid #c5a059;
    color: #c5a059;
    padding: 5px 10px;
    border-radius: 15px;
    margin: 5px 2px;
    cursor: pointer;
    font-size: 12px;
    transition: 0.3s;
}
.bot-suggest-btn:hover {
    background: #c5a059;
    color: #fff;
}

</style>





<!-- FLOATING BUTTON -->

<div class="ai-bot-btn" onclick="toggleAIChat()">

    <i class="fas fa-robot"></i>

</div>





<!-- CHAT BOX -->

<div class="ai-chat-container" id="aiChat">

    <div class="ai-header">

        <span>
            🤖 <?= $texts[$lang]['ai_title'] ?>
        </span>

        <button onclick="toggleAIChat()">✖</button>

    </div>



    <div class="ai-chat-body" id="chatBody">

        <div class="bot-message">

            <?= $texts[$lang]['ai_welcome'] ?>

        </div>

    </div>





    <div class="ai-chat-input">

        <input 
            type="text" 
            id="userInput"
            placeholder="<?= $texts[$lang]['ai_placeholder'] ?>"
        >

        <button onclick="sendMessage()">➤</button>

    </div>

</div>






<script>

function toggleAIChat(){

    let chat = document.getElementById("aiChat");

    if(chat.style.display === "flex"){

        chat.style.display = "none";

    }else{

        chat.style.display = "flex";
    }
}



function sendMessage() {
    let input = document.getElementById("userInput");
    let message = input.value.trim();
    let chatBody = document.getElementById("chatBody");

    if (message === "") return;

    // إظهار رسالة المستخدم
    chatBody.innerHTML += `<div class="user-message">${message}</div>`;
    input.value = "";
    chatBody.scrollTop = chatBody.scrollHeight;

    // إظهار أن البوت "يفكر"
    let typingId = "typing_" + Date.now();
    chatBody.innerHTML += `<div class="bot-message" id="${typingId}">...</div>`;

    // إرسال الطلب لقاعدة البيانات عبر ملف PHP
   fetch('bot_engine.php?query=' + encodeURIComponent(message) + '&lang=<?= $lang ?>')
    .then(response => response.json())
    .then(data => {
        document.getElementById(typingId).remove(); // إزالة النقاط الثلاث
        
        // عرض رد البوت
        let botMsg = `<div class="bot-message">${data.reply}</div>`;
        chatBody.innerHTML += botMsg;

        // عرض الاقتراحات كأزرار
        if (data.suggestions && data.suggestions.length > 0) {
            let sugHTML = `<div class="bot-suggestions">`;
            data.suggestions.forEach(s => {
                sugHTML += `<button class="bot-suggest-btn" onclick="quickSearch('${s}')">${s}</button>`;
            });
            sugHTML += `</div>`;
            chatBody.innerHTML += sugHTML;
        }
        chatBody.scrollTop = chatBody.scrollHeight;
    })
    .catch(error => {
        document.getElementById(typingId).innerHTML = "حدث خطأ في الاتصال بالخادم.";
        console.error('Error:', error);
    });
}

// دالة للاقتراحات السريعة
function quickSearch(txt) {
    document.getElementById("userInput").value = txt;
    sendMessage();
}

</script>

<!-- ================= END AI ASSISTANT ================= -->


<div id="mapModal" style="display: none; position: fixed; z-index: 99999; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0, 0, 0, 0.7); align-items: center; justify-content: center;">
    <div style="background: #fff; width: 80%; max-width: 800px; margin: 5% auto; padding: 20px; border-radius: 15px; position: relative; height: 70vh; box-shadow: 0 10px 30px rgba(0,0,0,0.3);">
        
        <span onclick="closeMapModal()" style="position: absolute; top: 10px; right: 20px; font-size: 30px; cursor: pointer; font-weight: bold; color: #333; z-index: 10;">×</span>
        
        <h3 id="modalTitle" style="margin-bottom: 15px; color: #333; font-family: 'Cairo', sans-serif; font-weight: 700; text-align: left;">Location</h3>
        
        <div id="mapContainer" style="width: 100%; height: 85%; border-radius: 10px; overflow: hidden; border: 1px solid #eee;">
            <iframe id="mapIframe" src="" width="100%" height="100%" style="border:0;" allowfullscreen="" loading="lazy"></iframe>
        </div>
        
    </div>
</div>

<?php include 'footer.php'; ?>