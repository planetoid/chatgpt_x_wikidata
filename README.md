# coscup2023

## Installation PHP packages

```bash
$ composer update
```

## 2020~2023年上映的電影
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

### other sqarql

```sparql
SELECT DISTINCT ?label ?boxLabel ?genreLabel ?dateLabel ?vactorLabel ?awardsLabel ?directorLabel ?musicLabel ?companyLabel ?actorLabel ?runtime {
        ?movie wdt:P31/wdt:P279* wd:Q11424 .
        ?movie rdfs:label ?label .
       ?movie wdt:P577 ?pubdate.
#       FILTER((?pubdate >= "2022-01-01T00:00:00Z"^^xsd:dateTime))
#    FILTER((?pubdate >= "2022-01-01T00:00:00Z"^^xsd:dateTime) && (?pubdate <= "2022-12-31T00:00:00Z"^^xsd:dateTime))
FILTER((?pubdate >= "2023-01-01T00:00:00Z"^^xsd:dateTime))
       ?movie wdt:P136 ?genre ;
                wdt:P577 ?date ;
                wdt:P57  ?director ;
                wdt:P86  ?music ;
                wdt:P2142 ?box;
                wdt:P2047  ?runtime;
                wdt:P272  ?company ;

        OPTIONAL{
            ?movie wdt:P161 ?actor .
        }
        OPTIONAL{
            ?movie wdt:P725 ?vactor .
        }
        OPTIONAL{
            ?movie wdt:P166 ?awards .
        }
        # SERVICE wikibase:label { bd:serviceParam wikibase:language "en". }
        SERVICE wikibase:label { bd:serviceParam wikibase:language "[AUTO_LANGUAGE],en". }
} LIMIT 100

# 包含重複結果
```

```sparql
#2017年上映的電影
SELECT DISTINCT ?item ?itemLabel ?itemDescription ?pubdate WHERE {
# SELECT DISTINCT ?item ?itemLabel ?itemDescription (GROUP_CONCAT(DISTINCT(?genreID); separator=", ") as ?genres) ?pubdate WHERE {
## SELECT ?item ?itemLabel ?itemDescription (GROUP_CONCAT(DISTINCT(?genreID); separator=", ") as ?genres) ?pubdate WHERE {
# SELECT DISTINCT ?item ?itemLabel ?itemDescription (GROUP_CONCAT(DISTINCT(?genreID); separator=", ") as ?genres)   ?pubdate WHERE {
  ?item wdt:P31 wd:Q11424.
  ?item rdfs:label ?film_title . 
  ## ?item wdt:P136 ?type.  
  ?item wdt:P136 ?genreID .
  ?genreID rdfs:label ?genre .
  ?item wdt:P577 ?pubdate.
  ?item wdt:P161 ?actorID .
  ?actorID rdfs:label ?actor .
  # FILTER((?pubdate >= "2021-01-01T00:00:00Z"^^xsd:dateTime))
  # FILTER((?pubdate >= "2022-01-01T00:00:00Z"^^xsd:dateTime))
    FILTER((?pubdate >= "2022-01-01T00:00:00Z"^^xsd:dateTime) && (?pubdate <= "2023-12-31T00:00:00Z"^^xsd:dateTime))
  SERVICE wikibase:label { bd:serviceParam wikibase:language "[AUTO_LANGUAGE],en". }
} 
# GROUP BY ?q ?film_title
# 已达到查询超时限制
```

```sparql
SELECT ?q ?film_title (GROUP_CONCAT(DISTINCT(?genreID); separator=", ") as ?genres) WHERE {
  ?q wdt:P31 wd:Q11424 .
  ?q rdfs:label ?film_title . 
  # FILTER (lang(?film_title) = "en")
  ?q wdt:P136 ?genreID .
  ?genreID rdfs:label ?genre .
  ?q wdt:P161 ?actorID .
  ?actorID rdfs:label ?actor .
  ?q wdt:P577 ?pubdate.
  FILTER((?pubdate >= "2022-01-01T00:00:00Z"^^xsd:dateTime))
  # FILTER (lang(?actor) = "en")
  SERVICE wikibase:label { bd:serviceParam wikibase:language "[AUTO_LANGUAGE],en". }
} 
GROUP BY ?q ?film_title
LIMIT 100

# 已达到查询超时限制
```

```spaqrl
SELECT ?q ?film_title (GROUP_CONCAT(DISTINCT(?genreID); separator=", ") as ?genres) WHERE {
  ?q wdt:P31 wd:Q11424 .
  ?q rdfs:label ?film_title . 
  # FILTER (lang(?film_title) = "en")
  ?q wdt:P136 ?genreID .
  ?genreID rdfs:label ?genre .
  ?q wdt:P161 ?actorID .
  ?actorID rdfs:label ?actor .
  ?q wdt:P577 ?pubdate.
  FILTER((?pubdate >= "2022-01-01T00:00:00Z"^^xsd:dateTime))
  # FILTER (lang(?actor) = "en")
  SERVICE wikibase:label { bd:serviceParam wikibase:language "[AUTO_LANGUAGE],en". }
} 
GROUP BY ?q ?film_title
LIMIT 100

# 已达到查询超时限制
```
