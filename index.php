<?php

$debug = false;
//$debug = true;

$file_path_of_qids = __DIR__ . '/qids.txt';

$folder_path_of_wikidata = __DIR__ . '/files/wikidata/';

$folder_path_of_enwiki = __DIR__ . '/files/enwiki/';
$folder_path_of_zhwiki = __DIR__ . '/files/zhwiki/';

$folder_path_of_openai = __DIR__ . '/files/openai/';
$folder_path_of_embedding_result = __DIR__ . '/files/embedding/';

$step_crawl_wikipedia = false;
$step_crawl_openai = true;
//----
require_once __DIR__ . '/scripts/JsonClass.php';
require_once __DIR__ . '/scripts/OpenAiClass.php';
require_once __DIR__ . '/config.php';

$openai_go = new OpenAiClass();
$json_go = new JsonClass();

$total_crawl_files = 0;
$file_content = file_get_contents($file_path_of_qids);
$qids = explode(PHP_EOL, $file_content);

//$qids = array();
//$qids[] = "Q102225";



echo 'count of $qids: ' . count($qids) . PHP_EOL;

if($step_crawl_wikipedia){
    foreach ($qids AS $qid){

        if ($debug) {
            echo '$qid is: ' . print_r($qid, true) . PHP_EOL;
        }

        if(trim($qid) !== ""){
            $file_name_of_wikidata = "{$qid}.json";
            $file_path_of_wikidata = $folder_path_of_wikidata . $file_name_of_wikidata;

            $json_content = file_get_contents($file_path_of_wikidata);
            $json_data = json_decode($json_content, true);
            if ($debug) {
                //echo '$json_data is: ' . print_r($json_data, true) . PHP_EOL;
            }

            $en_title = null;
            if(array_key_exists("entities", $json_data)
                && array_key_exists($qid, $json_data["entities"])
                && array_key_exists("sitelinks", $json_data["entities"][$qid])
                && array_key_exists("enwiki", $json_data["entities"][$qid]["sitelinks"])
                && array_key_exists("title", $json_data["entities"][$qid]["sitelinks"]["enwiki"])
            ){
                $en_title = $json_data["entities"][$qid]["sitelinks"]["enwiki"]["title"];
            }

            $zh_title = null;
            if(array_key_exists("entities", $json_data)
                && array_key_exists($qid, $json_data["entities"])
                && array_key_exists("sitelinks", $json_data["entities"][$qid])
                && array_key_exists("zhwiki", $json_data["entities"][$qid]["sitelinks"])
                && array_key_exists("title", $json_data["entities"][$qid]["sitelinks"]["zhwiki"])
            ){
                $zh_title = $json_data["entities"][$qid]["sitelinks"]["zhwiki"]["title"];
            }

            if ($debug) {
                echo '$en_title is: ' . print_r($en_title, true) . PHP_EOL;
                echo '$zh_title is: ' . print_r($zh_title, true) . PHP_EOL;
            }

            if(!is_null($zh_title)) {
                $total_crawl_files += getZhWikiData($qid, $zh_title);
            }elseif(!is_null($en_title)) {
                $total_crawl_files += getEnWikiData($qid, $en_title);
            }
        }

    }

    echo '$total_crawl_files: ' . $total_crawl_files . PHP_EOL;
}

