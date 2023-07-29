<?php

class OpenAiClass
{
    public $debug = false;


    // Modified the source code from mxp.tw
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
     * @param $text
     * @param $file_path
     * @return int
     */
    public function callEmbeddingAPIGivenText($text = "", $file_path = null) {
        $debug = $this->debug;

        if(trim($text) === ""){
            return 0;
        }

        $total_crawl_files_of_openai = 0;

        $is_crawl_openai = $this->isCallOpenAiApi($file_path);

        if($is_crawl_openai){
            if(file_exists($file_path)){
                rename($file_path, $file_path . ".bak");
            }

            if ($debug) {
                echo '$text is: ' . print_r($text, true) . PHP_EOL;
            }
            $embedding_data = $this->callEmbeddingAPI(array($text), $file_path);
            if(file_exists($file_path)){
                $total_crawl_files_of_openai++;
            }

        }
        return $total_crawl_files_of_openai;
    }

    /**
     * @param $file_path
     * @return bool
     */
    public function isCallOpenAiApi($file_path = ""){

        $json_go = new JsonClass();

        if(!file_exists($file_path)){
            return true;
        }

        $is_context_error = $this->isFileMetContextLengthError($file_path);
        if($is_context_error){
            return true;
        }

        $is_json_error = $json_go->isFileMetError($file_path);
        if($is_json_error){
            return true;
        }

        return false;
    }

    /**
     * @param $file_path
     * @return bool
     */
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

    /**
     * @param $qid
     * @param $text
     * @param $api_json_content
     * @param $file_path
     * @return int
     */
    public function transformApiDataToFriendlyFormat($qid = "", $text = "", $api_json_content = "", $file_path = null){
        $debug = $this->debug;
        $id = str_replace(array("Q"), "" ,$qid);

        $total_generated_files_of_embedding = 0;
        $embedding_data = json_decode($api_json_content, true);

        if ($debug) {
            echo '$text is: ' . print_r($text, true) . PHP_EOL;
            echo '$embedding_data is: ' . print_r($embedding_data, true) . PHP_EOL;
        }

        $document = array(
            'id' => $id,
            'qid' => $qid,
            'text'  => $text,
            'vect'   => $embedding_data['data'][0]['embedding'],
        );

        $file_content_of_embedding = json_encode($document);
        file_put_contents($file_path, $file_content_of_embedding);
        if(file_exists($file_path)){
            $total_generated_files_of_embedding++;
        }
        return $total_generated_files_of_embedding;
    }
}