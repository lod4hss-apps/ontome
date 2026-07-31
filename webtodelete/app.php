<?php

use Symfony\Component\HttpFoundation\Request;

/** @var \Composer\Autoload\ClassLoader $loader */
$loader = require __DIR__.'/../app/autoload.php';
include_once __DIR__.'/../var/bootstrap.php.cache';

$kernel = new AppKernel('prod', false);
$kernel->loadClassCache();
//$kernel = new AppCache($kernel);

// When using the HttpCache, you need to call the method in your front controller instead of relying on the configuration parameter
//Request::enableHttpMethodParameterOverride();
// --- FIX COMPATIBILITÉ PHP 8.1+ / SYMFONY 3 ---
// On supprime la clé 'full_path' inconnue de Symfony 3 dans $_FILES
if (PHP_VERSION_ID >= 80100 && !empty($_FILES)) {
    $cleanFiles = function (&$data) use (&$cleanFiles) {
        if (is_array($data)) {
            unset($data['full_path']);
            foreach ($data as &$value) {
                $cleanFiles($value);
            }
        }
    };
    $cleanFiles($_FILES);
}
// ----------------------------------------------

// Ligne d'origine de Symfony :
$request = Request::createFromGlobals();
$response = $kernel->handle($request);
$response->send();
$kernel->terminate($request, $response);
