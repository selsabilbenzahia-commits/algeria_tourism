<?php
session_start();
include 'db.php';
include 'lang.php';

if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit();
}

$lang = isset($_SESSION['lang']) ? $_SESSION['lang'] : 'en';
$dir = ($lang == 'ar') ? 'rtl' : 'ltr';

/* ================== COUNTS ================== */

$count_wilayas = mysqli_fetch_assoc(mysqli_query($conn,
"SELECT COUNT(*) as total FROM wilayas WHERE image IS NOT NULL AND image != ''"
))['total'];

$count_attr = mysqli_fetch_assoc(mysqli_query($conn,
"SELECT COUNT(*) as total FROM attractions"
))['total'];

$count_users = mysqli_fetch_assoc(mysqli_query($conn,
"SELECT COUNT(*) as total FROM users"
))['total'];

$count_rest = mysqli_fetch_assoc(mysqli_query($conn,
"SELECT COUNT(*) as total FROM restaurants"
))['total'];

$count_hotels = mysqli_fetch_assoc(mysqli_query($conn,
"SELECT COUNT(*) as total FROM hotels"
))['total'];

$count_comments = mysqli_fetch_assoc(mysqli_query($conn,
"SELECT COUNT(*) as total FROM comments"
))['total'];

/* ================== LATEST ================== */

$latest_query = mysqli_query($conn,
"SELECT name_ar, name_en FROM attractions ORDER BY id DESC LIMIT 3"
);

/* ================== TOP COMMENTED ================== */

$top_commented_query = mysqli_query($conn,"
SELECT a.name_ar, a.name_en, COUNT(c.id) as comment_count
FROM attractions a
JOIN comments c ON a.id = c.attraction_id
GROUP BY a.id
ORDER BY comment_count DESC LIMIT 3
");

/* ================== TYPES CHART ================== */

$types_query = mysqli_query($conn,"
SELECT c.name_ar, c.name_en, COUNT(a.id) as count
FROM categories c
LEFT JOIN attractions a ON c.id = a.categorie_id
GROUP BY c.id
");

$type_labels = [];
$type_counts = [];

while($t = mysqli_fetch_assoc($types_query)){

    $type_labels[] = ($lang == 'ar')
        ? $t['name_ar']
        : $t['name_en'];

    $type_counts[] = $t['count'];
}

/* ================== REVIEWS ================== */

$stars_data = [];

for($i=1;$i<=5;$i++){

    $res = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT COUNT(*) as total FROM site_reviews WHERE rating = $i"
    ));

    $stars_data[$i] = $res['total'];
}

