<?php
// Start session
session_start();

// Check if user is logged in and is an admin
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    http_response_code(401);
    echo json_encode(array('success' => false, 'message' => 'Unauthorized Access'));
    exit;
}

// Include database connection
require_once "dbConnect.php";


// Initialize variables
$title = $content = $category = $tags = $excerpt = $keyphrase = $featured_image = '';
$title_err = $content_err = $category_err = $file_err = '';

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Check which action is requested (Add Post or Save as Draft)
    if (isset($_POST['action']) && ($_POST['action'] == 'Add Post' || $_POST['action'] == 'Save as Draft')) {

        // Sanitize and validate input
        $title = trim(filter_input(INPUT_POST, 'title', FILTER_SANITIZE_SPECIAL_CHARS));
        $content = trim(filter_input(INPUT_POST, 'content', FILTER_SANITIZE_SPECIAL_CHARS));
        $category = trim(filter_input(INPUT_POST, 'category', FILTER_SANITIZE_SPECIAL_CHARS));
        $excerpt = trim(filter_input(INPUT_POST, 'excerpt', FILTER_SANITIZE_SPECIAL_CHARS));
        $tags = trim(filter_input(INPUT_POST, 'tags', FILTER_SANITIZE_SPECIAL_CHARS));
        $keyphrase = trim(filter_input(INPUT_POST, 'keyphrase', FILTER_SANITIZE_SPECIAL_CHARS));

        // Validate title, content, and category
        if (empty($title)) {
            $title_err = "Title is required";
        }

        if (empty($content)) {
            $content_err = "Content is required";
        }

        if (empty($category)) {
            $category_err = "Category is required";
        }

        // Handle file upload if a file is selected
        if (isset($_FILES['featured_image']) && $_FILES['featured_image']['error'] == 0) {
            $upload_dir = 'uploads/';
            $file_name = time() . '_' . basename($_FILES['featured_image']['name']);
            $upload_file = $upload_dir . $file_name;

            // Check file type and size
            $allowed_types = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            $max_size = 50 * 1024 * 1024; // 50MB in bytes

            $file_info = pathinfo($_FILES['featured_image']['name']);
            $file_ext = strtolower($file_info['extension']);

            // Check if file type is allowed and size is within limit
            if (in_array($file_ext, $allowed_types) && $_FILES['featured_image']['size'] <= $max_size) {
                // Move the file to the upload directory
                if (move_uploaded_file($_FILES['featured_image']['tmp_name'], $upload_file)) {
                    $featured_image = $upload_file;
                } else {
                    $file_err = "Error uploading file.";
                }
            } else {
                $file_err = "Invalid file type or size. Allowed types: JPG, JPEG, PNG, GIF, WebP. Max size: 50MB.";
            }
        }

        // If no errors, proceed to insert into database
        if (empty($title_err) && empty($content_err) && empty($category_err) && empty($file_err)) {

            // Prepare SQL statement
            $stmt = $mysqli->prepare("INSERT INTO blog_posts (title, excerpt, content, category, tags, keyphrase, featured_image, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");

            // Check if prepare() succeeded
            if ($stmt === false) {
                $response = array('success' => false, 'message' => 'Prepare failed: (' . $mysqli->errno . ') ' . $mysqli->error);
                echo json_encode($response);
                exit;
            }

            // Determine post status (Draft or Published)
            $status = ($_POST['action'] == 'Save as Draft') ? 'draft' : 'published';

            // Bind parameters
            $stmt->bind_param("ssssssss", $title, $excerpt, $content, $category, $tags, $keyphrase, $featured_image, $status);

            // Execute statement and check for success
            if ($stmt->execute()) {
                // Successful insertion
                $post_id = $mysqli->insert_id; // Get the ID of the newly inserted post

                // Construct post link (replace with your actual link logic)
                $post_link = 'post_post.php?id=' . $post_id;

                // Prepare response
                $success_msg = "New blog post published successfully.";
                $response = array('success' => true, 'message' => $success_msg, 'post_link' => $post_link);
                echo json_encode($response);
            } else {
                // Error handling for database insert failure
                $error_msg = "Error: " . $stmt->error;
                $response = array('success' => false, 'message' => $error_msg);
                echo json_encode($response);
            }

            // Close statement
            $stmt->close();
        } else {
            // Validation errors
            $response = array('success' => false, 'message' => 'Validation error. Please check your input.');
            echo json_encode($response);
        }
    } else {
        // Invalid form action
        $response = array('success' => false, 'message' => 'Invalid form action.');
        echo json_encode($response);
    }
} else {
    // Invalid request method
    $response = array('success' => false, 'message' => 'Invalid request method.');
    echo json_encode($response);
}

// Close MySQL connection
$mysqli->close();
?>
