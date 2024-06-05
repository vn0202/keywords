<?php

return [
    'host' => env('ELASTICSEARCH_HOST'),
    'user' => env('ELASTICSEARCH_USER'),
    'password' => env('ELASTICSEARCH_PASSWORD'),
    'cloud_id' => env('ELASTICSEARCH_CLOUD_ID'),
    'api_key' => env('ELASTICSEARCH_API_KEY'),
    'queue' => [
        'timeout' => env('SCOUT_QUEUE_TIMEOUT'),
    ],
    'indices' => [
        'mappings' => [
            env('SCOUT_PREFIX') . 'keywords' => [
                'properties' => [

                    'refine_word' => [
                        'type' => 'text',
                        'analyzer' => 'standard',
                    ],
                ],
            ],
            'default' => [
                'properties' => [
                    'id' => [
                        'type' => 'keyword',
                    ],
                ],
            ],
        ],
        'settings' => [
            'default' => [
                'number_of_shards' => 1,
                'number_of_replicas' => 0,
            ],

//            env("SCOUT_PREFIX") . "keywords" => [
//
//                "analysis" => [
//                    "tokenizer" => [
//                        "hyphen_tokenizer" => [
//                            "type" => "pattern",
//                            "pattern" => "_"
//                        ]
//                    ],
//                    "filter" => [
//
//                        "my_stop_filter" => [
//                            "type" => "stop",
//                            "stopwords" => ['bị', 'bởi', 'cả', 'các', 'cái', 'cần', 'càng', 'chỉ', 'chiếc', 'cho', 'chứ', 'chưa', 'chuyện', 'có', 'có thể', 'cứ', 'của', 'cùng', 'cũng', 'đã', 'đang', 'đây', 'để', 'đến nỗi', 'đều', 'điều', 'do', 'đó', 'được', 'dưới', 'gì', 'khi', 'không', 'là', 'lại', 'lên', 'lúc', 'mà', 'mỗi', 'một cách', 'này', 'nên', 'nếu', 'ngay', 'nhiều', 'như', 'nhưng', 'những', 'nơi', 'nữa', 'phải', 'qua', 'ra', 'rằng', 'rằng', 'rất', 'rất', 'rồi', 'sau', 'sẽ', 'so', 'sự', 'tại', 'theo', 'thì', 'trên', 'trước', 'từ', 'từng', 'và', 'vẫn', 'vào', 'vậy', 'vì', 'việc', 'với', 'vừa',]  // example stop words
//                        ]
//                    ],
//                    "analyzer" => [
//                        "hyphen_analyzer" => [
//                            "type" => "custom",
//                            "tokenizer" => "hyphen_tokenizer",
//                            'filter' => ['my_stop_filter'],
//                        ]
//                    ]
//                ]
//            ],

        ]
    ],

];
