<?php
// Start session and check authentication
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header("Location: login.php");
    exit;
}

// Initialize variables with default values
$successMessage = isset($_SESSION['success']) ? $_SESSION['success'] : null;
$fileUrls = isset($_SESSION['fileUrls']) ? $_SESSION['fileUrls'] : [];

// Clear session variables after fetching
unset($_SESSION['success']);
unset($_SESSION['fileUrls']);

// Include admin Header
include "admin_header.php"

?>

<!DOCTYPE html>
<html lang="en">
<head>
 <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Post</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" integrity="sha512-..." crossorigin="anonymous" />
    <script src="https://kit.fontawesome.com/87ab693041.js" crossorigin="anonymous"></script>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }
        .container {
            max-width: 600px;
            margin: auto;
            padding: 20px;
            border: 1px solid #ccc;
            border-radius: 10px;
            background-color: #f9f9f9;
        }
        h1 {
            text-align: center;
        }
        form {
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .drop-area {
            border: 2px dashed #ccc;
            border-radius: 10px;
            padding: 20px;
            text-align: center;
            cursor: pointer;
            margin-bottom: 10px;
            width: 100%;
        }
        .drop-area.hover {
            border-color: #333;
        }
        input[type="file"] {
            display: none;
        }
        button {
            padding: 10px;
            background-color: #28a745;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        button:hover {
            background-color: #218838;
        }
        .message {
            margin-top: 10px;
            text-align: center;
        }
        .error-message {
            color: red;
            font-weight: bold;
        }
        .success-message {
            color: green;
        }

    
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 1200px;
            margin: 20px auto;
            padding: 20px;
            background-color: #fff;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            border-radius: 8px;
        }
        header {
            background-color: #333;
            color: gold;
            padding: 10px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        header a {
            color: gold;
            text-decoration: none;
            margin-left: 15px;
        }
        header .user-info {
            display: flex;
            align-items: center;
        }
        header .user-info a {
            margin-left: 10px;
        }
        h1 {
            color: #333;
        }
        h2 {
            color: #333;
            margin-top: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        table, th, td {
            border: 1px solid #ccc;
        }
        th, td {
            padding: 10px;
            text-align: left;
        }
        th {
            background-color: #333;
            color: gold;
        }
        a.edit, a.delete {
            color: blue;
            text-decoration: none;
            margin-right: 5px;
        }
        a.delete {
            color: red;
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
        form {
            margin-top: 20px;
            background-color: #f9f9f9;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 5px rgba(0,0,0,0.1);
        }
        form label {
            display: block;
            margin-bottom: 10px;
            color: #333;
        }
        form input[type="text"], form textarea {
            width: 100%;
            padding: 10px;
            margin-bottom: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
        }
        form input[type="file"] {
            margin-bottom: 20px;
        }
        form input[type="submit"] {
            background-color: #333;
            color: #fff;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        form input[type="submit"]:hover {
            background-color: #555;
        }
          body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }
        .container {
            max-width: 600px;
            margin: auto;
            padding: 20px;
            border: 1px solid #ccc;
            border-radius: 10px;
            background-color: #f9f9f9;
        }
        h1 {
            text-align: center;
        }
        form {
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .drop-area {
            border: 2px dashed #ccc;
            border-radius: 10px;
            padding: 20px;
            text-align: center;
            cursor: pointer;
            margin-bottom: 10px;
            width: 100%;
        }
        .drop-area.hover {
            border-color: #333;
        }
        input[type="file"] {
            display: none;
        }
        button {
            padding: 10px;
            background-color: #28a745;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin-top: 10px;
        }
        button:hover {
            background-color: #218838;
        }
        .message {
            margin-top: 10px;
            text-align: center;
        }
        .error-message {
            color: red;
            font-weight: bold;
        }
        .success-message {
            color: green;
        }
        .file-list {
            margin-top: 10px;
            max-height: 200px;
            overflow-y: auto;
            padding: 5px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        .file-item {
            margin-bottom: 5px;
        }
        .file-link {
            display: inline-block;
            margin-right: 10px;
            text-decoration: none;
            color: #007bff;
        }
    </style>
</head>
<body>

<div class="container">
    <h1>Upload Files</h1>
    <form id="uploadForm" action="upload_handler.php" method="post" enctype="multipart/form-data">
        <div class="drop-area" id="dropArea">
            <p id="dropMessage">Drag & Drop your files here or click to select files</p>
            <input type="file" name="files[]" id="fileInput" multiple accept=".jpg, .jpeg, .png, .gif, .webp, .pdf, .doc, .docx, .zip" required>
        </div>
        <button type="submit">Upload</button>
    </form>
    <div class="file-list" id="fileList">
        <!-- File list will be populated dynamically -->
    </div>

    <?php
    if (!empty($successMessage)) {
        echo '<div class="message success-message">' . htmlspecialchars($successMessage) . '</div>';
        echo '<div class="file-list">';
        foreach ($fileUrls as $fileUrl) {
            echo '<div class="file-item">';
            echo '<a class="file-link" href="' . htmlspecialchars($fileUrl) . '">' . htmlspecialchars(basename($fileUrl)) . '</a>';
            echo '</div>';
        }
        echo '</div>';
    } elseif (isset($_SESSION['error'])) {
        echo '<div class="message error-message">' . htmlspecialchars($_SESSION['error']) . '</div>';
        unset($_SESSION['error']); // Clear error message after displaying
    }
    ?>
</div>
    <script>
        const dropArea = document.getElementById('dropArea');
        const fileInput = document.getElementById('fileInput');
        const dropMessage = document.getElementById('dropMessage');
        const uploadForm = document.getElementById('uploadForm');
        const fileList = document.getElementById('fileList');

        // Prevent default behaviors for drag-and-drop events
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            dropArea.addEventListener(eventName, preventDefaults, false);
            document.body.addEventListener(eventName, preventDefaults, false);
        });

        // Highlight drop area on drag enter
        ['dragenter', 'dragover'].forEach(eventName => {
            dropArea.addEventListener(eventName, highlight, false);
        });

        // Unhighlight drop area on drag leave
        ['dragleave', 'drop'].forEach(eventName => {
            dropArea.addEventListener(eventName, unhighlight, false);
        });

        // Handle file drop
        dropArea.addEventListener('drop', handleDrop, false);

        // Handle file input change
        fileInput.addEventListener('change', handleFileInputChange);

        // Clicking on drop area triggers file input click
        dropArea.addEventListener('click', () => {
            fileInput.click();
        });

        function preventDefaults(e) {
            e.preventDefault();
            e.stopPropagation();
        }

        function highlight() {
            dropArea.classList.add('hover');
        }

        function unhighlight() {
            dropArea.classList.remove('hover');
        }

        function handleDrop(e) {
            const dt = e.dataTransfer;
            const files = dt.files;

            if (files.length) {
                fileInput.files = files;
                updateDropMessage(files.length + ' file(s) selected');
                showFileList(files);
            }
        }

        function handleFileInputChange() {
            const files = fileInput.files;
            if (files.length) {
                updateDropMessage(files.length + ' file(s) selected');
                showFileList(files);
            } else {
                updateDropMessage('Drag & Drop your files here or click to select files');
                fileList.innerHTML = ''; // Clear file list if no files selected
            }
        }

        function updateDropMessage(message) {
            dropMessage.textContent = message;
        }

        function showFileList(files) {
            fileList.innerHTML = ''; // Clear existing file list
            Array.from(files).forEach(file => {
                const fileItem = document.createElement('div');
                fileItem.classList.add('file-item');
                const fileLink = document.createElement('a');
                fileLink.classList.add('file-link');
                fileLink.href = '#'; // Set a placeholder href
                fileLink.textContent = file.name;
                fileLink.onclick = () => {
                    copyToClipboard(fileLink.href);
                    return false;
                };
                fileItem.appendChild(fileLink);
                fileList.appendChild(fileItem);
            });
        }

       function copyToClipboard(text) {
    const elem = document.createElement('textarea');
    elem.value = text;
    document.body.appendChild(elem);
    elem.select();
    document.execCommand('copy');
    document.body.removeChild(elem);
    alert('Link copied to clipboard: ' + text);
}

    </script>
    <footer>
        &copy; <?php echo date('Y'); ?> SBA Awards. All Rights Reserved.
    </footer>
</body>
</html>