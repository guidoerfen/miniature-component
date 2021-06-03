<?php
return [
    'di_mapping' => [

        'class04_overridden_in_subfolder_dev' => [
            'class' => 'Miniature\Component\Tests\TestEnv\Classes\VerboseClass03',
            'singleton' => false,
            'args' => [
                'string_param' => 'text overridden in subfolder dev'
            ]
        ],
    ]
];