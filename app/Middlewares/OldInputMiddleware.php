<?php


namespace App\Middlewares;

use Slim\Http\Response;
use Slim\Http\Request;
use Twig_Environment;

class OldInputMiddleware
{
    /**
     * @var Twig_Environment
     */
    private $twig;

    public function __construct(Twig_Environment $twig)
    {
        $this->twig = $twig;
    }

    /**
     * @param Request $request
     * @param Response $response
     * @param callable $next
     * @return Response
     */
    public function __invoke(Request $request, Response $response, callable $next)
    {
        if (isset($_SESSION['old'])) {
            $this->twig->addGlobal('old', $_SESSION['old']);
        }

        $_SESSION['old'] = $request->getParams();

        $response = $next($request, $response);
        return $response;
    }
}