/* ================== SUGGESTIONS ================== */
$suggestions_query = mysqli_query($conn,"
    SELECT selected_options, suggestion, rating
    FROM site_reviews
    WHERE (selected_options IS NOT NULL AND selected_options != '')
    OR (suggestion IS NOT NULL AND suggestion != '')
    ORDER BY id DESC
    LIMIT 5
");

/* ================== NOTIFICATIONS ================== */

$notif_count = mysqli_fetch_assoc(mysqli_query($conn,
"SELECT COUNT(*) as total FROM notifications WHERE is_read = 0"
))['total'];

$notif_query = mysqli_query($conn,"
SELECT * FROM notifications
ORDER BY id DESC
LIMIT 5
");


/* ================== ADMIN ================== */

$admin_user = $_SESSION['admin'];

$admin_res = mysqli_query($conn,
"SELECT * FROM admins WHERE username = '$admin_user'"
);

$admin_data = mysqli_fetch_assoc($admin_res);

$admin_img =
(isset($admin_data['image']) && !empty($admin_data['image']))
? $admin_data['image']
: 'https://ui-avatars.com/api/?name='.$admin_user.'&background=c5a059&color=fff';

?>

<!DOCTYPE html>
<html lang="<?php echo $lang; ?>" dir="<?php echo $dir; ?>">

<head>

<meta charset="UTF-8">
<title>Admin Dashboard</title>

<link rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>

:root{
--gold:#c5a059;
--dark:#1e293b;
--light:#f1f5f9;
--accent:#3b82f6;
--success:#10b981;
}

*{
margin:0;
padding:0;
box-sizing:border-box;
font-family:'Cairo',sans-serif;
}

body{
display:flex;
background:var(--light);
min-height:100vh;
}

/* ================= SIDEBAR ================= */

.sidebar { width: 260px; height: 100vh; background: var(--dark); color: white; padding: 20px; position: fixed; <?php echo ($lang == 'ar') ? 'right: 0;' : 'left: 0;'; ?> }
.sidebar h2 { text-align: center; color: var(--gold); margin-bottom: 30px; border-bottom: 1px solid #334155; padding-bottom: 10px; font-size: 18px; }
.sidebar ul li { padding: 12px; border-radius: 8px; margin-bottom: 5px; }
.sidebar ul li a { color: white; text-decoration: none; display: flex; align-items: center; }
.sidebar ul li i { <?php echo ($lang == 'ar') ? 'margin-left: 10px;' : 'margin-right: 10px;'; ?> color: var(--gold); width: 20px; text-align: center; }

.admin-profile{
text-align:center;
padding-bottom:20px;
border-bottom:1px solid #334155;
margin-bottom:20px;
transition:0.3s;
cursor:pointer;
}

.admin-profile:hover{
transform:translateY(-3px);
}

.admin-profile img{
width:70px;
height:70px;
border-radius:50%;
border:3px solid var(--gold);
object-fit:cover;
}

.sidebar ul{
list-style:none;
}

/* ================= MAIN ================= */

.main-content{
<?php echo ($lang=='ar') ? 'margin-right:260px;' : 'margin-left:260px;'; ?>
width:calc(100% - 260px);
padding:30px;
}

/* ================= HEADER ================= */

.top-header{
display:flex;
justify-content:space-between;
align-items:center;
margin-bottom:30px;
}

/* ================= NOTIF ================= */

.notif-box{
position:relative;
}

.notif-btn{
background:white;
border:none;
width:45px;
height:45px;
border-radius:50%;
cursor:pointer;
font-size:18px;
color:var(--gold);
box-shadow:0 2px 10px rgba(0,0,0,0.08);
position:relative;
}

.notif-count{
position:absolute;
top:-5px;
right:-5px;
background:#ef4444;
color:white;
width:20px;
height:20px;
border-radius:50%;
font-size:11px;
display:flex;
align-items:center;
justify-content:center;
}

.notif-dropdown{
position:absolute;
top:55px;
<?php echo ($lang=='ar') ? 'left:0;' : 'right:0;'; ?>
width:280px;
background:white;
border-radius:15px;
padding:15px;
box-shadow:0 10px 30px rgba(0,0,0,0.1);
display:none;
z-index:999;
}

.notif-item{
padding:10px;
font-size:13px;
border-bottom:1px solid #f1f5f9;
}

/* ================= STATS ================= */

.stats-grid{
display:grid;
grid-template-columns:repeat(6,1fr);
gap:12px;
margin-bottom:25px;
}

.stat-card{
background:white;
padding:15px;
border-radius:12px;
box-shadow:0 2px 10px rgba(0,0,0,0.05);
text-align:center;
height:110px;
display:flex;
flex-direction:column;
justify-content:center;
border-top:4px solid var(--gold);
transition:0.3s;
text-decoration:none;
color:inherit;
}

.stat-card:hover{
transform:translateY(-3px);
}

.stat-card i{
font-size:20px;
margin-bottom:8px;
}

.stat-card h3{
font-size:11px;
color:#64748b;
margin-bottom:5px;
}

.stat-card p{
font-size:18px;
font-weight:bold;
color:var(--gold);
}

/* ================= CONTENT ================= */

.dashboard-row{
display:grid;
grid-template-columns:1fr 1fr 1fr;
gap:20px;
margin-top:20px;
}

.content-box{
background:white;
padding:20px;
border-radius:15px;
box-shadow:0 2px 10px rgba(0,0,0,0.05);
}

.content-box h3{
font-size:15px;
margin-bottom:15px;
color:var(--dark);
border-bottom:2px solid var(--light);
padding-bottom:10px;
display:flex;
align-items:center;
gap:10px;
}

.list-item{
display:flex;
align-items:center;
padding:10px 0;
border-bottom:1px solid #f8fafc;
font-size:13px;
}

.list-item i{
color:var(--success);
<?php echo ($lang=='ar') ? 'margin-left:10px;' : 'margin-right:10px;'; ?>
}

/* ================= REVIEWS ================= */

.review-progress{
margin-bottom:15px;
}

.review-progress span{
font-size:13px;
font-weight:bold;
display:block;
margin-bottom:5px;
}

.progress-bar{
width:100%;
height:10px;
background:#e2e8f0;
border-radius:20px;
overflow:hidden;
}

.progress-fill{
height:100%;
background:linear-gradient(to right,#c5a059,#facc15);
border-radius:20px;
}

.suggestion-item{
background:#f8fafc;
padding:12px;
border-radius:10px;
margin-bottom:10px;
font-size:13px;
border-left:4px solid var(--gold);
}

/* ================= BUTTONS ================= */

.quick-actions-modern{
display:flex;
flex-wrap:wrap;
gap:10px;
}

.action-btn{
display:inline-block;
padding:10px 15px;
background:var(--gold);
color:white;
border-radius:8px;
text-decoration:none;
font-size:13px;
transition:0.3s;
text-align:center;
}

.action-btn:hover{
background:var(--dark);
}

.quick-actions-modern .action-btn{
flex:1 1 45%;
}

.chart-wrapper{
height:220px;
display:flex;
align-items:center;
justify-content:center;
}

</style>
</head>

<body>

<div class="sidebar">

<a href="admin_profile.php" style="text-decoration:none;color:white;">
<div class="admin-profile">
<img src="<?php echo $admin_img; ?>" alt="Admin">
<h4 style="margin-top:10px;font-size:14px;">
<?php echo $_SESSION['admin']; ?>
</h4>
</div>
</a>

<ul>
<li>
<a href="admin_dashboard.php">
<i class="fas fa-home"></i>
<?php echo $texts[$lang]['home']; ?>
</a>
</li>
    <li><a href="manage_wilayas.php"><i class="fas fa-map"></i> <?php echo $texts[$lang]['wilaya_mgmt']; ?></a></li>
    <li><a href="manage_attractions.php"><i class="fas fa-camera"></i> <?php echo $texts[$lang]['attraction_mgmt']; ?></a></li>
    <li><a href="manage_restaurants.php"><i class="fas fa-utensils"></i> <?php echo $texts[$lang]['restaurant_mgmt']; ?></a></li>
    <li><a href="manage_hotels.php"><i class="fas fa-bed"></i> <?php echo $texts[$lang]['hotel_mgmt']; ?></a></li>
    <li><a href="manage_comments.php"><i class="fas fa-comments"></i> <?php echo $texts[$lang]['comments']; ?></a></li>
    <li style="margin-top: 20px; border-top: 1px solid #334155;"><a href="logout.php"><i class="fas fa-sign-out-alt"></i> <?php echo $texts[$lang]['logout']; ?></a></li>
</ul>
</div>

<div class="main-content">

<div class="top-header">
<h2>
<?php echo $texts[$lang]['welcome']; ?>
<?php echo $_SESSION['admin']; ?> 👋
</h2>

<div style="display:flex;align-items:center;gap:15px;">

<div class="notif-box">
<button class="notif-btn" onclick="toggleNotif()">
<i class="fas fa-bell"></i>
<?php if($notif_count > 0): ?>
<span class="notif-count">
<?php echo $notif_count; ?>
</span>
<?php endif; ?>
</button>

<div class="notif-dropdown" id="notifDropdown">
<?php while($n = mysqli_fetch_assoc($notif_query)): ?>
<div class="notif-item">
<?php echo $n['message']; ?>
</div>
<?php endwhile; ?>
</div>
</div>

<span style="color:var(--gold);font-weight:bold;">
<?php echo date('Y-m-d'); ?>
</span>

</div>
</div>

<div class="stats-grid">

<a href="manage_wilayas.php" class="stat-card">
<i class="fas fa-city"></i>
<h3><?php echo $texts[$lang]['wilayas']; ?></h3>
<p><?php echo $count_wilayas; ?></p>
</a>

<a href="manage_attractions.php" class="stat-card">
<i class="fas fa-place-of-worship"></i>
<h3><?php echo $texts[$lang]['attractions']; ?></h3>
<p><?php echo $count_attr; ?></p>
</a>

<a href="manage_restaurants.php" class="stat-card">
<i class="fas fa-utensils"></i>
<h3><?php echo $texts[$lang]['restaurants']; ?></h3>
<p><?php echo $count_rest; ?></p>
</a>

<a href="manage_hotels.php" class="stat-card">
<i class="fas fa-bed"></i>
<h3><?php echo $texts[$lang]['hotels']; ?></h3>
<p><?php echo $count_hotels; ?></p>
</a>

<a href="manage_comments.php" class="stat-card">
<i class="fas fa-comments"></i>
<h3><?php echo $texts[$lang]['total_comments']; ?></h3>
<p><?php echo $count_comments; ?></p>
</a>

<a href="manage_users.php" class="stat-card" style="border-top-color:var(--accent);">
<i class="fas fa-users"></i>
<h3><?php echo $texts[$lang]['users']; ?></h3>
<p><?php echo $count_users; ?></p>
</a>

</div>

<div class="dashboard-row">

<div class="content-box">
<h3>
<i class="fas fa-plus-circle" style="color:var(--success);"></i>
<?php echo $texts[$lang]['latest_additions']; ?>
</h3>
<?php while($row = mysqli_fetch_assoc($latest_query)): ?>
<div class="list-item">
<i class="fas fa-check-circle"></i>
<?php echo ($lang=='ar') ? $row['name_ar'] : $row['name_en']; ?>
</div>
<?php endwhile; ?>
</div>

<div class="content-box">
<h3>
<i class="fas fa-chart-pie" style="color:var(--gold);"></i>
<?php echo $texts[$lang]['attractions_by_type']; ?>
</h3>
<div class="chart-wrapper">
<canvas id="typeChart"></canvas>
</div>
</div>

<div class="content-box">
<h3>
<i class="fas fa-fire" style="color:#f97316;"></i>
<?php echo $texts[$lang]['top_commented']; ?>
</h3>
<?php while($top = mysqli_fetch_assoc($top_commented_query)): ?>
<div class="list-item">
<i class="fas fa-star" style="color:#f97316;"></i>
<?php echo ($lang=='ar') ? $top['name_ar'] : $top['name_en']; ?>
(<?php echo $top['comment_count']; ?>)
</div>
<?php endwhile; ?>
</div>

</div>

<?php
// Ratings Count
$five_star = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM site_reviews WHERE rating = 5"))['total'];
$four_star = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM site_reviews WHERE rating = 4"))['total'];
$three_star = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM site_reviews WHERE rating = 3"))['total'];
$two_star = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM site_reviews WHERE rating = 2"))['total'];
$one_star = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM site_reviews WHERE rating = 1"))['total'];
?>

<div class="dashboard-row modern-admin-row">

    <div class="content-box modern-chart-box"> 
        <h3> <i class="fas fa-chart-column" style="color:#f59e0b;"></i> <?php echo $texts[$lang]['rating_analytics']; ?> </h3> 
        <div class="modern-chart-wrapper"> <canvas id="ratingChart"></canvas> </div> 
        <div class="rating-summary"> 
            <div class="rate-card"><span>⭐ 5</span> <strong><?php echo $five_star; ?></strong> </div>
            <div class="rate-card"><span>⭐ 4</span> <strong><?php echo $four_star; ?></strong> </div>
            <div class="rate-card"><span>⭐ 3</span> <strong><?php echo $three_star; ?></strong></div> 
            <div class="rate-card"><span>⭐ 2</span> <strong><?php echo $two_star; ?></strong> </div> 
            <div class="rate-card"><span>⭐ 1</span> <strong><?php echo $one_star; ?></strong> </div> 
        </div> 
    </div>

    <div class="content-box modern-suggestions">
        <h3>
            <i class="fas fa-lightbulb" style="color:#3b82f6;"></i>
            <?php echo $texts[$lang]['latest_suggestions']; ?>
        </h3>

        <div class="suggestions-box">
            <?php while($sug = mysqli_fetch_assoc($suggestions_query)): ?>
                <div class="suggestion-item">
                    <div class="suggestion-icon">
                        <i class="fas fa-comment-dots"></i>
                    </div>

                    <div class="suggestion-content" style="width: 100%;">
                        <?php if(!empty($sug['selected_options'])): ?>
                            <p style="font-weight: bold; margin-bottom: 2px;">
                               <?php
                                $options = str_replace(",", " • ", $sug['selected_options']);
                                echo htmlspecialchars($options);
                               ?>
                            </p>
                        <?php endif; ?>

                        <?php if(!empty($sug['suggestion'])): ?>
                            <p style="background: rgba(59, 130, 246, 0.05); padding: 6px 10px; border-radius: 6px; font-size: 12px; color: #475569; margin: 4px 0; border-inline-start: 2px solid #3b82f6;">
                                <i class="fas fa-quote-<?php echo ($lang == 'ar') ? 'right' : 'left'; ?>" style="font-size: 10px; color: #3b82f6; opacity: 0.5;"></i> 
                                <?php echo htmlspecialchars($sug['suggestion']); ?>
                            </p>
                        <?php endif; ?>

                        <small>
                             ⭐ <?php echo $sug['rating']; ?>/5
                        </small>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </div>

</div>

<div class="content-box quick-actions-modern">
    <h3>
        <i class="fas fa-bolt" style="color:#eab308;"></i>
        <?php echo $texts[$lang]['quick_actions']; ?>
    </h3>

    <div class="quick-grid-modern">
        <a href="add_wilaya.php" class="quick-btn-modern">
            <i class="fas fa-map"></i>
            <span><?php echo $texts[$lang]['add_wilaya']; ?></span>
        </a>

        <a href="add_attraction.php" class="quick-btn-modern">
            <i class="fas fa-camera"></i>
            <span><?php echo $texts[$lang]['add_attraction']; ?></span>
        </a>

        <a href="add_restaurant.php" class="quick-btn-modern">
            <i class="fas fa-utensils"></i>
            <span><?php echo $texts[$lang]['add_restaurant']; ?></span>
        </a>

        <a href="add_hotel.php" class="quick-btn-modern">
            <i class="fas fa-bed"></i>
            <span><?php echo $texts[$lang]['add_hotel']; ?></span>
        </a>

        <a href="index.php" target="_blank" class="quick-btn-modern dark-btn-modern">
            <i class="fas fa-external-link-alt"></i>
            <span><?php echo $texts[$lang]['view_site']; ?></span>
        </a>
    </div>
</div>

<style>
.modern-admin-row{
    display:grid;
    grid-template-columns:2fr 1fr;
    gap:20px;
    margin-top:20px;
    align-items:start;
}

.modern-chart-box{
    overflow:hidden;
}

.modern-chart-wrapper{
    height:260px;
    margin-top:10px;
}

.rating-summary{
    display:flex;
    justify-content:space-between;
    gap:10px;
    margin-top:20px;
    flex-wrap:wrap;
}

.rate-card{
    flex:1;
    min-width:70px;
    background:#f8fafc;
    border-radius:14px;
    padding:14px;
    text-align:center;
    transition:.3s;
    border:1px solid #eef2f7;
}

.rate-card:hover{
    transform:translateY(-5px);
    background:#fffaf0;
    box-shadow:0 8px 20px rgba(0,0,0,0.06);
}

.rate-card span{
    display:block;
    font-size:14px;
    margin-bottom:8px;
}

.rate-card strong{
    color:#c5a059;
    font-size:20px;
    font-weight:800;
}

.modern-suggestions{
    max-height:500px;
    overflow:hidden;
}

.suggestions-box{
    max-height:380px;
    overflow-y:auto;
    padding-right:5px;
}

.suggestion-item{
    display:flex;
    gap:12px;
    padding:14px 0;
    border-bottom:1px solid #f1f5f9;
}

.suggestion-icon{
    width:40px;
    height:40px;
    border-radius:50%;
    background:#eff6ff;
    display:flex;
    align-items:center;
    justify-content:center;
    flex-shrink:0;
}

.suggestion-icon i{
    color:#3b82f6;
}

.suggestion-content p{
    font-size:13px;
    line-height:1.6;
    color:#334155;
    margin-bottom:6px;
}

.suggestion-content small{
    color:#f59e0b;
    font-weight:700;
}

/* QUICK ACTIONS */
.quick-actions-modern{
    margin-top:20px;
}

.quick-grid-modern{
    display:grid;
    grid-template-columns:repeat(5,1fr);
    gap:15px;
    margin-top:20px;
}

.quick-btn-modern{
    background:#fff;
    border:1px solid #e2e8f0;
    border-radius:18px;
    padding:22px 10px;
    text-align:center;
    text-decoration:none;
    transition:.3s;
    color:#1e293b;
    font-weight:700;
    position:relative;
    overflow:hidden;
}

.quick-btn-modern::before{
    content:'';
    position:absolute;
    width:100%;
    height:0;
    background:#f8fafc;
    left:0;
    bottom:0;
    transition:.3s;
    z-index:0;
}

.quick-btn-modern:hover::before{
    height:100%;
}

.quick-btn-modern:hover{
    transform:translateY(-5px);
    box-shadow:0 10px 25px rgba(0,0,0,0.08);
}

.quick-btn-modern i,
.quick-btn-modern span{
    position:relative;
    z-index:2;
}

.quick-btn-modern i{
    display:block;
    font-size:24px;
    margin-bottom:10px;
    color:#c5a059;
}

.dark-btn-modern{
    background:#1e293b;
    color:white;
}

.dark-btn-modern i{
    color:white;
}

/* RESPONSIVE */
@media(max-width:1000px){
    .modern-admin-row{
        grid-template-columns:1fr;
    }
    .quick-grid-modern{
        grid-template-columns:repeat(2,1fr);
    }
}
</style>

<script>
new Chart(document.getElementById('ratingChart'), {
    type: 'bar',
    data: {
        labels: ['5⭐', '4⭐', '3⭐', '2⭐', '1⭐'],
        datasets: [{
            label: 'Ratings',
            data: [
                <?php echo $five_star; ?>,
                <?php echo $four_star; ?>,
                <?php echo $three_star; ?>,
                <?php echo $two_star; ?>,
                <?php echo $one_star; ?>
            ],
            backgroundColor: [
                '#10b981',
                '#3b82f6',
                '#f59e0b',
                '#fb7185',
                '#ef4444'
            ],
            borderRadius:10,
            barThickness:40
        }]
    },
    options: {
        responsive:true,
        maintainAspectRatio:false,
        plugins:{
            legend:{
                display:false
            }
        },
        scales:{
            y:{
                beginAtZero:true,
                grid:{
                    display:false
                }
            },
            x:{
                grid:{
                    display:false
                }
            }
        }
    }
});
</script>

<script>
function toggleNotif(){
    const box = document.getElementById('notifDropdown');
    if(box.style.display === 'block'){
        box.style.display = 'none';
    }else{
        box.style.display = 'block';
    }
}

/* ================= PIE ================= */
new Chart(document.getElementById('typeChart'), {
type:'doughnut',
data:{
labels: <?php echo json_encode($type_labels); ?>,
datasets:[{
data: <?php echo json_encode($type_counts); ?>,
backgroundColor:[
'#c5a059',
'#3b82f6',
'#1e293b',
'#10b981',
'#f59e0b'
]
}]
},
options:{
maintainAspectRatio:false,
plugins:{
legend:{
position:'bottom',
labels:{
boxWidth:12,
font:{size:10}
}
}
}
}
});
</script>

</body>
</html>