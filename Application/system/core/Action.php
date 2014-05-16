<?php

/**
 * Description of Actions
 * 
 * @author Victor Angelier <vangelier@hotmail.com>
 * @copyright 2014 Ignite the Future
 * 
 */

class Action {
    
    var $crypto = null;
    
    var $command = array(
        "command" => "msg",
        "channel" => "public",
        "message"   =>  "Encrypted Message",
        "message_type"   =>  "Message type"
    );
    
    public function __construct(){
        $this->crypto = new Crypto();
    }
    
    public function get_command($command = ""){
        $command = json_decode($command, true);  
        return $command;
    }
}
