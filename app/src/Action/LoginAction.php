<?php
namespace App\Action;

use Slim\Views\Twig;
use Psr\Log\LoggerInterface;
use Slim\Http\Request;
use Slim\Http\Response;

final class LoginAction
{
    private $view;
    private $logger;
    private $loginmanager;

    public function __construct(Twig $view, LoggerInterface $logger, $loginmanager)
    {
        $this->view = $view;
        $this->logger = $logger;
        $this->loginmanager = $loginmanager;
    }

    public function __invoke(Request $request, Response $response, $args)
    {
        $this->logger->info("Login :: init");

        if($this->loginmanager->isLoggedIn()) {
            $this->logger->info("Login :: already logged in");

            return $response->withRedirect('/');
        }

        if($request->getMethod() == 'POST') {
            return $this->processLogin($request, $response, $args);
        }

        $args['failedlogin'] = isset($_GET['failed']);

        $args['isLoggedIn'] = $this->loginmanager->isLoggedIn();
        $args['isAdminUser'] = $this->loginmanager->isAdminUser();

        $this->logger->info("Login :: render");

        $this->view->render($response, 'login.twig', $args);

        return $response;
    }

    public function processLogin(Request $request, Response $response, $args)
    {

        $this->logger->info("Login :: login attempt");
        
        $params = $request->getParsedBody();
        if($this->loginmanager->login($params['username'], $params['password'])) {
            $this->logger->info("Login :: success");
            return $response->withRedirect("/");
        }

        $this->logger->info("Login :: failure");
        return $response->withRedirect("/login?failed=true");
    }
}
