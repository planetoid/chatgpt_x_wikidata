<?php

$debug = false;
//$debug = true;
//$question = "想知道跟「沙丘」類似的電影？";
//$question = "「沙丘」類似的科幻電影 講述出身貴族家庭的年輕繼承者背負起守護銀河系中最寶貴的資產也是最重要的元素";
//$question = "科幻電影 要包含守護銀河系、超能力的元素";
//$question = "原本被認為是銀河系的守護英雄，實際上是反派的壞人。";
//$question = "原本認為是銀河系的守護英雄，實際上是反派。原以為是壞人的變種人，其實是好人";
//$question = "一群高中生在面對生活挑戰時，如何用無窮熱情和堅定信念，追尋夢想和自我突破的故事";
//$question = "年輕人挑戰自我、突破界限、追尋夢想的感人電影";
//$question = "週五晚上可以跟朋友聚餐看的熱鬧電影";
//$question = "充滿奇幻元素的冒險電影";
$question = "2021年的科幻電影";
//$question = "動人心弦的政治劇情片，鮮明地描繪了一群平凡人為了推翻壓迫性的專制政權而展開的英勇鬥爭。這部電影充滿了激情、犧牲和勇氣，以及對自由和正義的堅定追求";
//$question = "描述一位年輕的貴族繼承者，他不僅承擔起維護銀河系中最珍貴資源的使命，這資源也是維繫整個宇宙的重要元素。";
//$question = "故事主角為一位名叫阿爾弗雷德的年輕男子，他出身於一個貴族家庭，繼承了家族的領地和權力。這個領地包含了一個名為「星石」的珍貴資源，這是整個銀河系中最寶貴的資產，也是保持銀河穩定的關鍵元素。阿爾弗雷德在父親過世後，必須肩負起保護「星石」以及他的家族領地的重責大任。透過此片，我們可以看到他如何將自己的命運與整個銀河系的安危緊密繫結，並踏上了保護這個至關重要資源的道路。";
$folder_path_of_openai_question = __DIR__ . '/../files/openai_of_question/';
$folder_path_of_embedding = __DIR__ . '/../files/embedding/';
$folder_path_of_openai_question = __DIR__ . '/../files/openai_of_question/';


//---
require_once __DIR__ . '/../load.php';
require_once __DIR__ . '/../config.php';

$openai_go = new OpenAiClass();
$embedding_go = new EmbeddingClass();

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
$result = $embedding_go->getAnswer($embedding_of_question);
var_dump($result);;
