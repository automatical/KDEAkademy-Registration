<?php
namespace App\Action;

use Slim\Views\Twig;
use Psr\Log\LoggerInterface;
use Slim\Http\Request;
use Slim\Http\Response;

final class LogoutAction
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
        $this->logger->info("Logout");
        
        $this->loginmanager->logout();

        return $response->withRedirect("/");
    }
}
