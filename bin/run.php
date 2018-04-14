#!/usr/bin/env php
<?php
// Set a base timezone
date_default_timezone_set('Europe/London');

// To help the built-in PHP dev server, check if the request was actually for
// something which should probably be served as a static file
if (PHP_SAPI === 'cli-server' && $_SERVER['SCRIPT_FILENAME'] !== __FILE__) {
    return false;
}

require __DIR__ . '/../vendor/autoload.php';

// Instantiate the app
$settings = require __DIR__ . '/../app/settings.php';

$app = new \Cilex\Application('Cilex');
$app['settings'] = $settings['settings'];

// Set up dependencies
require __DIR__ . '/../app/dependencies.php';

$app->command(new \App\Command\MailmanCommand());

$app->run();