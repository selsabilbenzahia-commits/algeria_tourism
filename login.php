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

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = $_POST['password'];
 
    $admin_query = "SELECT * FROM admins WHERE username='$username'";
    $admin_res = mysqli_query($conn, $admin_query);
    
    if (mysqli_num_rows($admin_res) > 0) {
        $admin = mysqli_fetch_assoc($admin_res);
        if ($password == $admin['password'] || password_verify($password, $admin['password'])) {
            $_SESSION['admin'] = $admin['username'];
            header("Location: admin_dashboard.php");
            exit();
        }
    }
 
    $user_query = "SELECT * FROM users WHERE name='$username'"; 
    $user_res = mysqli_query($conn, $user_query);

    if (mysqli_num_rows($user_res) > 0) {
        $user = mysqli_fetch_assoc($user_res);
        if ($password == $user['password'] || password_verify($password, $user['password'])) {
            $_SESSION['user'] = $user['name'];  
            $_SESSION['user_id'] = $user['id'];
            header("Location: index.php");  
            exit();
        }
    }
    
    $error = ($lang == 'ar') ?"اسم المستخدم أو كلمة المرور غير صحيحة!" : "Incorrect username or password!";
}
?>

<!DOCTYPE html>
<html lang="<?php echo $lang; ?>" dir="<?php echo ($lang == 'ar') ? 'rtl' : 'ltr'; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo ($lang == 'ar') ? 'تسجيل الدخول | حوس الجزائر' : 'Login | Haws Al-jazair'; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;700;900&display=swap');

        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Cairo', sans-serif; }
        body { background: #fff; height: 100vh; overflow: hidden; color: #333; }
         
        .login-wrapper { display: flex; height: 100vh; width: 100%; } 
        .form-section { flex: 1; background: #fff; display: flex; align-items: center; justify-content: center; padding: 60px; z-index: 2; overflow-y: auto; position: relative; }

        /* زر الرجوع الأنيق */
        .back-home { position: absolute; top: 30px; <?php echo ($lang == 'ar') ? 'right: 30px;' : 'left: 30px;'; ?> text-decoration: none; color: #c5a059; font-weight: 700; font-size: 15px; display: flex; align-items: center; gap: 8px; transition: 0.3s; }
        .back-home:hover { color: #333; transform: translateX(<?php echo ($lang == 'ar') ? '5px' : '-5px'; ?>); }

        .image-section.slider-v3-half { flex: 1; position: relative; overflow: hidden; height: 100%; }
        .slides-container-v3 { position: absolute; inset: 0; z-index: 1; }
        .slide-v3 { position: absolute; inset: 0; background-size: cover; background-position: center; opacity: 0; transition: opacity 2s ease-in-out; }
        .slide-v3.active { opacity: 1; }

        .slider-progress-v3 { position: absolute; top: 25px; left: 10%; right: 10%; display: flex; gap: 8px; z-index: 5; }
        .progress-bar-v3 { flex: 1; height: 4px; background: rgba(255, 255, 255, 0.3); border-radius: 4px; overflow: hidden; position: relative; }
        .progress-bar-v3.active span { content: ""; position: absolute; top: 0; left: 0; bottom: 0; background: #fff; width: 0; animation: progressFill 5s linear forwards; }

        @keyframes progressFill { from { width: 0; } to { width: 100%; } }

        .login-card { width: 100%; max-width: 440px; margin: auto; text-align: start; }
        .login-card h2 { color: #c5a059; font-size: 38px; font-weight: 900; margin-bottom: 12px; }
        .login-card p { color: #888; margin-bottom: 40px; font-size: 16px; }

        .input-box { margin-bottom: 25px; position: relative; }
        .input-box label { display: block; margin-bottom: 12px; color: #c5a059; font-size: 14px; font-weight: 700; }
        .input-box input { width: 100%; padding: 18px; <?php echo ($lang == 'ar') ? 'padding-left: 50px;' : 'padding-right: 50px;'; ?> background: #fdfdfd; border: 1px solid #eee; border-radius: 12px; color: #1a1a1a; outline: none; transition: 0.3s; font-size: 16px; text-align: start; }
        .input-box input:focus { border-color: #c5a059; background: #fff; }

        /* إصلاح أيقونة العين - تتموضع برمجياً حسب اللغة */
        .toggle-password { position: absolute; top: 48px; <?php echo ($lang == 'ar') ? 'left: 18px;' : 'right: 18px;'; ?> color: #bbb; cursor: pointer; font-size: 18px; z-index: 10; padding: 5px; }
        
        .btn-enter { width: 100%; padding: 18px; background: #c5a059; border: none; border-radius: 12px; color: #fff; font-weight: 900; font-size: 18px; margin-top: 20px; cursor: pointer; transition: 0.3s; }
        .btn-enter:hover { background: #333; transform: translateY(-3px); }

        .social-area p { color: #aaa; font-size: 13px; margin: 30px 0 20px; text-align: center; }
        .social-icons { display: flex; gap: 15px; justify-content: center; }
        .s-icon { width: 55px; height: 55px; border-radius: 50%; border: 1px solid #eee; display: flex; align-items: center; justify-content: center; color: #333; text-decoration: none; transition: 0.3s; font-size: 20px; }
        .s-icon:hover { border-color: #c5a059; color: #c5a059; background: rgba(197,160,89,0.02); }

        .error-alert { background: rgba(255, 71, 87, 0.05); color: #ff4757; padding: 15px; border-radius: 10px; border-inline-start: 4px solid #ff4757; margin-bottom: 30px; font-size: 15px; text-align: start; }
        .footer-text { margin-top: 40px; text-align: center; color: #aaa; font-size: 14px; }
        .footer-text a { color: #c5a059; text-decoration: none; font-weight: 700; }
    </style>
</head>
<body>

<div class="login-wrapper">
    <div class="form-section">
        <!-- زر الرجوع للصفحة الرئيسية -->
        <a href="index.php" class="back-home">
            <i class="fas <?php echo ($lang == 'ar') ? 'fa-arrow-right' : 'fa-arrow-left'; ?>"></i>
            <?php echo ($lang == 'ar') ? 'العودة للرئيسية' : 'Back to Home'; ?>
        </a>

        <div class="login-card">
            <h2><?php echo ($lang == 'ar') ? 'تسجيل الدخول' : 'LOG IN'; ?></h2>
            <p><?php echo ($lang == 'ar') ? 'أهلاً بكم مجدداً في حوس الجزائر' : 'Welcome back to Haws Al-Jazair'; ?></p>

            <?php if($error): ?>
                <div class="error-alert"><i class="fas fa-circle-exclamation"></i> <?php echo $error; ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="input-box">
                    <label><?php echo ($lang == 'ar') ? 'اسم المستخدم' : 'Username'; ?></label>
                    <input type="text" name="username" placeholder="<?php echo ($lang == 'ar') ? "أدخل اسمك" : "Enter your name"; ?>" required>
                </div>

                <div class="input-box">
                    <label><?php echo ($lang == 'ar') ? 'كلمة المرور' : 'Password'; ?></label>
                    <input type="password" name="password" id="myPass" placeholder="••••••••" required>
                    <i class="far fa-eye toggle-password" onclick="showPass()"></i>
                    <a href="forgot_password.php" style="float:<?php echo ($lang == 'ar') ? 'left' : 'right'; ?>; font-size:12px; color:#c5a059; text-decoration:none; margin-top:5px; font-weight:700;">
                        <?php echo ($lang == 'ar') ? 'هل نسيت كلمة السر؟' : 'Forgot password?'; ?>
                    </a>
                </div>

                <button type="submit" class="btn-enter"><?php echo ($lang == 'ar') ? 'تسجيل الدخول' : 'Sign In'; ?></button>
            </form>

            <div class="social-area">
                <p><?php echo ($lang == 'ar') ? 'أو سجل لدى' : 'Or login with';?></p>
                <div class="social-icons">
                    <a href="auth_google.php" class="s-icon"><i class="fab fa-google"></i></a>
                    <a href="auth_facebook.php" class="s-icon"><i class="fab fa-facebook-f"></i></a>
                </div>
            </div>

            <div class="footer-text">
             <p> <?php echo $texts[$lang]['sure']; ?> <a href="register.php"><?php echo $texts[$lang]['create_new_cc']; ?></a></p>
            </div>
        </div>
    </div>

    <div class="image-section slider-v3-half">
        <div class="slider-progress-v3">
            <div class="progress-bar-v3 active"><span></span></div>
            <div class="progress-bar-v3"><span></span></div>
            <div class="progress-bar-v3"><span></span></div>
        </div>

        <div class="slides-container-v3">
            <div class="slide-v3 active" style="background-image: url('https://upload.wikimedia.org/wikipedia/commons/c/c7/Jardin_d%27essai%C3%A0_Alger.jpg');"></div>
            <div class="slide-v3" style="background-image: url('https://safaryti.com/blogs/1747323395LmrtCLfBwm');"></div>
            <div class="slide-v3" style="background-image: url('https://i.pinimg.com/1200x/3e/43/a3/3e43a39b61305ae02ef89abbb28b80f3.jpg');"></div>
        </div>
    </div>
</div>

<script>
    function showPass() {
        var x = document.getElementById("myPass");
        var icon = document.querySelector(".toggle-password");
        if (x.type === "password") {
            x.type = "text";
            icon.classList.replace('fa-eye', 'fa-eye-slash');
        } else {
            x.type = "password";
            icon.classList.replace('fa-eye-slash', 'fa-eye');
        }
    }

    let currentSlide = 0;
    const slides = document.querySelectorAll('.slide-v3');
    const progressBars = document.querySelectorAll('.progress-bar-v3');
    const intervalTime = 5000; 

    function nextSlide() {
        slides[currentSlide].classList.remove('active');
        progressBars[currentSlide].classList.remove('active');
        currentSlide = (currentSlide + 1) % slides.length;
        slides[currentSlide].classList.add('active');
        progressBars[currentSlide].classList.add('active');
    }

    setInterval(nextSlide, intervalTime);
</script>

</body>
</html>