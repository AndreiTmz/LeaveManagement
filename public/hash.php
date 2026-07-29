<?php
// hash.php
$password = 'Password123!';
$hash = password_hash($password, PASSWORD_BCRYPT);

echo "<h1>Clean Hash Generator</h1>";
echo "<p><strong>Password:</strong> " . htmlspecialchars($password) . "</p>";
echo "<p><strong>Copy this exact hash:</strong></p>";
echo "<input type='text' value='" . $hash . "' style='width: 100%; padding: 10px; font-family: monospace;' readonly onclick='this.select();'>";

// this file was used only to generate a clean hash for the password "Password123!" 
// to be used in the database as default password for default employees.
