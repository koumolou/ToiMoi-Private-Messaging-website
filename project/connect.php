<?php
  
session_start();


require 'dbconnect.php';
require __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/env.php';
 




use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;



if(!isset($_SESSION['user_id'])){
  header("Location: index.php");
  exit();
}

$session_id = $_SESSION['user_id'];
$session_email = $_SESSION['email'];
$user = $_SESSION['name'];

$error = "";
$success = "";

if($_SERVER['REQUEST_METHOD'] === 'POST') {

  $invite_email = trim($_POST['email']);

  if(!filter_var($invite_email, FILTER_VALIDATE_EMAIL)){
    $error = "Email adddress is not valid";
  }
  else{
  $stm = mysqli_prepare($conn , "SELECT * FROM connections WHERE user1_id  = ? OR user2_id = ? ");
  mysqli_stmt_bind_param($stm, "ii", $session_id,$session_id );
  mysqli_stmt_execute($stm);
  $result = mysqli_stmt_get_result($stm);
  

  }

  if(mysqli_num_rows($result) > 0 ){
$error = "You have already connected with someone";
  }
else {
  
$token = bin2hex(random_bytes(32));

$stm = mysqli_prepare($conn, "INSERT INTO invitations (sender_id, receiver_email, token) VALUES (?, ?, ?) ");
mysqli_stmt_bind_param($stm,"iss", $session_id, $invite_email, $token );


if(mysqli_execute($stm)){

  $invite_link = "http://localhost/ToiMoi/src/accept_invite.php?token=$token";
loadEnv(); 

$mail = new PHPMailer(true);
$mail->isSMTP();
$mail->Host       = getenv('MAIL_HOST');
$mail->SMTPAuth   = true;
$mail->Username   = getenv('MAIL_USERNAME');  
$mail->Password   = getenv('MAIL_PASSWORD');    
$mail->SMTPSecure = 'tls';
$mail->Port       = getenv('MAIL_PORT');

$mail->setFrom(getenv('MAIL_USERNAME'), getenv('MAIL_FROM_NAME'));
$mail->addAddress($invite_email);

$mail->isHTML(true);
$mail->Subject = "You've been invited to ToiMoi!";
$mail->Body    = "
    <p>Hello,</p>
    <p>You’ve been invited to chat by <span>$user</span> on <strong>ToiMoi</strong>.</p>
    <p>Click this link to accept: <a href='$invite_link'>$invite_link</a></p>
";

try {
    $mail->send();
$success = "Invitation email sent successfully.";
} catch (Exception $e) {
 
$error = "Mailer Error: {$mail->ErrorInfo}";
}

}
}}


?>







<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connect</title>
  <script src="https://cdn.tailwindcss.com"></script>

    <link rel = "icon" href="./images/Favicon.png" >
</head>


<body class="dark:bg-gray-800">
<nav class="bg-white border-b border-gray-300 dark:bg-slate-900 mb-6">
  <div class="max-w-screen-xl flex flex-wrap items-center justify-between mx-auto p-4  border-b-slate-900">
  <a href="#" class="flex items-center space-x-3 rtl:space-x-reverse">
      <img src="./images/logo-dark.png" class="h-10 w-auto" alt="ToiMoi logo" id = "logo" />
      <span class="self-center text-2xl font-semibold whitespace-nowrap dark:text-white"></span>
  </a>
  <div class="flex md:order-2 space-x-3 md:space-x-0 rtl:space-x-reverse">
    <a href="logout.php">
    <button type="button" class=" cursor-pointer text-white bg-blue-950 hover:bg-blue-900 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-4 py-2 flex items-center space-x-2 dark:bg-slate-800 dark:hover:bg-slate-700 dark:focus:ring-slate-800">
      <span>Logout</span>
      <svg class="w-6 h-6 text-white dark:text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 12H5m14 0-4 4m4-4-4-4"/>
      </svg>
      
    </button>
  </a>
      
      <button data-collapse-toggle="navbar-cta" type="button" class="inline-flex items-center p-2 w-10 h-10 justify-center text-sm text-gray-500 rounded-lg md:hidden hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-gray-200 dark:text-gray-400 dark:hover:bg-gray-700 dark:focus:ring-gray-600" aria-controls="navbar-cta" aria-expanded="false">
        <span class="sr-only">Open main menu</span>
        <svg class="w-5 h-5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 17 14">
            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M1 1h15M1 7h15M1 13h15"/>
        </svg>
    </button>
  </div>
  <div class="items-center justify-between hidden w-full md:flex md:w-auto md:order-1" id="navbar-cta">
    <ul class="flex flex-col font-medium p-4 md:p-0 mt-4 border border-gray-100 rounded-lg bg-gray-50 md:space-x-8 rtl:space-x-reverse md:flex-row md:mt-0 md:border-0 md:bg-white dark:bg-gray-800 md:dark:bg-gray-900 dark:border-gray-700">
      <li>
        <a href="index.php" class="block py-2 px-3 md:p-0 text-gray-900 rounded-sm hover:bg-gray-100 md:hover:bg-transparent md:hover:text-blue-700 md:dark:hover:text-blue-500 dark:text-white dark:hover:bg-gray-700 dark:hover:text-white md:dark:hover:bg-transparent dark:border-gray-700">Home</a>
      </li>
       
      <li>
        <a href="chat.php" class="block py-2 px-3 md:p-0 text-gray-900 rounded-sm hover:bg-gray-100 md:hover:bg-transparent md:hover:text-blue-700 md:dark:hover:text-blue-500 dark:text-white dark:hover:bg-gray-700 dark:hover:text-white md:dark:hover:bg-transparent dark:border-gray-700">Chat Room</a>
      </li>
      <li>
        <a href="#" class="block py-2 px-3 md:p-0 text-white bg-blue-700 rounded-sm md:bg-transparent md:text-blue-700 md:dark:text-blue-500" aria-current="page">Connect</a>
      </li>
      
  </div>
  </div>
