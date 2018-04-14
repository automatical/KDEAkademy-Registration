<?php
namespace App\Action\API;

use Slim\Views\Twig;
use Psr\Log\LoggerInterface;
use Slim\Http\Request;
use Slim\Http\Response;

final class RegistrationAction
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
        $this->logger->info("API :: init");

        $response = $response->withHeader('Content-type', 'application/json');

        // Get Conference
        $conferenceMapper = $this->database->mapper('Entity\Conference');
        $results = $conferenceMapper->all()->where(['slug' => $args['conferenceSlug']]);

        if(!$results->count()) {
            return $this->failureResponse($response);
        }

        if(!isset($_GET['feifjw0efjwef']) && !$this->loginmanager->isLoggedIn()) {
            return $this->failureResponse($response);
        }

        $adminMapper = $this->database->mapper('Entity\Admin');
        
        if(!$adminMapper->where([
                'conference_id' => $results->first()->id,
                'dn' => $this->loginmanager->getCurrentUser()
            ])->count()) {
        
            $this->logger->info("Stats :: not an admin user");

            return $response->withRedirect('/?access=denied');   
        }


        $profileMapper = $this->database->mapper('Entity\Profile');

        $conference = $results->first();

        $registrationMapper = $this->database->mapper('Entity\Registration');
        $registrations = [];
        foreach($registrationMapper->all()->where(['conference_id' => $conference->id]) as $registration) {
            $registration->data = json_decode($registration->data);
            $registrations[] = $registration;
        }

        $args['data'] = [
            'conference' => [
                'id' => $conference->id,
                'name' => $conference->name,
                'description' => $conference->description,
                'start_date' => $conference->start_date,
                'end_date' => $conference->end_date,
            ],
            'registrations' => array_map(function($entity) use ($profileMapper) {
                $entity->profile = $profileMapper->all()->where(['dn' => $entity->dn])->first();
                $entity->profile->data = json_decode($entity->profile->data);
                return $entity;
            }, $registrations)
        ];

        $this->logger->info("Stats :: render");
        
        $this->view->render($response, 'api/registration.twig', $args);
        return $response;   
    }

    private function failureResponse($response) {
        $args['data'] = [
            'success' => false
        ];

        $this->view->render($response, 'api/registration.twig', $args);
        return $response;
    }
}
