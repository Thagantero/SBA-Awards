<?php
// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Start a session
session_start();

// Include necessary files
include 'dbConnect.php';

// Include the header file at the beginning of the script
include 'header.php';

// Initialize $logged_in with a default value
$logged_in = false; // Assuming the default state is not logged in

// Check if user is logged in
if (isset($_SESSION['user_id'])) {
    $logged_in = true;
}

// Fetch the 3 most recent blog posts from the database
$recent_posts = [];
$query = "SELECT id, title, excerpt, featured_image, created_at FROM blog_posts ORDER BY created_at DESC LIMIT 3";
if ($result = $mysqli->query($query)) {
    while ($row = $result->fetch_assoc()) {
        $recent_posts[] = $row;
    }
    $result->free();
} else {
    echo "Error fetching blog posts: " . $mysqli->error;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SBA Awards - Home</title>
    <link rel="stylesheet" href="styles.css">
    <script src="https://kit.fontawesome.com/87ab693041.js" crossorigin="anonymous"></script>
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
            background-color: gold; /* Set header background to gold */
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px;
            position: fixed; /* Make the header sticky */
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000; /* Ensure it stays on top of other content */
        }
        nav .logo a {
            text-decoration: none;
            color: black; /* Changed to match the gold background */
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
            color: black; /* Changed to match the gold background */
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
            max-width: 800px;
            margin: 70px auto 20px; /* Adjust top margin to account for the fixed header */
            padding: 20px;
            background-color: #111;
            border-radius: 10px;
            color: gold; /* Ensure text color is consistent */
        }
        .event-section {
            margin-top: 20px;
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
        }
        .event {
            width: 100%; /* Adjust width to fit one event per row on smaller screens */
            background-color: #222;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
        }
        .event h2 {
            margin-top: 0;
        }
        .event img {
            width: 100%;
            border-radius: 5px;
            margin-bottom: 10px;
        }
        .event p {
            margin: 10px 0;
        }
        .event .buy-ticket-btn {
            background-color: gold;
            color: black;
            border: none;
            border-radius: 5px;
            padding: 10px 20px;
            font-size: 16px;
            cursor: pointer;
            text-decoration: none;
        }
        .event .buy-ticket-btn:hover {
            background-color: #f7d32e;
        }
        @media (min-width: 768px) {
            .event {
                width: 48%; /* Display two events per row on larger screens */
            }
                .blog-post {
            background-color: #222;
            padding: 20px;
            border-radius: 10px;
            width: 30%;
        }
               .blog-archive {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            margin-top: 20px;
        }
        .blog-post {
            background-color: #222;
            padding: 20px;
            border-radius: 10px;
            width: calc(30% - 20px); /* Adjusted width calculation */
            margin-bottom: 20px;
            color: gold; /* Text color */
        }
        .blog-post h3 {
            margin-top: 0;
        }
        .blog-post p {
            margin: 10px 0;
            color: #ccc; /* Adjusted text color */
        }
        .blog-post img {
            width: 100%;
            border-radius: 5px;
            margin-bottom: 10px;
        }
        .blog-post button {
            padding: 10px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            background-color: gold;
            color: black;
            text-decoration: none; /* Ensure button text is black */
            display: block; /* Make button full width */
            width: 100%; /* Ensure button is full width */
            text-align: center; /* Center align button text */
        }
        .blog-post button:hover {
            background-color: #f7d32e;
        }
        /* Global styles and resets */
body, html {
    margin: 0;
    padding: 0;
}

.header {
    background-color: #333;
    color: gold;
    padding: 10px 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    width: 100%;
    position: fixed;
    z-index: 1000;
}

.header a {
    color: gold;
    text-decoration: none;
    margin-left: 15px;
    margin-right: 20px;
}

.header .logo img {
    max-width: 60px;
    height: auto;
}

        }
        .slider-container {
            width: 100%;
            height: 400px;
            overflow: hidden;
            position: relative;
        }
        .slider {
            display: flex;
            transition: transform 0.5s ease;
        }
        .slide {
            min-width: 100%;
            overflow: hidden;
            position: relative;
            display: none; /* Initially hide all slides */
        }
        .slide img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .slide-caption {
            position: absolute;
            bottom: 20px;
            left: 20px;
            color: #fff;
            font-size: 24px;
            font-weight: bold;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.6);
        }


.slide1 {
    background-color: #1abc9c; /* Optional: background color for slide */
}

.slide2 {
    background-color: #3498db; /* Optional: background color for slide */
}

