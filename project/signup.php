<?php  


require 'dbconnect.php';

if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}


session_start();
$errorpass = "";
$email2 = "";

/* ===========================
   IF COMING FROM INVITATION LINK (GET)
=========================== */
if (isset($_GET['email']) && isset($_GET['token'])) {
    $_SESSION['invite_email'] = $_GET['email'];
    $_SESSION['invite_token'] = $_GET['token'];

    // User still needs to sign up → don't process yet
    // You can pre-fill the signup email field if you want
    $email2 = $_SESSION['invite_email'];
}

/* ===========================
   SIGNUP (POST)
=========================== */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name     = trim($_POST['name']);
    $email    = trim($_POST['email']);
    $password = trim($_POST['password']);

    $pattern = "/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/";

    if (!preg_match($pattern, $password)) {
        $errorpass = "Min 8 chars, incl. upper, lower, number & symbol.";
    } else {
        $hashpass = password_hash($password, PASSWORD_DEFAULT);

        $sql = "INSERT INTO users (email, password, username) VALUES (?, ?, ?)";
        $stm = mysqli_prepare($conn, $sql);

        if ($stm) {
            mysqli_stmt_bind_param($stm, "sss", $email, $hashpass, $name);
            if (mysqli_stmt_execute($stm)) {
                // New user created
                $_SESSION['name']  = $name;
                $_SESSION['email'] = $email;

                // ✅ If signup came from invitation, process it now
                if (isset($_SESSION['invite_email']) && isset($_SESSION['invite_token'])) {
                    $invite_email = $_SESSION['invite_email'];
                    $invite_token = $_SESSION['invite_token'];

                    // Lookup invitation
                    $stmt = mysqli_prepare($conn, "SELECT * FROM invitations WHERE token=? AND status='pending'");
                    mysqli_stmt_bind_param($stmt, "s", $invite_token);
                    mysqli_stmt_execute($stmt);
                    $res1 = mysqli_stmt_get_result($stmt);

                    if ($inv = mysqli_fetch_assoc($res1)) {
                        $sender_id      = $inv['sender_id'];
                        $receiver_email = $inv['receiver_email'];

                        // Get receiver_id (the user who just signed up)
                        $stm1 = mysqli_prepare($conn, "SELECT id FROM users WHERE email = ?");
                        mysqli_stmt_bind_param($stm1, "s", $receiver_email);
                        mysqli_stmt_execute($stm1);
                        $res2 = mysqli_stmt_get_result($stm1);

                        if ($row = mysqli_fetch_assoc($res2)) {
                            $receiver_id = $row['id'];

                            // Insert connection
                            $stmt3 = mysqli_prepare($conn, "INSERT INTO connections (user1_id, user2_id) VALUES (?, ?)");
                            mysqli_stmt_bind_param($stmt3, "ii", $sender_id, $receiver_id);
                            mysqli_stmt_execute($stmt3);
                            mysqli_stmt_close($stmt3);

                            // Mark invitation accepted
                            $stmt4 = mysqli_prepare($conn, "UPDATE invitations SET status='accepted' WHERE id=?");
                            mysqli_stmt_bind_param($stmt4, "i", $inv['sender_id']);
                            mysqli_stmt_execute($stmt4);
                            mysqli_stmt_close($stmt4);
                        }
                        mysqli_stmt_close($stm1);
                    }
                    mysqli_stmt_close($stmt);

                    // Clear invite session
                    unset($_SESSION['invite_email']);
                    unset($_SESSION['invite_token']);
                }

                mysqli_stmt_close($stm);
                header("Location: Dashboard.php");
                exit;
            } else {
                $errorpass = "Database insert error: " . mysqli_error($conn);
            }
            mysqli_stmt_close($stm);
        } else {
            $errorpass = "Statement preparation failed: " . mysqli_error($conn);
        }
    }
}
?>









 











<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ToiMoi - Private Messaging site </title>
    <script src="https://cdn.tailwindcss.com"></script>

    <link rel = "icon" href="./images/Favicon.png" >
