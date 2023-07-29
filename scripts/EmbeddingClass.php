<?php

require_once __DIR__ . '/../load.php';

class EmbeddingClass
{
    public $debug = false;

    // Modified the source code from mxp.tw
    // [OpenAI] 使用 PHP 搭配 Embeddings 開發個人化 AI 問答機器人 – YourGPT – 一介資男 https://www.mxp.tw/9785/
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
        return array_slice($results, 0, 5);
        //return $results;
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

}