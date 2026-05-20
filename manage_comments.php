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



// كود الحذف

if (isset($_GET['delete'])) {

    $id = intval($_GET['delete']);

    mysqli_query($conn, "DELETE FROM comments WHERE id = $id");

    header("Location: manage_comments.php");

    exit();

}



// استعلام يجلب التعليقات مع اسم المستخدم

$query = "SELECT c.*, u.name as user_name FROM comments c

          JOIN users u ON c.user_id = u.id ORDER BY c.created_at DESC";

$result = mysqli_query($conn, $query);

?>



<!DOCTYPE html>

<html lang="<?php echo $lang; ?>" dir="<?php echo $dir; ?>">

<head>

    <meta charset="UTF-8">

    <title><?php echo $texts[$lang]['admin_panel']; ?> | <?php echo $texts[$lang]['comments']; ?></title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;700&display=swap" rel="stylesheet">

    <style>

        :root { --gold: #c5a059; --dark: #1e293b; --light: #f8fafc; }

        * { margin:0; padding:0; box-sizing:border-box; font-family:'Cairo',sans-serif; }

        body { display: flex; background: var(--light); min-height: 100vh; }



        /* السايدبار الديناميكي */

        .sidebar {

            width: 260px; height: 100vh; background: var(--dark); color: white; padding: 20px; position: fixed;

            <?php echo ($lang == 'ar') ? 'right: 0;' : 'left: 0;'; ?>

        }

        .sidebar h2 { text-align: center; color: var(--gold); margin-bottom: 30px; border-bottom: 1px solid #334155; padding-bottom: 10px; font-size: 18px; }

        .sidebar ul { list-style: none; }

        .sidebar ul li { padding: 12px; border-radius: 8px; margin-bottom: 5px; }

        .sidebar ul li a { color: white; text-decoration: none; display: flex; align-items: center; }

        .sidebar ul li i { <?php echo ($lang == 'ar') ? 'margin-left: 10px;' : 'margin-right: 10px;'; ?> color: var(--gold); width: 20px; text-align: center; }



        /* المحتوى الرئيسي */

        .main-content {

            <?php echo ($lang == 'ar') ? 'margin-right: 260px;' : 'margin-left: 260px;'; ?>

            width: calc(100% - 260px); padding: 40px;

        }

       

        .table-container { background: white; padding: 30px; border-radius: 15px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); overflow-x: auto; }

        table { width: 100%; border-collapse: collapse; margin-top: 20px; text-align: <?php echo ($lang == 'ar') ? 'right' : 'left'; ?>; }

        th { background: #f1f5f9; color: #64748b; padding: 15px; font-size: 14px; text-transform: capitalize; }

        td { padding: 15px; border-bottom: 1px solid #f1f5f9; vertical-align: middle; }

       

        .btn-delete { color: #ef4444; text-decoration: none; font-size: 18px; transition: 0.3s; }

        .btn-delete:hover { color: #b91c1c; }



        .user-badge { background: #e2e8f0; padding: 4px 10px; border-radius: 20px; font-size: 12px; color: #475569; }

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

        <h1 style="margin-bottom: 30px;"><?php echo ($lang == 'ar') ? 'إدارة التعليقات والآراء' : 'Review Comments'; ?></h1>



        <div class="table-container">

            <table>

                <thead>

                    <tr>

                        <th style="width: 20%;"><?php echo ($lang == 'ar') ? 'المستخدم' : 'User'; ?></th>

                        <th style="width: 50%;"><?php echo ($lang == 'ar') ? 'التعليق' : 'Comment'; ?></th>

                        <th style="width: 20%;"><?php echo ($lang == 'ar') ? 'التاريخ' : 'Date'; ?></th>

                        <th style="width: 10%;"><?php echo $texts[$lang]['col_actions']; ?></th>

                    </tr>

                </thead>

                <tbody>

                    <?php while($row = mysqli_fetch_assoc($result)): ?>

                    <tr>

                        <td>

                            <span class="user-badge"><i class="fas fa-user" style="font-size: 10px; margin: 0 5px;"></i> <?php echo $row['user_name']; ?></span>

                        </td>

                        <td style="font-size:13px; color:#475569; line-height: 1.6;">

                            <?php echo htmlspecialchars($row['comment']); ?>

                        </td>

                        <td style="font-size:11px; color:#94a3b8; direction: ltr;">

                            <?php echo date('Y-m-d H:i', strtotime($row['created_at'])); ?>

                        </td>

                        <td>

                            <a href="manage_comments.php?delete=<?php echo $row['id']; ?>"

                               class="btn-delete"

                               title="<?php echo ($lang == 'ar') ? 'حذف التعليق' : 'Delete Comment'; ?>"

                               onclick="return confirm('<?php echo $texts[$lang]['confirm_delete']; ?>')">

                                <i class="fas fa-trash-alt"></i>

                            </a>

                        </td>

                    </tr>

                    <?php endwhile; ?>

                    <?php if(mysqli_num_rows($result) == 0): ?>

                        <tr>

                            <td colspan="4" style="text-align: center; padding: 40px; color: #94a3b8;">

                                <?php echo ($lang == 'ar') ? 'لا توجد تعليقات حالياً.' : 'No comments found.'; ?>

                            </td>

                        </tr>

                    <?php endif; ?>

                </tbody>

            </table>

        </div>

    </div>

</body>

</html> 

