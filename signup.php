<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Signup - SBA Awards</title>
    <style>
    body {
        font-family: Arial, sans-serif;
        background-color: black;
        color: gold;
        margin: 0;
        padding: 0;
    }

    header {
        background-color: gold;
        color: black;
        padding: 10px;
        text-align: center;
    }

    header img {
        max-width: 200px;
        height: auto;
        margin: 10px auto;
        display: block;
    }

    nav {
        background-color: #222;
        padding: 10px;
    }

    nav ul {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    nav ul li {
        display: inline;
        margin-right: 10px;
    }

    nav ul li a {
        text-decoration: none;
        color: gold;
        padding: 5px 10px;
        border-radius: 5px;
    }

    nav ul li a:hover {
        background-color: gold;
        color: black;
    }

    .container {
        max-width: 400px;
        margin: 20px auto;
        padding: 20px;
        background-color: #111;
        border-radius: 10px;
        color: gold;
    }

    form label {
        display: block;
        margin-bottom: 5px;
    }

    form input[type="text"],
    form input[type="email"],
    form input[type="password"] {
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

    footer {
        background-color: gold;
        color: black;
        padding: 10px;
        text-align: center;
        position: absolute;
        bottom: 0;
        width: 100%;
    }
    </style>
</head>

<body>

    <header>
        <img src="admin/images/your_logo.png" alt="SBA Awards Logo">
    </header>

    <nav>
        <ul>
            <li><a href="voting_website.html">Home</a></li>
            <li><a href="about.html">About</a></li>
            <li><a href="contact.html">Contact</a></li>
            <li><a href="login.html">Login</a></li>
            <li><a href="signup.php">Signup</a></li>
            <li><a href="nominate.html">Nominate</a></li> <!-- Add the Nominate page link -->
        </ul>
    </nav>

    <div class="container">
        <h2>Signup</h2>
        <form action="signup_process.php" method="POST">
            <label for="FirstName">First Name:</label>
            <input type="text" id="FirstName" name="FirstName" required>
            <label for="SecondName">Second Name:</label>
            <input type="text" id="SecondName" name="SecondName" required>
            <label for="username">Username:</label>
            <input type="text" id="Username" name="Username" required>
            <label for="email">Email:</label>
            <input type="email" id="Email" name="Email" required>
            <label for="password">Password:</label>
            <input type="password" id="Password" name="Password" required>
            <button type="submit">Signup</button>
        </form>
    </div>

    <footer>
        <p>&copy; 2024 SBA Awards</p>
    </footer>

</body>

</html>