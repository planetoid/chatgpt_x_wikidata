<?php


// Dune: Part Two - Wikidata https://www.wikidata.org/wiki/Q109228991
$qid = "Q109228991";

$folder_path = __DIR__ . '/../files/wikidata/';

//---
require_once __DIR__ . '/../load.php';

$wikidata_go = new WikiDataClass();

$file_name = "{$qid}.json";
$file_path = $folder_path . $file_name;
$total_crawl_files = $wikidata_go->crawlGivenQid($qid, $file_path);
var_dump($total_crawl_files);