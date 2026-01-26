<?php
// Check if mod_rewrite is enabled
if (in_array('mod_rewrite', apache_get_modules())) {
    echo "mod_rewrite is enabled";
} else {
    echo "mod_rewrite is NOT enabled";
}

// Also test a simple redirect
echo "<br><br>Testing rewrite rules...<br>";
echo "<a href='signup'>Try signup link</a><br>";
echo "<a href='login'>Try login link</a><br>";
?>