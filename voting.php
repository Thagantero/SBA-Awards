<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Voting - SBA Awards</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: black;
            color: gold;
            margin: 0;
            padding: 0;
        }
        header, footer {
            background-color: gold;
            color: black;
            padding: 10px;
            text-align: center;
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
            max-width: 800px;
            margin: 20px auto;
            padding: 20px;
            background-color: #111;
            border-radius: 10px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        table, th, td {
            border: 1px solid gold;
            padding: 10px;
            text-align: left;
        }
        th {
            background-color: #222;
        }
        th, td img {
            width: 20px;
            height: 20px;
            margin-right: 5px;
        }
        tr:nth-child(even) {
            background-color: #222;
        }
        tr:hover {
            background-color: #333;
        }
        footer {
            background-color: gold;
            color: black;
            padding: 10px;
            text-align: center;
            width: 100%;
        }
    </style>
</head>
<body>
    <header>
        <h1>SBA Awards</h1>
    </header>
    
    <nav>
        <ul>
            <li><a href="voting_website.html">Home</a></li>
            <li><a href="about.html">About</a></li>
            <li><a href="contact.html">Contact</a></li>
            <li><a href="login.html">Login</a></li>
            <li><a href="signup.html">Signup</a></li>
            <li><a href="nominate.html">Nominate</a></li> <!-- Add the Nominate page link -->
        </ul>
    </nav>
    
    <div class="container">
        <h2>Voting</h2>
        <form action="#" method="GET">
            <input type="text" name="search" placeholder="Search nominees">
            <button type="submit">Search</button>
        </form>
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Votes</th>
                </tr>
            </thead>
            <tbody>
                <!-- Add PHP code here to fetch and display nominees -->
            </tbody>
        </table>
    </div>
    
    <footer>
        <p>&copy; 2024 SBA Awards</p>
    </footer>
</body>
</html>