if($step_crawl_openai){
    $total_crawl_files = 0;
    foreach ($qids AS $qid){

        if ($debug) {
            echo '$qid is: ' . print_r($qid, true) . PHP_EOL;
        }

        $file_name = "{$qid}.json";
        $file_path_of_zhwiki = $folder_path_of_zhwiki . $file_name;
        $file_path_of_openai = $folder_path_of_openai . $file_name;
        $file_path_of_embedding_result = $folder_path_of_embedding_result . $file_name;

        if(file_exists($file_path_of_zhwiki)){
            $json_content = file_get_contents($file_path_of_zhwiki);
            $json_data = json_decode($json_content, true);

            $text = null;
            if(is_array($json_data)
                && array_key_exists("query", $json_data)
                && array_key_exists("pages", $json_data["query"])
                && array_key_exists(0, $json_data["query"]["pages"])
                && array_key_exists("extract", $json_data["query"]["pages"][0])
            ) {
                $text = $json_data["query"]["pages"][0]["extract"];
            }

            if ($debug) {
                //echo '$text is: ' . print_r($text, true) . PHP_EOL;
            }

            if ($debug) {
                //echo '$json_data is: ' . print_r($json_data, true) . PHP_EOL;
            }
            //exit();

            $is_crawl_openai = $openai_go->isCallOpenAiApi($file_path_of_openai);

            if($is_crawl_openai
                && !is_null($text)
            ){
                if(file_exists($file_path_of_openai)){
                    rename($file_path_of_openai, $file_path_of_openai . ".bak");
                }

                $text = mb_substr($text, 0, 6000, "UTF-8");
                if ($debug) {
                    echo '$text is: ' . print_r($text, true) . PHP_EOL;
                }
                //exit();
                $emb      = embedding(array($text));
                /*$document = [
                    'order' => $qid,
                    'text'  => $text,
                    'vec'   => $emb['data'][0]['embedding'],
                ];
                */
                //到這邊，就是完成取得每一個要建檔的 Embeddings 向量資料了

                $file_content_of_openai = json_encode($emb, JSON_UNESCAPED_UNICODE);
                file_put_contents($file_path_of_openai, $file_content_of_openai);
                if(file_exists($file_path_of_openai)){
                    $total_crawl_files++;
                }

            }

            if(!is_null($text)
                && file_exists($file_path_of_openai)
                //&& !file_exists($file_path_of_embedding_result)
            ){
                $file_content = file_get_contents($file_path_of_openai);
                $emb = json_decode($file_content, true);

                if ($debug) {
                    //echo '$text is: ' . print_r($text, true) . PHP_EOL;
                }
                //exit();
                //$emb      = embedding(array($text));

                $document = [
                    'order' => $qid,
                    'text'  => $text,
                    'vec'   => $emb['data'][0]['embedding'],
                ];
                //到這邊，就是完成取得每一個要建檔的 Embeddings 向量資料了

                $file_content_of_embedding = json_encode($document);
                file_put_contents($file_path_of_embedding_result, $file_content_of_embedding);
                if(file_exists($file_path_of_embedding_result)){
                    $total_crawl_files++;
                }

            }

        }


    }
    echo '$total_crawl_files: ' . $total_crawl_files . PHP_EOL;
}


function getEnWikiData($qid = "", $title = ""){
    global $folder_path_of_enwiki;
    $file_name = "{$qid}.json";
    $file_path = $folder_path_of_enwiki . $file_name;

    $title = urlencode($title);
    //$url = "https://en.wikipedia.org/w/api.php?action=query&prop=extracts&titles={$title}&explaintext=1&formatversion=2";
    $url = "https://en.wikipedia.org/w/api.php?action=query&prop=extracts&titles={$title}&explaintext=1&formatversion=2&format=json&origin=*";

    return crawl($url, $file_path);
}

function getZhWikiData($qid = "", $title = ""){
    global $debug;
    global $folder_path_of_zhwiki;
    $file_name = "{$qid}.json";
    $file_path = $folder_path_of_zhwiki . $file_name;

    $title = urlencode($title);
    //$url = "https://zh.wikipedia.org/w/api.php?action=query&prop=extracts&titles={$zh_title}&explaintext=1&formatversion=2&format=json";
    $url = "https://zh.wikipedia.org/w/api.php?action=query&prop=extracts&titles={$title}&explaintext=1&formatversion=2&format=json&origin=*";

    if ($debug) {
        echo '$url is: ' . print_r($url, true) . PHP_EOL;
    }

    return crawl($url, $file_path);
}

function crawl($url = "", $file_path = ""){
    global $debug;


    $json_go = new JsonClass();

    $is_crawl = $json_go->isFileMetError($file_path);
    if(!$is_crawl){
        return 0;
    }

    // create a new cURL resource
    $ch = curl_init();

    // set URL and other appropriate options
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10.9; rv:32.0) Gecko/20100101 Firefox/32.0',
    ));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HEADER, 0);

    // grab URL and pass it to the browser
    $out = curl_exec($ch);

    // close cURL resource, and free up system resources
    curl_close($ch);

    $fp = fopen($file_path, 'w');
    fwrite($fp, $out);
    fclose($fp);

    if(file_exists($file_path)){
        return 1;
    }
    return 0;
}


// [OpenAI] 使用 PHP 搭配 Embeddings 開發個人化 AI 問答機器人 – YourGPT – 一介資男 https://www.mxp.tw/9785/
function embedding($input) {
    global $debug;
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
        //'Authorization: Bearer sk-你的OPENAI_API_KEY',
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
    curl_close($ch);
    return json_decode($response, true);
}