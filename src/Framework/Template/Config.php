<?php
// Used for security functions
define("SECURITY_HASH", "InsertYourSecurityHashHere");
// Base path at site url
define("BASEPATH", "/");
// Base folder when for application
define("BASE_APPLICATION_FOLDER", "/Api/");
// Relative path to routing file
define("ROUTING_PATH", "App/Routing.php");

// Determine if its production server or local
if(stripos($_SERVER['SERVER_NAME'], 'local') !== false) {
	define("SERVER_LIVE", false);
} else {
	define("SERVER_LIVE", true);
}

?>