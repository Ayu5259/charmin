<?php
require_once 'config/session.php';

// Clear all session variables
session_unset();
session_destroy();

// Redirect to home page
header('Location: index.php');
exit();
?>