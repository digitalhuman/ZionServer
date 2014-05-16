<?php

/**
 * Description of Channels
 * 
 * @author Victor Angelier <vangelier@hotmail.com>
 * @copyright 2014 Ignite the Future
 * 
 */

class Channels {
    
    protected $list = array();
    
    public function __construct() {
        
    }
    
    /**
     * Add user to the channel
     * 
     * @param type $channel
     * @param type $client_socket
     * @param type $key
     */
    public function add_user($channel, $client_socket, $key){
        if(!isset($this->list[$channel])){
            $this->list[$channel] = array();
        }
        if(!in_array($key, $this->list[$channel])){
            $this->list[$channel][$key] = $client_socket;
        }
    }
    
    /**
     * Delete this client from all channels
     * 
     * @param type $key
     */
    public function delete_user($key){
        foreach($this->list as $channel => $client){
            foreach($this->list[$channel] as $k => $socket){
                if($key === $k){
                    unset($this->list[$channel][$k]);
                }
            }
        }
    }
    
    /**
     * Get all users in this channel
     * 
     * @param type $channel
     * @return type
     */
    public function get_channel_users($channel = "public"){
        return $this->list[$channel];
    }
}
