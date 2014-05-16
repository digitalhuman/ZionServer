<?php

/**
 * Description of Zion
 * 
 * @author Victor Angelier <vangelier@hotmail.com>
 * @copyright 2014 Ignite the Future
 * 
 */

class Zion extends SSLServer implements iZionServer {
    
    protected $clients = array(); //Client storage
    protected $master = null; //Master socket
    protected $command = null; //Command handler
    protected $read = array();
    protected $crypto = null;
    protected $channels = null;

    /**
     * Construct
     * @param type $host
     * @param type $port
     */
    public function __construct($host = "0.0.0.0", $port = "994") {
        $this->load_helper("logfile");
        
        //Get our crypto
        $this->crypto = new Crypto();
        
        //Channel store
        $this->channels = new Channels();
        
        //Construct the SSL server
        parent::__construct($host, $port);        
    }
    
    
    /**
     * Disconnect
     */
    function __destruct()
    {
        //Log if possible
        log_message("Server shutdown");
        
        foreach($this->clients as $client) {            
            socket_close($client);
        }
        socket_close($this->master);
        socket_shutdown($this->master, 2);
    }
    
    /**
     * Load core helper file
     * @param type $name
     */
    public function load_helper($name = ""){
        if($name !== ""){
            require_once $name.".php";
        }
    }
    
    /**
     * Start listening
     */
    public function run(){
        set_time_limit(0);
        ob_implicit_flush();
        
        //Start listening
        $this->master = $this->open_socket();        
        
        echo "Server started and running. Waiting for connections.\n";
        log_message("Server started and running. Waiting for connections.");
        
        while(true){
            
            $this->read = array();
            $this->read[] = $this->master;
            $this->read = array_merge($this->read, $this->clients);
            
            // Set up a blocking call to socket_select
            if(socket_select($this->read, $write = NULL, $except = NULL, $tv_sec = 5) === FALSE){
                echo socket_strerror(socket_last_error())."\n";
                continue;
            }
            
            $this->acceptConnections();            
        }
        
        socket_close($this->master);
        socket_shutdown($this->master, 2);
        exit();
    }
    
    /**
     * Start accepting incomming connections
     */
    public function acceptConnections(){
        //Check
        if(in_array($this->master, $this->read)){
            
            if(($client_socket = socket_accept($this->master)) === false) {
                echo "Socket_accept() failed: reason: " . socket_strerror(socket_last_error($this->master)) . "\n";
            }

            //Store our client
            $this->clients[] = $client_socket;
            
            //New client connected
            $this->onClientConnect($client_socket);

            //Send welcome message
            if($this->welcomeMessage() !== false){
                socket_write($client_socket, $this->welcomeMessage());
            }
            
        }

        //Reading part
        foreach($this->clients as $k => $client_socket){
            if(in_array($client_socket, $this->read)){
                
                //Read client input
                if(false === ($buf = @socket_read($client_socket, 2048, PHP_NORMAL_READ))) {
                    //Client disconnected. Free resources
                    $this->onClientDisconnect($k, $client_socket);
                    break;
                }
                if(!$buf = trim($buf)) {
                    continue;
                }
                if($buf == 'quit') {
                    $this->onClientDisconnect($k, $client_socket);
                    break;
                }
                if($buf == 'shutdown') {
                    $this->onClientDisconnect($k, $client_socket);
                    break 2;
                }

                //Decode the json string
                $message = json_decode($buf, true);
                echo $buf."\n";
                
                //Add user to the channel object and handle the command
                $this->channels->add_user($message['channel'], $k, $k);
                $message["from"] = $k;
                $this->onCommandReceived($message);
            }
        }
    }

    /**
     * On command received handlerr
     * @param type $command
     */
    public function onCommandReceived($command) {
        $handler = new CommandHandler($this->channels, $this->clients);   
        $response = $handler->handleCommand($command);
                
        //If empty dont do anything
        if($response !== "\n"){
            
            $message = "[{$this->channels->get_nickname_by_channel_key($command['from'], $command["channel"])}]: {$response}\n";
            switch($command["message_type"]){

                case "C" ://We have a channel wide command
                    //Send a message to the users in this channel
                    $this->send_channel_message($command["channel"], $message);
                    break;

                case "P" ://We have a private message/command
                    $this->send_channel_user($message, $command, $command["user"]);
                    break;
            }
        }
    }
    
    /**
     * Send a message to a specific user in a specific channel
     * 
     * @param type $message
     * @param type $command
     * @param type $k
     */
    public function send_channel_user($message, $command, $k){
        $channel_users = $this->channels->get_channel_users($command['channel']);
        if(isset($this->clients[$k]) && isset($channel_users[$k])){
            socket_write($this->clients[$k], $message);
        }else{
            socket_write($this->clients[$command["from"]], "User is not in this channel\n");
        }
    }

    /**
     * Send message to 1 channel specific
     * 
     * @param type $channel
     * @param type $message
     */
    public function send_channel_message($channel, $message){
        foreach($this->channels->get_channel_users($channel) as $k => $user){
            if(isset($this->clients[$k])){
                socket_write($this->clients[$k], $message);
            }
        }
    }
    
    /**
     * On client disconnect event
     * @param type $socket
     */
    public function onClientDisconnect($k, $socket) {
        //Close the socket
        unset($this->read[$k]);
        unset($this->clients[$k]);
        
        //Delete from all channels. Client is disconnected
        $this->channels->delete_user($k);
        
        if(@socket_getpeername($socket, $address) !== false){

            //Remove this client from our list
            $this->send_message("Client {$address} disconnected.\n");
            echo "Client {$address} disconnected.\n";
            
        }else{
            log_message("Can't find ipaddress of this client.");
        }
        
        @socket_close($socket);
        @socket_shutdown($socket);
    }
    
    /**
     * On client connect event
     * @param type $socket
     */
    public function onClientConnect($socket) {
        if(socket_getpeername($socket, $address) !== false){
            
            //Try to set keepalive
            if(socket_set_option($socket, SOL_SOCKET, SO_KEEPALIVE, 1) === FALSE){
                echo "Socket_set_option() failed: reason: " . socket_strerror(socket_last_error($socket)) . "\n";
            }
            
            //Send message to all clients
            $this->send_message("Client: {$address} connected.\n");
            echo "Client {$address} connected.\n";
            
        }else{
            log_message("Can't find ipaddress of this client.");
        }
    }
    
    /**
     * Send message to all connected clients
     * @param type $data
     */
    public function send_message($data){
        foreach($this->clients as $client_socket){
            socket_write($client_socket, $data."\n");
        }
    }
    
    /**
     * Get server welcome message
     * @return boolean
     */
    private function welcomeMessage(){
        if(file_exists(APPPATH.'/welcome.txt')){
            return file_get_contents(APPPATH."/welcome.txt");
        }else{
            log_message("No welcome.txt found in ".APPPATH);
            return false;
        }
    }

}
