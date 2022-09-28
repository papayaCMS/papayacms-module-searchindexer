# papayacms-module-searchindexer
A module to feed search indexes after publishing pages or other content. Updated for and successfully tested with Elasticsearch 6.1.1.

This readme helps you to set up Elasticsearch.


First steps for creating index and mapping on the elasticsearch end point:


Step 1: create index with settings:

    curl -XPUT 'http://host:9200/myindex/' -H 'Content-type: application/json' -d '
    {
      "settings": {
        "index": {
          "analysis": {
            "filter": {
              "stemmer": {
                "type": "stemmer",
                "language": "german"
              },
              "autocompleteFilter": {
                "max_shingle_size": "6",
                "min_shingle_size": "2",
                "type": "shingle",
                "filler_token": "",
                "output_unigrams" : true
              },
              "stopwords": {
                "type": "stop",
                "stopwords": [
                  "aber","als","am","an","auch","auf","aus","bei","bin","bis","bist","da","dadurch","daher","darum","das","daß","dass","dein","deine","dem","den","der","des","dessen","deshalb","die","dies","dieser","dieses","doch","dort","du","durch","ein","eine","einem","einen","einer","eines","er","es","euer","eure","für","hatte","hatten","hattest","hattet","hier","hinter","ich","ihr","ihre","im","in","ist","ja","jede","jedem","jeden","jeder","jedes","jener","jenes","jetzt","kann","kannst","können","könnt","machen","mein","meine","mit","muß","mußt","musst","müssen","müßt","nach","nachdem","nein","nicht","nun","oder","seid","sein","seine","sich","sie","sind","soll","sollen","sollst","sollt","sonst","soweit","sowie","und","unser","unsere","unter","vom","von","vor","wann","warum","was","weiter","weitere","wenn","wer","werde","werden","werdet","weshalb","wie","wieder","wieso","wir","wird","wirst","wo","woher","wohin","zu","zum","zur","über"
                ]
              }
            },
            "analyzer": {
              "didYouMean": {
                "filter": [
                  "lowercase"
                ],
                "char_filter": [
                  "html_strip"
                ],
                "type": "custom",
                "tokenizer": "standard"
              },
              "autocomplete": {
                "filter": [
                  "lowercase",
                  "stopwords",
                  "autocompleteFilter"
                ],
                "char_filter": [
                  "html_strip"
                ],
                "type": "custom",
                "tokenizer": "standard"
              },
              "default": {
                "filter": [
                  "lowercase",
                  "stopwords",
                  "stemmer"
                ],
                "char_filter": [
                  "html_strip"
                ],
                "type": "custom",
                "tokenizer": "standard"
              }
            }
          }
        }
      }
    }
    '

Step 2: create mapping on index:
    
    curl -XPUT 'http://host:9200/myindex/_mapping/de/' -H 'Content-type: application/json' -d '
    {
      "de": {
        "properties": {
          "autocomplete": {
            "type": "text",
            "analyzer": "autocomplete",
            "fielddata": true
          },
          "content": {
            "type": "text",
            "analyzer" : "default",
            "copy_to": [
              "did_you_mean",
              "autocomplete"
            ]
          },
          "did_you_mean": {
            "type": "text",
            "analyzer": "didYouMean",
            "fielddata": true
          },
          "title": {
            "type": "text",
            "analyzer" : "default",
            "copy_to": [
              "autocomplete",
              "did_you_mean"
            ]
          }
        }
      }
    }
    '
    
Step 3: start the indexer cronjob from package Elasticsearch
    
    Attention!!! please empty the table "papaya_search_indexer_status" before you start the indexer cronjob
    
if you need to delete index:
    
    curl -XDELETE 'host:9200/myindex'
    
    
Manually search request
    
    curl -XPOST 'http://host:9200/myindex/de/_search/' -d '
    {
      "suggest": {
        "didYouMean": {
          "text": "*patient*",
          "phrase": {
            "field": "did_you_mean"
          }
        }
      },
      "from":0,
      "size":10,
      "query": {
        "query_string": {
          "query": "*patient* OR patient",
          "fields": ["title^2","content"]
        }
      },
      "sort" : {
            "_score" : {
                "order" : "desc"
            }
      },
      "highlight" : {
        "fragment_size" : 100,
        "number_of_fragments" : 3,
        "no_match_size": 100,
        "fields" : {
            "content" : {
    
            }
        }
      }
    }
    '
    
Manually suggest request
    
    curl -XPOST 'http://host:9200/myindex/de/_search/' -d '
    {
      "size": 0,
      "aggregations": {
        "didYouMean": {
          "terms": {
            "field": "didYouMean",
            "order": {
              "_count": "desc"
            },
            "include": {
              "pattern": "patient.*"
            }
          }
        },
        "autocomplete": {
          "terms": {
            "field": "autocomplete",
            "order": {
              "_count": "desc"
            },
            "include": {
              "pattern": "patient.*"
            }
          }
        }
      }
    }
    '
