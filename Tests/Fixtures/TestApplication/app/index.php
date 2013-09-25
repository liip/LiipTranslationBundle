<?php

// Because debugging in the browser is much easier than in phpunit...

use Symfony\Component\HttpFoundation\Request;

$loader = require_once __DIR__.'/../../../bootstrap.php';
require_once __DIR__.'/AppKernel.php';
$kernel = new AppKernel('test', true);
$kernel->loadClassCache();
$request = Request::createFromGlobals();
$response = $kernel->handle($request);
$response->send();
$kernel->terminate($request, $response);
