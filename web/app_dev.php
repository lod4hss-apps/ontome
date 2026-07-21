<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Debug\Debug;

// If you don't want to setup permissions the proper way, just uncomment the following PHP line
// read http://symfony.com/doc/current/setup.html#checking-symfony-application-configuration-and-setup
// for more information
//umask(0000);

// This check prevents access to debug front controllers that are deployed by accident to production servers.
// Feel free to remove this, extend it, or make something more sophisticated.
if (isset($_SERVER['HTTP_CLIENT_IP'])
    || isset($_SERVER['HTTP_X_FORWARDED_FOR'])
    || !(in_array(@$_SERVER['REMOTE_ADDR'], ['127.0.0.1', '::1']) || php_sapi_name() === 'cli-server')
) {
    header('HTTP/1.0 403 Forbidden');
    exit('You are not allowed to access this file. Check '.basename(__FILE__).' for more information.');
}

/** @var \Composer\Autoload\ClassLoader $loader */
$loader = require __DIR__.'/../app/autoload.php';
Debug::enable();

$kernel = new AppKernel('dev', true);
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
