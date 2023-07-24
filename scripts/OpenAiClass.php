<?php

class OpenAiClass
{

    /**
     * @param $file_path
     * @return bool
     */
    public function isCallOpenAiApi($file_path = ""){

        if(!file_exists($file_path)){
            return true;
        }

        $is_context_error = $this->isFileMetContextLengthError($file_path);
        if($is_context_error){
            return true;
        }

        return false;
    }

    public function isFileMetContextLengthError($file_path = ""){

        if(!file_exists($file_path)){
            return false;
        }

        $file_content = file_get_contents($file_path);
        return $this->isFileMetContextLengthErrorGivenFileContent($file_content);

    }

    /**
     * @param $file_content
     * @return bool
     */
    public function isFileMetContextLengthErrorGivenFileContent($file_content = ""){

        $json_data = json_decode($file_content, true);
        if(array_key_exists("error", $json_data)
            && array_key_exists("code", $json_data["error"])
            && $json_data["error"]["code"] === "context_length_exceeded"

        ){
            return true;
        }

        if(array_key_exists("error", $json_data)
            && array_key_exists("message", $json_data["error"])

        ){
            $message = $json_data["error"]["message"];
            $pattern = "/(This model's maximum context length)/iu";
            if(preg_match($pattern, $message)){
                return true;
            }
        }

        return false;

    }
}