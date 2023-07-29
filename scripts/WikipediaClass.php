<?php

require_once __DIR__ . '/../load.php';

class WikipediaClass
{
    public $debug = false;

    public $folder_path_of_enwiki = __DIR__ . '/../files/enwiki/';
    public $folder_path_of_zhwiki = __DIR__ . '/../files/zhwiki/';

    /**
     * @param $qid
     * @param $title
     * @return mixed
     */
    public function getEnWikiData($qid = "", $title = ""){
        $debug = $this->debug;
        $folder_path_of_enwiki = $this->folder_path_of_enwiki;
        $file_name = "{$qid}.json";
        $file_path = $folder_path_of_enwiki . $file_name;

        $title = urlencode($title);
        //$url = "https://en.wikipedia.org/w/api.php?action=query&prop=extracts&titles={$title}&explaintext=1&formatversion=2";
        $url = "https://en.wikipedia.org/w/api.php?action=query&prop=extracts&titles={$title}&explaintext=1&formatversion=2&format=json&origin=*";

        return $this->crawl($url, $file_path);
    }

    /**
     * @param $json_content
     * @return mixed|null
     */
    public function getExtractFromJsonContent($json_content = ""){
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
        return $text;
    }

    /**
     * @param $qid
     * @param $title
     * @return mixed
     */
    public function getZhWikiData($qid = "", $title = ""){
        $debug = $this->debug;
        $folder_path_of_zhwiki = $this->folder_path_of_zhwiki;
        $file_name = "{$qid}.json";
        $file_path = $folder_path_of_zhwiki . $file_name;

        $title = urlencode($title);
        //$url = "https://zh.wikipedia.org/w/api.php?action=query&prop=extracts&titles={$zh_title}&explaintext=1&formatversion=2&format=json";
        $url = "https://zh.wikipedia.org/w/api.php?action=query&prop=extracts&titles={$title}&explaintext=1&formatversion=2&format=json&origin=*";

        if ($debug) {
            echo '$url is: ' . print_r($url, true) . PHP_EOL;
        }

        return $this->crawl($url, $file_path);
    }

    /**
     * @param $url
     * @param $file_path
     * @return int
     */
    public function crawl($url = "", $file_path = ""){
        $debug = $this->debug;

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
}