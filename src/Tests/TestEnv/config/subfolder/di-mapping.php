<?php
return [
    'di_mapping' => [
        'class03_overridden_in_subfolder' => [
            'class' => 'Miniature\Component\Tests\TestEnv\Classes\VerboseClass01',
            'singleton' => false,
            'args' => [
                'string_param' => 'text overridden in subfolder'
            ]
        ],
    ]
];