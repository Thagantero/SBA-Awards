<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Include database connection
require_once "dbConnect.php";

// Fetch traffic data from the database
$sql = "SELECT date, page_views, visitors, countries, devices, pages_visited, posts_visited FROM traffic_stats";
$result = $mysqli->query($sql);

if ($result) {
    $trafficData = [];
    while ($row = $result->fetch_assoc()) {
        // Prepare the data in a format suitable for the frontend (JSON)
        $trafficData[] = [
            'date' => $row['date'],
            'page_views' => (int) $row['page_views'],
            'visitors' => (int) $row['visitors'],
            'countries' => json_decode($row['countries'], true), // Assuming countries is stored as JSON in DB
            'devices' => json_decode($row['devices'], true), // Assuming devices is stored as JSON in DB
            'pages_visited' => json_decode($row['pages_visited'], true), // Assuming pages_visited is stored as JSON in DB
            'posts_visited' => json_decode($row['posts_visited'], true), // Assuming posts_visited is stored as JSON in DB
        ];
    }
    // Output the data as JSON
    echo json_encode($trafficData);
} else {
    // Handle database error if query fails
    echo json_encode(['error' => 'Error fetching traffic data: ' . $mysqli->error]);
}

// Close database connection
$mysqli->close();
?>
