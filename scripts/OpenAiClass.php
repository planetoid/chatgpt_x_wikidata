<?php

class OpenAiClass
{
    public $debug = false;

    // [OpenAI] 使用 PHP 搭配 Embeddings 開發個人化 AI 問答機器人 – YourGPT – 一介資男 https://www.mxp.tw/9785/

    /**
     * @param $input
     * @param $file_path
     * @return mixed|void
     */
    public function callEmbeddingAPI($input = array(), $file_path = null) {
        $debug = $this->debug;
        $api_key = getenv("OPENAI_API_TOKEN");

        if ($debug) {
            //echo '$api_key is: ' . print_r($api_key, true) . PHP_EOL;
        }
        //exit();

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://api.openai.com/v1/embeddings');
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Authorization: Bearer {$api_key}",
            'Content-Type: application/json; charset=utf-8',
        ]);

        $json_array = [
            'model' => 'text-embedding-ada-002',
            'input' => $input,
        ];
        $body = json_encode($json_array);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        $response = curl_exec($ch);

        if (!$response) {
            die('Error: "' . curl_error($ch) . '" - Code: ' . curl_errno($ch));
        }

        if(!is_null($file_path)){
            $json_data = json_decode($response, true);
            $file_content = json_encode($json_data, JSON_UNESCAPED_UNICODE);
            file_put_contents($file_path, $file_content);
        }

        curl_close($ch);

        return json_decode($response, true);
    }

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