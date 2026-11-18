<?php include 'header.php'; ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Signup - SBA Awards</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: black;
            color: gold;
            margin: 0;
            padding: 0;
        }
      
        .container {
            max-width: 400px;
            margin: 20px auto;
            padding: 20px;
            background-color: #111;
            border-radius: 10px;
            color: gold;
            margin-bottom: 60px; /* Add margin bottom equal to footer height */
        }

        form label {
            display: block;
            margin-bottom: 5px;
        }
        form input[type="text"],
        form input[type="email"],
        form input[type="password"],
        form input[type="tel"] {
            width: 100%;
            padding: 8px;
            margin-bottom: 10px;
            border: 1px solid gold;
            border-radius: 5px;
            background-color: black;
            color: gold;
        }
        form button[type="submit"] {
            padding: 10px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            background-color: gold;
            color: black;
        }
        form button[type="submit"]:hover {
            background-color: #f7d32e;
        }
        footer, header {
            background-color: gold;
            color: black;
            padding: 10px;
            text-align: center;
            position: relative; /* Change absolute to relative */
            bottom: 0;
            width: 100%;
        }

        .password-container {
            position: relative;
        }
        .toggle-password {
            position: absolute;
            right: 10px;
            top: 10px;
            cursor: pointer;
        }
        .login-link {
            text-align: center;
            margin-top: 20px;
        }
        .login-link a {
            color: gold;
            text-decoration: underline;
        }
        .login-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

    <div class="container">
        <h2>Signup</h2>
        <form action="signup_process.php" method="POST">
            <!-- Username -->
            <label for="username"><i class="fas fa-user"></i> Username:</label>
            <input type="text" id="username" name="username" placeholder="Enter your username" required>
            <!-- First Name -->
            <label for="firstname"><i class="fas fa-user"></i> First Name:</label>
            <input type="text" id="firstname" name="firstname" placeholder="Enter your first name" required>
            <!-- Last Name -->
            <label for="lastname"><i class="fas fa-user"></i> Last Name:</label>
            <input type="text" id="lastname" name="lastname" placeholder="Enter your last name" required>
            <!-- Email -->
            <label for="email"><i class="fas fa-envelope"></i> Email:</label>
            <input type="email" id="email" name="email" placeholder="Enter your email" required>
            <!-- Phone -->
            <label for="phone"><i class="fas fa-phone"></i> Phone:</label>
            <input type="tel" id="phone" name="phone" placeholder="Enter your phone number" pattern="[0-9]{1,}" title="Please enter digits only" required>
            <!-- Password -->
            <label for="password"><i class="fas fa-lock"></i> Password:</label>
            <div class="password-container">
                <input type="password" id="password" name="password" placeholder="Enter your password" required>
                <i class="fas fa-eye toggle-password" onclick="togglePassword(this)"></i>
            </div>
            <!-- Confirm Password -->
            <label for="confirm_password"><i class="fas fa-lock"></i> Confirm Password:</label>
            <div class="password-container">
                <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm your password" required>
                <i class="fas fa-eye toggle-password" onclick="togglePassword(this)"></i>
            </div>
            <button type="submit">Signup</button>
        </form>
        <div class="login-link">
            <p>Already have an account? <a href="login.php"><i class="fas fa-sign-in-alt"></i> Login</a></p>
        </div>
    </div>

<?php include 'footer.php'; ?>

    <script>
        function togglePassword(element) {
            var input = element.previousElementSibling;
            var type = input.getAttribute('type');
            input.setAttribute('type', type === 'password' ? 'text' : 'password');
            element.classList.toggle('fa-eye');
            element.classList.toggle('fa-eye-slash');
        }
    </script>
</body>
</html>