</head>
<body class = "dark:bg-gray-800">
    <nav class="bg-white border-b border-gray-300 dark:bg-slate-900">
        <div class="max-w-7xl flex flex-wrap items-center justify-between mx-auto p-4  border-b-slate-900">
        <a href="index.php" class="flex items-center space-x-3 rtl:space-x-reverse">
            <img src="./images/logo-dark.png" class="h-10 w-auto" alt="ToiMoi logo" id = "logo" />
            <span class="self-center text-2xl font-semibold whitespace-nowrap dark:text-white"></span>
        </a>
        <div class="flex md:order-2 space-x-3 md:space-x-0 rtl:space-x-reverse">
          <a href="signup.php">
          <button type="button" class=" cursor-pointer text-white bg-blue-950 hover:bg-blue-900 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-4 py-2 flex items-center space-x-2 dark:bg-slate-800 dark:hover:bg-slate-700 dark:focus:ring-slate-800">
            <span>Sign up</span>
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
              <a href="index.php" class="block py-2 px-3 md:p-0 text-white bg-blue-700 rounded-sm md:bg-transparent md:text-blue-700 md:dark:text-blue-500" >Home</a>
            </li>
            <li>
              <a href="index.php" class="block py-2 px-3 md:p-0 text-gray-900 rounded-sm hover:bg-gray-100 md:hover:bg-transparent md:hover:text-blue-700 md:dark:hover:text-blue-500 dark:text-white dark:hover:bg-gray-700 dark:hover:text-white md:dark:hover:bg-transparent dark:border-gray-700">Chat Room</a>
            </li>
            <li>
              <a href="index.php" class="block py-2 px-3 md:p-0 text-gray-900 rounded-sm hover:bg-gray-100 md:hover:bg-transparent md:hover:text-blue-700 md:dark:hover:text-blue-500 dark:text-white dark:hover:bg-gray-700 dark:hover:text-white md:dark:hover:bg-transparent dark:border-gray-700">Connect</a>
            </li>
        </div>
        </div>
      </nav>
      

      
