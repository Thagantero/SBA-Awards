<?php
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
} else {
    echo "<p>Category not specified.</p>";
}
?>
