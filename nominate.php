<?php
// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
include 'dbConnect.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nomination - SBA Awards</title>
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
            max-width: 600px;
            margin: 20px auto;
            padding: 20px;
            background-color: #111;
            border-radius: 10px;
            color: gold;
            flex: 1;
        }
        form label {
            display: block;
            margin-bottom: 5px;
        }
        form input[type="text"],
        form input[type="email"],
        form textarea,
        form select,
        form button,
        form input[type="number"],
        form input[type="file"],
        form input[type="date"] {
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
            width: 100%;
            margin-top: auto;
        }
        /* Countdown Timer Styles */
        #countdown {
            text-align: center;
            margin-bottom: 20px;
        }
        #countdown p {
            font-size: 1.2em;
        }
        .countdown-container {
            display: flex;
            justify-content: center;
            gap: 10px;
            font-size: 1.5em;
        }
        .countdown-box {
            background-color: black;
            border: 2px solid gold;
            border-radius: 10px;
            padding: 10px;
            min-width: 50px;
            text-align: center;
        }
        .countdown-label {
            font-size: 0.8em;
            color: white;
        }
    </style>
</head>
<body>

<?php include 'header.php'; ?>
    
<div class="container">
    <h2>Nominate a Contestant</h2>

    <?php
    // Calculate the remaining time until July 31st midnight EAT
    $deadline = strtotime('31 July 2024 23:59:59 Africa/Nairobi');
    $now = time();
    $difference = $deadline - $now;

    if ($difference > 0) {
        // Calculate remaining days, hours, minutes, and seconds
        $days = floor($difference / (60 * 60 * 24));
        $hours = floor(($difference % (60 * 60 * 24)) / (60 * 60));
        $minutes = floor(($difference % (60 * 60)) / 60);
        $seconds = $difference % 60;
        ?>

        <div id="countdown">
            <p>Nominations end in:</p>
            <div class="countdown-container">
                <div class="countdown-box">
                    <div id="days"><?php echo $days; ?></div>
                    <div class="countdown-label">Days</div>
                </div>
                <div class="countdown-box">
                    <div id="hours"><?php echo $hours; ?></div>
                    <div class="countdown-label">Hours</div>
                </div>
                <div class="countdown-box">
                    <div id="minutes"><?php echo $minutes; ?></div>
                    <div class="countdown-label">Minutes</div>
                </div>
                <div class="countdown-box">
                    <div id="seconds"><?php echo $seconds; ?></div>
                    <div class="countdown-label">Seconds</div>
                </div>
            </div>
        </div>

        <script>
            // Update countdown every second
            setInterval(updateCountdown, 1000);

            function updateCountdown() {
                var deadline = new Date("July 31, 2024 23:59:59").getTime();
                var now = new Date().getTime();
                var difference = deadline - now;

                if (difference > 0) {
                    var days = Math.floor(difference / (1000 * 60 * 60 * 24));
                    var hours = Math.floor((difference % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                    var minutes = Math.floor((difference % (1000 * 60 * 60)) / (1000 * 60));
                    var seconds = Math.floor((difference % (1000 * 60)) / 1000);

                    document.getElementById("days").innerHTML = days;
                    document.getElementById("hours").innerHTML = hours;
                    document.getElementById("minutes").innerHTML = minutes;
                    document.getElementById("seconds").innerHTML = seconds;
                } else {
                    document.getElementById("countdown").innerHTML = "<p>Nominations have ended.</p>";
                    // Disable form submission
                    document.querySelector("form").addEventListener("submit", function(event) {
                        event.preventDefault();
                        alert("Nominations have ended. You cannot submit the form.");
                    });
                }
            }
        </script>

    <?php } else { ?>
        <p>Nominations have ended.</p>
    <?php } ?>
    <h3>Please fill out the form below to nominate a contestant or Yourself:</h3>
    <p>Successful candidates will be contacted via email.</p>
    <form action="nominee_submission_process.php" method="POST" enctype="multipart/form-data" id="nominationForm">
        <label for="name">Full Name:</label>
        <input type="text" id="name" name="name" required>
        <label for="email">Email:</label>
        <input type="email" id="email" name="email" required>
        <label for="dob">Date of Birth:</label>
        <input type="date" id="dob" name="dob" required>
        <label for="country">Country:</label>
        <input type="text" id="country" name="country" required>
        <label for="companyName">Company Name:</label>
        <input type="text" id="companyName" name="companyName" required>
        <label for="companyDescription">Company Description:</label>
        <textarea id="companyDescription" name="companyDescription" rows="4" required></textarea>
        <label for="category">Category:</label>
        <select id="category" name="category">
            <option value="Retail">Retail</option>
            <option value="Hospitality">Hospitality</option>
            <option value="Food & Beverage">Food & Beverage</option>
            <option value="Healthcare">Healthcare</option>
            <option value="Technology">Technology</option>
            <option value="Manufacturing">Manufacturing</option>
            <option value="Construction">Construction</option>
            <option value="Education">Education</option>
            <option value="Finance & Insurance">Finance & Insurance</option>
            <option value="Real Estate">Real Estate</option>
            <option value="Transportation & Logistics">Transportation & Logistics</option>
            <option value="Agriculture">Agriculture</option>
            <option value="Energy & Utilities">Energy & Utilities</option>
            <option value="Entertainment & Media">Entertainment & Media</option>
            <option value="Automotive">Automotive</option>
            <option value="Telecommunications">Telecommunications</option>
            <option value="Professional Services">Professional Services</option>
            <option value="Beauty & Wellness">Beauty & Wellness</option>
            <option value="Environmental Services">Environmental Services</option>
            <option value="Arts & Crafts">Arts & Crafts</option>
        </select>
        <label for="businessStartedYear">Year the Business Started:</label>
        <input type="number" id="businessStartedYear" name="businessStartedYear" min="1900" max="2100" required>
        <label for="facebook">Facebook Link:</label>
        <input type="text" id="facebook" name="facebook" placeholder="Enter Facebook link">
        <label for="twitter">Twitter Link:</label>
        <input type="text" id="twitter" name="twitter" placeholder="Enter Twitter link">
        <label for="instagram">Instagram Link:</label>
        <input type="text" id="instagram" name="instagram" placeholder="Enter Instagram link">
        <label for="website">Website Link:</label>
        <input type="text" id="website" name="website" placeholder="Enter website link">
        <label for="employees">Number of Employees:</label>
        <input type="number" id="employees" name="employees" min="1" required>
        <label for="image">Image:</label>
        <input type="file" id="image" name="image" accept="image/*" required>
        <label for="ownershipProof">Proof of Company Ownership:</label>
        <input type="file" id="ownershipProof" name="ownershipProof" accept="image/*,application/pdf" required>
        <p>Company ownership details may include registration certificates, receipts and invoices, contracts, etc.</p>
        <button type="submit">Submit Nomination</button>
    </form>
</div>

<?php include 'footer.php'; ?>

</body>
</html>
