<?php
return [
    'di_mapping' => [
        'class01' => [
            'class' => 'Miniature\Component\Tests\TestEnv\Classes\VerboseClass01',
            'singleton' => false,
            'args' => [
                'string_param' => 'original text'
            ]
        ],
        'class02_overridden_in_dev' => [
            'class' => 'Miniature\Component\Tests\TestEnv\Classes\VerboseClass01',
            'singleton' => false,
            'args' => [
                'string_param' => 'original text'
            ]
        ],
        'class03_overridden_in_subfolder' => [
            'class' => 'Miniature\Component\Tests\TestEnv\Classes\VerboseClass01',
            'singleton' => false,
            'args' => [
                'string_param' => 'original text'
            ]
        ],
        'class04_overridden_in_subfolder_dev' => [
            'class' => 'Miniature\Component\Tests\TestEnv\Classes\VerboseClass01',
            'singleton' => false,
            'args' => [
                'string_param' => 'original text'
            ]
        ],
    ]
];