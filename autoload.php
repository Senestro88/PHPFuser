<?php

// THE ROOT DIRECTORY
$PHPFUSER_ROOT_DIR = str_replace("\\", DIRECTORY_SEPARATOR, dirname('' . __FILE__ . '') . DIRECTORY_SEPARATOR);

$PHPFUSER_OS = strtolower(PHP_OS);
if (substr($PHPFUSER_OS, 0, 3) === "win") {
    $PHPFUSER_OS = "Windows";
} elseif (substr($PHPFUSER_OS, 0, 4) == "unix") {
    $PHPFUSER_OS = "Unix";
} elseif (substr($PHPFUSER_OS, 0, 5) == "linux") {
    $PHPFUSER_OS = "Linux";
} else {
    $PHPFUSER_OS = "Unknown";
}

// THE 'SRC' DIRECTORY
$PHPFUSER_SRC_FIR = $PHPFUSER_ROOT_DIR . 'src' . DIRECTORY_SEPARATOR;

// THE MAIN CONSTANT
if (!defined("PHPFUSER")) {
    define('PHPFUSER', array(
        "OS" => $PHPFUSER_OS,
        "DIRECTORIES" => array(
            "ROOT" => $PHPFUSER_ROOT_DIR,
            "SRC" => $PHPFUSER_SRC_FIR,
            "DATA" => $PHPFUSER_ROOT_DIR . "assets" . DIRECTORY_SEPARATOR . "data" . DIRECTORY_SEPARATOR,
            "IMAGES" => $PHPFUSER_ROOT_DIR . "assets" . DIRECTORY_SEPARATOR . "images" . DIRECTORY_SEPARATOR,
            "LIBRARIES" => $PHPFUSER_ROOT_DIR . "assets" . DIRECTORY_SEPARATOR . "libraries" . DIRECTORY_SEPARATOR,
            "PLUGINS" => $PHPFUSER_ROOT_DIR . "assets" . DIRECTORY_SEPARATOR . "plugins" . DIRECTORY_SEPARATOR,
            "FONTS" => $PHPFUSER_ROOT_DIR . "assets" . DIRECTORY_SEPARATOR . "fonts" . DIRECTORY_SEPARATOR,
        ),
    ));
}

// LOAD THE COMPOSER IF THE DIRECTORY IS FOUND
$PHPFUSER_VENDOR_DIR = $PHPFUSER_ROOT_DIR . 'vendor' . DIRECTORY_SEPARATOR;
if (is_dir($PHPFUSER_VENDOR_DIR) && is_readable($PHPFUSER_VENDOR_DIR)) {
    require_once $PHPFUSER_VENDOR_DIR . 'autoload.php';
}

// THE AUTOLOADER CALLBACK
if (!function_exists("PHPFUSER_AUTOLOADER")) {

    function PHPFUSER_AUTOLOADER(string $classname) {
        $namespace = 'PHPFuser';
        // Check if the class belongs to the PHPFuser namespace
        if (strpos($classname, $namespace, 0) === 0) {
            // Get the relative class path by removing the namespace
            $RELATIVE_CLASS = str_replace("$namespace\\", '', $classname);
            $RELATIVE_PATH = str_replace('\\', DIRECTORY_SEPARATOR, $RELATIVE_CLASS) . '.php';
            // Get the "src" directory and the OS type
            $PHPFUSER_SRC_FIR = PHPFUSER['DIRECTORIES']['SRC'];
            $PHPFUSER_OS = strtolower(PHPFUSER['OS']);
            // Build the absolute class path
            $CLASS_PATH = $PHPFUSER_SRC_FIR . DIRECTORY_SEPARATOR . $RELATIVE_PATH;
            // If the OS is Unix, Linux, or unknown, ensure the path starts with a slash
            if (in_array($PHPFUSER_OS, ['unix', 'linux', 'unknown'])) {
                $CLASS_PATH = DIRECTORY_SEPARATOR . ltrim($CLASS_PATH, DIRECTORY_SEPARATOR);
            }
            // Check if the file exists and is readable, then require it
            if (is_file($CLASS_PATH) && is_readable($CLASS_PATH)) {
                require_once $CLASS_PATH;
            }
        }
    }
}
// UNREGISTER THE AUTOLOADER IF REGISTERED
spl_autoload_unregister("PHPFUSER_AUTOLOADER");
// REGISTER THE AUTOLOADER IF NOT REGISTERED
spl_autoload_register("PHPFUSER_AUTOLOADER", true, true);
