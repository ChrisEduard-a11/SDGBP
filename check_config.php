<?php
echo "<h3>PHP Configuration Debug</h3>";
echo "Loaded Configuration File: " . php_ini_loaded_file() . "<br>";
echo "Zip Extension Loaded: " . (extension_loaded('zip') ? "<span style='color:green'>YES</span>" : "<span style='color:red'>NO</span>") . "<br>";
echo "ZipArchive Class Exists: " . (class_exists('ZipArchive') ? "<span style='color:green'>YES</span>" : "<span style='color:red'>NO</span>") . "<br>";
echo "<hr>";
echo "<b>Note:</b> If 'Zip Extension Loaded' is NO, please <b>Restart Apache</b> in your XAMPP Control Panel.";
