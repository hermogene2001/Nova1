<?php
// Start session only if it's not already active
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
session_unset();
session_destroy();
header("Location: ../index.php");
exit;
?>
