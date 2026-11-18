<?php
session_start();

// Include dbConnect.php
include 'dbConnect.php';

$email = '';
$otp = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['email'], $_POST['otp'])) {
        $email = $mysqli->real_escape_string($_POST['email']);
        $otp = $mysqli->real_escape_string($_POST['otp']);

        // Check if the OTP is correct using prepared statement
        $sql = "SELECT * FROM users WHERE email=? AND otp=?";
        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param("ss", $email, $otp);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            // Update account status
            $updateSql = "UPDATE users SET status='activated', otp=NULL WHERE email=?";
            $stmt = $mysqli->prepare($updateSql);
            $stmt->bind_param("s", $email);
            if ($stmt->execute()) {
                echo "<div class='otp-container'>";
                echo "<h2>OTP Verified</h2>";
                echo "<p class='otp-sent'>Your account has been activated.</p>";
                echo "<form action='login.php' method='POST'>";
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
            // Invalid OTP
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

            // Add the "Resend OTP" button with countdown
            echo "<button id='resendOtpBtn' class='btn btn-secondary' onclick='resendOTP()'>";
            echo "Resend OTP <span id='countdown' style='display: none;'></span>";
            echo "</button>";
            echo "<div id='resend-confirmation' style='display: none; color: green; margin-top: 10px;'>";
            echo "OTP has been successfully resent to <span id='resentEmail'></span>.";
            echo "</div>";
            echo "</div>";
        }
    } else {
        // Error: Missing parameters
        echo "<div class='otp-container'>";
        echo "<h2>Error</h2>";
        echo "<p class='otp-sent'>Email or OTP parameter is missing. Please try again.</p>";
        echo "</div>";
    }
} else {
    // Display OTP verification form
    if (isset($_GET['email'])) {
        $email = htmlspecialchars($_GET['email']);
        echo "<div class='otp-container'>";
        echo "<h2>Verify OTP</h2>";
        echo "<form action='verify_otp.php' method='POST'>";
        echo "<input type='hidden' name='email' value='$email'>";
        echo "<div class='form-group'>";
        echo "<label for='otp'>Enter OTP:</label>";
        echo "<input type='text' class='form-control' id='otp' name='otp' required>";
        echo "</div>";
        echo "<button type='submit' class='btn btn-primary'>Verify OTP</button>";

        // Add the "Resend OTP" button with countdown
        echo "<button id='resendOtpBtn' class='btn btn-secondary' onclick='resendOTP()'>";
        echo "Resend OTP <span id='countdown' style='display: none;'></span>";
        echo "</button>";

        echo "</form>";
        echo "</div>";
    } else {
        // Error: Missing email parameter
        echo "<div class='otp-container'>";
        echo "<h2>Error</h2>";
        echo "<p class='otp-sent'>Email parameter is missing. Please try again.</p>";
        echo "</div>";
    }
}

$mysqli->close();
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
            background-color: grey;
            color: black;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin-top: 10px;
            width: 100%;
        }

        .btn-secondary:hover {
            background-color: green;
        }
    </style>
</head>
<body>
<script>
    let countdown = 60;
    let interval;

    function startCountdown() {
        interval = setInterval(() => {
            countdown--;
            document.getElementById('countdown').textContent = ` (${countdown}s)`;

            if (countdown === 0) {
                clearInterval(interval);
                document.getElementById('countdown').textContent = ''; // Reset countdown text
                document.getElementById('countdown').style.display = 'none';
                document.getElementById('resendOtpBtn').removeAttribute('disabled');
            }
        }, 1000);
    }

    function resendOTP() {
        const email = '<?php echo isset($_GET['email']) ? $_GET['email'] : ''; ?>';
        const xhr = new XMLHttpRequest();
        xhr.open('POST', 'resend_otp.php', true);
        xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
        xhr.onload = function () {
            if (this.status === 200) {
                document.getElementById('resend-confirmation').style.display = 'block';
                document.getElementById('resentEmail').textContent = email; // Set the resent email
                countdown = 60;
                document.getElementById('countdown').textContent = ` (${countdown}s)`;
                document.getElementById('countdown').style.display = 'inline';
                document.getElementById('resendOtpBtn').setAttribute('disabled', 'true');
                startCountdown(); // Start countdown
                setTimeout(() => {
                    document.getElementById('resend-confirmation').style.display = 'none';
                }, 3000); // Hide confirmation after 3 seconds
            } else {
                console.error('Error:', this.responseText);
            }
        };
        xhr.send('email=' + encodeURIComponent(email));
    }

    document.addEventListener('DOMContentLoaded', (event) => {
        startCountdown(); // Start countdown when the page loads
    });
</script>

</body>
</html>
