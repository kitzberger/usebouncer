<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'Usebouncer',
    'description' => 'Mail address validation via usebouncer.com (for powermail)',
    'category' => 'system',
    'state' => 'stable',
    'author' => 'Philipp Kitzberger',
    'author_email' => 'typo3@kitze.net',
    'author_company' => '',
    'version' => '2.0.2',
    'constraints' => [
        'depends' => [
            'typo3' => '11.5.0-12.4.99',
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
];
