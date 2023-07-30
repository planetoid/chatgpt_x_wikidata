# ChatGPT x Wikidata

## Purpose

這是 ChatGPT x Wikidata - COSCUP 2023 分享的概念專案

* 抓取 WIkiData 與 WIkipedia 上的電影資料
* 透過 OpenAI API 處理電影資料的 Embedding 向量
* 詢問 OpenAI 問題，得到推薦的電影

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

3. `scripts/step1_call_apis.php` 設定要啟用的步驟

* $step_crawl_wikidata 依據  `qids.txt` 抓取 WikiData 條目資料
* $step_crawl_wikipedia 依據 WikiData 條目抓取 Wikipedia 條目資料對應的電影描述
* $step_crawl_openai 依據 Wikipedia 條目的電影描述 (篩選有中文描述的條目)，呼叫 OpenAi Embedding API
* $step_generate_embedding_files 將 OpenAi Embedding API 轉成比較容易閱讀的檔案格式

4. `scripts/step2_qa.php` 輸入要詢問的問題，將會在產生結果網頁檔案 (位於 `files/search_result/`)

## Example file

* 問：「在不遠的未來，李凱，一位才華洋溢的程式設計師，偶然發現一段神秘的程式碼，進入了一個由程式碼構成的平行宇宙。他發現自己有能力改變這些程式碼來影響現實世界。當一個想利用他的能力來掌控世界的黑客組織「黑色螢幕」發現他時，李凱必須學會控制這股力量，並阻止黑客的陰謀。在這場數位戰爭中，他和他的朋友們一起，經歷了友情、愛情和犧牲的考驗。」
* 答：  [回答結果][./example/search_result/9d467662d52c27fefee211a2817600ab.html]

  

## COPYRIGHT

除非函數有額外標明原作者，將以 MIT license 釋出原始碼

[./example/search_result/9d467662d52c27fefee211a2817600ab.html]: 