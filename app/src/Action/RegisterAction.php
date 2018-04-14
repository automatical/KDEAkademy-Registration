<?php
namespace App\Action;

use Slim\Views\Twig;
use Psr\Log\LoggerInterface;
use Slim\Http\Request;
use Slim\Http\Response;

final class RegisterAction
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
        $this->logger->info("Register :: init");

        if(!$this->loginmanager->isLoggedIn()) {
            $this->logger->info("Register :: not logged in");

            return $response->withRedirect('/login');
        }

        if($request->getMethod() == 'POST') {
            return $this->processSave($request, $response, $args);
        }

        $args['isLoggedIn'] = $this->loginmanager->isLoggedIn();
        $args['isAdminUser'] = $this->loginmanager->isAdminUser();

        $conferenceMapper = $this->database->mapper('Entity\Conference');
        $conferences = $conferenceMapper->where(['slug' => $args['conferenceSlug']]);

        if(!$conferences->count()) {
            return $response->withRedirect("/");
        }

        $args['conference'] = $conferences->first();

        $baseRegistration = 
            ['dn' => $this->loginmanager->getCurrentUser(),
            'conference_id' => $args['conference']->id];

        $registrationMapper = $this->database->mapper('Entity\Registration');
        $registrations = $registrationMapper->where($baseRegistration);

        if($registrations->count()) {
            $registration = $registrations->first();
        } else {
            $registration = $registrationMapper->build($baseRegistration);
        }

        $args['form'] = $this->formbuilder->buildForm(json_decode($args['conference']->form), json_decode($registration->data), [2,99]);

        $this->logger->info("Register :: render");

        $args['braintreekey'] = 'w3nr9fcp8xmpvppc';

        $this->view->render($response, 'register.twig', $args);
        return $response;
    }

    public function processSave(Request $request, Response $response, $args)
    {
        $this->logger->info("Register :: save");

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
        } else {
            $registration = $registrationMapper->build($baseRegistration);
        }

        $registration->data = json_encode($request->getParsedBody());
        $registration->cancelled = 0;

        $registrationMapper->save($registration);

        $this->logger->info("Register :: saved");

        return $response->withRedirect(implode('', ["/?registrationsuccess=", $args['conference']->slug]));
    }
}
