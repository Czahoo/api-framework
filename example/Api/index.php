<?php
error_reporting(E_ALL & ~E_NOTICE);
//ini_set("display_errors", true);
//ini_set("display_startup_errors", true);
date_default_timezone_set("Europe/Warsaw");
setlocale(LC_TIME, 'pl_PL');

// Register vendor autoloader
require_once '../vendor/autoload.php';

// Required for storing last request history
session_start();
// Detect api type
Framework::detectContext($_GET['API_TYPE']);
// Translate page request into params
Framework::translateUrl($_GET['URL']);
// Register autoloader and run proper controller
Framework::run();