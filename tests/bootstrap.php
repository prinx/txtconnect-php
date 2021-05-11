<?php

require __DIR__.'/../vendor/autoload.php';

use VCR\VCR;

VCR::configure()
    ->setStorage('json')
    ->setBlackList([
        'vendor/prinx',
        'vendor/nunomaduro',
        'vendor/giggsey',
    ]);
