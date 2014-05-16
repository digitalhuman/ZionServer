<?php
/**
 * Description of SSLServer
 * 
 * @author Victor Angelier <vangelier@hotmail.com>
 * @copyright 2014 Ignite the Future
 * 
 */

class SSLServer {
    
    private $context = null;
    protected $socket = null;
    private $port = 994;
    private $host = "localhost";
    
    public function __construct($host = 0, $port = 0) {
        //Create context
        if($this->context === null){
            
            if(PEM_FILENAME !== "" && PEM_PASSPHRASE !== ""){

                //Create context
                $this->context = stream_context_create();
                //Setup the SSL Options
                stream_context_set_option($this->context, 'ssl', 'local_cert', PEM_FILENAME);  // Our SSL Cert in PEM format
                stream_context_set_option($this->context, 'ssl', 'passphrase', PEM_PASSPHRASE); // Private key Password
                stream_context_set_option($this->context, 'ssl', 'allow_self_signed', true);
                stream_context_set_option($this->context, 'ssl', 'verify_peer', false);
            
            }else{
                log_message(__FILE__.":".__LINE__.": PEM filename and/or password not defined.");
            }
            
        } 
        $this->host = $host;
        $this->port = $port;
    }
    
    /**
     * Create SSL server socket
     * @return socket
     */
    public function open_socket(){
        //Create the server socket
        if($this->socket === null){
            
            //Create socket
            if (($this->socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP)) === false) {
                echo "Socket_create() failed: reason: " . socket_strerror(socket_last_error()) . "\n";
            }
            
            if(socket_set_option($this->socket, SOL_SOCKET, SO_KEEPALIVE, 1) === FALSE){
                echo "Socket_set_option() failed: reason: " . socket_strerror(socket_last_error($this->socket)) . "\n";
            }
            
            if(socket_set_option($this->socket, SOL_SOCKET, SO_REUSEADDR, 1) === FALSE){
                echo "Socket_set_option() failed: reason: " . socket_strerror(socket_last_error($this->socket)) . "\n";
            }

            if (socket_bind($this->socket, $this->host, $this->port) === false) {
                echo "Socket_bind() failed: reason: " . socket_strerror(socket_last_error($this->socket)) . "\n";
            }

            if (socket_listen($this->socket, 5) === false) {
                echo "Socket_listen() failed: reason: " . socket_strerror(socket_last_error($this->socket)) . "\n";
            }
            
        }
        return $this->socket;
    }
}
