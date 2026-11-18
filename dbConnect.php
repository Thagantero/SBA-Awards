<?php
// dbConnect.php

$servername = (getenv('SBA_DB_HOST') ?: 'localhost');
$username = (string) getenv('SBA_DB_USER');
$password = (string) getenv('SBA_DB_PASSWORD');
$database = (string) getenv('SBA_DB_NAME');

// Connect to the database
$mysqli = new mysqli($servername, $username, $password, $database);

// Check connection
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}
?>