.slide3 {
    background-color: #e74c3c; /* Optional: background color for slide */
}


        }
        body {
            font-family: Arial, sans-serif;
            background-color: black;
            color: gold;
            margin: 0;
            padding: 0;
        }
        /* Additional styles for the slider */
        .slider-container {
            width: 100%;
            height: 400px; /* Adjust height as needed */
            overflow: hidden;
            position: relative;
        }
        .slider {
            display: flex;
            transition: transform 0.5s ease;
        }
        .slide {
            min-width: 100%;
            overflow: hidden;
            position: relative;
        }
        .slide img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .slide1 {
            background-color: #1abc9c; /* Optional: background color for slide */
        }
        .slide2 {
            background-color: #3498db; /* Optional: background color for slide */
        }
        .slide3 {
            background-color: #e74c3c; /* Optional: background color for slide */
        }
    </style>
</head>
<body>

<div class="slider-container">
    <div class="slider">
        <div class="slide slide1">
            <img src="http://localhost/SBA-awards/uploads/header1.jpg" alt="Header 1">
            <div class="slide-caption">Celebrating Excellence</div>
        </div>
        <div class="slide slide2">
            <img src="http://localhost/SBA-awards/uploads/header2.jpg" alt="Header 2">
            <div class="slide-caption">Recognizing Achievement</div>
        </div>
        <div class="slide slide3">
            <img src="http://localhost/SBA-awards/uploads/header3.jpg" alt="Header 3">
            <div class="slide-caption">Honoring Innovation</div>
        </div>
    </div>
</div>

