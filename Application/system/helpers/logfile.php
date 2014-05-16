<?php
/**
 * Description of logfile
 * 
 * @author Victor Angelier <vangelier@hotmail.com>
 * @copyright 2014 Ignite the Future
 * 
 */
if(!function_exists("log_message")){
    
    function log_message($data){
        if(file_exists(APPPATH."/logs/logfile.txt")){
            file_put_contents(APPPATH."/logs/logfile.txt", date("[Y-m-d H:i:s]")."\t".$data."\n", FILE_APPEND);
        }else{
            throw new Exception("Logfile does not exists.");
        }
    }
}