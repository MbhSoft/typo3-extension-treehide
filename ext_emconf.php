<?php

$EM_CONF['treehide'] = [
    'title' => 'Treehide',
    'description' => 'Adds context menu items to hide and unhide pages recursive',
    'category' => 'misc',
    'author' => 'Marc Bastian Heinrichs',
    'author_email' => 'typo3@mbh-software.de',
    'author_company' => 'MBH Softwarelösungen',
    'state' => 'stable',
    'uploadfolder' => false,
    'createDirs' => '',
    'clearCacheOnLoad' => true,
    'version' => '2.0.0',
    'constraints' => [
        'depends' => ['typo3' => '12.4.0-12.4.99'],
        'conflicts' => [],
        'suggests' => [],
    ],
];
