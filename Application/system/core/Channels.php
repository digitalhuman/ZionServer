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
     * Get Socket KeyIndex by the users nickname in a channel
     * @param type $nickname
     * @param type $channel
     * @return boolean
     */
    public function get_keyindex_by_nickname($nickname = "", $channel = ""){
        foreach($this->list[$channel] as $k => $nick){
            if($nickname === $nick){
                return $k;
            }
        }
        return false;
    }
    
    /**
     * Leave the channel
     * @param type $channel
     * @param type $key
     * @return boolean
     */
    public function leave_channel($channel = "", $key = ""){
        if(isset($this->list[$channel])){
            if(isset($this->list[$channel][$key])){
                unset($this->list[$channel][$key]);
                return true;
            }
        }
        return false;
    }
    
    /**
     * Join / create new channel
     * @param type $channel
     * @param type $key
     * @return boolean
     */
    public function join_channel($channel = "", $key = ""){
        if(!isset($this->list[$channel])){
            $this->list[$channel] = array();
        }
        if(!isset($this->list[$channel][$key])){
            $this->list[$channel][$key] = $this->get_nickname_by_channel_key($key, $channel);
            return true;
        }
        return false;
    }

    /**
     * Get NickName by channel and KeyIndex
     * 
     * @param type $key
     * @param type $chan
     * @return type
     */
    public function get_nickname_by_channel_key($key = "", $chan = "public"){
        foreach($this->list[$chan] as $k => $user){
            if($k === $key){
                return $this->list[$chan][$key];
            }
        }
        return $key;
    }
    
    /**
     * Set nickName
     * @param type $k //Socket Key index
     * @param type $name //Nickname
     * @param type $chan  //Channel name
     */
    public function set_nickname($k, $name = "", $chan = ""){
        foreach($this->list[$chan] as $key => $user){
            if($k === $key){
                $this->list[$chan][$key] = $name;
            }
        }
    }
    
    /**
     * Add user to the channel
     * 
     * @param type $channel
     * @param type $nickname
     * @param type $key
     */
    public function add_user($channel, $nickname, $key){
        if(!isset($this->list[$channel])){
            $this->list[$channel] = array();
        }
        if(!in_array($key, $this->list[$channel])){
            if(!isset($this->list[$channel][$key])){
                $this->list[$channel][$key] = $nickname;
            }
        }
    }
    
    /**
     * Delete this client from all channels
     * 
     * @param type $key
     */
    public function delete_user($key){
        foreach($this->list as $channel => $client){
            foreach($this->list[$channel] as $k => $nickname){
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
