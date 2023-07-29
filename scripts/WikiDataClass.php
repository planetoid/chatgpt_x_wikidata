<?php

require_once __DIR__ . '/../load.php';

class WikiDataClass
{
    public $debug = false;

    public $folder_path_of_wikidata = __DIR__ . '/../files/wikidata/';

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

}