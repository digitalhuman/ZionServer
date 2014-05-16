<?php
/**
 * Description of Command
 * 
 * @author Victor Angelier <vangelier@hotmail.com>
 * @copyright 2014 Ignite the Future
 * 
 */

class CommandHandler {
    
    protected $channels = null;
    protected $crypto = null;
    protected $clients = null;
    
    public function __construct($channels = null, $clients = null) {
        $this->channels = $channels;
        $this->clients = $clients;
        $this->crypto = new Crypto();
    }
    
    /**
     * Handle the command
     * @param type $command
     */
    public function handleCommand($command = ""){
        
        $message = $this->crypto->decrypt($command["message"]); 
        //Check what command we have
        switch($command["command"]){
            
            //First some server wide commands
            case "/setnick" :
                $this->channels->set_nickname($command["from"], $message, $command["channel"]);
                $response = "Nickname set to: {$message}";
                break;
            
            case "/join" :
                $this->channels->join_channel($message, $command["from"]);
                $response = "Joined channel {$message}\n";
                break;
            
            case "/leave" :
                $this->channels->leave_channel($message, $command["from"]);
                $response = "Left channel {$message}\n";
                break;
            
            case "/msg" :
                $nickname = substr($message,0,1);
                if($nickname === "@"){

                    $nickname = rtrim(substr($message, 1, strpos($message, " ", 1)));
                    $k = $this->channels->get_keyindex_by_nickname($nickname, $command["channel"]);
                        
                    if(isset($this->clients[$k])){                        
                        //Send the private message
                        $message = str_replace("@{$nickname}", "", $message);
                        socket_write($this->clients[$k], "[".$this->channels->get_nickname_by_channel_key($command["from"], $command["channel"])."]:" .
                                $message. "\n");
                    }
                    
                    $response = "\n";
                }else{
                    $response = $this->crypto->decrypt($command["message"]);
                }
                break;
                
            //Ping keep alive command
            case "/ping" :
                $response = "pong\n";
                break;
                
            default :
                $response = $message;
                break;
        }
        
        return $response;
    }
}
