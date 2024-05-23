<?php
$target_dir = "uploads/";

if (file_exists($target_dir)) {
    echo "The directory 'uploads' exists.";
} else {
    echo "The directory 'uploads' does not exist.";
}
?>
