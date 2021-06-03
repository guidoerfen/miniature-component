<?php

return [
    'di_mapping' => [
        'class01' => [
            'class' => 'Miniature\Component\Tests\TestEnv\Classes\VerboseClass04',
            'singleton' => false,
            'args' => [
                'string_param' => 'text overidden by do-not-scan'
            ]
        ],
    ]
];
