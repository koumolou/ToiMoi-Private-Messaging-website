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
                header("Location: dashboard.php");
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
    <title>ToiMoi - Private Messaging site</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="icon" href="./images/Favicon.png">
    <style>
      /* Prevent iOS zoom on input focus */
      input[type="text"],
      input[type="email"],
      input[type="password"] {
        font-size: 16px !important;
      }

      html {
        -webkit-text-size-adjust: 100%;
        scroll-behavior: smooth;
      }

      .min-safe-h {
        min-height: 100dvh;
      }
    </style>
</head>
<body class="dark:bg-gray-800">

<!-- NAV -->
<nav class="bg-white border-b border-gray-300 dark:bg-slate-900">
  <div class="max-w-7xl mx-auto px-4 py-3 flex items-center justify-between">

    <!-- Logo -->
    <a href="index.php" class="flex items-center space-x-3">
      <img src="./images/logo-dark.png" class="h-9 w-auto" alt="ToiMoi logo" id="logo" />
    </a>

    <!-- Mobile: login button + hamburger -->
    <div class="flex items-center space-x-2 md:hidden">
      <a href="index.php">
        <button type="button" class="cursor-pointer text-white bg-blue-950 hover:bg-blue-900 font-medium rounded-lg text-sm px-3 py-2 flex items-center space-x-1.5 dark:bg-slate-800 dark:hover:bg-slate-700">
          <span>Login</span>
          <svg class="w-4 h-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M19 12H5m14 0-4 4m4-4-4-4"/>
          </svg>
        </button>
      </a>
      <button id="menu-toggle" type="button" class="inline-flex items-center p-2 w-10 h-10 justify-center text-sm text-gray-500 rounded-lg hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-gray-200 dark:text-gray-400 dark:hover:bg-gray-700 dark:focus:ring-gray-600">
        <span class="sr-only">Open main menu</span>
        <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 17 14">
          <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M1 1h15M1 7h15M1 13h15"/>
        </svg>
      </button>
    </div>

    <!-- Desktop nav links + login -->
    <div class="hidden md:flex md:items-center md:space-x-8">
      <ul class="flex space-x-8 font-medium">
        <li><a href="index.php" class="py-2 text-blue-700 dark:text-blue-500">Home</a></li>
        <li><a href="index.php" class="py-2 text-gray-900 hover:text-blue-700 dark:text-white dark:hover:text-blue-500">Chat Room</a></li>
        <li><a href="index.php" class="py-2 text-gray-900 hover:text-blue-700 dark:text-white dark:hover:text-blue-500">Connect</a></li>
      </ul>
      <a href="index.php">
        <button type="button" class="cursor-pointer text-white bg-blue-950 hover:bg-blue-900 font-medium rounded-lg text-sm px-4 py-2 flex items-center space-x-2 dark:bg-slate-800 dark:hover:bg-slate-700">
          <span>Login</span>
          <svg class="w-5 h-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M19 12H5m14 0-4 4m4-4-4-4"/>
          </svg>
        </button>
      </a>
    </div>
  </div>

  <!-- Mobile dropdown -->
  <div id="mobile-menu" class="hidden md:hidden border-t border-gray-200 dark:border-gray-700">
    <ul class="flex flex-col font-medium px-4 py-3 space-y-2 bg-gray-50 dark:bg-gray-800">
      <li><a href="index.php" class="block py-2 px-3 text-blue-700 dark:text-blue-500 rounded-sm">Home</a></li>
      <li><a href="index.php" class="block py-2 px-3 text-gray-900 rounded-sm hover:bg-gray-100 dark:text-white dark:hover:bg-gray-700">Chat Room</a></li>
      <li><a href="index.php" class="block py-2 px-3 text-gray-900 rounded-sm hover:bg-gray-100 dark:text-white dark:hover:bg-gray-700">Connect</a></li>
    </ul>
  </div>
</nav>

