<?php

require_once __DIR__ . '/../load.php';

class WikiDataClass
{
    public $debug = false;

    public $folder_path_of_wikidata = __DIR__ . '/../files/wikidata/';

    /**
     * @param $qid
     * @param $file_path
     * @return int
     */
    public function crawlGivenQid($qid = "", $file_path = null){
        $wikipedia_go = new WikipediaClass();

        $url = "https://www.wikidata.org/w/api.php?action=wbgetentities&format=json&props=labels|sitelinks&ids={$qid}&languages=zh";
        return $wikipedia_go->crawl($url, $file_path);
    }

    /**
     * @param $qid
     * @return mixed|void|null
     */
    public function getLabelAsTitle($qid = ""){
        $debug = $this->debug;
        $folder_path_of_wikidata = $this->folder_path_of_wikidata;

        if(trim($qid) === ""){
            return null;
        }

        $file_name_of_wikidata = "{$qid}.json";
        $file_path_of_wikidata = $folder_path_of_wikidata . $file_name_of_wikidata;

        if(!file_exists($file_path_of_wikidata)){
            return null;
        }

        $json_content = file_get_contents($file_path_of_wikidata);
        $json_data = json_decode($json_content, true);
        if ($debug) {
            //echo '$json_data is: ' . print_r($json_data, true) . PHP_EOL;
        }

        if(array_key_exists("entities", $json_data)
            && array_key_exists($qid, $json_data["entities"])
            && array_key_exists("labels", $json_data["entities"][$qid])
            && array_key_exists("zh", $json_data["entities"][$qid]["labels"])
            && array_key_exists("value", $json_data["entities"][$qid]["labels"]["zh"])
        ){
            return $json_data["entities"][$qid]["labels"]["zh"]["value"];
        }

        if(array_key_exists("entities", $json_data)
            && array_key_exists($qid, $json_data["entities"])
            && array_key_exists("labels", $json_data["entities"][$qid])
            && array_key_exists("en", $json_data["entities"][$qid]["labels"])
            && array_key_exists("value", $json_data["entities"][$qid]["labels"]["en"])
        ){
            return $json_data["entities"][$qid]["labels"]["en"]["value"];
        }

    }

    /**
     * @param $qid
     * @return array|null
     */
    public function getWikipediaTitleData($qid = ""){
        $debug = $this->debug;
        $folder_path_of_wikidata = $this->folder_path_of_wikidata;

        if(trim($qid) === ""){
            return null;
        }


        $file_name_of_wikidata = "{$qid}.json";
        $file_path_of_wikidata = $folder_path_of_wikidata . $file_name_of_wikidata;

        if(!file_exists($file_path_of_wikidata)){
            return null;
        }

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

        return array($en_title, $zh_title);
    }

    /**
     * @param $qid
     * @return array|null
     */
    public function getPostData($qid = ""){
        $debug = $this->debug;
        $folder_path_of_wikidata = $this->folder_path_of_wikidata;

        if(trim($qid) === ""){
            return null;
        }


        $file_name_of_wikidata = "{$qid}.json";
        $file_path_of_wikidata = $folder_path_of_wikidata . $file_name_of_wikidata;

        if(!file_exists($file_path_of_wikidata)){
            return null;
        }

        $json_content = file_get_contents($file_path_of_wikidata);
        $json_data = json_decode($json_content, true);
        if ($debug) {
            //echo '$json_data is: ' . print_r($json_data, true) . PHP_EOL;
        }

        $en_label = null;
        $zh_label = null;

        if(array_key_exists("entities", $json_data)
            && array_key_exists($qid, $json_data["entities"])
            && array_key_exists("labels", $json_data["entities"][$qid])
            && array_key_exists("en", $json_data["entities"][$qid]["labels"])
            && array_key_exists("value", $json_data["entities"][$qid]["labels"]["en"])
        ){
            $en_label = $json_data["entities"][$qid]["labels"]["en"]["value"];
        }

        if(array_key_exists("entities", $json_data)
            && array_key_exists($qid, $json_data["entities"])
            && array_key_exists("labels", $json_data["entities"][$qid])
            && array_key_exists("zh", $json_data["entities"][$qid]["labels"])
            && array_key_exists("value", $json_data["entities"][$qid]["labels"]["zh"])
        ){
            $zh_label = $json_data["entities"][$qid]["labels"]["zh"]["value"];
        }


        $label = $zh_label;
        if(is_null($label)){
            $label = $en_label;
        }

        $enwiki_title = null;
        if(array_key_exists("entities", $json_data)
            && array_key_exists($qid, $json_data["entities"])
            && array_key_exists("sitelinks", $json_data["entities"][$qid])
            && array_key_exists("enwiki", $json_data["entities"][$qid]["sitelinks"])
            && array_key_exists("title", $json_data["entities"][$qid]["sitelinks"]["enwiki"])
        ){
            $enwiki_title = $json_data["entities"][$qid]["sitelinks"]["enwiki"]["title"];
        }

        $zhwiki_title = null;
        if(array_key_exists("entities", $json_data)
            && array_key_exists($qid, $json_data["entities"])
            && array_key_exists("sitelinks", $json_data["entities"][$qid])
            && array_key_exists("zhwiki", $json_data["entities"][$qid]["sitelinks"])
            && array_key_exists("title", $json_data["entities"][$qid]["sitelinks"]["zhwiki"])
        ){
            $zhwiki_title = $json_data["entities"][$qid]["sitelinks"]["zhwiki"]["title"];
        }

        $wikipedia_link = null;
        $enwiki_link = null;
        $zhwiki_link = null;
        if(!is_null($enwiki_title)){
            $enwiki_link = "https://en.wikipedia.org/wiki/" . urlencode($enwiki_title);
        }
        if(!is_null($zhwiki_title)){
            $zhwiki_link = "https://zh.wikipedia.org/zh-tw/" . urlencode($zhwiki_title);
            $zhwiki_link = "https://zh.wikipedia.org/zh-tw/" . rawurlencode($zhwiki_title);
        }

        $wikipedia_link = $zhwiki_link;
        if(is_null($wikipedia_link)){
            $wikipedia_link = $enwiki_link;
        }

        if ($debug) {
            echo '$enwiki_title is: ' . print_r($enwiki_title, true) . PHP_EOL;
            echo '$zhwiki_title is: ' . print_r($zhwiki_title, true) . PHP_EOL;
        }

        return array(
            "en_label" => $en_label,
            "zh_label" => $zh_label,
            "label" => $label,
            "enwiki_title" => $enwiki_title,
            "zhwiki_title" => $zhwiki_title,
            "enwiki_link" => $enwiki_link,
            "zhwiki_link" => $zhwiki_link,
            "wikipedia_link" => $wikipedia_link
        );
    }

}