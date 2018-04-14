<?php
// DIC configuration

if(method_exists($app, 'get')) {
    $container = $app->getContainer();
} else {
    $container = $app;
}

// -----------------------------------------------------------------------------
// Service providers
// -----------------------------------------------------------------------------

// Twig
$container['view'] = function ($c) {
    $settings = $c->get('settings');
    $view = new Slim\Views\Twig($settings['view']['template_path'], $settings['view']['twig']);

    // Add extensions
    $view->addExtension(new Slim\Views\TwigExtension($c->get('router'), $c->get('request')->getUri()));
    $view->addExtension(new Twig_Extension_Debug());

    return $view;
};

// Flash messages
$container['flash'] = function ($c) {
    return new Slim\Flash\Messages;
};

// -----------------------------------------------------------------------------
// Service factories
// -----------------------------------------------------------------------------

// monolog
$container['logger'] = function ($c) {
    $settings = $c->get('settings');
    $logger = new Monolog\Logger($settings['logger']['name']);
    $logger->pushProcessor(new Monolog\Processor\UidProcessor());
    $logger->pushHandler(new Monolog\Handler\StreamHandler($settings['logger']['path'], Monolog\Logger::DEBUG));
    return $logger;
};

// -----------------------------------------------------------------------------
// Database
// -----------------------------------------------------------------------------
$container['database'] = function ($c) {
    if(method_exists($c, 'get')) {
        $config = $c->get('settings')['database'];
    } else {
        $config = $c['settings']['database'];
    }

    $cfg = new \Spot\Config();
    $cfg->addConnection('mysqli', [
        'dbname' => $config['db'],
        'user' => $config['username'],
        'password' => $config['password'],
        'host' => $config['hostname'],
        'driver' => 'pdo_mysql',
        'charset' => 'utf8'
    ]);
    $spot = new \Spot\Locator($cfg);

    return $spot;
};

// -----------------------------------------------------------------------------
// Utility Classes
// -----------------------------------------------------------------------------

$container['formbuilder'] = function ($c) {
    return new App\Forms\FormBuilder($c->get('view'));
};

$container['loginmanager'] = function ($c) {
    return new App\Login\LoginManager($c->get('settings')['login'], $c->get('database'), $c->get('settings')['login']['override']);
};

// -----------------------------------------------------------------------------
// Action factories
// -----------------------------------------------------------------------------

$container[App\Action\HomeAction::class] = function ($c) {
    return new App\Action\HomeAction($c->get('view'),
        $c->get('logger'),
        $c->get('formbuilder'),
        $c->get('loginmanager'),
        $c->get('database'));
};

$container[App\Action\LoginAction::class] = function ($c) {
    return new App\Action\LoginAction($c->get('view'),
        $c->get('logger'),
        $c->get('loginmanager'));
};

$container[App\Action\LogoutAction::class] = function ($c) {
    return new App\Action\LogoutAction($c->get('view'),
        $c->get('logger'),
        $c->get('loginmanager'));
};

$container[App\Action\ProfileAction::class] = function ($c) {
    return new App\Action\ProfileAction($c->get('view'), 
        $c->get('logger'),
        $c->get('formbuilder'),
        $c->get('loginmanager'),
        $c->get('database'));
};

$container[App\Action\RegisterAction::class] = function ($c) {
    return new App\Action\RegisterAction($c->get('view'), 
        $c->get('logger'),
        $c->get('formbuilder'),
        $c->get('loginmanager'),
        $c->get('database'));
};

$container[App\Action\CancelAction::class] = function ($c) {
    return new App\Action\CancelAction($c->get('view'), 
        $c->get('logger'),
        $c->get('formbuilder'),
        $c->get('loginmanager'),
        $c->get('database'));
};

$container[App\Action\StatsAction::class] = function ($c) {
    return new App\Action\StatsAction($c->get('view'), 
        $c->get('logger'),
        $c->get('formbuilder'),
        $c->get('loginmanager'),
        $c->get('database'));
};

// -----------------------------------------------------------------------------
// API Action factories
// -----------------------------------------------------------------------------

$container[App\Action\API\RegistrationAction::class] = function ($c) {
    return new App\Action\API\RegistrationAction($c->get('view'), 
        $c->get('logger'),
        $c->get('formbuilder'),
        $c->get('loginmanager'),
        $c->get('database'));
};