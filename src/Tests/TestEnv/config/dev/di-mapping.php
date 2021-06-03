<?php
return [
    'di_mapping' => [
        'class02_overridden_in_dev' => [
            'class' => 'Miniature\Component\Tests\TestEnv\Classes\VerboseClass02',
            'singleton' => false,
            'args' => [
                'string_param' => 'text overridden in dev'
            ]
        ],
    ]
];
