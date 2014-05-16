<?php
/**
 * Description of client
 * 
 * @author Victor Angelier <vangelier@hotmail.com>
 * @copyright 2014 Ignite the Future
 * 
 */
define('PEM_FILENAME', __DIR__."/ssl_server.crt"); //SSL Server PEM file
$ip="localhost";     //Set the TCP IP Address to connect too
$port="994";        //Set the TCP PORT to connect too
$command="hi";       //Command to run
require_once("Crypto.php");

/* Get the port for the WWW service. */
$service_port = $port;

/* Get the IP address for the target host. */
$address = $ip;

/* Create a TCP/IP socket. */
$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
if ($socket === false) {
    echo "socket_create() failed: reason: " . socket_strerror(socket_last_error()) . "\n";
} else {
    echo "OK.\n";
}

echo "Attempting to connect to '$address' on port '$service_port'...";
$result = socket_connect($socket, $address, $service_port);
if ($result === false) {
    echo "socket_connect() failed.\nReason: ($result) " . socket_strerror(socket_last_error($socket)) . "\n";
} else {
    echo "OK.\n";
}

$crypt = new Crypto();
$command = array(
    "command"   =>  "/setnick",
    "channel"   => "public",
    "message"   =>  $crypt->encrypt("DigitalHuman"),
    "message_type"   =>  "C"
);
$in = json_encode($command)."\n";

$out = '';

echo "Sending HTTP HEAD request...";
socket_write($socket, $in, strlen($in));
echo "OK.\n";
echo socket_read($socket, 2048)."\n";

$command = array(
    "command"   =>  "/msg",
    "channel"   => "private",
    "message"   =>  $crypt->encrypt("Hoi daar"),
    "message_type"   =>  "C"
);
$in = json_encode($command)."\n";
echo $in."\n";

socket_write($socket, $in, strlen($in));

echo "Reading response:\n\n";
while ($out = socket_read($socket, 2048)) {
    echo $out;
}

echo "Closing socket...";
socket_close($socket);
echo "OK.\n\n";