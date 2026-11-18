<?php
// sessionUtils.php


// Function to check if a user is logged in
function isUserLoggedIn() {
    return isset($_SESSION['loggedin']) && $_SESSION['loggedin'] == true;
}

// Function to redirect users to a login page if they are not logged in
function redirectToLoginPage() {
    if (!isUserLoggedIn()) {
        header("Location: login.php");
        exit();
    }
}

// Function to redirect users to a home page if they are already logged in
function redirectToHomePage() {
    if (isUserLoggedIn()) {
        header("Location: home.php");
        exit();
    }
}
?>
