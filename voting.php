<?php
// Include database connection
include 'dbConnect.php';

// Check for connection errors
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

// Initialize search term
$searchTerm = isset($_GET['search']) ? '%' . $mysqli->real_escape_string($_GET['search']) . '%' : '';

// Prepare SQL query to fetch nominees based on search term
$query = "SELECT * FROM nominees WHERE name LIKE ?";
$stmt = $mysqli->prepare($query);
$stmt->bind_param("s", $searchTerm);
$stmt->execute();
$result = $stmt->get_result();

// Build HTML for search results
$searchResults = array();

while ($nominee = $result->fetch_assoc()) {
    $html = "<div>";
    $html .= "<h3>" . htmlspecialchars($nominee['name']) . "</h3>";
    $html .= "<p>Company: " . htmlspecialchars($nominee['companyName']) . "</p>";
    $html .= "<p>Category: " . htmlspecialchars($nominee['category']) . "</p>";
    $html .= "<p>Country: " . htmlspecialchars($nominee['country']) . "</p>";
    $html .= "<button onclick='vote(" . htmlspecialchars($nominee['id']) . ")'>Vote</button>";
    $html .= "</div>";
    $searchResults[] = $html;
}

// If no results found, provide feedback
if (empty($searchResults)) {
    echo "<p>No nominees found matching your search.</p>";
} else {
    // Output all search results
    foreach ($searchResults as $resultHtml) {
        echo $resultHtml;
    }
}

$stmt->close();

// Fetch top ten nominees
$top_ten_query = $mysqli->query("SELECT * FROM nominees ORDER BY votes DESC LIMIT 10");

include 'header.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="voting.js"></script>
    <script src="https://kit.fontawesome.com/87ab693041.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="styles.css">
    <title>SBA Awards - Voting</title>
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

    .spacer {
        height: 70px; /* Adjust height to match the header height */
    }

    .container {
        max-width: 800px;
        margin: 20px auto; /* Adjust top margin to account for the fixed header */
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
    }

    th, td {
        padding: 10px;
        text-align: left;
    }

    th {
        background-color: #222;
    }

    td img {
        width: 20px;
        height: 20px;
        margin-right: 5px;
    }

    /* Increase the width of the name column */
    th:nth-child(2), td:nth-child(2) {
        width: 25%; /* Adjust percentage as needed */
    }

    .search-container {
        display: flex;
        justify-content: space-between;
        margin-bottom: 10px;
    }

    .search-box {
        background-color: white;
        color: #ffd700;
        border: 2px solid gold;
        padding: 5px 10px;
        border-radius: 5px;
        flex: 1;
        margin-right: 10px; /* Add space between search box and button */
    }

    .search-button {
        background-color: #ffd700;
        color: #000;
        border: none;
        padding: 8px 16px;
        border-radius: 5px;
        cursor: pointer;
    }

    .vote-button {
        background-color: green;
        color: #fff;
        border: none;
        padding: 8px 16px;
        border-radius: 5px;
        cursor: pointer;
    }

    /* Change link color to gold */
    a {
        color: gold;
        text-decoration: none;
    }

    a:hover {
        text-decoration: underline;
    }

    .countdown {
        text-align: center;
        font-size: 24px;
        margin-bottom: 20px;
    }

    .countdown div {
        display: inline-block;
        margin: 0 10px;
        background-color: #333;
        padding: 10px 20px;
        border-radius: 5px;
    }

    .countdown div span {
        display: block;
        font-size: 18px;
        color: #ffd700;
    }

</style>
</head>
<body>
<div class="container">

<div id="countdown" class="countdown">
    <div>
        <span id="days">0</span> Days
    </div>
    <div>
        <span id="hours">0</span> Hours
    </div>
    <div>
        <span id="minutes">0</span> Minutes
    </div>
    <div>
        <span id="seconds">0</span> Seconds
    </div>
</div>
    <div class="search-container">
    <input type="text" id="search" placeholder="Search nominees..." class="search-box">
    <button class="search-button" onclick="fetchNominees()">Search</button>
</div>
<div id="nominees"></div> <!-- Placeholder for search results -->
<section id="top-ten">
    <h2>Top Ten Nominees</h2>
    <table>
        <thead>
            <tr>
                <th>Position</th>
                <th>Name</th>
                <th>Company Name</th>
                <th>Category</th>
                <th>Country</th>
                <th>Votes</th>
                <th>Vote</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $position = 1;
            while ($nominee = $top_ten_query->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . $position++ . "</td>";
                echo "<td><a href='nominee.php?id=" . htmlspecialchars($nominee['id']) . "'>" . htmlspecialchars($nominee['name']) . "</a></td>";
                echo "<td>" . htmlspecialchars($nominee['companyName']) . "</td>";
                echo "<td>" . htmlspecialchars($nominee['category']) . "</td>";
                echo "<td><img src='https://upload.wikimedia.org/wikipedia/commons/4/49/Flag_of_Kenya.svg' alt='Kenya'> " . htmlspecialchars($nominee['country']) . "</td>";
                echo "<td>" . htmlspecialchars($nominee['votes']) . "</td>";
                echo "<td><a href='credits.php?nominee_id=" . htmlspecialchars($nominee['id']) . "' class='vote-button'>Vote</a></td>";
                echo "</tr>";
            }
            ?>
        </tbody>
    </table>
</section>
</div>
<script>
function fetchNominees() {
    const searchQuery = document.getElementById('search').value.trim(); // Trim whitespace
    if (searchQuery !== '') {
        const xhr = new XMLHttpRequest();
        xhr.open('GET', `fetch_nominees.php?search=${encodeURIComponent(searchQuery)}`, true);
        xhr.onreadystatechange = function() {
            if (xhr.readyState === XMLHttpRequest.DONE) {
                if (xhr.status === 200) {
                    document.getElementById('nominees').innerHTML = xhr.responseText;
                } else {
                    console.error('Error fetching nominees:', xhr.status, xhr.statusText);
                }
            }
        };
        xhr.send();
    } else {
        document.getElementById('nominees').innerHTML = ''; // Clear nominees container if search query is empty
    }
}


    function vote(nomineeId) {
        const xhr = new XMLHttpRequest();
        xhr.open('POST', 'voting_process.php', true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.onreadystatechange = function() {
            if (xhr.readyState === XMLHttpRequest.DONE && xhr.status === 200) {
                // Reload the page to show updated vote counts
                fetchNominees();
            }
        };
        xhr.send('nominee_id=' + nomineeId);
    }

    document.addEventListener('DOMContentLoaded', function() {
        fetchNominees();
        startCountdown();
    });

    function startCountdown() {
        const countdownElement = document.getElementById('countdown');
        const endDate = new Date('July 31, 2024 23:59:59').getTime();

        const interval = setInterval(() => {
            const now = new Date().getTime();
            const distance = endDate - now;

            if (distance < 0) {
                clearInterval(interval);
                countdownElement.innerHTML = 'Voting has ended.';
                return;
            }

            const days = Math.floor(distance / (1000 * 60 * 60 * 24));
            const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((distance % (1000 * 60)) / 1000);

            document.getElementById('days').textContent = days;
            document.getElementById('hours').textContent = hours;
            document.getElementById('minutes').textContent = minutes;
            document.getElementById('seconds').textContent = seconds;
        }, 1000);
    }
</script>
<!-- Insert Footer -->
<?php include 'footer.php'; ?>

</body>
</html>