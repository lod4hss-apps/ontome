<?php

declare(strict_types=1);

use Rector\Doctrine\Set\DoctrineSetList;
use Rector\Config\RectorConfig;
use Rector\Symfony\Set\SymfonySetList;
use Rector\Symfony\Set\SensiolabsSetList;

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
        DoctrineSetList::ANNOTATIONS_TO_ATTRIBUTES,
        SymfonySetList::ANNOTATIONS_TO_ATTRIBUTES,
    ])
    ->withSymfonyContainerXml(__DIR__ . '/var/cache/dev/App_KernelDevDebugContainer.xml');