<?php
session_start();
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/src/Database/MongoDBClient.php';

use App\Database\MongoDBClient;

$loginError = '';

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['login'])) {
    try {
        $collection = MongoDBClient::getCollection('user');

        $email = trim($_POST['email']);
        $password = trim($_POST['password']);

        $user = $collection->findOne(['email' => $email]);

        if ($user) {
            // ✅ Use password_verify for hashed passwords
            if (password_verify($password, $user['password_hash'])) {
                $_SESSION['user_id'] = (string)$user['_id'];
                $_SESSION['user_name'] = $user['name'];
                header("Location: dashboard.php");
                exit();
            } else {
                $loginError = "Incorrect password.";
            }
        } else {
            $loginError = "No user found with that email.";
        }
    } catch (Exception $e) {
        $loginError = "Connection failed: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>User Login - Personal Finance Manager</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <style>
    body {
      font-family: 'Segoe UI', sans-serif;
      margin: 0;
      padding: 0;
      background: linear-gradient(to right, #74ebd5, #acb6e5);
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      background-attachment: fixed;
    }
    .form-section {
      background: rgba(255, 255, 255, 0.9);
      backdrop-filter: blur(10px);
      border-radius: 16px;
      padding: 30px 40px;
      box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
      width: 100%;
      max-width: 500px;
      animation: fadeIn 1s ease-in-out;
    }
    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(20px); }
      to { opacity: 1; transform: translateY(0); }
    }
    h2 { font-weight: 700; color: #333; }
    label { font-weight: 500; }
    .btn-success { background: #28a745; border: none; }
    .btn-success:hover { background: #218838; }
    .input-group-text {
      background: transparent;
      border-left: 0;
      cursor: pointer;
    }
  </style>
</head>
<body>

<div class="form-section">
  <h2 class="text-center mb-4">🔐 User Login</h2>

  <form method="POST">
    <div class="mb-3">
      <label>Email</label>
      <input type="email" name="email" class="form-control" required>
    </div>
    <div class="mb-3">
      <label>Password</label>
      <div class="input-group">
        <input type="password" name="password" class="form-control" id="password" required>
        <span class="input-group-text" onclick="togglePassword()">
          <i class="bi bi-eye" id="toggleIcon"></i>
        </span>
      </div>
    </div>
    <button class="btn btn-success w-100" type="submit" name="login">Login</button>
    <p class="text-center mt-3">Don't have an account? <a href="frontDB.php">Register here</a></p>
  </form>

  <?php if (!empty($loginError)): ?>
      <div class="alert alert-danger mt-3"><?= htmlspecialchars($loginError) ?></div>
  <?php endif; ?>
</div>

<script>
  function togglePassword() {
    const passwordInput = document.getElementById('password');
    const icon = document.getElementById('toggleIcon');
    if (passwordInput.type === "password") {
      passwordInput.type = "text";
      icon.classList.remove("bi-eye");
      icon.classList.add("bi-eye-slash");
    } else {
      passwordInput.type = "password";
      icon.classList.remove("bi-eye-slash");
      icon.classList.add("bi-eye");
    }
  }
</script>

</body>
</html>
