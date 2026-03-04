<?php
session_start();



require 'dbconnect.php';


$success = "";
$red = "";

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$username = $_SESSION['name'];


$stm = mysqli_prepare($conn, "SELECT status FROM invitations WHERE sender_id = ?");
mysqli_stmt_bind_param($stm, 'i', $user_id);
mysqli_stmt_execute($stm);
$result = mysqli_stmt_get_result($stm);

if ($result && mysqli_num_rows($result) > 0) {
    $row = mysqli_fetch_assoc($result);

    if ($row['status'] === 'accepted') {
       $success = "You have a connection with your TOI";
    } else {
       
        $stm2 = mysqli_prepare($conn, "UPDATE invitations SET status = 'accepted' WHERE sender_id = ?");
        mysqli_stmt_bind_param($stm2, "i",  $user_id);
        mysqli_stmt_execute($stm2);
        $result = mysqli_stmt_get_result($stm2);
        if (mysqli_stmt_affected_rows($stm2) > 0) {

          $row = mysqli_fetch_assoc($result);

           $success = "You have a connection with your TOI now";
        } else {
          $red = "Connection with your TOI isn't accpeted yet";
        }
    }
} else {
   $red =  "You have not created a connection with your TOI";
}
?>















<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home Page</title>
  <script src="https://cdn.tailwindcss.com"></script>

    <link rel = "icon" href="./images/Favicon.png" >
</head>



<body class ="dark:bg-gray-800 font-display  ">
<nav class="bg-white border-b border-gray-300 dark:bg-slate-900">
  <div class="max-w-7xl flex flex-wrap items-center justify-between mx-auto p-4  border-b-slate-900">
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
        <a href="#" class="block py-2 px-3 md:p-0 text-white bg-blue-700 rounded-sm md:bg-transparent md:text-blue-700 md:dark:text-blue-500" aria-current="page">Home</a>
      </li>
      <li>
        <a href="chat.php" class="block py-2 px-3 md:p-0 text-gray-900 rounded-sm hover:bg-gray-100 md:hover:bg-transparent md:hover:text-blue-700 md:dark:hover:text-blue-500 dark:text-white dark:hover:bg-gray-700 dark:hover:text-white md:dark:hover:bg-transparent dark:border-gray-700">Chat Room</a>
      </li>
      <li>
        <a href="connect.php" class="block py-2 px-3 md:p-0 text-gray-900 rounded-sm hover:bg-gray-100 md:hover:bg-transparent md:hover:text-blue-700 md:dark:hover:text-blue-500 dark:text-white dark:hover:bg-gray-700 dark:hover:text-white md:dark:hover:bg-transparent dark:border-gray-700">Connect</a>
      </li>
      
  </div>
  </div>
</nav>



<div class = "h-[calc(100vh-9rem)]  m-auto  max-w-sm overflow-hidden p-2 mt-14">
<div class="w-full max-w-sm bg-white border border-gray-200 rounded-lg mt-16 shadow-sm dark:bg-gray-800 dark:border-gray-700">

   
    <div class="flex flex-col items-center pb-10">
       
        <div class ="  flex justify-center  items-center w-24 h-24 mb-3 rounded-full shadow-lg mt-5  bg-gray-100 dark:bg-gray-600"><span class ="text-xl"><?php  echo strtoupper( substr($username, 0, 1)) ?></span></div>
        <h5 class=" text-xl font-medium  text-black mb-4 dark:text-white">Welcome,  <?php echo $username; ?></h5>
        <span class="text-medium text-gray-500 dark:text-gray-400"><?php echo $success; ?></span>
         <span class="text-medium text-gray-500 dark:text-gray-400"><?php echo $red ?></span>
        <div class="flex mt-4 md:mt-6">
            <a href="connect.php" class="inline-flex items-center px-4 py-2 text-sm font-medium text-center text-white bg-blue-950 rounded-lg hover:bg-blue-900 focus:ring-4 focus:outline-none focus:ring-blue-300 dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">Connect</a>
            <a href="chat.php" class="py-2 px-4 ms-2 text-sm font-medium text-gray-900 focus:outline-none bg-white rounded-lg border border-gray-200 hover:bg-gray-100 hover:text-blue-700 focus:z-10 focus:ring-4 focus:ring-gray-100 dark:focus:ring-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:border-gray-600 dark:hover:text-white dark:hover:bg-gray-700">Message</a>
        </div>
    </div>
</div>

   
    

</div>



  <script src="../node_modules/flowbite/dist/flowbite.min.js"></script>

  <script src = "main.js"></script>

</body>
</html>