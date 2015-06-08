<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

// set the timezone for all date functions
date_default_timezone_set('UTC');

// include the kirby bootstrapper file
require_once(dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'bootstrap.php');

// include the library testcase file
require_once(__DIR__ . DS . 'testcase.php');