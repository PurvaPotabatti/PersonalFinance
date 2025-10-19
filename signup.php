<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Sign Up - Personal Finance Manager</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet"> <!-- Bootstrap Icons -->
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
      background: rgba(255, 255, 255, 0.8);
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

    h2 {
      font-weight: 700;
      color: #333;
    }

    label {
      font-weight: 500;
    }

    .btn-success {
      background: #28a745;
      border: none;
    }

    .btn-success:hover {
      background: #218838;
    }

    .input-group-text {
      background: transparent;
      border-left: 0;
      cursor: pointer;
    }
  </style>
</head>
<body>

  <div class="form-section">
    <h2 class="text-center mb-4">📝 Sign Up for Finance Manager</h2>

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
        <div class="input-group">
          <input type="password" name="password" class="form-control" id="password" required>
          <span class="input-group-text" onclick="togglePassword()">
            <i class="bi bi-eye" id="toggleIcon"></i>
          </span>
        </div>
      </div>
      <div class="mb-3">
        <label>Phone Number</label>
        <input type="text" name="phone_number" class="form-control">
      </div>
      <button class="btn btn-success w-100" type="submit" name="register">Register</button>
    </form>

    <?php
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['register'])) {
        $conn = mysqli_connect("localhost", "root", "", "finance_manager", 3307);
        if (!$conn) {
            die("<div class='alert alert-danger mt-3'>Connection failed: " . mysqli_connect_error() . "</div>");
        }

        $name = mysqli_real_escape_string($conn, $_POST['name']);
        $email = $_POST['email'];
        $password = $_POST['password'];
        $phone = mysqli_real_escape_string($conn, $_POST['phone_number']);

        $check = "SELECT * FROM Users WHERE email = '$email'";
        $result = mysqli_query($conn, $check);

        if (mysqli_num_rows($result) > 0) {
            echo "<div class='alert alert-warning mt-3'>Email already registered. Please <a href='login.php'>login</a>.</div>";
        } else {
            $sql = "INSERT INTO Users (name, email, password_hash, phone_number)
                    VALUES ('$name', '$email', '$password', '$phone')";

            if (mysqli_query($conn, $sql)) {
                echo "<div class='alert alert-success mt-3'>Account created successfully. <a href='login.php'>Click here to login</a>.</div>";
            } else {
                echo "<div class='alert alert-danger mt-3'>Error: " . mysqli_error($conn) . "</div>";
            }
        }

        mysqli_close($conn);
    }
    ?>

    <div class="text-center mt-3">
      Already have an account? <a href="login.php">Login here</a>
    </div>
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
