<?php
// Connect to the database
$mysqli = new mysqli((getenv('SBA_DB_HOST') ?: 'localhost'), (string) getenv('SBA_DB_USER'), (string) getenv('SBA_DB_PASSWORD'), (string) getenv('SBA_DB_NAME'));

// Check for connection errors
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

// Fetch top ten nominees
$query = "SELECT * FROM nominees ORDER BY votes DESC LIMIT 10";
$result = $mysqli->query($query);

$position = 1;
while ($nominee = $result->fetch_assoc()) {
    echo "<tr>";
    echo "<td>" . $position++ . "</td>";
    echo "<td>" . htmlspecialchars($nominee['name']) . "</td>";
    echo "<td>" . htmlspecialchars($nominee['companyName']) . "</td>";
    echo "<td>" . htmlspecialchars($nominee['category']) . "</td>";
    echo "<td>" . htmlspecialchars($nominee['country']) . "</td>";
    echo "<td>" . htmlspecialchars($nominee['votes']) . "</td>";
    echo "</tr>";
}
?>
