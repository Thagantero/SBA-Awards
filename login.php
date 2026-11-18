<?php
// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Start or resume session
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Include the header file at the beginning of the script
include 'header.php';

// Check if the user is already logged in
$logged_in = isset($_SESSION['user_id']);

// Check for error message from login_process.php
$error_message = isset($_GET['error']) ? $_GET['error'] : '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://kit.fontawesome.com/87ab693041.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="styles.css">
    <title>Login - SBA Awards</title>
    <style>
       body {
    font-family: Arial, sans-serif;
    background-color: black;
    color: gold;
    margin: 0;
    padding: 0;
    display: flex;
    flex-direction: column;
    min-height: 100vh;
}

header, footer {
    background-color: gold;
    color: black;
    padding: 10px;
    text-align: center;
}

nav {
    background-color: gold;
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px;
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    z-index: 1000;
}

nav .logo a {
    text-decoration: none;
    color: black;
    font-size: 1.5em;
}

nav ul {
    list-style: none;
    padding: 0;
    margin: 0;
    display: flex;
    align-items: center;
}

nav ul li {
    margin-right: 10px;
}

nav ul li a {
    text-decoration: none;
    color: black;
    padding: 5px 10px;
    border-radius: 5px;
}

nav ul li a:hover {
    background-color: black;
    color: gold;
}

nav .auth-links {
    display: flex;
}

nav .auth-links li {
    margin-right: 0;
    margin-left: 10px;
}

.container {
    width: flex;
    margin: 70px auto 20px;
    padding: 20px;
    background-color: #111;
    border-radius: 10px;
    flex: 1;
}

.login-signup-form {
    background-color: #222;
    padding: 20px;
    border-radius: 10px;
    text-align: center; /* Center align form elements */
}

.login-signup-form .input-container {
    display: flex;
    align-items: center;
    background-color: #333;
    padding: 10px;
    border-radius: 5px;
    margin-bottom: 10px;
}

.login-signup-form .input-container input {
    border: none;
    outline: none;
    background: none;
    color: gold;
    width: 100%;
    padding-left: 10px;
    height: 10px;
}

.login-signup-form .input-container .fas {
    color: gold;
}

.login-signup-form button {
    padding: 10px;
    margin-bottom: 20px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    width: flex; /* Make button full width */
    display: block; /* Ensure button takes full width */
    margin: 0 auto; /* Center horizontally */
}

.login-signup-form .logout-button {
    background-color: red;
    color: white;
}

.login-signup-form .logout-button:hover {
    background-color: darkred;
}

.login-signup-form .home-button {
    background-color: gold;
    color: black;
}

.login-signup-form .home-button:hover {
    background-color: #f7d32e;
}

.error-message {
    color: red;
    text-align: center;
    margin-top: 10px;
}

.horizontal-buttons {
    display: flex;
    justify-content: space-between;
}

.vertical-links {
    display: flex;
    flex-direction: column;
    align-items: center;
    margin-top: 10px;
}

.vertical-links p {
    color: white; /* Set paragraph color to white */
}

.vertical-links a {
    color: gold; /* Set link color to gold */
    text-decoration: none; /* Remove underline from links */
}

.vertical-links a:hover {
    text-decoration: underline; /* Add underline on hover */
}

footer {
    background-color: gold;
    color: black;
    padding: 10px;
    text-align: center;
    width: 100%;
    margin-top: auto;
    position: relative;
    bottom: 0;
}

@media (max-width: 768px) {
    nav ul {
        flex-direction: column;
        display: none;
    }
    nav ul.show {
        display: flex;
    }
    nav .burger {
        display: block;
        cursor: pointer;
    }
    nav .burger div {
        width: 25px;
        height: flex;
        background-color: black;
        margin: 5px;
        transition: all 0.3s ease;
    }
    .horizontal-buttons {
        flex-direction: column;
    }
    .horizontal-buttons button {
        margin-bottom: 10px;
        width: 100%;
    }
    .account-actions {
        display: flex;
        justify-content: center;
        gap: 20px; /* Adjust the gap between items as needed */
    }

   .account-actions p {
   color: white;
   margin: 0; /* Removes default paragraph margin */
   }

   .account-actions a {
   color: gold;
   }

}

    </style>
</head>
<body>

<div class="container">
    <?php if ($logged_in): ?>
        <p>You are already logged in.</p>
        <div class="horizontal-buttons">
            <form action="logout.php" method="post" style="display: inline;">
                <button type="submit" class="logout-button">Logout</button>
            </form>
            <a href="index.php" style="display: inline;">
                <button class="home-button">Back Home</button>
            </a>
        </div>
    <?php else: ?>
        <div class="login-signup-form">
            <h2>Login</h2>
            
            <!-- Error message placeholder -->
            <?php if (!empty($error_message)): ?>
                <p class="error-message"><?php echo htmlspecialchars($error_message); ?></p>
            <?php endif; ?>
            
            <form action="login_process.php" method="post">
                <div class="input-container">
                    <i class="fas fa-envelope"></i>
                    <input type="email" name="email" placeholder="Email" required>
                </div>
                <div class="input-container">
                    <i class="fas fa-lock"></i>
                    <input type="password" name="password" placeholder="Password" id="password" required>
                    <i class="fas fa-eye" id="togglePassword" style="cursor: pointer;"></i>
                </div>
                <button type="submit">Login</button>
            </form>
 <div class="account-actions">
    <p>Don't have an account? <a href="signup.php">Register</a></p>
    <p>Lost Password? <a href="reset_password.php">Reset</a></p>
</div>

        </div>
    <?php endif; ?>
</div>

<script>
    function toggleMenu() {
        const navLinks = document.querySelector('.nav-links');
        navLinks.classList.toggle('show');
    }

    const togglePassword = document.querySelector('#togglePassword');
    const password = document.querySelector('#password');

    togglePassword.addEventListener('click', function (e) {
        // toggle the type attribute
        const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
        password.setAttribute('type', type);
        // toggle the eye icon
        this.classList.toggle('fa-eye-slash');
    });
</script>
<?php include 'footer.php'; ?>
</body>
</html>
