<?php
if (!file_exists($file = __DIR__.'/../vendor/autoload.php')) {
    throw new RuntimeException("Install dependencies using composer to run the test suite.");
}
$autoload = require_once $file;
