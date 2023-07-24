<?php

$debug = true;
$file_path_of_qids = __DIR__ . '/../qids.txt';
$folder_path_of_embedding_result = __DIR__ . '/../files/embedding/';
$folder_path_of_typesense = __DIR__ . '/../files/typesense_jsonl/';

$step_crawl_wikipedia = false;
$step_crawl_openai = true;
//----
require_once __DIR__ . '/JsonClass.php';
require_once __DIR__ . '/../config.php';

$json_go = new JsonClass();

$total_crawl_files = 0;
$file_content = file_get_contents($file_path_of_qids);
$qids = explode(PHP_EOL, $file_content);
echo 'count of $qids: ' . count($qids) . PHP_EOL;

//$qids = array();
//$qids[] = "Q18407";
//$qids[] = "Q102225";


$total_generated_files = 0;

foreach ($qids as $qid){

    if(trim($qid) !== ""){
        if ($debug) {
            echo '$qid is: ' . print_r($qid, true) . PHP_EOL;
        }

        $file_name_of_embedding = "{$qid}.json";
        $file_path_of_embedding_result = $folder_path_of_embedding_result . $file_name_of_embedding;

        $file_name_of_typesense = "{$qid}.jsonl";
        $file_path_of_typesense = $folder_path_of_typesense . $file_name_of_typesense;

        if(file_exists($file_path_of_embedding_result)){
            $file_content = file_get_contents($file_path_of_embedding_result);
            $json_data = json_decode($file_content, true);

            if(array_key_exists("vec", $json_data)
                && !is_null($json_data["vec"])
                && !file_exists($file_path_of_typesense)
            ){
                $total_generated_files += convertJsonToJsonl($file_path_of_embedding_result, $file_path_of_typesense);
            }
        }

    }

}
echo '$total_generated_files: ' . $total_generated_files . PHP_EOL;

function convertJsonToJsonl($path_of_json, $path_of_jsonl){

    $file_content = file_get_contents($path_of_json);
    $json_data = json_decode($file_content, true);
    $jsonl_data = array();
    $jsonl_data[] = $json_data;
    $jsonl_file_content = json_encode($jsonl_data, JSON_UNESCAPED_UNICODE);
    file_put_contents($path_of_jsonl, $jsonl_file_content);
    // jq -c '.[]' documents.json > documents.jsonl
    /*
    $command = "jq -c '.[]' {$path_of_json} > {$path_of_jsonl}";
    exec($command, $output);
    */

    if(file_exists($path_of_jsonl)){
        return 1;
    }
    return 0;
}