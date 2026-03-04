<?php

require_once __DIR__ . '/dbconnect.php';


if (!$conn) {
  die("Connection failed: " . mysqli_connect_error());
}

if($_SERVER['REQUEST_METHOD'] ===  'POST') {

$email = trim( $_POST['email']);
$password = trim( $_POST['password']);
$password2 = trim($_POST['password2']);
$submit = $_POST['submit'];



if(isset($submit)){

  if(!$password === $password2){

    echo "password doesn't match";
  }
    else {
      echo "your email is" . $email . "and password is ". $password;
    }
  }
}




mysqli_close($conn)

?>