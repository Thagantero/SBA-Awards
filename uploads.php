<?php
session_start();

// Start session and check authentication
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header("Location: login.php");
    exit;
}

// Include admin Header
include "admin_header.php";

// Function to get metadata for a file
function getFileMetadata($filename, $metadata) {
    return isset($metadata[$filename]) ? $metadata[$filename] : [];
}

// Define upload directory and metadata file
$uploadDir = "uploads/";
$metadataFile = "metadata.json";

// Read metadata
$metadata = [];
if (file_exists($metadataFile)) {
    $metadata = json_decode(file_get_contents($metadataFile), true);
}

// Get list of files
$files = array_diff(scandir($uploadDir), array('..', '.'));

// Sorting options
$sortOptions = [
    'date_desc' => 'Date (Most Recent)',
    'date_asc' => 'Date (Oldest)',
    'name_asc' => 'Name (A-Z)',
    'name_desc' => 'Name (Z-A)',
];

// Default sorting by date_desc
$sort = 'date_desc';

// Determine sorting order
if (isset($_GET['sort']) && array_key_exists($_GET['sort'], $sortOptions)) {
    $sort = $_GET['sort'];
    switch ($sort) {
        case 'date_asc':
            usort($files, function($a, $b) use ($uploadDir) {
                return filemtime($uploadDir . $a) - filemtime($uploadDir . $b);
            });
            break;
        case 'name_asc':
            sort($files);
            break;
        case 'name_desc':
            rsort($files);
            break;
        default: // date_desc
            rsort($files);
            break;
    }
}

