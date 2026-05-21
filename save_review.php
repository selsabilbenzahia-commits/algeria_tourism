<?php

session_start();
include 'db.php';

if(!isset($_SESSION['user_id'])){
    exit("login_required");
}

$user_id = $_SESSION['user_id'];

$rating = mysqli_real_escape_string($conn, $_POST['rating']);

$suggestion = isset($_POST['suggestion'])
? mysqli_real_escape_string($conn, $_POST['suggestion'])
: '';

$selected_options = isset($_POST['selected_options'])
? mysqli_real_escape_string($conn, $_POST['selected_options'])
: '';

$check = mysqli_query($conn,
"SELECT * FROM site_reviews WHERE user_id='$user_id'");

if(mysqli_num_rows($check) > 0){
    exit("already_reviewed");
}

mysqli_query($conn,
"INSERT INTO site_reviews
(user_id,rating,suggestion,selected_options)
VALUES
('$user_id','$rating','$suggestion','$selected_options')"
);

// notifications

$message = "New site review added (" . $rating . " Stars)";

mysqli_query($conn,
"INSERT INTO notifications (message, is_read)
VALUES ('$message', 0)"
);

if(!empty($selected_options) || !empty($suggestion)){

    $message2 = "New improvement suggestion received";

    mysqli_query($conn,
    "INSERT INTO notifications (message, is_read)
    VALUES ('$message2', 0)"
    );
}

echo "success";

?>