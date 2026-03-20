<?php

use MbhSoftware\Treehide\Controller\HidePagesRecursiveController;

return [
    'treehide_hidepagesrecursive' => [
        'path' => '/treehide/hidepagesrecursive',
        'target' => HidePagesRecursiveController::class . '::mainAction',
    ],
];
