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

if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    mysqli_query($conn, "DELETE FROM attractions WHERE id = $id");
    header("Location: manage_attractions.php");
    exit();
}

// الاستعلام المصلح مع استخدام Aliases لأسماء الولايات
$query = "SELECT a.*, 
                 w.name_en AS wilaya_name_en, 
                 w.name_ar AS wilaya_name_ar, 
          (SELECT image FROM attraction_images WHERE attraction_id = a.id LIMIT 1) as main_image
          FROM attractions a 
          LEFT JOIN wilayas w ON a.wilaya_id = w.id 
          ORDER BY a.id DESC";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="<?php echo $lang; ?>" dir="<?php echo $dir; ?>">
<head>
    <meta charset="UTF-8">
    <title><?php echo $texts[$lang]['admin_panel']; ?> | <?php echo $texts[$lang]['attraction_mgmt']; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;700&display=swap" rel="stylesheet">
    <style>
        :root { --gold: #c5a059; --dark: #1e293b; --light: #f8fafc; }
        * { margin:0; padding:0; box-sizing:border-box; font-family:'Cairo',sans-serif; }
        body { display: flex; background: var(--light); min-height: 100vh; }
        .sidebar { width: 260px; height: 100vh; background: var(--dark); color: white; padding: 20px; position: fixed; <?php echo ($lang == 'ar') ? 'right: 0;' : 'left: 0;'; ?> }
        .sidebar h2 { text-align: center; color: var(--gold); margin-bottom: 30px; border-bottom: 1px solid #334155; padding-bottom: 10px; font-size: 18px; }
        .sidebar ul { list-style: none; }
        .sidebar ul li { padding: 12px; border-radius: 8px; margin-bottom: 5px; }
        .sidebar ul li a { color: white; text-decoration: none; display: flex; align-items: center; }
        .sidebar ul li i { <?php echo ($lang == 'ar') ? 'margin-left: 10px;' : 'margin-right: 10px;'; ?> color: var(--gold); width: 20px; text-align: center; }
        .main-content { <?php echo ($lang == 'ar') ? 'margin-right: 260px;' : 'margin-left: 260px;'; ?> width: calc(100% - 260px); padding: 40px; }
        .table-container { background: white; padding: 30px; border-radius: 15px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; text-align: <?php echo ($lang == 'ar') ? 'right' : 'left'; ?>; }
        th { background: #f1f5f9; color: #64748b; padding: 15px; font-size: 14px; text-transform: capitalize; }
        td { padding: 15px; border-bottom: 1px solid #f1f5f9; vertical-align: middle; }
        .att-img { width: 60px; height: 60px; border-radius: 8px; object-fit: cover; }
        .btn-edit { color: #3b82f6; <?php echo ($lang == 'ar') ? 'margin-left: 15px;' : 'margin-right: 15px;'; ?> text-decoration: none; font-size: 18px; }
        .btn-delete { color: #ef4444; text-decoration: none; font-size: 18px; }
        .add-btn { background: var(--gold); color: white; padding: 10px 20px; border-radius: 8px; text-decoration: none; display: flex; align-items: center; gap: 8px; font-weight: bold; }
    </style>
</head>
<body>

    <div class="sidebar">
        <h2><?php echo $texts[$lang]['tourism_mgmt']; ?></h2>
        <ul>
            <li><a href="admin_dashboard.php"><i class="fas fa-home"></i> <?php echo $texts[$lang]['home']; ?></a></li>
            <li><a href="manage_wilayas.php"><i class="fas fa-map"></i> <?php echo $texts[$lang]['wilaya_mgmt']; ?></a></li>
            <li><a href="manage_attractions.php"><i class="fas fa-camera"></i> <?php echo $texts[$lang]['attraction_mgmt']; ?></a></li>
            <li><a href="manage_restaurants.php"><i class="fas fa-utensils"></i> <?php echo $texts[$lang]['restaurant_mgmt']; ?></a></li>
            <li><a href="manage_hotels.php"><i class="fas fa-bed"></i> <?php echo $texts[$lang]['hotel_mgmt']; ?></a></li>
            <li><a href="manage_comments.php"><i class="fas fa-comments"></i> <?php echo $texts[$lang]['comments']; ?></a></li>
            <li style="margin-top: 20px; border-top: 1px solid #334155;"><a href="logout.php"><i class="fas fa-sign-out-alt"></i> <?php echo $texts[$lang]['logout']; ?></a></li>
        </ul>
    </div>

    <div class="main-content">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
            <h1><?php echo $texts[$lang]['attraction_mgmt']; ?></h1>
            <a href="add_attraction.php" class="add-btn"><i class="fas fa-plus"></i> <?php echo $texts[$lang]['add_attraction']; ?></a>
        </div>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th><?php echo $texts[$lang]['col_image']; ?></th>
                        <th><?php echo $texts[$lang]['col_att_name']; ?></th>
                        <th><?php echo $texts[$lang]['col_wilaya']; ?></th>
                        <th><?php echo $texts[$lang]['col_coords']; ?></th>
                        <th><?php echo $texts[$lang]['col_desc']; ?></th>
                        <th><?php echo $texts[$lang]['col_actions']; ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = mysqli_fetch_assoc($result)): ?>
                    <tr>
                        <td>
                            <img src="img/attractions/<?php echo $row['main_image']; ?>" class="att-img" onerror="this.src='images/default.jpg'">
                        </td>
                        <td>
                            <strong><?php echo ($lang == 'ar' && !empty($row['name_ar'])) ? $row['name_ar'] : $row['name_en']; ?></strong>
                        </td>
                        <td>
                            <?php echo ($lang == 'ar' && !empty($row['wilaya_name_ar'])) ? $row['wilaya_name_ar'] : $row['wilaya_name_en']; ?>
                        </td>
                        <td style="color: #64748b; font-size: 12px; direction: ltr;"><?php echo $row['lat'] . ' / ' . $row['lng']; ?></td>
                        <td style="color: #64748b; font-size: 13px;">
                            <?php 
                                $desc = ($lang == 'ar' && !empty($row['description_ar'])) ? $row['description_ar'] : $row['description_en'];
                                echo mb_substr($desc, 0, 40) . '...'; 
                            ?>
                        </td>
                        <td>
                            <a href="edit_attraction.php?id=<?php echo $row['id']; ?>" class="btn-edit" title="Edit"><i class="fas fa-edit"></i></a>
                            <a href="manage_attractions.php?delete=<?php echo $row['id']; ?>" class="btn-delete" title="Delete" onclick="return confirm('<?php echo $texts[$lang]['confirm_delete']; ?>')"><i class="fas fa-trash"></i></a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>