<?php

require_once __DIR__ . '/dbconnect.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $sender_id = mysqli_real_escape_string($conn, $_POST["sender_id"]);
    $message = mysqli_real_escape_string($conn, $_POST["text"]);

    // Find receiver dynamically
    $query = "SELECT user1_id, user2_id 
              FROM connections 
              WHERE user1_id = '$sender_id' OR user2_id = '$sender_id'
              LIMIT 1";
    $result = mysqli_query($conn, $query);

    if ($row = mysqli_fetch_assoc($result)) {
        $receiver_id = ($row['user1_id'] == $sender_id) ? $row['user2_id'] : $row['user1_id'];

        if (!empty($message)) {
            $sql = "INSERT INTO messages (sender_id, receiver_id, message, status, created_at)
                    VALUES ('$sender_id', '$receiver_id', '$message', 0, NOW())";
            mysqli_query($conn, $sql);
        }
    }
}

mysqli_close($conn);
?>
