<?php


namespace App\Middlewares;

use Slim\Http\Response;
use Slim\Http\Request;
use Twig_Environment;

class FlashMiddleware
{
    /**
     * @var Twig_Environment
     */
    private $twig;

    /**
     * FlashMiddleware constructor.
     * @param Twig_Environment $twig
     */
    public function __construct(Twig_Environment $twig)
    {
        $this->twig = $twig;
    }

    /**
     * @param Request $request
     * @param Response $response
     * @param callable $next
     * @return mixed
     */
    public function __invoke(Request $request, Response $response, callable $next)
    {
        $flash = isset($_SESSION['flash']) ? $_SESSION['flash'] : [];

        $this->twig->addGlobal('flash', $flash);

        if (isset($_SESSION['flash'])) {
            unset($_SESSION['flash']);
        }

        return $next($request, $response);
    }
}
