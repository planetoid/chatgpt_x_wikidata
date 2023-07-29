# ChatGPT x Wikidata - coscup2023

## Installation PHP packages

```bash
$ composer update
```

## Usage

1. 查詢 2020~2023年上映的電影，匯出至 `qids.txt`
https://w.wiki/74YJ

```sparql
# 2020~2023年上映的電影

SELECT DISTINCT ?item ?itemLabel ?pubdate WHERE {
  ?item wdt:P31 wd:Q11424.
  ?item wdt:P577 ?pubdate.
  FILTER((?pubdate >= "2020-01-01T00:00:00Z"^^xsd:dateTime) && (?pubdate <= "2023-12-31T00:00:00Z"^^xsd:dateTime))
  SERVICE wikibase:label { bd:serviceParam wikibase:language "[AUTO_LANGUAGE],en". }
}

# 12873 rows
```

2. 設定 `config.php` 中的 `OPENAI_API_TOKEN`。如何取得 OpenAI API Token (Key)，請看 [Where do I find my Secret API Key? \| OpenAI Help Center](https://help.openai.com/en/articles/4936850-where-do-i-find-my-secret-api-key) 網頁說明。

3. `step1_call_apis.php` 設定要啟用的步驟

* $step_crawl_wikidata 依據  `qids.txt` 抓取 WikiData 條目資料
* $step_crawl_wikipedia 依據 WikiData 條目抓取 Wikipedia 條目資料對應的電影描述
* $step_crawl_openai 依據 Wikipedia 條目的電影描述 (篩選有中文描述的條目)，呼叫 OpenAi Embedding API
* $step_generate_embedding_files 將 OpenAi Embedding API 轉成比較容易閱讀的檔案格式

4. `step2_qa.php` 輸入要詢問的問題，將會在產生結果網頁檔案 (位於 `files/search_result/`)

## COPYRIGHT

除非函數有額外標明原作者，將以 MIT license 釋出原始碼