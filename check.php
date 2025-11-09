<?php
// Get the full request URI
$currentUrl = $_SERVER['REQUEST_URI'];

// Check if the current URL is exactly "/ctf/pages/" or "/ctf/pages"
if ($currentUrl === '/ctf/pages/' || $currentUrl === '/ctf/pages/') {
    // Redirect to 404.php
    header("Location: 404.php");
    exit();
}


?>