// Handle form submission for editing metadata or deleting file
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['action'])) {
        if ($_POST['action'] == 'edit_metadata') {
            $file = $_POST['file'];
            $newFileName = $_POST['new_file_name'];
            $alt_text = $_POST['alt_text'];
            $caption = $_POST['caption'];
            $description = $_POST['description'];

            // Rename file if new name is different
            if ($newFileName !== $file) {
                $oldFilePath = $uploadDir . $file;
                $newFilePath = $uploadDir . $newFileName;

                if (file_exists($oldFilePath)) {
                    rename($oldFilePath, $newFilePath);
                }

                // Update metadata with new file name
                $metadata[$newFileName] = $metadata[$file];
                unset($metadata[$file]);
            }

            // Update metadata fields
            $metadata[$newFileName] = [
                'alt_text' => $alt_text,
                'caption' => $caption,
                'description' => $description,
            ];

            // Save updated metadata to file
            file_put_contents($metadataFile, json_encode($metadata, JSON_PRETTY_PRINT));

            // Redirect to avoid resubmission on refresh
            header("Location: uploads.php");
            exit;
        } elseif ($_POST['action'] == 'delete_file') {
            $fileToDelete = $_POST['file'];

            // Delete file from server
            if (file_exists($uploadDir . $fileToDelete)) {
                unlink($uploadDir . $fileToDelete);
                // Remove metadata entry if exists
                if (isset($metadata[$fileToDelete])) {
                    unset($metadata[$fileToDelete]);
                    file_put_contents($metadataFile, json_encode($metadata, JSON_PRETTY_PRINT));
                }
            }

            // Redirect to avoid resubmission on refresh
            header("Location: uploads.php");
            exit;
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Uploaded Files</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" integrity="sha512-..." crossorigin="anonymous" />
    <script src="https://kit.fontawesome.com/87ab693041.js" crossorigin="anonymous"></script>
    <style>
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
        h1 {
            text-align: center;
            color: #333;
        }
        .file-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        .file-item {
            border: 1px solid #ccc;
            border-radius: 5px;
            padding: 10px;
            text-align: center;
            background-color: #f9f9f9;
            position: relative;
        }
        .file-link {
            display: block;
            margin-bottom: 10px;
            text-decoration: none;
            color: #007bff;
            word-wrap: break-word;
        }
        .file-preview {
            margin-bottom: 10px;
            max-width: 100%;
            max-height: 150px;
            object-fit: cover;
            border-radius: 5px;
        }
        .file-placeholder {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 150px;
            font-size: 50px;
            color: #ccc;
            border-radius: 5px;
        }
        .edit-btn,
        .delete-btn {
            background-color: #007bff;
            color: #fff;
            border: none;
            padding: 5px 10px;
            border-radius: 5px;
            cursor: pointer;
            margin-top: 5px;
            display: inline-block;
            transition: background-color 0.3s ease;
        }
        .edit-btn:hover,
        .delete-btn:hover {
            background-color: #0056b3;
        }
        .edit-form {
            display: none;
            margin-top: 10px;
            padding: 10px;
            background-color: #f0f0f0;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        .edit-form input[type="text"],
        .edit-form textarea {
            width: calc(100% - 22px);
            padding: 5px;
            margin-bottom: 5px;
            border: 1px solid #ccc;
            border-radius: 3px;
            box-sizing: border-box;
        }
        .edit-form button,
        .delete-form button {
            padding: 5px 10px;
            background-color: #007bff;
            color: #fff;
            border: none;
            border-radius: 3px;
            cursor: pointer;
        }
        .edit-form button:hover,
        .delete-form button:hover {
            background-color: #0056b3;
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
    </style>
</head>
<body>

<div class="container">


    <!-- Sort Dropdown -->
    <div>
        <label for="sort">Sort By:</label>
        <select id="sort" name="sort" onchange="location = this.value;">
            <?php foreach ($sortOptions as $key => $option) {
                $selected = ($key == $sort) ? 'selected' : '';
                echo '<option value="?sort=' . htmlspecialchars($key) . '" ' . $selected . '>' . htmlspecialchars($option) . '</option>';
            } ?>
        </select>
    </div>

    <!-- File Grid -->
    <div class="file-grid">
        <?php foreach ($files as $file) {
            $fileMetadata = getFileMetadata($file, $metadata);
            $filePath = $uploadDir . $file;
            $fileExt = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
            $hasPreview = in_array($fileExt, ['jpg', 'jpeg', 'png', 'gif', 'webp']);
        ?>

        <div class="file-item">
            <form action="uploads.php" method="post" class="edit-form">
                <input type="hidden" name="action" value="edit_metadata">
                <input type="hidden" name="file" value="<?php echo htmlspecialchars($file); ?>">
                <input type="text" name="new_file_name" value="<?php echo htmlspecialchars($file); ?>" style="width: 100%; margin-bottom: 10px;" required>
                <label for="alt_text_<?php echo htmlspecialchars($file); ?>">Alt Text:</label><br>
                <input type="text" id="alt_text_<?php echo htmlspecialchars($file); ?>" name="alt_text" value="<?php echo htmlspecialchars($fileMetadata['alt_text'] ?? ''); ?>"><br>
                <label for="caption_<?php echo htmlspecialchars($file); ?>">Caption:</label><br>
                <input type="text" id="caption_<?php echo htmlspecialchars($file); ?>" name="caption" value="<?php echo htmlspecialchars($fileMetadata['caption'] ?? ''); ?>"><br>
                <label for="description_<?php echo htmlspecialchars($file); ?>">Description:</label><br>
                <textarea id="description_<?php echo htmlspecialchars($file); ?>" name="description"><?php echo htmlspecialchars($fileMetadata['description'] ?? ''); ?></textarea><br>
                <button type="submit" class="edit-btn">Save</button>
            </form>

            <a class="file-link" href="<?php echo htmlspecialchars($filePath); ?>" target="_blank"><?php echo htmlspecialchars($file); ?></a>
            
            <?php if ($hasPreview) { ?>
                <img class="file-preview" src="<?php echo htmlspecialchars($filePath); ?>" alt="Preview">
            <?php } else { ?>
                <div class="file-placeholder">
                    <i class="fas fa-file"></i>
                </div>
            <?php } ?>

            <button class="edit-btn" onclick="toggleEditForm('<?php echo 'edit-form-' . htmlspecialchars($file); ?>')">Edit Metadata</button>
            <form action="uploads.php" method="post" onsubmit="return confirm('Are you sure you want to delete this file?');">
                <input type="hidden" name="action" value="delete_file">
                <input type="hidden" name="file" value="<?php echo htmlspecialchars($file); ?>">
                <button type="submit" class="delete-btn">Delete File</button>
            </form>
        </div>

        <?php } ?>
    </div>
</div>

<script>
    function toggleEditForm(formId) {
        const form = document.getElementById(formId);
        form.style.display = (form.style.display === 'none' || form.style.display === '') ? 'block' : 'none';
    }
</script>

<footer>
    &copy; <?php echo date('Y'); ?> SBA Awards. All Rights Reserved.
</footer>

</body>
</html>
