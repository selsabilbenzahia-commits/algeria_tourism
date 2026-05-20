<?php
session_start();
session_unset(); // يمسح كل متغيرات الجلسة
session_destroy(); // يدمر الجلسة تماماً
header("Location: index.php"); // يعيده للرئيسية ليرى زر "تسجيل الدخول" مجدداً
exit();
?>