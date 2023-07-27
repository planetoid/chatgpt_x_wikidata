<?php

$debug = true;
$file_path_of_qids = __DIR__ . '/../qids.txt';
$folder_path_of_embedding_result = __DIR__ . '/../files/embedding/';
$folder_path_of_typesense = __DIR__ . '/../files/typesense_jsonl/';

$step_convert_json_to_jsonl = false;
//$step_convert_json_to_jsonl = true;

//$step_import_to_typesense = false;
$step_import_to_typesense = true;

$typesense_api_key = getenv("TYPESENSE_API_KEY");
$typesense_collection_name = "test-collection";

use Typesense\Client;

//----
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../load.php';

$json_go = new JsonClass();

$total_crawl_files = 0;
$file_content = file_get_contents($file_path_of_qids);
$qids = explode(PHP_EOL, $file_content);
echo 'count of $qids: ' . count($qids) . PHP_EOL;

$qids = array();
//$qids[] = "Q18407";
//$qids[] = "Q102225";
$qids[] = "Q163872";


if($step_convert_json_to_jsonl){
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
}

if($step_import_to_typesense){

    $client = new Client(
        [
            'api_key'         => $typesense_api_key,
            'nodes'           => [
                [
                    'host'     => 'localhost', // For Typesense Cloud use xxx.a1.typesense.net
                    'port'     => '8108',      // For Typesense Cloud use 443
                    'protocol' => 'http',      // For Typesense Cloud use https
                ],
            ],
            'connection_timeout_seconds' => 2,
        ]
    );

    $file_list = array_diff(scandir($folder_path_of_typesense), array('.', '..', '.DS_Store'));

    $file_list = array();
    //$file_list[] = "Q99964772.jsonl";
    $file_list[] = "Q163872.jsonl";

    foreach ($file_list AS $file_name){
        $file_path = $folder_path_of_typesense . $file_name;
        if ($debug) {
            echo '$file_path is: ' . print_r($file_path, true) . PHP_EOL;
        }

        $documentsInJsonl = file_get_contents($file_path);

        $json_data = json_decode($documentsInJsonl, true);

        $text = $json_data[0]['text'];
        $text = str_replace(array("\r\n", "\n", "\r"), '', $text);
        $text = str_replace(array('"'), "'", $text);
        $json_data[0]["text"] = $text;
        $json_data = $json_data[0];

        if ($debug) {
            echo '$json_data is: ' . print_r($json_data, true) . PHP_EOL;
        }

        //unset($json_data[0]['vec']);

        //$documentsInJsonl = json_encode($json_data, JSON_UNESCAPED_UNICODE);
        $documentsInJsonl = json_encode($json_data);
        echo $documentsInJsonl . PHP_EOL;

        //$import_result = $client->collections[$typesense_collection_name]->documents->import($documentsInJsonl, ['action' => 'create']);
        //$import_result = $client->collections[$typesense_collection_name]->documents->import($documentsInJsonl, ['action' => 'upsert']);
        //$import_result = $client->collections[$typesense_collection_name]->documents->create($json_data);
        $import_result = $client->collections[$typesense_collection_name]->documents->upsert($json_data);
        //$import_result = $client->collections[$typesense_collection_name]->documents->create($documentsInJsonl);
        //var_dump($import_result);

        if ($debug) {
            echo '$import_result is: ' . print_r($import_result, true) . PHP_EOL;
        }
    }
}


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