<?php 
session_start();
include 'db.php'; 
include('lang.php');

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

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    
    $check_email = mysqli_query($conn, "SELECT * FROM users WHERE email='$email'");
    
    if (mysqli_num_rows($check_email) > 0) {
        $success = ($lang == 'ar') ? "تم إرسال تعليمات استعادة كلمة المرور إلى بريدك الإلكتروني." : "Password reset instructions have been sent to your email.";
    } else {
        $error = ($lang == 'ar') ? "هذا البريد الإلكتروني غير مسجل لدينا!" : "This email is not registered!";
    }
}
?>

<!DOCTYPE html>
<html lang="<?php echo $lang; ?>" dir="<?php echo ($lang == 'ar') ? 'rtl' : 'ltr'; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo ($lang == 'ar') ? 'استعادة كلمة المرور | حوس الجزائر' : 'Reset Password | Haws Al-jazair'; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;700;900&display=swap');

        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Cairo', sans-serif; }
        body { background: #fff; height: 100vh; overflow: hidden; color: #333; }
         
        .login-wrapper { display: flex; height: 100vh; width: 100%; } 
        .form-section { flex: 1; background: #fff; display: flex; align-items: center; justify-content: center; padding: 60px; z-index: 2; position: relative; }

        .back-home { position: absolute; top: 30px; <?php echo ($lang == 'ar') ? 'right: 30px;' : 'left: 30px;'; ?> text-decoration: none; color: #c5a059; font-weight: 700; font-size: 15px; display: flex; align-items: center; gap: 8px; transition: 0.3s; }
        .back-home:hover { color: #333; transform: translateX(<?php echo ($lang == 'ar') ? '5px' : '-5px'; ?>); }

        .image-section.slider-v3-half { flex: 1; position: relative; overflow: hidden; height: 100%; background: #f9f9f9; }
        .slide-v3 { position: absolute; inset: 0; background-size: cover; background-position: center; background-image: url('https://safaryti.com/blogs/1747323395LmrtCLfBwm'); opacity: 0.8; }

        .login-card { width: 100%; max-width: 440px; margin: auto; text-align: start; }
        .login-card h2 { color: #c5a059; font-size: 32px; font-weight: 900; margin-bottom: 12px; }
        .login-card p { color: #888; margin-bottom: 40px; font-size: 16px; line-height: 1.6; }

        .input-box { margin-bottom: 25px; position: relative; }
        .input-box label { display: block; margin-bottom: 12px; color: #c5a059; font-size: 14px; font-weight: 700; }
        .input-box input { width: 100%; padding: 18px; background: #fdfdfd; border: 1px solid #eee; border-radius: 12px; color: #1a1a1a; outline: none; transition: 0.3s; font-size: 16px; }
        .input-box input:focus { border-color: #c5a059; background: #fff; }

        .btn-enter { width: 100%; padding: 18px; background: #c5a059; border: none; border-radius: 12px; color: #fff; font-weight: 900; font-size: 18px; margin-top: 10px; cursor: pointer; transition: 0.3s; }
        .btn-enter:hover { background: #333; transform: translateY(-3px); }

        .error-alert { background: rgba(255, 71, 87, 0.05); color: #ff4757; padding: 15px; border-radius: 10px; border-inline-start: 4px solid #ff4757; margin-bottom: 20px; font-size: 15px; }
        .success-alert { background: rgba(46, 213, 115, 0.05); color: #2ed573; padding: 15px; border-radius: 10px; border-inline-start: 4px solid #2ed573; margin-bottom: 20px; font-size: 15px; }
        
        .footer-text { margin-top: 40px; text-align: center; color: #aaa; font-size: 14px; }
        .footer-text a { color: #c5a059; text-decoration: none; font-weight: 700; }
    </style>
</head>
<body>

<div class="login-wrapper">
    <div class="form-section">
        <a href="login.php" class="back-home">
            <i class="fas <?php echo ($lang == 'ar') ? 'fa-arrow-right' : 'fa-arrow-left'; ?>"></i>
            <?php echo ($lang == 'ar') ? 'العودة لتسجيل الدخول' : 'Back to Login'; ?>
        </a>

        <div class="login-card">
            <h2><?php echo ($lang == 'ar') ? 'نسيت كلمة المرور؟' : 'Forgot Password?'; ?></h2>
            <p><?php echo ($lang == 'ar') ? 'لا تقلق، أدخل بريدك الإلكتروني المسجل وسنرسل لك رابطاً لاستعادة الوصول لحسابك.' : 'No worries! Enter your registered email and we will send you a link to reset your password.'; ?></p>

            <?php if($error): ?>
                <div class="error-alert"><i class="fas fa-circle-exclamation"></i> <?php echo $error; ?></div>
            <?php endif; ?>

            <?php if($success): ?>
                <div class="success-alert"><i class="fas fa-check-circle"></i> <?php echo $success; ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="input-box">
                    <label><?php echo ($lang == 'ar') ? 'البريد الإلكتروني' : 'Email Address'; ?></label>
                    <input type="email" name="email" placeholder="example@mail.com" required>
                </div>

                <button type="submit" class="btn-enter"><?php echo ($lang == 'ar') ? 'إرسال الرابط' : 'Send Reset Link'; ?></button>
            </form>

            <div class="footer-text">
                <p><?php echo ($lang == 'ar') ? 'تذكرت كلمة المرور؟' : 'Remembered your password?'; ?> 
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