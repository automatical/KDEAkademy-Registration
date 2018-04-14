<?php
namespace App\Action;

use Slim\Views\Twig;
use Psr\Log\LoggerInterface;
use Slim\Http\Request;
use Slim\Http\Response;

final class CancelAction
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
        $this->logger->info("Register :: cancel");

        if(!$this->loginmanager->isLoggedIn()) {
            $this->logger->info("Register :: not logged in");

            return $response->withRedirect('/login');
        }

        return $this->processCancel($request, $response, $args);

        $this->view->render($response, 'register.twig', $args);
        return $response;
    }

    public function processCancel(Request $request, Response $response, $args)
    {
        $this->logger->info("Register :: cancel");

        $conferenceMapper = $this->database->mapper('Entity\Conference');
        $conferences = $conferenceMapper->where(['slug' => $args['conferenceSlug']]);

        if(!$conferences->count()) {
            return $response->withRedirect("/");
        }

        $args['conference'] = $conferences->first();

        $baseRegistration = [
            'dn' => $this->loginmanager->getCurrentUser(),
            'conference_id' => $args['conference']->id
        ];

        $registrationMapper = $this->database->mapper('Entity\Registration');
        $registrations = $registrationMapper->where($baseRegistration);

        if($registrations->count()) {
            $registration = $registrations->first();
        }

        $registration->cancelled = 1;

        $registrationMapper->save($registration);

        $this->logger->info("Register :: saved");

        return $response->withRedirect(implode('', ["/?registrationcancelled=", $args['conference']->slug]));
    }
}
