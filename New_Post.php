<?php
// Start the session
session_start();

// Check if user is logged in and is an admin
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header('Location: login.php');
    exit;
}
// Include admin Header
include "admin_header.php"
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Blog Post</title>
    <style>

        body {
            font-family: Arial, sans-serif;
            background-color: #fff;
            color: #000;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        h1 {
            text-align: center;
            color: #000;
        }
        form {
            background-color: #f9f9f9;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        label {
            display: block;
            margin-bottom: 10px;
        }
        input[type="text"], input[type="file"], textarea, select {
            width: 100%;
            padding: 10px;
            margin-bottom: 20px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        footer {
            background-color: #333;
            color: gold;
            text-align: center;
            padding: 10px 0;
            position: fixed;
            bottom: 0;
            width: 100%;
        }
        button {
            background-color: gold;
            color: black;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
        }
        button:hover {
            background-color: #333;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Create a New Blog Post</h1>
        <form id="add-post-form" action="add_post.php" method="post" enctype="multipart/form-data">
            <label for="title">Title:</label>
            <input type="text" id="title" name="title" required>
            
            <label for="excerpt">Excerpt:</label>
            <textarea id="excerpt" name="excerpt" rows="2"></textarea>

            <label for="content">Content:</label>
            <textarea id="content" name="content" rows="10" required></textarea>
            
            <label for="category">Category:</label>
            <select id="category" name="category" required>
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
            
            <label for="tags">Tags:</label>
            <input type="text" id="tags" name="tags">

            <label for="keyphrase">Keyphrase:</label>
            <input type="text" id="keyphrase" name="keyphrase">

            <label for="featured_image">Featured Image:</label>
            <input type="file" id="featured_image" name="featured_image">
            
            <div id="success-message" style="display: none;"></div>
            <button type="submit" name="action" value="Save as Draft">Save as Draft</button>
            <button type="submit" name="action" value="Add Post">Publish</button>
        </form>
    </div>
 
        <div class="message success" id="success-message">
            Post published successfully! <a href="#" id="post-link">View Post</a>
        </div>
        <div class="message error" id="error-message">
            There was an error publishing your post. Please try again.
        </div>
    </div>

    <script>
        function submitForm(event) {
            event.preventDefault(); // Prevent default form submission

            // Get form data
            var form = document.getElementById('add-post-form');
            var formData = new FormData(form);

            // Send AJAX request
            var xhr = new XMLHttpRequest();
            xhr.open('POST', 'add_post.php', true);
            xhr.onreadystatechange = function() {
                if (xhr.readyState == 4 && xhr.status == 200) {
                    // Handle response
                    var response = JSON.parse(xhr.responseText);
                    if (response.success) {
                        // Display success message
                        var successMessage = document.getElementById('success-message');
                        successMessage.style.display = 'block';
                        successMessage.innerHTML = response.message;

                        // Optionally display post link
                        var postLink = document.createElement('a');
                        postLink.href = response.post_link;
                        postLink.textContent = 'View Post';
                        successMessage.appendChild(postLink);

                        // Clear form fields if needed
                        form.reset();
                    } else {
                        // Handle errors if needed
                        alert('Error: ' + response.message);
                    }
                }
            };
            xhr.send(formData);
        }
    </script>
        <footer>
        &copy; <?php echo date('Y'); ?> SBA Awards. All Rights Reserved.
    </footer>
</body>
</html>
