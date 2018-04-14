<?php
// Routes

$app->get('/', App\Action\HomeAction::class)
    ->setName('homepage');

$app->get('/login', App\Action\LoginAction::class)
    ->setName('login');

$app->post('/login', App\Action\LoginAction::class)
	->setName('login');

$app->get('/logout', App\Action\LogoutAction::class)
    ->setName('logout');

$app->get('/profile', App\Action\ProfileAction::class)
    ->setName('profile');

$app->post('/profile', App\Action\ProfileAction::class)
    ->setName('profile');

$app->get('/register/{conferenceSlug}', App\Action\RegisterAction::class)
    ->setName('register');

$app->post('/register/{conferenceSlug}', App\Action\RegisterAction::class)
    ->setName('register');

$app->get('/cancel/{conferenceSlug}', App\Action\CancelAction::class)
    ->setName('cancel');

$app->get('/stats/{conferenceSlug}', App\Action\StatsAction::class)
    ->setName('stats');

// API

$app->get('/api/registration/{conferenceSlug}', App\Action\API\RegistrationAction::class)
	->setName('api/register');