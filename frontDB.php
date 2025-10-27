<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Sign Up - Personal Finance Manager</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      font-family: 'Segoe UI', sans-serif;
      background-color: #f4f6f8;
    }
    .form-section {
      background: white;
      border-radius: 16px;
      padding: 30px;
      box-shadow: 0 4px 8px rgba(0,0,0,0.05);
    }
  </style>
</head>
<body>

<div class="container my-5">
  <h2 class="text-center mb-4">📝 Sign Up for Finance Manager</h2>

  <div class="row justify-content-center">
    <div class="col-md-6 form-section">
      <form method="POST">
        <div class="mb-3">
          <label>Name</label>
          <input type="text" name="name" class="form-control" required>
        </div>
        <div class="mb-3">
          <label>Email</label>
          <input type="email" name="email" class="form-control" required>
        </div>
        <div class="mb-3">
          <label>Password</label>
          <input type="password" name="password" class="form-control" required>
        </div>
        <div class="mb-3">
          <label>Phone Number</label>
          <input type="text" name="phone_number" class="form-control">
        </div>
        <button class="btn btn-success w-100" type="submit" name="register">Register</button>
      </form>

      <!-- ✅ PHP Sign-up logic with MongoDB Atlas -->
      <?php
      require __DIR__ . '/vendor/autoload.php';
      use MongoDB\Client;
      use MongoDB\Driver\ServerApi;

      // Load environment variables
      $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
      $dotenv->safeLoad();

      $uri = $_ENV['MONGODB_URI'] ?? null;

      if (!$uri) {
          echo "<div class='alert alert-danger mt-3'>❌ MONGODB_URI not found in .env file.</div>";
          exit;
      }

      // Connect to MongoDB Atlas
      $apiVersion = new ServerApi(ServerApi::V1);
      $client = new Client($uri, [], ['serverApi' => $apiVersion]);
      $db = $client->selectDatabase('finance_manager');
      $collection = $db->selectCollection('user');

      if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['register'])) {
          // Sanitize inputs
          $name = trim($_POST['name']);
          $email = trim($_POST['email']);
          $password = password_hash(trim($_POST['password']), PASSWORD_DEFAULT);
          $phone = trim($_POST['phone_number']);
          $user_id = uniqid();

          // Check if user already exists
          $existingUser = $collection->findOne(['email' => $email]);

          if ($existingUser) {
              echo "<div class='alert alert-warning mt-3'>
                      Email already registered. Please <a href='login.php'>login</a>.
                    </div>";
          } else {
              $insertResult = $collection->insertOne([
                  'user_id' => $user_id,
                  'name' => $name,
                  'email' => $email,
                  'password_hash' => $password,
                  'phone_number' => $phone,
                  'created_at' => new MongoDB\BSON\UTCDateTime()
              ]);

              if ($insertResult->getInsertedCount() > 0) {
                  echo "<div class='alert alert-success mt-3'>
                          ✅ Account created successfully. <a href='login.php'>Click here to login</a>.
                        </div>";
              } else {
                  echo "<div class='alert alert-danger mt-3'>
                          ❌ Something went wrong while creating the account.
                        </div>";
              }
          }
      }
      ?>

      <div class="text-center mt-3">
        Already have an account? <a href="login.php">Login here</a>
      </div>
    </div>
  </div>
</div>

</body>
</html>
