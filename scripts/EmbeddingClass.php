<?php

require_once __DIR__ . '/../load.php';

class EmbeddingClass
{
    public $debug = false;

    public $folder_path_of_embedding = __DIR__ . '/../files/embedding/';

    public $folder_path_of_search_result = __DIR__ . '/../files/search_result/';

    // Modified the source code from mxp.tw
    // [OpenAI] 使用 PHP 搭配 Embeddings 開發個人化 AI 問答機器人 – YourGPT – 一介資男 https://www.mxp.tw/9785/

    public function getAnswer($embedding_of_question, $size = 10) {
        $debug = $this->debug;
        $folder_path_of_embedding = $this->folder_path_of_embedding;

        // 將提問的問題與每一個可能的答案都做一次餘弦相似度演算法（cosine similarity），選擇出最接近的答案
        $file_name_list = array_diff(scandir($folder_path_of_embedding), array('.', '..', '.DS_Store'));

        //$file_name_list = array();
        //$file_name_list[] = "Q99964772.json";
        //$file_name_list[] = "Q163872.json";

        $results = [];
        foreach ($file_name_list AS $index => $file_name) {
            // remove file extension
            $qid = pathinfo($file_name, PATHINFO_FILENAME);

            $file_path = $folder_path_of_embedding . $file_name;
            if ($debug) {
                echo '$file_path is: ' . print_r($file_path, true) . PHP_EOL;
            }
            $json_content = file_get_contents($file_path);
            $json_data = json_decode($json_content, true);
            $embedding_of_existing_movie = $json_data["vect"];
            $content_of_existing_movie = $json_data["text"];


            $similarity = $this->cosineSimilarity($embedding_of_existing_movie, $embedding_of_question);
            // 把計算結果存起來，後面要排序，取最佳解
            if(!is_null($similarity)){
                $results[] = [
                    'similarity' => $similarity,
                    'index' => $index,
                    'qid' => $qid,
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

        if($size > 0){
            return array_slice($results, 0, $size);
        }

        return $results;
    }

    // Modified the source code from mxp.tw
    // [OpenAI] 使用 PHP 搭配 Embeddings 開發個人化 AI 問答機器人 – YourGPT – 一介資男 https://www.mxp.tw/9785/
    public function cosineSimilarity($u, $v) {
        $dotProduct = 0;
        $length_of_u    = 0;
        $length_of_v    = 0;

        if(is_null($u)){
            return null;
        }

        for ($i = 0; $i < count($u); $i++) {
            $dotProduct += $u[$i] * $v[$i];
            $length_of_u += $u[$i] * $u[$i];
            $length_of_v += $v[$i] * $v[$i];
        }
        $length_of_u = sqrt($length_of_u);
        $length_of_v = sqrt($length_of_v);

        if ($length_of_u == 0 || $length_of_v == 0) {
            // Either vector has length 0
            // Handle this error as you see fit
            return null;
        }

        return $dotProduct / ($length_of_u * $length_of_v);
    }

    /**
     * @param $question
     * @param $suggest_data
     * @param $file_path
     * @return string|null
     */
    public function showSearchResult($question = "", $suggest_data = array(), $file_path = null){

        $wikidata_go = new WikiDataClass();

        if(empty($suggest_data)){
            return null;
        }

        if(!is_null($file_path)
            && !file_exists($file_path)
        ){
            //$file_content = json_encode($suggest_data);
            //file_put_contents($file_path, $file_content);
        }

        $fields_data = array(
            "similarity" => "相似度",
            "title" => "電影名稱",
            "qid" => "WikiData 條目編號",
            "input" => "Wikipeida 條目摘要",
            "wikipedia_link" => "Wikipeida 條目連結",
        );


        $output = <<<EOT
<!doctype html>
<head lang="zh">
<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <link rel="stylesheet" href="../../htdocs/style.css">
    <!-- Bootstrap CSS -->
    <!-- <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.4.1/dist/css/bootstrap.min.css" integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous"> -->
    <link rel="stylesheet" href="../../htdocs/bootstrap.min.css">
    
    <!-- Optional JavaScript -->
    <!-- jQuery first, then Popper.js, then Bootstrap JS -->
    <!-- <script src="https://code.jquery.com/jquery-3.4.1.slim.min.js" integrity="sha384-J6qa4849blE2+poT4WnyKhv5vZF5SrPo0iEjwBvKU7imGFAV0wwj1yYfoRSJoZ+n" crossorigin="anonymous"></script> -->
    <script src="../../htdocs/jquery-3.4.1.slim.min.js"></script>
    <!-- <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js" integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo" crossorigin="anonymous"></script> -->
    <script src="../../htdocs/popper.min.js"></script>
    <!-- <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.4.1/dist/js/bootstrap.min.js" integrity="sha384-wfSDF2E50Y2D1uUdj0O3uMBJnjuUD4Ih7YwaYd1iqfktj0Uod8GCExl3Og8ifwB6" crossorigin="anonymous"></script> -->
    <script src="../../htdocs/bootstrap.min.js"></script>
    <!-- <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/datatables/1.10.20/css/dataTables.bootstrap4.min.css"> -->
    <link rel="stylesheet" href="../../htdocs/dataTables.bootstrap4.min.css">
    <script src="../../htdocs/sort_table.js"></script>
    
    <!-- <script src="https://cdnjs.cloudflare.com/ajax/libs/datatables/1.10.20/js/jquery.dataTables.min.js"></script> -->
    <script src="../../htdocs/jquery.dataTables.min.js"></script>
    <!-- <script src="https://cdnjs.cloudflare.com/ajax/libs/datatables/1.10.20/js/dataTables.bootstrap4.min.js"></script> -->
    <script src="../../htdocs/dataTables.bootstrap4.min.js"></script>

</head>

<body>
   
    <div class="container">
        <h1> 搜尋結果 </h1>
        <div><span style="font-weight: bold;">詢問問題：</span>{$question}</div>
        <br />
        
        <table id="wikidata" class="table table-striped table-bordered table-sm" style="width:100%">
            <thead>
                <tr>

EOT;

        foreach ($fields_data AS $field_name => $label){
            $output .= <<<EOT

            <th class="th-sm {$field_name}">$label</th>
EOT;

        }

        $output .= <<<EOT

        </tr>
  </thead>
  <tbody>
EOT;


        foreach ($suggest_data AS $row){

            $qid = $row['qid'];
            $post_data = $wikidata_go->getPostData($qid);
            $label = null;
            $wikipedia_link = null;
            if(!is_null($post_data)){
                $label = $post_data["label"];
                $wikipedia_link = $post_data["wikipedia_link"];
            }
            $row["title"] = $label;
            $row["wikipedia_link"] = $wikipedia_link;

            $output .= <<<EOT
        <tr>
EOT;
            foreach ($fields_data AS $field_name => $label){
                $field_value = null;
                if(array_key_exists($field_name, $row)){
                    $field_value = $row[$field_name];
                    $raw_field_value = htmlentities($field_value);

                    switch ($field_name) {
                        case "qid":
                            $field_value = "<a href='https://www.wikidata.org/wiki/{$field_value}?uselang=zh-tw' target='_blank'>" . $field_value . "</a>";
                            break;
                        case "wikipedia_link":
                            $field_value = "<a href='{$field_value}' target='_blank'>" . "Link" . "</a>";
                            break;
                        default:
                            $field_value = htmlentities($field_value);
                    }

                }
                $output .= <<<EOT
            <td class="{$field_name} nowrap" title="{$raw_field_value}">
                {$field_value}
            </td>
EOT;
            }
            $output .= <<<EOT
        </tr>
EOT;
        }



        $output .= <<<EOT

                </tbody>
EOT;

        $output .= <<<EOT
            </table>
EOT;

        $output .= <<<EOT

                </tbody>
            </table>
        </div>
    </body>
</html>
EOT;

        if(!is_null($file_path)
        ){
            $file_content = $output;
            file_put_contents($file_path, $file_content);
        }

        return $output;
    }

}