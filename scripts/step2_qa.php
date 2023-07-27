<?php

$debug = false;
//$debug = true;
//$question = "想知道跟「沙丘」類似的電影？";
//$question = "「沙丘」類似的科幻電影 講述出身貴族家庭的年輕繼承者背負起守護銀河系中最寶貴的資產也是最重要的元素";
$question = "科幻電影 要包含守護銀河系、超能力的元素";
//$question = "描述一位年輕的貴族繼承者，他不僅承擔起維護銀河系中最珍貴資源的使命，這資源也是維繫整個宇宙的重要元素。";
//$question = "故事主角為一位名叫阿爾弗雷德的年輕男子，他出身於一個貴族家庭，繼承了家族的領地和權力。這個領地包含了一個名為「星石」的珍貴資源，這是整個銀河系中最寶貴的資產，也是保持銀河穩定的關鍵元素。阿爾弗雷德在父親過世後，必須肩負起保護「星石」以及他的家族領地的重責大任。透過此片，我們可以看到他如何將自己的命運與整個銀河系的安危緊密繫結，並踏上了保護這個至關重要資源的道路。";
$folder_path_of_openai_question = __DIR__ . '/../files/openai_of_question/';
$folder_path_of_embedding = __DIR__ . '/../files/embedding/';
$folder_path_of_openai_question = __DIR__ . '/../files/openai_of_question/';


//---
require_once __DIR__ . '/../load.php';
require_once __DIR__ . '/../config.php';

$openai_go = new OpenAiClass();

$question = trim($question);
$file_name = md5($question) . ".json";
$file_path = $folder_path_of_openai_question . $file_name;
$is_crawl_openai = $openai_go->isCallOpenAiApi($file_path);
if($is_crawl_openai){
    $question = $openai_go->callEmbeddingAPI(array($question), $file_path);
}


if(!file_exists($file_path)){
    $error = 'The $file_path of question is not exists!';
    throw new Exception($error);
}

$json_content = file_get_contents($file_path);
$json_data = json_decode($json_content, true);
$embedding_of_question = $json_data['data'][0]['embedding'];
$result = getAnswer($embedding_of_question);
var_dump($result);;

//function getAnswer($prompts, $inputs, $question) {
function getAnswer($embedding_of_question) {
    global $debug;
    global $folder_path_of_embedding;

    // 將提問的問題與每一個可能的答案都做一次餘弦相似度演算法（cosine similarity），選擇出最接近的答案
    $file_name_list = array_diff(scandir($folder_path_of_embedding), array('.', '..', '.DS_Store'));

    //$file_name_list = array();
    //$file_name_list[] = "Q99964772.json";
    //$file_name_list[] = "Q163872.json";

    $results = [];
    foreach ($file_name_list AS $index => $file_name) {
        $file_path = $folder_path_of_embedding . $file_name;
        if ($debug) {
            echo '$file_path is: ' . print_r($file_path, true) . PHP_EOL;
        }
        $json_content = file_get_contents($file_path);
        $json_data = json_decode($json_content, true);
        $embedding_of_existing_movie = $json_data["vect"];
        $content_of_existing_movie = $json_data["text"];


        $similarity = cosineSimilarity($embedding_of_existing_movie, $embedding_of_question);
        // 把計算結果存起來，後面要排序，取最佳解
        if(!is_null($similarity)){
            $results[] = [
                'similarity' => $similarity,
                'index' => $index,
                'input' => $content_of_existing_movie,
            ];
        }
    }

    usort($results, function ($a, $b) {
        if ($a['similarity'] < $b['similarity']) {
            return -1;
        } elseif ($a['similarity'] == $b['similarity']) {
            return 0;
        } else {
            return 1;
        }
    });


    //return end($results);

    $results = array_reverse($results);
    return array_slice($results, 0, 5);
    //return $results;
}
function cosineSimilarity($u, $v) {
    $dotProduct = 0;
    $uLength    = 0;
    $vLength    = 0;

    if(is_null($u)){
        return null;
    }

    for ($i = 0; $i < count($u); $i++) {
        $dotProduct += $u[$i] * $v[$i];
        $uLength += $u[$i] * $u[$i];
        $vLength += $v[$i] * $v[$i];
    }
    $uLength = sqrt($uLength);
    $vLength = sqrt($vLength);

    if ($uLength == 0 || $vLength == 0) {
        // Either vector has length 0
        // Handle this error as you see fit
        return null;
    }

    return $dotProduct / ($uLength * $vLength);
}