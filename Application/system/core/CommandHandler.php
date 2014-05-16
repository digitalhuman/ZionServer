<?php
/**
 * Description of Command
 * 
 * @author Victor Angelier <vangelier@hotmail.com>
 * @copyright 2014 Ignite the Future
 * 
 */

class CommandHandler {
    
    protected $action = null;
    
    public function __construct() {
        $this->action = new Action();
    }
    
    /**
     * Handle the command
     * @param type $command
     */
    public function handleCommand($command = ""){

        //Check what command we have
        switch($command){
            
            //First some server wide commands
            case "list" :
                $response = "list\n";
                break;
            
            //Ping keep alive command
            case "ping" :
                $response = "pong\n";
                break;
                
            default :
                $response = $this->action->get_command($command);
                break;
        }
        
        return $this->action->get_command($command);;
    }
}
