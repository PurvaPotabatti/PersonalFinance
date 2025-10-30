<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Sign Up - Personal Finance Manager</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
  <style>
    body {
      background: linear-gradient(to right, #e0f7fa, #fce4ec);
      font-family: 'Segoe UI', sans-serif;
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
    }
    .form-section {
      background: white;
      border-radius: 20px;
      padding: 40px;
      box-shadow: 0 10px 25px rgba(0,0,0,0.1);
      width: 100%;
      max-width: 480px;
    }
    .form-section h2 {
      font-weight: 800;
      text-align: center;
      color: #2e2e2e;
      margin-bottom: 25px;
    }
    .form-label {
      font-weight: 600;
      color: #333;
    }
    .form-control {
      border-radius: 10px;
      padding: 10px 12px;
    }
    .btn-success {
      border-radius: 10px;
      font-weight: 600;
      font-size: 16px;
      padding: 10px 0;
    }
    .btn-success:hover {
      background-color: #43a047;
    }
    .text-center a {
      color: #007bff;
      text-decoration: none;
    }
    .text-center a:hover {
      text-decoration: underline;
    }
    .alert {
      border-radius: 10px;
    }
  </style>
</head>
<body>

<div class="form-section">
  <h2><i class="fas fa-user-plus me-2"></i>Sign Up</h2>

  <form method="POST">
    <div class="mb-3">
      <label class="form-label">Name</label>
      <input type="text" name="name" class="form-control" placeholder="Enter your full name" required>
    </div>
    <div class="mb-3">
      <label class="form-label">Email</label>
      <input type="email" name="email" class="form-control" placeholder="Enter your email" required>
    </div>
    <div class="mb-3">
      <label class="form-label">Password</label>
      <input type="password" name="password" class="form-control" placeholder="Create a password" required>
    </div>
    <div class="mb-3">
      <label class="form-label">Phone Number</label>
      <input type="text" name="phone_number" class="form-control" placeholder="Optional">
    </div>
    <button class="btn btn-success w-100" type="submit" name="register">
      <i class="fas fa-check-circle me-2"></i>Register
    </button>
  </form>

  <!-- ✅ PHP Sign-up logic with MongoDB Atlas -->
  <?php
  require __DIR__ . '/vendor/autoload.php';
  require_once __DIR__ . '/DB.php'; // Include the local connection core
  use MongoDB\BSON\UTCDateTime;
  
  if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['register'])) {
      try {
          $db = DB::getDatabase();
          $collection = $db->selectCollection('user');

          $name = trim($_POST['name']);
          $email = trim($_POST['email']);
          $password = password_hash(trim($_POST['password']), PASSWORD_DEFAULT);
          $phone = trim($_POST['phone_number']);
          // Removed $user_id = uniqid(); - We rely on MongoDB's _id

          $existingUser = $collection->findOne(['email' => $email]);

          if ($existingUser) {
              echo "<div class='alert alert-warning mt-3 text-center'>
                      ⚠️ Email already registered. Please <a href='login.php'>login</a>.
                    </div>";
          } else {
              $insertResult = $collection->insertOne([
                  // CRITICAL FIX: Removed redundant and confusing 'user_id' field. We use _id.
                  'name' => $name,
                  'email' => $email,
                  'password_hash' => $password,
                  'phone_number' => $phone,
                  'created_at' => new UTCDateTime()
              ]);

              if ($insertResult->getInsertedCount() > 0) {
                  echo "<div class='alert alert-success mt-3 text-center'>
                      ✅ Account created successfully. <a href='login.php'>Click here to login</a>.
                    </div>";
              } else {
                  echo "<div class='alert alert-danger mt-3 text-center'>
                      ❌ Something went wrong while creating the account.
                    </div>";
              }
          }
      } catch (Exception $e) {
          echo "<div class='alert alert-danger mt-3 text-center'>
                  ❌ Connection/Database Error: " . htmlspecialchars($e->getMessage()) . "
                </div>";
      }
  }
  ?>

  <div class="text-center mt-3">
    Already have an account? <a href="login.php">Login here</a>
  </div>
</div>

</body>
</html>
