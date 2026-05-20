<?php
include "db.php"; 
session_start();

if(!isset($_SESSION['user_id'])) {
    echo "login_required";
    exit;
}

if(isset($_POST['item_id']) && isset($_POST['item_type'])) {
    $user_id = $_SESSION['user_id']; 
    $item_id = $_POST['item_id'];
    $item_type = $_POST['item_type']; 

    $check_query = "SELECT * FROM favorites WHERE user_id = '$user_id' AND item_id = '$item_id' AND item_type = '$item_type'";
    $check_result = mysqli_query($conn, $check_query);
    
    if(mysqli_num_rows($check_result) > 0) {
        $delete_query = "DELETE FROM favorites WHERE user_id = '$user_id' AND item_id = '$item_id' AND item_type = '$item_type'";
        if(mysqli_query($conn, $delete_query)) {
            echo "removed";
        }
    } else {
        $insert_query = "INSERT INTO favorites (user_id, item_id, item_type) VALUES ('$user_id', '$item_id', '$item_type')";
        if(mysqli_query($conn, $insert_query)) {
            echo "added";
        }
    }
}
?>
