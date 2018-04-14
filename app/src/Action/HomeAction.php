<?php
namespace App\Action;

use Slim\Views\Twig;
use Psr\Log\LoggerInterface;
use Slim\Http\Request;
use Slim\Http\Response;

final class HomeAction
{
    private $view;
    private $logger;
    private $loginmanager;
    private $formbuilder;
    private $database;

    public function __construct(Twig $view, LoggerInterface $logger, $formbuilder, $loginmanager, $database)
    {
        $this->view = $view;
        $this->logger = $logger;
        $this->formbuilder = $formbuilder;
        $this->loginmanager = $loginmanager;
        $this->database = $database;
    }

    public function __invoke(Request $request, Response $response, $args)
    {
        $this->logger->info("Dashboard :: init");

        if(!$this->loginmanager->isLoggedIn()) {
            $this->logger->info("Dashboard :: not logged in");
            return $response->withRedirect('/login');
        }

        $profileMapper = $this->database->mapper('Entity\Profile');
        $existingProfile = $profileMapper->where(['dn' => $this->loginmanager->getCurrentUser()]);
        if(!$existingProfile->count()) {
            $this->logger->info("Dashboard :: new user, redirect to profile :: " . $this->loginmanager->getCurrentUser());
            return $response->withRedirect('/profile?newuser=true');
        }

        $args['isLoggedIn'] = $this->loginmanager->isLoggedIn();
        $args['currentPage'] = 'home';

        $conferenceMapper = $this->database->mapper('Entity\Conference');
        $args['conferences'] = $conferenceMapper->where(['enabled' => 1]);

        $args['isRegistered'] = [];

        $registrationMapper = $this->database->mapper('Entity\Registration');
        foreach($args['conferences'] as $conference) {
            $args['isRegistered'][$conference->id] = (boolean)$registrationMapper->where([
                'conference_id' => $conference->id,
                'cancelled' => 0,
                'dn' => $this->loginmanager->getCurrentUser()
            ])->count();
        }

        $adminMapper = $this->database->mapper('Entity\Admin');
        foreach($args['conferences'] as $conference) {
            $args['isAdmin'][$conference->id] = (boolean)$adminMapper->where([
                'conference_id' => $conference->id,
                'dn' => $this->loginmanager->getCurrentUser()
            ])->count();
        }

        $args['profilesuccess'] = isset($_GET['profilesuccess']);
        $args['registrationsuccess'] = isset($_GET['registrationsuccess']);
        $args['registrationcancelled'] = isset($_GET['registrationcancelled']);
        $args['accessdenied'] = isset($_GET['access']);

        $this->logger->info("Dashboard :: render");

        //Form Data
        $this->view->render($response, 'home.twig', $args);
        return $response;
    }
}