<div class="container">
    <p>SBA Awards (Startups Businesses Africa Awards) is an esteemed event aimed at recognizing and rewarding African startup businesses that demonstrate exceptional innovation, creativity, and impact. By celebrating the entrepreneurial spirit of Africa, SBA Awards strives to inspire and empower the next generation of entrepreneurs, driving economic growth and social development across the continent.</p>
    
    <!-- Only show login and signup buttons if user is not logged in -->
    <?php if (!$logged_in): ?>
    <div class="login-signup-form">
        <button onclick="window.location.href='login.php'">Login</button>
        <button onclick="window.location.href='signup.php'">Signup</button>
        <button onclick="window.location.href='voting.php'">Vote</button>
    </div>
    <?php endif; ?>

    <!-- Always show Vote button, adjust Buy Credits button visibility based on login status -->
    <div class="auth-links">
        <?php if ($logged_in): ?>
        <button onclick="window.location.href='buy_credits.php'" class="buy-credits-button">Buy Credits</button>
        <button onclick="window.location.href='voting.php'">Vote</button>
        <?php endif; ?>
    </div>

    <h2>Upcoming Events</h2>
    <div class="event-section">
        <!-- Event 1 -->
        <div class="event">
            <h2>SBA Awards Launch Party</h2>
            <img src="uploads/_6fa571f7-801f-4a6f-bdfd-166afedb394d.jpg" alt="Event Image">
            <p>Date: August 15, 2024</p>
            <p>Location: KICC Nairobi, Kenya</p>
            <a href="events.php" class="buy-ticket-btn">Buy Tickets</a>
        </div>

        <!-- Event 2 -->
        <div class="event">
            <h2>Inaugural SBA Awards Gala</h2>
            <img src="uploads/_5400ecbc-1c09-4802-b651-40f03fdb1a74.jpg" alt="Event Image">
            <p>Date: September 20, 2024</p>
            <p>Location: KICC Nairobi, Kenya</p>
            <a href="events.php" class="buy-ticket-btn">Buy Tickets</a>
        </div>
    </div>

    <h2>Top 10 Candidates</h2>
    <table>
        <thead>
            <tr>
                <th>Company</th>
                <th>Founder</th>
                <th>Category</th>
                <th>Country</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Milenial Mindset</td>
                <td>Marcia</td>
                <td>NGO</td>
                <td><img src="https://upload.wikimedia.org/wikipedia/commons/a/af/Flag_of_South_Africa.svg" alt="South Africa"> South Africa</td>
            </tr>
            <tr>
                <td>Thagana Solutions</td>
                <td>Thagana Peters</td>
                <td>Tech</td>
                <td><img src="https://upload.wikimedia.org/wikipedia/commons/4/49/Flag_of_Kenya.svg" alt="Kenya"> Kenya</td>
            </tr>
            <tr>
                <td>Safaricom</td>
                <td>Peter Ndegwa</td>
                <td>Telcom</td>
                <td><img src="https://upload.wikimedia.org/wikipedia/commons/4/49/Flag_of_Kenya.svg" alt="Kenya"> Kenya</td>
            </tr>
            <tr>
                <td>Nasha Poem</td>
                <td>Annie Nasha</td>
                <td>Publishing</td>
                <td><img src="https://upload.wikimedia.org/wikipedia/commons/4/49/Flag_of_Kenya.svg" alt="Kenya"> Kenya</td>
            </tr>
            <tr>
                <td>Cdave Music</td>
                <td>Cdave Honcho</td>
                <td>Music & Art</td>
                <td><img src="https://upload.wikimedia.org/wikipedia/commons/4/4e/Flag_of_Uganda.svg" alt="Uganda"> Uganda</td>
            </tr>
            <tr>
                <td>SBA Awards</td>
                <td>Brian Muchiri</td>
                <td>Event</td>
                <td><img src="https://upload.wikimedia.org/wikipedia/commons/4/49/Flag_of_Kenya.svg" alt="Kenya"> Kenya</td>
            </tr>
            <tr>
                <td>Family Bank</td>
                <td>Rebecca Mbithi</td>
                <td>Banking</td>
                <td><img src="https://upload.wikimedia.org/wikipedia/commons/4/49/Flag_of_Kenya.svg" alt="Kenya"> Kenya</td>
            </tr>
            <tr>
                <td>Kidogo</td>
                <td>Sabrina Habib</td>
                <td>Education</td>
                <td><img src="https://upload.wikimedia.org/wikipedia/commons/4/49/Flag_of_Kenya.svg" alt="Kenya"> Kenya</td>
            </tr>
            <tr>
                <td>Shepherds of Nature</td>
                <td>Hassan</td>
                <td>Environmentalist</td>
                <td><img src="https://upload.wikimedia.org/wikipedia/commons/4/49/Flag_of_Kenya.svg" alt="Kenya"> Kenya</td>
            </tr>
            <tr>
                <td>Live Wire</td>
                <td>Jonte</td>
                <td>Media</td>
                <td><img src="https://upload.wikimedia.org/wikipedia/commons/7/79/Flag_of_Nigeria.svg" alt="Nigeria" style="width:20px;height:auto;">Nigeria</td>
            </tr>
            <tr>
                <td>WCB</td>
                <td>Chibu Dangote</td>
                <td>Entertainment</td>
                <td><img src="https://upload.wikimedia.org/wikipedia/commons/3/38/Flag_of_Tanzania.svg" alt="Tanzania"> Tanzania</td>
            </tr>
            <tr>
                <td>Pen Publishers</td>
                <td>Wamwanda</td>
                <td>Publishing</td>
                <td><img src="https://upload.wikimedia.org/wikipedia/commons/4/49/Flag_of_Kenya.svg" alt="Kenya"> Kenya</td>
            </tr>
            <tr>
                <td>XFM</td>
                <td>Likkle Bangi</td>
                <td>Music & Art</td>
                <td><img src="https://upload.wikimedia.org/wikipedia/commons/4/4e/Flag_of_Uganda.svg" alt="Uganda"> Uganda</td>
            </tr>
        </tbody>
    </table>

    <h2>Recent Articles</h2>
    <div class="blog-archive">
        <?php foreach ($recent_posts as $post): ?>
        <div class="blog-post">
            <?php if (!empty($post['featured_image'])): ?>
            <img src="<?php echo htmlspecialchars($post['featured_image'] ?? ''); ?>" alt="<?php echo htmlspecialchars($post['title'] ?? ''); ?>">
            <?php endif; ?>
            <h3><?php echo htmlspecialchars($post['title'] ?? ''); ?></h3>
            <p><?php echo htmlspecialchars($post['excerpt'] ?? ''); ?></p>
            <button onclick="window.location.href='blog_post.php?id=<?php echo htmlspecialchars($post['id'] ?? ''); ?>'">Read More</button>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<?php include 'footer.php'; ?>

<script>
    function toggleMenu() {
        const navLinks = document.querySelector('.nav-links');
        navLinks.classList.toggle('show');
    }
</script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let slideIndex = 0;
    const slides = document.querySelectorAll('.slide');
    const totalSlides = slides.length;

    function showSlides() {
        slides.forEach((slide, index) => {
            slide.style.display = 'none';
        });
        slideIndex++;
        if (slideIndex > totalSlides) {
            slideIndex = 1;
        }
        slides[slideIndex - 1].style.display = 'block';
        setTimeout(showSlides, 5000);
    }

    // Initially show the first slide
    if (slides.length > 0) {
        slides[0].style.display = 'block';
    }

    showSlides();
});
</script>


</body>
</html>
