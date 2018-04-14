<?php
namespace App\Action;

use Slim\Views\Twig;
use Psr\Log\LoggerInterface;
use Slim\Http\Request;
use Slim\Http\Response;

final class ProfileAction
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
        $this->logger->info("Profile :: init");

        if(!$this->loginmanager->isLoggedIn()) {
            $this->logger->info("Profile :: not logged in, redirecting");

            return $response->withRedirect('/login');
        }

        if($request->getMethod() == 'POST') {
            return $this->processSave($request, $response, $args);
        }

        $profileMapper = $this->database->mapper('Entity\Profile');
        $existingProfile = $profileMapper->where(['dn' => $this->loginmanager->getCurrentUser()]);

        if($existingProfile->count()) {
            $args['profile'] = $existingProfile->first();
            $args['profile'] = json_decode($args['profile']->data);
        } else {
            $userMapper = $this->database->mapper('Entity\User');
            $currentUser = $userMapper->where(['dn' => $this->loginmanager->getCurrentUser()])->first();

            $args['profile'] = [
                'Email' => $currentUser->email,
                'Full_Name' => $currentUser->cn,
                'Irc_Nick' => $currentUser->ircnick
            ];
        }

        //Form Data
        $baseFormFilename = dirname(__FILE__) . "/../../configuration/baseform.json";
        $form = $this->formbuilder->buildForm(json_decode(file_get_contents($baseFormFilename)), $args['profile'], [6, 99]);

        $args['isLoggedIn'] = $this->loginmanager->isLoggedIn();
        $args['isAdminUser'] = $this->loginmanager->isAdminUser();
        $args['currentPage'] = 'profile';
        $args['form'] = $form;

        $args['saved'] = "";
        if(isset($_GET['success'])) {
            $args['saved'] = true;
        }

        $args['newuser'] = isset($_GET['newuser']);

        $this->logger->info("Profile :: render");

        $this->view->render($response, 'profile.twig', $args);
        return $response;
    }

    public function processSave(Request $request, Response $response, $args)
    {
        $this->logger->info("Profile :: saving profile");

        $profileMapper = $this->database->mapper('Entity\Profile');

        $existingProfile = $profileMapper->where(['dn' => $this->loginmanager->getCurrentUser()]);
        if($existingProfile->count()) {
            $profile = $existingProfile->first();
        } else {
            $profile = $profileMapper->build(['dn' => $this->loginmanager->getCurrentUser()]);
        }

        $profile->data = json_encode($request->getParsedBody());

        $profileMapper->save($profile);

        $this->logger->info("Profile :: saved");

        return $response->withRedirect("/?profilesuccess=true");
    }
}
