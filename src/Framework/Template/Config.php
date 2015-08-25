<?php
// Used for security functions
define("SECURITY_HASH", "InsertYourSecurityHashHere");
// Base path at site url
define("BASEPATH", "/");

// Determine if its production server or local
if(stripos($_SERVER['SERVER_NAME'], 'local') !== false) {
	define("SERVER_LIVE", false);
} else {
	define("SERVER_LIVE", true);
}

?>