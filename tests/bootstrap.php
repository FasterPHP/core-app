<?php
/**
 * Unit Testing Bootstrap.
 */
declare(strict_types=1);

namespace FasterPhp\CoreApp;

require_once __DIR__ . '/../vendor/autoload.php';

error_reporting(E_ALL);
date_default_timezone_set('Europe/London');

// Setup App
$rootPath = dirname(__DIR__);
$app = new App($rootPath);
