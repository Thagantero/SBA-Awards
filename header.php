<?php
// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Initialize variables with default values
$username = (string) getenv('SBA_DB_USER');
$voting_credits = 0;  // Initialize with default value

// Check if user is logged in
if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
    // Fetch user's username and voting credits
    if (isset($_SESSION['user_id'])) {
        $user_id = $_SESSION['user_id'];

        // Connect to the database (adjust database credentials as needed)
        $mysqli = new mysqli((getenv('SBA_DB_HOST') ?: 'localhost'), (string) getenv('SBA_DB_USER'), (string) getenv('SBA_DB_PASSWORD'), (string) getenv('SBA_DB_NAME'));

        // Check for connection errors
        if ($mysqli->connect_error) {
            die("Connection failed: " . $mysqli->connect_error);
        }

        // Fetch user data
        $user_query = $mysqli->prepare("SELECT username, voting_credits FROM users WHERE id = ?");
        $user_query->bind_param('i', $user_id);
        $user_query->execute();
        $user_result = $user_query->get_result();
        $user_data = $user_result->fetch_assoc();

        // If user data found, assign values
        if ($user_data) {
            $username = htmlspecialchars($user_data['username']);
            $voting_credits = $user_data['voting_credits'];
        }

        // Close user query
        $user_query->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SBA Awards</title>
    <script src="https://kit.fontawesome.com/87ab693041.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="styles.css">
    <style>
        /* Additional CSS styles for dropdown and nominee display */
        .category-dropdown {
            display: none;
            position: absolute;
            background-color: #f9f9f9;
            box-shadow: 0px 8px 16px 0px rgba(0,0,0,0.2);
            z-index: 1;
        }
        .category-dropdown a {
            color: black;
            padding: 12px 16px;
            text-decoration: none;
            display: block;
        }
        .category-dropdown a:hover {
            background-color: #f1f1f1;
        }
        .nav-links li {
            position: relative;
            display: inline-block;
        }
        .nav-links li:hover .category-dropdown {
            display: block;
        }
        .nominee-list {
            margin-top: 20px;
            max-width: 600px; /* Adjust as needed */
            margin-left: auto;
            margin-right: auto;
        }
        .nominee-list ul {
            padding-left: 0;
            list-style-type: none;
        }
        .nominee-list li {
            margin-bottom: 10px;
        }
        .logo img {
            max-width: 60px; /* Example: Adjust the max-width as needed */
            height: auto; /* Maintain aspect ratio */
            display: block; /* Ensure proper alignment and spacing */
        }
    </style>
</head>
<body>
    <header>
        <nav>
            <div class="logo">
                <a href="home.php"><img src="http://localhost/SBA-awards/uploads/logo1.png" alt="Logo"></a>
            </div>
            <ul class="nav-links">
                <li><a href="home.php">Home</a></li>
                <li>
                    <a href="#">Category</a>
                    <!-- Move the category dropdown menu here -->
                    <div class="category-dropdown">
                        <a href="/category/retail">Retail</a>
                        <a href="/category/hospitality">Hospitality</a>
                        <a href="/category/food-beverage">Food & Beverage</a>
                        <a href="/category/healthcare">Healthcare</a>
                        <a href="/category/technology">Technology</a>
                        <a href="/category/manufacturing">Manufacturing</a>
                        <a href="/category/construction">Construction</a>
                        <a href="/category/education">Education</a>
                        <a href="/category/finance-insurance">Finance & Insurance</a>
                        <a href="/category/real-estate">Real Estate</a>
                        <a href="/category/transportation-logistics">Transportation & Logistics</a>
                        <a href="/category/agriculture">Agriculture</a>
                        <a href="/category/energy-utilities">Energy & Utilities</a>
                        <a href="/category/entertainment-media">Entertainment & Media</a>
                        <a href="/category/automotive">Automotive</a>
                        <a href="/category/telecommunications">Telecommunications</a>
                        <a href="/category/professional-services">Professional Services</a>
                        <a href="/category/beauty-wellness">Beauty & Wellness</a>
                        <a href="/category/environmental-services">Environmental Services</a>
                        <a href="/category/arts-crafts">Arts & Crafts</a>
                    </div>
                </li>
                <li><a href="about.php">About</a></li>
                <li><a href="contact.php">Contact</a></li>
                <li><a href="nominate.php">Nominate</a></li>
                <li><a href="blog.php">Blog</a></li>
                <!-- Add more navigation links as needed -->
            </ul>
            <ul class="auth-links">
                <?php if(isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) { ?>
                    <li><a href="profile.php"><i class="fas fa-user"></i> <?php echo htmlspecialchars($username); ?></a></li>
                    <li><a href="credits_profile.php">Credits: <?php echo htmlspecialchars($voting_credits); ?></a></li>
                    <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                <?php } else { ?>
                    <li><a href="login.php"><i class="fas fa-sign-in-alt"></i> Login</a></li>
                    <li><a href="signup.php"><i class="fas fa-user-plus"></i> Signup</a></li>
                <?php } ?>
            </ul>
            <div class="burger" onclick="toggleMenu()">
                <div class="line1"></div>
                <div class="line2"></div>
                <div class="line3"></div>
            </div>
        </nav>
    </header>
    
    <div class="nominee-list" id="nomineeList">
        <?php
        // Check if a category is selected via GET request
        if (isset($_GET['category'])) {
            $selectedCategory = htmlspecialchars($_GET['category']);

            // Connect to the database (adjust database credentials as needed)
            $mysqli = new mysqli((getenv('SBA_DB_HOST') ?: 'localhost'), (string) getenv('SBA_DB_USER'), (string) getenv('SBA_DB_PASSWORD'), (string) getenv('SBA_DB_NAME'));

            // Check for connection errors
            if ($mysqli->connect_error) {
                die("Connection failed: " . $mysqli->connect_error);
            }

            // Function to fetch nominees by category
            function fetchNomineesByCategory($category, $mysqli) {
                $nominees = [];

                // Prepare SQL statement to fetch nominees by category
                $stmt = $mysqli->prepare("SELECT name FROM nominees WHERE category = ?");
                $stmt->bind_param('s', $category);
                $stmt->execute();
                $result = $stmt->get_result();

                // Fetch nominees and store in array
                while ($row = $result->fetch_assoc()) {
                    $nominees[] = $row['name']; // Assuming 'name' is the column for nominee's name
                }

                return $nominees;
            }

            // Fetch nominees by selected category
            $nominees = fetchNomineesByCategory($selectedCategory, $mysqli);

            // Display nominees
            if (!empty($nominees)) {
                echo "<ul>";
                foreach ($nominees as $nominee) {
                    echo "<li>$nominee</li>";
                }
                echo "</ul>";
            } else {
                echo "<p>No nominees found for this category.</p>";
            }

            // Close database connection
            $mysqli->close();
        }
        ?>
    </div>

</body>
</html>
