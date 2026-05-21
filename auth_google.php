<?php 
session_start();
include('lang.php');

$lang = $_SESSION['lang'] ?? 'ar';

$_SESSION['user'] = "Google User";
$_SESSION['user_id'] = 999; 

header("refresh:3;url=index.php");
?>

<!DOCTYPE html>
<html lang="<?php echo $lang; ?>" dir="<?php echo ($lang == 'ar') ? 'rtl' : 'ltr'; ?>">
<head>
    <meta charset="UTF-8">
    <title>Google Authentication</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Cairo:wght@400;700&display=swap');
        body { background: #f4f7f6; height: 100vh; display: flex; align-items: center; justify-content: center; font-family: 'Cairo', sans-serif; margin: 0; }
        .auth-card { background: #fff; padding: 40px; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); text-align: center; max-width: 400px; width: 90%; }
        .google-icon { font-size: 50px; color: #DB4437; margin-bottom: 20px; }
        .loader { border: 3px solid #f3f3f3; border-top: 3px solid #c5a059; border-radius: 50%; width: 30px; height: 30px; animation: spin 1s linear infinite; margin: 20px auto; }
        @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
        h2 { color: #333; font-size: 20px; }
        p { color: #888; font-size: 14px; }
    </style>
</head>
<body>
    <div class="auth-card">
        <i class="fab fa-google google-icon"></i>
        <h2><?php echo ($lang == 'ar') ? 'جاري التحقق عبر Google' : 'Authenticating via Google'; ?></h2>
        <div class="loader"></div>
        <p><?php echo ($lang == 'ar') ? 'يرجى الانتظار لحظة...' : 'Please wait a moment...'; ?></p>
    </div>
</body>
</html>