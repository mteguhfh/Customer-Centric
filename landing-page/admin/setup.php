<?php
/**
 * Setup Tool - Password Generator for Centric CMS
 *
 * 1. Upload this file to your server
 * 2. Run: php setup.php your_new_password
 * 3. Copy the output hash
 * 4. Replace the $password_hash value in admin/index.php
 * 5. Delete this file from the server
 *
 * Default password: centricadmin
 */

if (php_sapi_name() !== 'cli') {
    die('Run this script from CLI: php setup.php <password>');
}

if (!isset($argv[1])) {
    die("Usage: php setup.php <new_password>\n");
}

$password = $argv[1];
$hash = password_hash($password, PASSWORD_BCRYPT);

echo "Password: $password\n";
echo "Hash: $hash\n";
echo "\nCopy this hash and update \$password_hash in admin/index.php\n";
