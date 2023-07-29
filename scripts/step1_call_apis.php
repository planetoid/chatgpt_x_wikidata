<?php

$debug = false;
//$debug = true;

$file_path_of_qids = __DIR__ . '/../qids.txt';

$folder_path_of_wikidata = __DIR__ . '/../files/wikidata/';

$folder_path_of_enwiki = __DIR__ . '/../files/enwiki/';
$folder_path_of_zhwiki = __DIR__ . '/../files/zhwiki/';

$folder_path_of_openai = __DIR__ . '/../files/openai/';
$folder_path_of_embedding_result = __DIR__ . '/../files/embedding/';

$step_crawl_wikidata = false;
$step_crawl_wikipedia = false;
$step_crawl_openai = false;
$step_generate_embedding_files = false;
//$step_generate_embedding_files = true;

//----
require_once __DIR__ . '/../load.php';
require_once __DIR__ . '/../config.php';

$wikidata_go = new WikiDataClass();
$openai_go = new OpenAiClass();
$json_go = new JsonClass();
$wikipedia_go = new WikipediaClass();


$folder_path_list = array();
$folder_path_list[] = $folder_path_of_wikidata;
$folder_path_list[] = $folder_path_of_enwiki;
$folder_path_list[] = $folder_path_of_zhwiki;
$folder_path_list[] = $folder_path_of_openai;
$folder_path_list[] = $folder_path_of_embedding_result;

foreach ($folder_path_list AS $folder_path){
    if (!file_exists($folder_path)) {
        mkdir($folder_path, 0777, true);
    }
}

$total_crawl_files = 0;
$file_content = file_get_contents($file_path_of_qids);
$qids = explode(PHP_EOL, $file_content);

/*
$qids = array();
$qids[] = "Q163872";
$qids[] = "Q102225";
*/


echo 'count of $qids: ' . count($qids) . PHP_EOL;

if($step_crawl_wikidata){
    echo '# $step_crawl_wikidata' . PHP_EOL;
    foreach ($qids AS $qid){
        $file_name = "{$qid}.json";
        $file_path = $folder_path_of_wikidata . $file_name;
        $total_crawl_files += $wikidata_go->crawlGivenQid($qid, $file_path);
    }
    echo '$total_crawl_files: ' . $total_crawl_files . PHP_EOL;
}

if($step_crawl_wikipedia){
    echo '# $step_crawl_wikipedia' . PHP_EOL;
    foreach ($qids AS $qid){

        if ($debug) {
            echo '$qid is: ' . print_r($qid, true) . PHP_EOL;
        }

        if(trim($qid) !== ""){
            list($en_title, $zh_title) = $wikidata_go->getWikipediaTitleData($qid);

            if ($debug) {
                echo '$en_title is: ' . print_r($en_title, true) . PHP_EOL;
                echo '$zh_title is: ' . print_r($zh_title, true) . PHP_EOL;
            }

            if(!is_null($zh_title)) {
                $total_crawl_files += $wikipedia_go->getZhWikiData($qid, $zh_title);
            }elseif(!is_null($en_title)) {
                $total_crawl_files += $wikipedia_go->getEnWikiData($qid, $en_title);
            }
        }
    }

    echo '$total_crawl_files: ' . $total_crawl_files . PHP_EOL;
}

if($step_crawl_openai | $step_generate_embedding_files){
    echo '# $step_crawl_openai or $step_generate_embedding_files' . PHP_EOL;
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
            $text = $wikipedia_go->getExtractFromJsonContent($json_content);
            $text = mb_substr($text, 0, 5500, "UTF-8");

            if($step_crawl_openai){
                $total_crawl_files_of_openai = $openai_go->callEmbeddingAPIGivenText($text, $file_path_of_openai);
            }

            if($step_generate_embedding_files
                && file_exists($file_path_of_openai)
            ){
                $file_content = file_get_contents($file_path_of_openai);
                $openai_api_result = json_decode($file_content, true);
                $total_generated_files_of_embedding = $openai_go->transformApiDataToFriendlyFormat($qid, $text, $openai_api_result, $file_path_of_embedding_result);
            }


        }


    }
    echo '$total_crawl_files_of_openai: ' . $total_crawl_files_of_openai . PHP_EOL;
    echo '$total_generated_files_of_embedding: ' . $total_generated_files_of_embedding . PHP_EOL;
}



