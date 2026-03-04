<?php
session_start();


require_once __DIR__ . '/dbconnect.php';

// Make sure user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: connect.php");
    exit();
}


// Logged in user's ID
$sender_id = $_SESSION['user_id'];

// Find the receiver dynamically from `connections`
$query = "SELECT user1_id, user2_id 
          FROM connections 
          WHERE user1_id = '$sender_id' OR user2_id = '$sender_id'
          LIMIT 1";
$result = mysqli_query($conn, $query);

if ($row = mysqli_fetch_assoc($result)) {
    $receiver_id = ($row['user1_id'] == $sender_id) ? $row['user2_id'] : $row['user1_id'];
} else {
    // If no connection, stop execution
    header("Location: index.php");
    exit;
}

mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Chat Room</title>
<script src="https://cdn.tailwindcss.com"></script>

  <link rel="icon" href="./images/Favicon.png">
  <style>
    #chat-messages::-webkit-scrollbar { display: none; }
  </style>
</head>

<body class = "dark:bg-gray-800"  >
 <nav class="bg-white border-b border-gray-300 dark:bg-slate-900">
  <div class="max-w-7xl flex flex-wrap items-center justify-between mx-auto p-4  border-b-slate-900">
  <a href="index.php" class="flex items-center space-x-3 rtl:space-x-reverse">
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
        <a href="#" class=" block py-2 px-3 md:p-0 text-white bg-blue-700 rounded-sm md:bg-transparent md:text-blue-700 md:dark:text-blue-500" aria-current="page">Chat Room</a>
      </li>
      <li>
       
        <a href="connect.php" class="block py-2 px-3 md:p-0 text-gray-900 rounded-sm hover:bg-gray-100 md:hover:bg-transparent md:hover:text-blue-700 md:dark:hover:text-blue-500 dark:text-white dark:hover:bg-gray-700 dark:hover:text-white md:dark:hover:bg-transparent dark:border-gray-700">Connect</a>
      </li>
      
  </div>
  </div>
</nav>


  <!-- Chat Section -->
  <div class="flex flex-col h-[calc(100vh-6rem)] md:w-150 mx-auto w-120 rounded">
    <!-- Messages -->

    <div id="chat-messages" class="flex-1 space-y-3.5 overflow-y-auto p-4 mb-3.5">
     
    </div>

    <!-- Input -->
    <form id="chat-form" class="flex items-center p-3 bg-gray-50 dark:bg-gray-700">
      <textarea id="chat" rows="1" class="block mx-4 p-2.5 flex-1 text-sm text-gray-900 bg-white rounded-lg border border-gray-300 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-800 dark:border-gray-600 dark:text-white" placeholder="Your message..."></textarea>

      <button type="submit" class="inline-flex justify-center p-2 text-blue-600 rounded-full cursor-pointer hover:bg-blue-100 dark:text-blue-500 dark:hover:bg-gray-600">
        <svg class="w-5 h-5 rotate-90" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 18 20">
          <path d="m17.914 18.594-8-18a1 1 0 0 0-1.828 0l-8 18a1 1 0 0 0 1.157 1.376L8 18.281V9a1 1 0 0 1 2 0v9.281l6.758 1.689a1 1 0 0 0 1.156-1.376Z"/>
        </svg>
      </button>
    </form>
  </div>

  <script>
  const form = document.getElementById('chat-form');
  const textarea = document.getElementById('chat');
  const chatBox = document.getElementById('chat-messages');

  const senderId = <?php echo $sender_id; ?>;
  const receiverId = <?php echo $receiver_id; ?>;

  // Fetch messages from server
  function loadMessages() {
    fetch(`fetch-messages.php?sender_id=${senderId}&receiver_id=${receiverId}`)
      .then(res => res.json())
      .then(data => {
        chatBox.innerHTML = '';
        data.forEach(msg => {
          const msgDiv = document.createElement('div');
          msgDiv.className = (msg.sender_id == senderId)
            ? 'bg-blue-500 text-white px-4 py-2 rounded-lg   max-w-[75%] ml-auto shadow-md break-words text-left font-semibold'
            : 'bg-gray-300 text-gray-900 px-4 py-2 rounded-lg self-start max-w-[75%] shadow-md break-words text-left font-semibold';
          msgDiv.textContent = msg.message;
          chatBox.appendChild(msgDiv);
        });
        chatBox.scrollTop = chatBox.scrollHeight;
      })
      .catch(err => console.error(err));
  }

  // Send message
  form.addEventListener('submit', (e) => {
    e.preventDefault();
    const text = textarea.value.trim();
    if (text === '') return;

    fetch('send-message.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: `sender_id=${senderId}&text=${encodeURIComponent(text)}`
    })
    .then(() => {
      textarea.value = '';
      loadMessages();
    })
    .catch(err => console.error(err));
  });

  // Press Enter to send (without Shift)
  textarea.addEventListener('keydown', (event) => {
    if (event.key === 'Enter' && !event.shiftKey) {
      event.preventDefault();
      form.requestSubmit();
    }
  });

  // Auto-refresh chat every 5000s
//  setInterval(loadMessages, 5000);
  loadMessages();
  </script>
</body>
</html>
