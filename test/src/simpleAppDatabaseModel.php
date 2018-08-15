<?php

$sdm = [
    'school' // database name
    => [
        '__udms_config' => [
          // config database
            'mysql_mysql' // only config for mysql:mysql
            =>[
                //'utf8' => 'utf8_general_ci'
            ]
        ],
        'class' // table name
        => [
            '__udms_config' => [
              'mysql_mysql' // only config for mysql:mysql
              =>[
                //'engine' => 'InnoDB',
                //'charset' => [
                //  'utf8' => 'utf8_persian_ci'
                //]
              ]
            ],
            'id' // col name
            => [
                'type' => 'int',
                'length' => 5,
                'index' => 'primary',
                'auto' => []
            ],
            'c_id' => [
                '__udms_rel' => [ // relation and nested example
                    'course_rels' => 'id'
                ]
            ],
            't_id' => [
                '__udms_rel' => [ // relation and nested example
                    'teacher' => 'id'
                ]
            ]
        ],
        'course' => [
            'id' => [
                'type' => 'int',
                'index' => 'primary',
                'auto' => []
            ],
            'name' => [
                'type' => 'text',
                '__udms_config' => [
                  'mysql_mysql' => [
                      'charset' => [
                          'utf8' => 'utf8_persian_ci'
                      ]
                  ]
                ]
            ]
        ],
        'course_rels' => [
            'id' => [
                'type' => 'int',
                'index' => 'primary',
                'auto' => []
            ],
            'c_id' => [
                '__udms_rel' => [
                    'course' => 'id'
                ]
            ],
            'sub_id' => [
                '__udms_rel' => [
                    'course' => 'id'
                ]
            ]
        ],
        'student' => [
            'id' => [
                'type' => 'int',
                'index' => 'primary',
                'auto' => [
                    'start' => 93000000,
                    'add' => 43
                ]
            ],
            'fname' => [
                'type' => 'text'
            ],
            'lname' => [
                'type' => 'text'
            ]
        ],
        'teacher' => [
            'id' => [
                'type' => 'int',
                'index' => 'primary',
                'auto' => [
                    'start' => 73500,
                    'add' => 73
                ]
            ],
            'fname' => [
                'type' => 'text'
            ],
            'lname' => [
                'type' => 'text'
            ]
        ]
    ]
];
