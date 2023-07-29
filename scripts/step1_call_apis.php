<?php

$debug = false;
//$debug = true;

$file_path_of_qids = __DIR__ . '/../qids.txt';

$folder_path_of_wikidata = __DIR__ . '/../files/wikidata/';

$folder_path_of_enwiki = __DIR__ . '/../files/enwiki/';
$folder_path_of_zhwiki = __DIR__ . '/../files/zhwiki/';

$folder_path_of_openai = __DIR__ . '/../files/openai/';
$folder_path_of_embedding_result = __DIR__ . '/../files/embedding/';

$step_crawl_wikipedia = false;
$step_crawl_openai = false;
$step_generate_embedding_files = false;
//$step_generate_embedding_files = true;

//----
require_once __DIR__ . '/../load.php';
require_once __DIR__ . '/../config.php';

$openai_go = new OpenAiClass();
$json_go = new JsonClass();

$total_crawl_files = 0;
$file_content = file_get_contents($file_path_of_qids);
$qids = explode(PHP_EOL, $file_content);

/*
$qids = array();
$qids[] = "Q163872";
$qids[] = "Q102225";
*/


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

if($step_crawl_openai | $step_generate_embedding_files){
    $total_crawl_files_of_openai = 0;
    $total_generated_files_of_embedding = 0;
    foreach ($qids AS $qid){

        $id = str_replace(array("Q"), "" ,$qid);
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
                $text = mb_substr($text, 0, 5500, "UTF-8");
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

                if ($debug) {
                    echo '$text is: ' . print_r($text, true) . PHP_EOL;
                }
                $emb      = $openai_go->callEmbeddingAPI(array($text), $file_path_of_openai);
                if(file_exists($file_path_of_openai)){
                    $total_crawl_files_of_openai++;
                }

            }

            if($step_generate_embedding_files
                && !is_null($text)
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
                    'id' => $id,
                    'qid' => $qid,
                    'text'  => $text,
                    'vect'   => $emb['data'][0]['embedding'],
                ];
                //到這邊，就是完成取得每一個要建檔的 Embeddings 向量資料了

                $file_content_of_embedding = json_encode($document);
                file_put_contents($file_path_of_embedding_result, $file_content_of_embedding);
                if(file_exists($file_path_of_embedding_result)){
                    $total_generated_files_of_embedding++;
                }
            }


        }


    }
    echo '$total_crawl_files_of_openai: ' . $total_crawl_files_of_openai . PHP_EOL;
    echo '$total_generated_files_of_embedding: ' . $total_generated_files_of_embedding . PHP_EOL;
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




/**
 * @param $file_path
 * @return int
 */
function importToTypesense($file_path = ""){
    global $debug;
    global $typesense_api_key;
    global $typesense_collection_name;

    if(!file_exists($file_path)){
        return 0;
    }

    $command = "curl -H \"X-TYPESENSE-API-KEY: {$typesense_api_key}\" -X POST --data-binary @{$file_path} \"http://localhost:8108/collections/{$typesense_collection_name}/documents/import?return_id=true\"";
    //$command = "curl -H \"X-TYPESENSE-API-KEY: {$typesense_api_key}\" -X POST --data-binary @{$file_path} \"http://localhost:8108/collections/{$typesense_collection_name}/documents/upsert?return_id=true\"";
    // > met { "message": "Not Found"}

    //$command = escapeshellcmd($command);

    exec($command, $output);

    if ($debug) {
        echo '$output is: ' . gettype($output) . ' ' . print_r($output, true) . PHP_EOL;
    }

    if(is_array(json_decode($output, true))){
        $json_data = json_decode($output, true);
        if(array_key_exists("success", $json_data)
            && $json_data["success"] === true
        ){
            return 1;
        }
    }
    return 0;
}