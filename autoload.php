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
if (!function_exists("PHPFuserAutoloader")) {
    function PHPFuserAutoloader(string $classname) {
        $split = \explode("\\", $classname);
        $namespace = 'PHPFuser';
        // Check if the class belongs to the PHPFuser namespace
        if (strpos($classname, $namespace, 0) === 0 && $split[0] === $namespace) {
            $className = str_replace("$namespace\\", '', $classname);
            $relativeName = str_replace('\\', DIRECTORY_SEPARATOR, $className) . '.php';
            require_once PHPFUSER['DIRECTORIES']['SRC'] . $relativeName;
        }
    }
}
// UNREGISTER THE AUTOLOADER IF REGISTERED
spl_autoload_unregister("PHPFuserAutoloader");
// REGISTER THE AUTOLOADER IF NOT REGISTERED
spl_autoload_register("PHPFuserAutoloader", true, true);