</nav>

<div class = "h-full m-auto overflow-hidden p-2 mt-16">


  

<form class="max-w-sm mx-auto mb-5" method="post" action= "<?php echo htmlspecialchars( $_SERVER['PHP_SELF']); ?>">
  <div class = "flex justify-center ">
    <h2 class="text-2xl font-light  text-center mb-3 dark:text-white"><?php echo "Welcome,". " ". substr(strtoupper($_SESSION['name']),0,1). substr($_SESSION['name'], 1, ) ?></h2>
       
  </div>
<div class="flex justify-center items-center mt-7">
  <div class="relative inline-flex items-center justify-center w-20 h-20 overflow-hidden bg-gray-100 rounded-full dark:bg-gray-600">
    <span class="font-medium text-gray-600 dark:text-gray-300">MOI</span>
  </div>
</div>
  
    
        <h2 class="text-xl font-light text-center mb-4 mt-12 dark:text-white" >Connect With Your favorite <span class = "font-bold">TOI</span></h2>

  

  <!-- Email Field -->
  <div class="mb-5">
    <label for="email" class="block mb-2 text-sm font-medium text-white dark:text-white">Email</label>
    <div class="relative">
      <input type="email" id="email" class="shadow-xs bg-gray-50 border  border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-slate-950 focus:border-slate-950 block w-full pl-10 p-2.5 dark:bg-gray-50 dark:border-gray-300 dark:placeholder-gray-400 dark:text-black dark:focus:ring-slate-950" placeholder="person@email.com" required  name = "email"/>
      <svg class="absolute left-3 top-1/2 transform -translate-y-1/2 w-6 h-6 text-gray-800 dark:text-gray-800" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 24 24">
        <path fill-rule="evenodd" d="M8 4a4 4 0 1 0 0 8 4 4 0 0 0 0-8Zm-2 9a4 4 0 0 0-4 4v1a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2v-1a4 4 0 0 0-4-4H6Zm7.25-2.095c.478-.86.75-1.85.75-2.905a5.973 5.973 0 0 0-.75-2.906 4 4 0 1 1 0 5.811ZM15.466 20c.34-.588.535-1.271.535-2v-1a5.978 5.978 0 0 0-1.528-4H18a4 4 0 0 1 4 4v1a2 2 0 0 1-2 2h-4.535Z" clip-rule="evenodd"/>
      </svg>
    </div>
  </div>


 <div class="flex justify-center md:justify-center rounded-md" role="group">
    <button type="submit" class="flex items-center justify-center text-white bg-blue-950 hover:bg-blue-900 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm w-auto max-w-xs px-5 py-2.5 text-center dark:bg-slate-900 dark:hover:bg-slate-800 dark:focus:ring-slate-800" name = "submit">
      <span>Connect</span>
      <svg class="w-4 h-4 ml-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M19 12H5m14 0-4 4m4-4-4-4" />
      </svg>
    </button>
  </div>
</div>



</form>

  <div class= " text-center mt-5 text-sm rounded-lg " >
  
  <div>
  <span class="font-medium text-center text-black"><?php echo $error; ?></span> 
  </div>
</div>

 <div class= " text-center mt-5 text-sm rounded-lg" >
  
  <div>
  <span class="font-medium text-center text-white "><?php echo $success; ?></span> 
  </div>
</div>





  <script src="../node_modules/flowbite/dist/flowbite.min.js"></script>

<script src="main.js" ></script>

</body>
</html>

