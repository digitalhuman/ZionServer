<?php

/**
 * Description of iZion
 * 
 * @author Victor Angelier <vangelier@hotmail.com>
 * @copyright 2014 Ignite the Future
 * 
 */

interface iZionServer {
    
    public function onClientConnect($socket);
    public function onClientDisconnect($key, $socket);
}
