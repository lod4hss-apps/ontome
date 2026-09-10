<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Symfony\Set\SymfonySetList;

// return RectorConfig::configure()
//     ->withPaths([
//         __DIR__ . '/src',
//     ])
//     // uncomment to reach your current PHP version
//     // ->withPhpSets()
//     ->withTypeCoverageLevel(0)
//     ->withDeadCodeLevel(0)
//     ->withCodeQualityLevel(0);

return RectorConfig::configure()
    ->withSets([
        SymfonySetList::SYMFONY_CODE_QUALITY,
    ])
    ->withSymfonyContainerXml(__DIR__ . '/var/cache/dev/App_KernelDevDebugContainer.xml');