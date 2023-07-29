<?php

$path = __DIR__ . '/vendor/autoload.php';
if(file_exists($path)){
    require_once ($path);
}

require_once __DIR__ . '/scripts/JsonClass.php';
require_once __DIR__ . '/scripts/OpenAiClass.php';
require_once __DIR__ . '/scripts/WikipediaClass.php';
require_once __DIR__ . '/scripts/WikiDatalass.php';