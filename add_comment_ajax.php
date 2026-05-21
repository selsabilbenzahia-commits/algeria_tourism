<?php
include 'db.php';
session_start();

if (isset($_POST['comment_text']) && isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $comment = mysqli_real_escape_string($conn, $_POST['comment_text']);

    $user_query = mysqli_query($conn, "SELECT name FROM users WHERE id = '$user_id'");
    $user_data = mysqli_fetch_assoc($user_query);
    $user_name = $user_data['name'] ?? 'Guest'; 

    if (isset($_POST['id'])) {
        $id = $_POST['id'];
        $sql = "INSERT INTO comments (user_id, attraction_id, comment) VALUES ('$user_id', '$id', '$comment')";
    } else if (isset($_POST['wilaya_id'])) {
        $id = $_POST['wilaya_id'];
        $sql = "INSERT INTO comments (user_id, wilaya_id, comment) VALUES ('$user_id', '$id', '$comment')";
    }

    if (mysqli_query($conn, $sql)) {
        echo '<div class="single-comment-v2">
                <div class="u-avatar-v2">'.mb_substr($user_name, 0, 1, 'utf-8').'</div>
                <div class="u-text-v2">
                    <strong>'.$user_name.'</strong>
                    <p>'.$comment.'</p>
                </div>
              </div>';
    }
}
?>