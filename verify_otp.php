<?php
// Database connection code
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "sba_awards";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['email'], $_POST['otp'])) {
    // Get form data
    $email = $conn->real_escape_string($_POST['email']);
    $otp = $conn->real_escape_string($_POST['otp']);

    // Check if the OTP is correct
    $sql = "SELECT * FROM users WHERE email='$email' AND otp='$otp'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        // OTP is correct, update the user status to 'activated' and clear the OTP
        $updateSql = "UPDATE users SET status='activated', otp=NULL WHERE email='$email'";
        if ($conn->query($updateSql) === TRUE) {
            echo "<div class='otp-container'>";
            echo "<h2>OTP Verified</h2>";
            echo "<p class='otp-sent'>Your OTP has been verified successfully. Your account has been activated.</p>";
            echo "<form action='login.html' method='POST'>";
            echo "<input type='hidden' name='email' value='$email'>";
            echo "<button type='submit' class='btn btn-primary'>Log In</button>";
            echo "</form>";
            echo "</div>";
        } else {
            echo "<div class='otp-container'>";
            echo "<h2>Error</h2>";
            echo "<p class='otp-sent'>There was an error updating your account status. Please try again later.</p>";
            echo "</div>";
        }
    } else {
        // OTP is incorrect
        echo "<div class='otp-container'>";
        echo "<h2>Invalid OTP</h2>";
        echo "<p class='otp-sent'>The OTP you entered is incorrect. Please try again.</p>";
        echo "<form action='verify_otp.php' method='POST'>";
        echo "<input type='hidden' name='email' value='$email'>";
        echo "<div class='form-group'>";
        echo "<label for='otp'>Enter OTP:</label>";
        echo "<input type='text' class='form-control' id='otp' name='otp' required>";
        echo "</div>";
        echo "<button type='submit' class='btn btn-primary'>Verify OTP</button>";
        echo "</form>";
        echo "<button id='resend-otp' class='btn btn-secondary' onclick='resendOTP()'>Resend OTP</button>";
        echo "<p id='resend-confirmation' style='display: none; color: gold;'>OTP has been resent.</p>";
        echo "</div>";
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SBA Awards - Verify OTP</title>
    <style>
        body {
            background-color: white;
            color: black;
            font-family: Arial, sans-serif;
            text-align: center;
            padding: 20px;
        }
        .otp-container {
            background-color: black;
            color: gold;
            border: 2px solid gold;
            border-radius: 10px;
            padding: 20px;
            display: inline-block;
            margin-top: 20px;
            width: 300px;
            text-align: left;
        }
        h2 {
            color: gold;
            text-align: center;
        }
        .otp-sent {
            background-color: #333;
            color: gold;
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 5px;
            text-align: center;
        }
        .form-group {
            margin: 15px 0;
            text-align: left;
        }
        .form-control {
            padding: 10px;
            width: calc(100% - 22px); /* Adjusted to account for padding and border */
            border: 1px solid gold;
            border-radius: 5px;
            margin-top: 5px;
        }
        .btn-primary {
            background-color: gold;
            color: black;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin-top: 10px;
            width: 100%;
        }
        .btn-primary:hover {
            background-color: darkgoldenrod;
        }
        .btn-secondary {
            background-color: #ccc;
            color: black;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin-top: 10px;
            width: 100%;
        }
        .btn-secondary:hover {
            background-color: #aaa;
        }
    </style>
    <script>
        function resendOTP() {
            const email = "<?php echo $_POST['email']; ?>";

            fetch('resend_otp.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ email })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('resend-confirmation').style.display = 'block';
                    setTimeout(() => {

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SBA Awards - Verify OTP</title>
    <style>
        body {
            background-color: white;
            color: black;
            font-family: Arial, sans-serif;
            text-align: center;
            padding: 20px;
        }
        .otp-container {
            background-color: black;
            color: gold;
            border: 2px solid gold;
            border-radius: 10px;
            padding: 20px;
            display: inline-block;
            margin-top: 20px;
            width: 300px;
            text-align: left;
        }
        h2 {
            color: gold;
            text-align: center;
        }
        .otp-sent {
            background-color: #333;
            color: gold;
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 5px;
            text-align: center;
        }
        .form-group {
            margin: 15px 0;
            text-align: left;
        }
        .form-control {
            padding: 10px;
            width: calc(100% - 22px); /* Adjusted to account for padding and border */
            border: 1px solid gold;
            border-radius: 5px;
            margin-top: 5px;
        }
        .btn-primary {
            background-color: gold;
            color: black;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin-top: 10px;
            width: 100%;
        }
        .btn-primary:hover {
            background-color: darkgoldenrod;
        }
        .btn-secondary {
            background-color: #ccc;
            color: black;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin-top: 10px;
            width: 100%;
        }
        .btn-secondary:hover {
            background-color: #aaa;
        }
    </style>
    <script>
        function resendOTP() {
            const email = "<?php echo $_POST['email']; ?>";

            fetch('resend_otp.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ email })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('resend-confirmation').style.display = 'block';
                    setTimeout(() => {
                        document.getElementById('resend-confirmation').style.display = 'none';
                    }, 5000);
                } else {
                    alert(data.message);
                }
            })
            .catch(error => console.error('Error:', error));
        }
    </script>
</head>
<body>

</body>
</html>