<?php
namespace App\Action;

use Slim\Views\Twig;
use Psr\Log\LoggerInterface;
use Slim\Http\Request;
use Slim\Http\Response;

final class StatsAction
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
        $this->logger->info("Stats :: init");

        if(!$this->loginmanager->isLoggedIn()) {
            $this->logger->info("Stats :: not logged in");

            return $response->withRedirect('/login');
        }

        $args['isLoggedIn'] = $this->loginmanager->isLoggedIn();

        $conferenceMapper = $this->database->mapper('Entity\Conference');
        $conferences = $conferenceMapper->where(['slug' => $args['conferenceSlug']]);

        if(!$conferences->count()) {
            return $response->withRedirect("/");
        }

        $adminMapper = $this->database->mapper('Entity\Admin');
        if(!$adminMapper->where([
                'conference_id' => $conferences->first()->id,
                'dn' => $this->loginmanager->getCurrentUser()
            ])->count()) {
        
            $this->logger->info("Stats :: not an admin user");

            return $response->withRedirect('/?access=denied');   
        }

        $args['conference'] = $conferences->first();

        $registrationMapper = $this->database->mapper('Entity\Registration');
        $registrations = $registrationMapper->all()->where(['conference_id' => $args['conference']->id]);

        $args['registrationdata'] = [];
        $args['registrationcount'] = $registrations->count();
        foreach($registrations as $registration) {
            $regdata = (array)json_decode($registration->data);
            foreach($regdata as $key => $reg) {
                if(is_object($reg)) {
                    foreach((array)$reg as $k => $v) {
                        $regdata[implode("_", [$key, $k])] = $v;
                    }
                    unset($regdata[$key]);
                }
                unset($regdata['Save']);
            }

            $args['registrationdata'][] = array_merge(['dn' => $registration->dn], $regdata);
        }

        $args['registrationkeys'] = array_keys($args['registrationdata'][0]);

        $this->logger->info("Stats :: render");

        $this->view->render($response, 'stats.twig', $args);
        return $response;
    }
}

