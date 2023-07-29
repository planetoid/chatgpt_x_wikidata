<?php

class JsonClass
{
    public function isFileMetError($file_path = ""){

        if(!file_exists($file_path)){
            return false;
        }

        $json_content = file_get_contents($file_path);
        if(!is_array(json_decode($json_content, true))){
            return true;
        }
        return false;
    }
}