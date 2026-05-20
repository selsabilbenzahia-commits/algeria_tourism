<?php
include "db.php"; 
session_start();

$lang = $_GET['lang'] ?? ($_SESSION['lang'] ?? 'en');

// معالجة حذف المستخدم عن طريق الأجاكس (AJAX) لسرعة الأداء
if (isset($_POST['action']) && $_POST['action'] == 'delete_user') {
    $u_id = mysqli_real_escape_string($conn, $_POST['user_id']);
    $delete_query = "DELETE FROM users WHERE id = '$u_id'";
    if (mysqli_query($conn, $delete_query)) {
        echo "success";
    } else {
        echo "error";
    }
    exit();
}

// جلب إجمالي عدد المستخدمين
$count_query = "SELECT COUNT(*) as total FROM users";
$count_result = mysqli_query($conn, $count_query);
$count_data = mysqli_fetch_assoc($count_result);
$total_users = $count_data['total'];

// جلب تفاصيل المستخدمين بناءً على أعمدة قاعدة البيانات الخاصة بكِ
$users_query = "SELECT id, name, email, profile_image, phone, created_at FROM users ORDER BY created_at DESC";
$users_result = mysqli_query($conn, $users_query);
?>

<!DOCTYPE html>
<html lang="<?php echo $lang; ?>" dir="<?php echo ($lang == 'ar') ? 'rtl' : 'ltr'; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo ($lang == 'ar') ? 'إدارة المستخدمين' : 'Users Management'; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

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
            --table-hover: #333;
        }
        
        html.light-mode, html.light-mode body {
            --bg-color: #f5f6fa;
            --card-bg: #ffffff;
            --text-color: #2f3640;
            --border-color: #dcdde1;
            --table-hover: #f1f2f6;
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

        .admin-container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 20px;
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            border-bottom: 2px solid var(--main-color);
            padding-bottom: 15px;
        }

        .back-to-dash {
            color: var(--text-color);
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
            transition: 0.3s;
        }

        .back-to-dash:hover {
            color: var(--main-color);
        }

        .stats-card {
            background: var(--card-bg);
            border-radius: 12px;
            padding: 20px;
            display: inline-flex;
            align-items: center;
            gap: 20px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
            margin-bottom: 25px;
            border-left: 5px solid var(--main-color);
        }

        .stats-icon {
            font-size: 35px;
            color: var(--main-color);
        }

        .table-responsive {
            background: var(--card-bg);
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.3);
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            text-align: <?php echo ($lang == 'ar') ? 'right' : 'left'; ?>;
        }

        th {
            background-color: rgba(197, 160, 89, 0.1);
            color: var(--main-color);
            padding: 15px;
            font-weight: 600;
            border-bottom: 2px solid var(--border-color);
        }

        td {
            padding: 15px;
            border-bottom: 1px solid var(--border-color);
            vertical-align: middle;
        }

        tr:hover td {
            background-color: var(--table-hover);
        }

        .user-avatar {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid var(--main-color);
        }

        .default-avatar-icon {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            background: #444;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 20px;
        }

        .btn-action-delete {
            background: #ff4d4d;
            color: white;
            border: none;
            padding: 8px 14px;
            border-radius: 6px;
            cursor: pointer;
            transition: 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .btn-action-delete:hover {
            background: #ff3333;
            transform: scale(1.05);
        }

        .no-users-alert {
            text-align: center;
            padding: 40px;
            color: #b3b3b3;
        }
    </style>
</head>
<body>

    <div class="admin-container">
        
        <div class="page-header">
            <div>
                <h2><i class="fas fa-users-cog" style="color: var(--main-color); margin-left: 10px;"></i> <?php echo ($lang == 'ar') ? 'لوحة التحكم - إدارة المستخدمين' : 'Dashboard - Users Management'; ?></h2>
            </div>
            <a href="admin_dashboard.php" class="back-to-dash">
                <i class="fas <?php echo ($lang == 'ar' ? 'fa-arrow-right' : 'fa-arrow-left'); ?>"></i>
                <?php echo ($lang == 'ar' ? 'الرجوع للوحة الرئيسية' : 'Back to Dashboard'); ?>
            </a>
        </div>

        <div class="stats-card">
            <div class="stats-icon"><i class="fas fa-users"></i></div>
            <div>
                <span style="color: #b3b3b3; font-size: 14px; display: block;"><?php echo ($lang == 'ar') ? 'إجمالي المشتركين' : 'Total Registered Users'; ?></span>
                <strong style="font-size: 24px; color: var(--text-color);"><?php echo $total_users; ?></strong>
            </div>
        </div>

        <div class="table-responsive">
            <?php if(mysqli_num_rows($users_result) > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th><?php echo ($lang == 'ar') ? 'الصورة الشخصية' : 'Avatar'; ?></th>
                            <th><?php echo ($lang == 'ar') ? 'اسم المستخدم' : 'Username'; ?></th>
                            <th><?php echo ($lang == 'ar') ? 'البريد الإلكتروني' : 'Email Address'; ?></th>
                            <th><?php echo ($lang == 'ar') ? 'رقم الهاتف' : 'Phone Number'; ?></th>
                            <th><?php echo ($lang == 'ar') ? 'تاريخ الانضمام' : 'Joined Date'; ?></th>
                            <th><?php echo ($lang == 'ar') ? 'إجراءات' : 'Actions'; ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($user = mysqli_fetch_assoc($users_result)): ?>
                            <tr id="user-row-<?php echo $user['id']; ?>">
                                <td>
                                    <?php if(!empty($user['profile_image'])): ?>
                                        <img src="<?php echo $user['profile_image']; ?>" class="user-avatar">
                                    <?php else: ?>
                                        <div class="default-avatar-icon"><i class="fas fa-user"></i></div>
                                    <?php endif; ?>
                                </td>
                                <td><strong><?php echo htmlspecialchars($user['name']); ?></strong></td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td><?php echo !empty($user['phone']) ? htmlspecialchars($user['phone']) : '—'; ?></td>
                                <td style="font-size: 13px; color: #b3b3b3;"><?php echo date('Y-m-d', strtotime($user['created_at'])); ?></td>
                                <td>
                                    <button class="btn-action-delete" onclick="confirmDeleteUser(<?php echo $user['id']; ?>, '<?php echo htmlspecialchars($user['name']); ?>')">
                                        <i class="fas fa-trash-alt"></i> <?php echo ($lang == 'ar') ? 'حذف' : 'Delete'; ?>
                                    </button>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="no-users-alert">
                    <i class="fas fa-folder-open" style="font-size: 40px; margin-bottom: 15px; display: block; color: var(--main-color);"></i>
                    <p><?php echo ($lang == 'ar') ? 'لا يوجد مستخدمين مسجلين حالياً في النظام.' : 'No users found in the system right now.'; ?></p>
                </div>
            <?php endif; ?>
        </div>

    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        function confirmDeleteUser(userId, userName) {
            const titleText = "<?php echo ($lang == 'ar') ? 'هل أنت متأكد؟' : 'Are you sure?'; ?>";
            const textContent = "<?php echo ($lang == 'ar') ? 'سيتم حذف حساب المستخدم ' : 'You are about to delete '; ?>" + userName + "<?php echo ($lang == 'ar') ? ' نهائياً وبشكل كامل!' : ' permanently!'; ?>";
            const confirmBtnText = "<?php echo ($lang == 'ar') ? 'نعم، احذفه!' : 'Yes, delete it!'; ?>";
            const cancelBtnText = "<?php echo ($lang == 'ar') ? 'إلغاء' : 'Cancel'; ?>";

            Swal.fire({
                title: titleText,
                text: textContent,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ff4d4d',
                cancelButtonColor: '#64748b',
                confirmButtonText: confirmBtnText,
                cancelButtonText: cancelBtnText
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: 'manage_users.php',
                        method: 'POST',
                        data: { action: 'delete_user', user_id: userId },
                        success: function(response) {
                            if (response.trim() === 'success') {
                                Swal.fire(
                                    "<?php echo ($lang == 'ar') ? 'تم الحذف!' : 'Deleted!'; ?>",
                                    "<?php echo ($lang == 'ar') ? 'تمت إزالة الحساب بنجاح.' : 'The user account has been removed.'; ?>",
                                    'success'
                                );
                                $('#user-row-' + userId).fadeOut(600, function() {
                                    $(this).remove();
                                });
                            } else {
                                Swal.fire("Error", "<?php echo ($lang == 'ar') ? 'حدث خطأ غير متوقع أثناء الحذف.' : 'An error occurred during deletion.'; ?>", "error");
                            }
                        }
                    });
                }
            });
        }
    </script>
</body>
</html>