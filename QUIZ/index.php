<?php
// ---- Time Restriction ----
date_default_timezone_set('Asia/Kolkata');
$current_time = date('H:i');
$start_time = '10:59'; // 8:00 PM
$end_time   = '10:59'; // 8:30 PM
$accessible = !($current_time < $start_time || $current_time > $end_time);
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Quiz Portal — Login</title>
  <link rel="stylesheet" href="style.css">
</head>
<body oncontextmenu="return false;">
  <main class="centered">
    <div class="card login-card">
      <h1 class="title">Welcome to the Quiz</h1>
      <p class="subtitle">Enter your email address to begin.</p>

      <form method="post" action="login.php" id="loginForm" autocomplete="off">
        <label class="label">
          Email address
          <input 
            type="email" 
            name="user_email" 
            id="user_email" 
            required 
            maxlength="100" 
            placeholder="you@example.com" 
            autocomplete="off" 
          />
        </label>

        <div class="actions">
          <button type="submit" class="btn primary">Start Quiz</button>
        </div>
      </form>

      <p class="note">Make sure you do not switch tabs or minimize the window — session will end.</p>
    </div>
  </main>

  <script>
    // Prevent text selection / copy
    document.addEventListener('selectstart', e => e.preventDefault());
    document.addEventListener('copy', e => e.preventDefault());
    document.addEventListener('cut', e => e.preventDefault());
    document.addEventListener('contextmenu', e => e.preventDefault());

    // Email validation
    document.getElementById('loginForm').addEventListener('submit', function (e) {
      const email = document.getElementById('user_email').value.trim();
      const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
      if (!emailRegex.test(email)) {
        e.preventDefault();
        alert('Please enter a valid email address.');
      }
    });

    // Time-based access restriction (server-controlled)
    const accessible = <?php echo $accessible ? 'true' : 'false'; ?>;
    if (!accessible) {
  alert(
    "⏰ The quiz is accessible only between 8:00 PM and 8:30 PM (IST).\n\n" +
    "📋 Important Guidelines:\n\n" +
    "1️⃣ The assessment must be started by entering your registered email.\n" +
    "2️⃣ Do not switch windows or tabs while attempting the assessment — doing so will automatically end your attempt.\n" +
    "3️⃣ Ensure a stable internet connection during the assessment duration.\n" +
    "4️⃣ Attempt the assessment independently; collaboration is not allowed.\n\n" +
    "Please return during the allowed time window."
  );
  document.querySelector('.login-card').style.opacity = '0.5';
  document.querySelectorAll('input, button').forEach(el => el.disabled = true);
}
  </script>
</body>
</html>