<?php
// Check if mod_rewrite is loaded
if (in_array('mod_rewrite', apache_get_modules())) {
    echo "mod_rewrite module is loaded<br>";
} else {
    echo "mod_rewrite module is NOT loaded<br>";
}

// Check server software
echo "Server Software: " . $_SERVER['SERVER_SOFTWARE'] . "<br>";

// Check if .htaccess is being processed
if (isset($_SERVER['REDIRECT_URL'])) {
    echo "Redirect URL: " . $_SERVER['REDIRECT_URL'] . "<br>";
}

// Show all server variables related to rewrite
foreach ($_SERVER as $key => $value) {
    if (strpos($key, 'REDIRECT') !== false) {
        echo "$key: $value<br>";
    }
}
?>