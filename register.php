<?php 
session_start();
include 'db.php'; 
include('lang.php');

// إدارة اللغة
if (isset($_GET['lang'])) {
    $lang = $_GET['lang'];
    $_SESSION['lang'] = $lang; 
} elseif (isset($_SESSION['lang'])) {
    $lang = $_SESSION['lang'];
} else {
    $lang = 'ar';
}

$error = "";
$success = "";

// معالجة البيانات عند إرسال النموذج
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // التحقق من تطابق كلمات المرور
    if ($password !== $confirm_password) {
        $error = ($lang == 'ar') ? "كلمات المرور غير متطابقة!" : "Passwords do not match!";
    } else {
        // التحقق من وجود المستخدم مسبقاً
        $check_user = mysqli_query($conn, "SELECT * FROM users WHERE name='$username' OR email='$email'");
        if (mysqli_num_rows($check_user) > 0) {
            $error = ($lang == 'ar') ? "اسم المستخدم أو البريد الإلكتروني موجود مسبقاً!" : "Username or Email already exists!";
        } else {
            // تشفير كلمة المرور وحفظ البيانات
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $insert_query = "INSERT INTO users (name, email, password) VALUES ('$username', '$email', '$hashed_password')";
            
            if (mysqli_query($conn, $insert_query)) {
                $success = ($lang == 'ar') ? "تم إنشاء الحساب بنجاح! يمكنك الآن تسجيل الدخول." : "Account created successfully! You can now log in.";
                // توجيه تلقائي بعد 3 ثواني لصفحة الدخول
                header("refresh:3;url=login.php");
            } else {
                $error = ($lang == 'ar') ? "حدث خطأ أثناء التسجيل، حاول مجدداً." : "Error occurred, please try again.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="<?php echo $lang; ?>" dir="<?php echo ($lang == 'ar') ? 'rtl' : 'ltr'; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo ($lang == 'ar') ? 'إنشاء حساب جديد | حوس الجزائر' : 'Register | Haws Al-jazair'; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;700;900&display=swap');

        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Cairo', sans-serif; }
        body { background: #fff; height: 100vh; overflow: hidden; color: #333; }
         
        .login-wrapper { display: flex; height: 100vh; width: 100%; } 
        .form-section { flex: 1; background: #fff; display: flex; align-items: center; justify-content: center; padding: 40px; z-index: 2; overflow-y: auto; position: relative; }

        .back-home { position: absolute; top: 30px; <?php echo ($lang == 'ar') ? 'right: 30px;' : 'left: 30px;'; ?> text-decoration: none; color: #c5a059; font-weight: 700; font-size: 15px; display: flex; align-items: center; gap: 8px; transition: 0.3s; }
        .back-home:hover { color: #333; transform: translateX(<?php echo ($lang == 'ar') ? '5px' : '-5px'; ?>); }

        .image-section.slider-v3-half { flex: 1; position: relative; overflow: hidden; height: 100%; }
        .slide-v3 { position: absolute; inset: 0; background-size: cover; background-position: center; background-image: url('https://i.pinimg.com/1200x/3e/43/a3/3e43a39b61305ae02ef89abbb28b80f3.jpg'); }

        .login-card { width: 100%; max-width: 440px; margin: auto; text-align: start; }
        .login-card h2 { color: #c5a059; font-size: 32px; font-weight: 900; margin-bottom: 8px; }
        .login-card p { color: #888; margin-bottom: 30px; font-size: 15px; }

        .input-box { margin-bottom: 18px; position: relative; }
        .input-box label { display: block; margin-bottom: 8px; color: #c5a059; font-size: 14px; font-weight: 700; }
        .input-box input { width: 100%; padding: 15px; background: #fdfdfd; border: 1px solid #eee; border-radius: 12px; color: #1a1a1a; outline: none; transition: 0.3s; font-size: 15px; }
        .input-box input:focus { border-color: #c5a059; background: #fff; }

        .btn-enter { width: 100%; padding: 16px; background: #c5a059; border: none; border-radius: 12px; color: #fff; font-weight: 900; font-size: 17px; margin-top: 15px; cursor: pointer; transition: 0.3s; }
        .btn-enter:hover { background: #333; transform: translateY(-3px); }

        .error-alert { background: rgba(255, 71, 87, 0.05); color: #ff4757; padding: 12px; border-radius: 10px; border-inline-start: 4px solid #ff4757; margin-bottom: 20px; font-size: 14px; }
        .success-alert { background: rgba(46, 213, 115, 0.05); color: #2ed573; padding: 12px; border-radius: 10px; border-inline-start: 4px solid #2ed573; margin-bottom: 20px; font-size: 14px; }
        
        .footer-text { margin-top: 30px; text-align: center; color: #aaa; font-size: 14px; }
        .footer-text a { color: #c5a059; text-decoration: none; font-weight: 700; }
    </style>
</head>
<body>

<div class="login-wrapper">
    <div class="form-section">
        <a href="index.php" class="back-home">
            <i class="fas <?php echo ($lang == 'ar') ? 'fa-arrow-right' : 'fa-arrow-left'; ?>"></i>
            <?php echo ($lang == 'ar') ? 'العودة للرئيسية' : 'Back to Home'; ?>
        </a>

        <div class="login-card">
            <h2><?php echo ($lang == 'ar') ? 'إنشاء حساب جديد' : 'CREATE ACCOUNT'; ?></h2>
            <p><?php echo ($lang == 'ar') ? 'انضم إلينا واستكشف جمال الجزائر' : 'Join us and explore the beauty of Algeria'; ?></p>

            <?php if($error): ?>
                <div class="error-alert"><i class="fas fa-circle-exclamation"></i> <?php echo $error; ?></div>
            <?php endif; ?>

            <?php if($success): ?>
                <div class="success-alert"><i class="fas fa-check-circle"></i> <?php echo $success; ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="input-box">
                    <label><?php echo ($lang == 'ar') ? 'اسم المستخدم' : 'Username'; ?></label>
                    <input type="text" name="username" placeholder="<?php echo ($lang == 'ar') ? "اختر اسماً مميزاً" : "Choose a unique name"; ?>" required>
                </div>

                <div class="input-box">
                    <label><?php echo ($lang == 'ar') ? 'البريد الإلكتروني' : 'Email Address'; ?></label>
                    <input type="email" name="email" placeholder="example@mail.com" required>
                </div>

                <div class="input-box">
                    <label><?php echo ($lang == 'ar') ? 'كلمة المرور' : 'Password'; ?></label>
                    <input type="password" name="password" placeholder="••••••••" required>
                </div>

                <div class="input-box">
                    <label><?php echo ($lang == 'ar') ? 'تأكيد كلمة المرور' : 'Confirm Password'; ?></label>
                    <input type="password" name="confirm_password" placeholder="••••••••" required>
                </div>

                <button type="submit" class="btn-enter"><?php echo ($lang == 'ar') ? 'تسجيل حساب جديد' : 'Register Now'; ?></button>
            </form>

            <div class="footer-text">
                <p><?php echo ($lang == 'ar') ? 'لديك حساب بالفعل؟' : 'Already have an account?'; ?> 
                <a href="login.php"><?php echo ($lang == 'ar') ? 'تسجيل الدخول' : 'Log In'; ?></a></p>
            </div>
        </div>
    </div>

    <div class="image-section slider-v3-half">
        <div class="slide-v3"></div>
    </div>
</div>

</body>
</html>