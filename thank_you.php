<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thank You - SBA Awards</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: black;
            color: gold;
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100vh;
        }
        .container {
            text-align: center;
            padding: 20px;
            background-color: #111;
            border-radius: 10px;
            color: gold;
        }
        button {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            background-color: gold;
            color: black;
            margin-top: 20px;
        }
        button:hover {
            background-color: #f7d32e;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Thank You for Logging In!</h2>
    <p>You have successfully logged in to SBA Awards.</p>
    <button onclick="location.href='voting.php'">Vote Now</button>
</div>

</body>
</html>
