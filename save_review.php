<?php

session_start();
include 'db.php';

if(!isset($_SESSION['user_id'])){

    exit("login_required");
}

$user_id = $_SESSION['user_id'];

$rating = $_POST['rating'];
$suggestion = mysqli_real_escape_string($conn,$_POST['suggestion']);
$options = mysqli_real_escape_string($conn,$_POST['selected_options']);


// منع التقييم مرتين

$check = mysqli_query($conn,
"SELECT * FROM site_reviews WHERE user_id='$user_id'");

if(mysqli_num_rows($check) > 0){

    exit("already_reviewed");
}


mysqli_query($conn,

"INSERT INTO site_reviews
(user_id,rating,suggestion,selected_options)
VALUES
('$user_id','$rating','$suggestion','$options')"

);

echo "success";

?>