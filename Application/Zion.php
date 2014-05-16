#!/usr/bin/env php
<?php
/**
 * Description of Zion
 * 
 * Entry point of the application
 * 
 * @author Victor Angelier <vangelier@hotmail.com>
 * @copyright 2014 Ignite the Future
 * 
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);

//Define vars
define('APPPATH', __DIR__);
define('PEM_FILENAME', __DIR__."/ssl_server.crt"); //SSL Server PEM file
define('PEM_PASSPHRASE', '$ldkSD09@askAP#'); //PEM password

//Set include paths
set_include_path(get_include_path().PATH_SEPARATOR."/".__DIR__."/system/");
set_include_path(get_include_path().PATH_SEPARATOR."/".__DIR__."/system/core");
set_include_path(get_include_path().PATH_SEPARATOR."/".__DIR__."/system/helpers");
set_include_path(get_include_path().PATH_SEPARATOR."/".__DIR__."/system/interfaces");

//Autoload classes when needed
function __autoload($class = ""){
    if($class !== ""){
        require_once($class.".php");
    }
}


//Start our server
require_once(APPPATH."/system/Zion.php");
$server = new Zion();
$server->run();