<!-- MAIN CONTENT -->
<div class="min-safe-h flex items-center justify-center px-4 py-10 dark:bg-gray-800">

  <div class="w-full max-w-sm">

    <!-- Header -->
    <div class="flex justify-center items-center mb-8 space-x-3">
      <h2 class="text-3xl sm:text-4xl font-medium text-center dark:text-white">Sign Up</h2>
      <svg class="w-9 h-9 sm:w-11 sm:h-11 text-slate-900 dark:text-white" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 24 24">
        <path fill-rule="evenodd" d="M12 20a7.966 7.966 0 0 1-5.002-1.756l.002.001v-.683c0-1.794 1.492-3.25 3.333-3.25h3.334c1.84 0 3.333 1.456 3.333 3.25v.683A7.966 7.966 0 0 1 12 20ZM2 12C2 6.477 6.477 2 12 2s10 4.477 10 10c0 5.5-4.44 9.963-9.932 10h-.138C6.438 21.962 2 17.5 2 12Zm10-5c-1.84 0-3.333 1.455-3.333 3.25S10.159 13.5 12 13.5c1.84 0 3.333-1.455 3.333-3.25S13.841 7 12 7Z" clip-rule="evenodd"/>
      </svg>
    </div>

    <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post">

      <!-- Username -->
      <div class="mb-5">
        <label for="name" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Username</label>
        <div class="relative">
          <input type="text" id="name" name="name" required placeholder="Username"
            class="shadow-sm bg-gray-50 border border-gray-300 text-gray-900 rounded-lg focus:ring-slate-950 focus:border-slate-950 block w-full pl-10 p-3 dark:bg-gray-50 dark:border-gray-300 dark:placeholder-gray-400 dark:text-black" />
          <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-600" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 24 24">
            <path fill-rule="evenodd" d="M8 4a4 4 0 1 0 0 8 4 4 0 0 0 0-8Zm-2 9a4 4 0 0 0-4 4v1a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2v-1a4 4 0 0 0-4-4H6Zm7.25-2.095c.478-.86.75-1.85.75-2.905a5.973 5.973 0 0 0-.75-2.906 4 4 0 1 1 0 5.811ZM15.466 20c.34-.588.535-1.271.535-2v-1a5.978 5.978 0 0 0-1.528-4H18a4 4 0 0 1 4 4v1a2 2 0 0 1-2 2h-4.535Z" clip-rule="evenodd"/>
          </svg>
        </div>
      </div>

      <!-- Email -->
      <div class="mb-5">
        <label for="email" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Email</label>
        <div class="relative">
          <input type="email" id="email" name="email" required placeholder="person@email.com"
            value="<?php echo htmlspecialchars($email2); ?>"
            class="shadow-sm bg-gray-50 border border-gray-300 text-gray-900 rounded-lg focus:ring-slate-950 focus:border-slate-950 block w-full pl-10 p-3 dark:bg-gray-50 dark:border-gray-300 dark:placeholder-gray-400 dark:text-black" />
          <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-600" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 16">
            <path d="m10.036 8.278 9.258-7.79A1.979 1.979 0 0 0 18 0H2A1.987 1.987 0 0 0 .641.541l9.395 7.737Z"/>
            <path d="M11.241 9.817c-.36.275-.801.425-1.255.427-.428 0-.845-.138-1.187-.395L0 2.6V14a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V2.5l-8.759 7.317Z"/>
          </svg>
        </div>
      </div>

      <!-- CSRF token -->
      <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">

      <!-- Password -->
      <div class="mb-5">
        <label for="password" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Your password</label>
        <div class="relative">
          <input type="password" id="password" name="password" required placeholder="Enter your Password"
            class="shadow-sm bg-gray-50 border border-gray-300 text-gray-900 rounded-lg focus:ring-slate-950 focus:border-slate-950 block w-full pl-10 p-3 dark:bg-gray-50 dark:border-gray-300 dark:placeholder-gray-400 dark:text-black" />
          <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-600" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 24 24">
            <path fill-rule="evenodd" d="M8 10V7a4 4 0 1 1 8 0v3h1a2 2 0 0 1 2 2v7a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2v-7a2 2 0 0 1 2-2h1Zm2-3a2 2 0 1 1 4 0v3h-4V7Zm2 6a1 1 0 0 1 1 1v3a1 1 0 1 1-2 0v-3a1 1 0 0 1 1-1Z" clip-rule="evenodd"/>
          </svg>
        </div>
      </div>

      <!-- Repeat Password -->
      <div class="mb-5">
        <label for="password2" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Repeat password</label>
        <div class="relative">
          <input type="password" id="password2" required placeholder="Re-enter your Password"
            class="shadow-sm bg-gray-50 border border-gray-300 text-gray-900 rounded-lg focus:ring-slate-950 focus:border-slate-950 block w-full pl-10 p-3 dark:bg-gray-50 dark:border-gray-300 dark:placeholder-gray-400 dark:text-black" />
          <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-600" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 24 24">
            <path fill-rule="evenodd" d="M8 10V7a4 4 0 1 1 8 0v3h1a2 2 0 0 1 2 2v7a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2v-7a2 2 0 0 1 2-2h1Zm2-3a2 2 0 1 1 4 0v3h-4V7Zm2 6a1 1 0 0 1 1 1v3a1 1 0 1 1-2 0v-3a1 1 0 0 1 1-1Z" clip-rule="evenodd"/>
          </svg>
        </div>
      </div>

      <!-- PHP error block (preserved) -->
      <?php if (!empty($errorpass)): ?>
      <div class="text-center mb-4 text-sm text-red-800 rounded-lg dark:bg-gray-800 dark:text-red-400" role="alert">
        <span class="font-medium">Info Danger alert!</span> <?php echo $errorpass; ?>
      </div>
      <?php endif; ?>

      <!-- Submit -->
      <button type="submit" id="button" name="submit"
        class="w-full flex items-center justify-center text-white cursor-pointer bg-blue-950 hover:bg-blue-900 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-3 mt-6 transition-colors dark:bg-slate-950 dark:hover:bg-slate-900 dark:focus:ring-slate-900">
        <span>Register new account</span>
        <svg class="w-4 h-4 ml-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M19 12H5m14 0-4 4m4-4-4-4" />
        </svg>
      </button>

      <hr class="mt-6 mb-5 border-gray-200 dark:border-gray-600">

      <p class="text-center text-sm text-gray-700 dark:text-white">
        Already have an account?
        <a href="./index.php" class="text-blue-700 hover:underline decoration-blue-500 dark:text-blue-400">Log in</a>
      </p>

    </form>
  </div>
</div>

<script>
  // Mobile hamburger toggle
  const toggle = document.getElementById('menu-toggle');
  const menu = document.getElementById('mobile-menu');
  toggle?.addEventListener('click', () => {
    menu.classList.toggle('hidden');
  });

  // Password match validation
  const password = document.getElementById('password');
  const password2 = document.getElementById('password2');

  function validatePassword() {
    if (password.value !== password2.value) {
      password2.setCustomValidity("Passwords don't match");
    } else {
      password2.setCustomValidity('');
    }
  }

  password.oninput = validatePassword;
  password2.oninput = validatePassword;
</script>

<script src="../node_modules/flowbite/dist/flowbite.min.js"></script>
<script src="main.js"></script>

</body>
</html>





