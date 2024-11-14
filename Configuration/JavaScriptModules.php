<?php

return [
    // required import configurations of other extensions,
    // in case a module imports from another package
    'dependencies' => ['backend'],
    'tags' => [
        'backend.contextmenu',
    ],
    'imports' => [
        // recursive definiton, all *.js files in this folder are import-mapped
        // trailing slash is required per importmap-specification
        '@mbhsoft/treehide/' => 'EXT:treehide/Resources/Public/JavaScript/Modules/',
    ],
];
