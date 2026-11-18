<?php
// Include dbConnect.php (adjust path as necessary)
include 'dbConnect.php';

// Check for connection errors
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

$searchTerm = isset($_GET['search']) ? '%' . $mysqli->real_escape_string($_GET['search']) . '%' : '';

// Prepare the SQL query to fetch nominees based on search query
$query = "SELECT * FROM nominees WHERE name LIKE ?";
$stmt = $mysqli->prepare($query);
$stmt->bind_param("s", $searchTerm);
$stmt->execute();
$result = $stmt->get_result();

// Initialize an array to hold the HTML for search results
$searchResults = array();

// Build HTML for each search result
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
$mysqli->close();
?>