<div class = "h-full m-auto overflow-hidden p-2 mt-16">

    <div class = "flex justify-center mb-6 space-x-3 ">
        <h2 class="text-4xl font-medium text-center dark:text-white">SignUp</h2>
        <svg class="w-11 h-11 items-center text-slate-900 dark:text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 24 24">
          <path fill-rule="evenodd" d="M12 20a7.966 7.966 0 0 1-5.002-1.756l.002.001v-.683c0-1.794 1.492-3.25 3.333-3.25h3.334c1.84 0 3.333 1.456 3.333 3.25v.683A7.966 7.966 0 0 1 12 20ZM2 12C2 6.477 6.477 2 12 2s10 4.477 10 10c0 5.5-4.44 9.963-9.932 10h-.138C6.438 21.962 2 17.5 2 12Zm10-5c-1.84 0-3.333 1.455-3.333 3.25S10.159 13.5 12 13.5c1.84 0 3.333-1.455 3.333-3.25S13.841 7 12 7Z" clip-rule="evenodd"/>
        </svg> 
        
      </div>
    
    
    <form class="max-w-sm mx-auto" action="<?php echo htmlspecialchars( $_SERVER['PHP_SELF']); ?>" method="post">
           <!-- Username -->
        <div class="mb-5">
          <label for="name" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Username</label>
          <div class="relative">
            <input type="text" id="name" name = "name" class="shadow-xs bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-slate-950 focus:border-slate-950 block w-full pl-10 p-2.5 dark:bg-gray-50 dark:border-gray-300 dark:placeholder-gray-400 dark:text-black dark:focus:ring-slate-950" placeholder="Username" required />
            <svg class="absolute left-3 top-1/2 transform -translate-y-1/2 w-6 h-6 text-gray-800 dark:text-black" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 24 24">
              <path fill-rule="evenodd" d="M8 4a4 4 0 1 0 0 8 4 4 0 0 0 0-8Zm-2 9a4 4 0 0 0-4 4v1a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2v-1a4 4 0 0 0-4-4H6Zm7.25-2.095c.478-.86.75-1.85.75-2.905a5.973 5.973 0 0 0-.75-2.906 4 4 0 1 1 0 5.811ZM15.466 20c.34-.588.535-1.271.535-2v-1a5.978 5.978 0 0 0-1.528-4H18a4 4 0 0 1 4 4v1a2 2 0 0 1-2 2h-4.535Z" clip-rule="evenodd"/>
            </svg>
          </div>
        </div>




        <!-- Email Field -->
        <div class="mb-5">
          <label for="email" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Email</label>
          <div class="relative">
            <input type="email" id="email" name = "email" value="<?php echo htmlspecialchars($email2); ?>"  class="shadow-xs bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-slate-950 focus:border-slate-950 block w-full pl-10 p-2.5 dark:bg-gray-50 dark:border-gray-300 dark:placeholder-gray-400 dark:text-black dark:focus:ring-slate-950" placeholder="person@email.com" required />
           
            <svg class = "absolute left-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-800 dark:text-black" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 16">
        <path d="m10.036 8.278 9.258-7.79A1.979 1.979 0 0 0 18 0H2A1.987 1.987 0 0 0 .641.541l9.395 7.737Z"/>
        <path d="M11.241 9.817c-.36.275-.801.425-1.255.427-.428 0-.845-.138-1.187-.395L0 2.6V14a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V2.5l-8.759 7.317Z"/>
    </svg>
          </div>
        </div>

       
          <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
      
        <!-- Password Field -->
        <div class="mb-5">
          <label for="password" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Your password</label>
          <div class="relative">
            <input type="password" name = "password"  id = "password" class="shadow-xs bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-slate-950 focus:border-slate-950 block w-full pl-10 p-2.5 dark:bg-gray-50 dark:border-gray-300 dark:placeholder-gray-400 dark:text-black dark:focus:ring-slate-950" placeholder="Enter your Password" required />

            <svg class="absolute left-3 top-1/2 transform -translate-y-1/2 w-6 h-6 text-gray-800 dark:text-black" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 24 24">
              <path fill-rule="evenodd" d="M8 10V7a4 4 0 1 1 8 0v3h1a2 2 0 0 1 2 2v7a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2v-7a2 2 0 0 1 2-2h1Zm2-3a2 2 0 1 1 4 0v3h-4V7Zm2 6a1 1 0 0 1 1 1v3a1 1 0 1 1-2 0v-3a1 1 0 0 1 1-1Z" clip-rule="evenodd"/>
            </svg>
          </div>
        </div>

        <!--Repeat password Field-->
        <div class="mb-5">
            <label for="repeat-password" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Repeat password</label>
            <div class="relative">
              <input type="password"  id ="password2" class="shadow-xs bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-slate-950 focus:border-slate-950 block w-full pl-10 p-2.5 dark:bg-gray-50 dark:border-gray-300 dark:placeholder-gray-400 dark:text-black dark:focus:ring-slate-950 dark:focus:border-slate-950" placeholder="Re-enter your Password" required />
              <svg class="absolute left-3 top-1/2 transform -translate-y-1/2 w-6 h-6 text-gray-800 dark:text-black" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 24 24">
                <path fill-rule="evenodd" d="M8 10V7a4 4 0 1 1 8 0v3h1a2 2 0 0 1 2 2v7a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2v-7a2 2 0 0 1 2-2h1Zm2-3a2 2 0 1 1 4 0v3h-4V7Zm2 6a1 1 0 0 1 1 1v3a1 1 0 1 1-2 0v-3a1 1 0 0 1 1-1Z" clip-rule="evenodd"/>
              </svg>
            </div>
          </div>

          
<?php if (!empty($errorpass)): ?>
  <div class= " text-center  text-sm text-red-800 rounded-lg dark:bg-gray-800 dark:text-red-400" role="alert">
  
  <div>
  <span class="font-medium text-center"> Info Danger alert!</span> <?php echo $errorpass; ?>
  </div>
</div>
<?php endif; ?>


          <div class="flex justify-center md:justify-center mt-10">
        <button type="submit" id="button" name = "submit" class="  flex items-center justify-center text-white cursor-pointer bg-blue-950 hover:bg-blue-900 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-slate-950 dark:hover:bg-slate-900 dark:focus:ring-slate-900">

            <span>Register new account</span>
          <svg class="w-4 h-4 ml-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M19 12H5m14 0-4 4m4-4-4-4" />
          </svg>
        </button>
    </div>
    </form>
  



<script>
  const password = document.getElementById('password');
  const password2 = document.getElementById('password2');

  function validatePassword(){
    if(password.value !== password2.value){
      password2.setCustomValidity("Passwords don't match");
    } else {
      password2.setCustomValidity('');  
    }
  }

  password.oninput = validatePassword;
  password2.oninput = validatePassword;
  
</script>

 <script src="../node_modules/flowbite/dist/flowbite.min.js"></script>
 <script src = "main.js"></script>


</body>
</html>





