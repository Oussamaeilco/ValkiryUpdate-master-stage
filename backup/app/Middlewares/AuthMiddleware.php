<?php


namespace App\Middlewares;

use App\Models\User;
use Slim\Container;
use Slim\Http\Request;
use Slim\Http\Response;

class AuthMiddleware
{
    /**
     * @var Container
     */
    private $container;

    /**
     * AuthMiddleware constructor.
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * @param Request $request
     * @param Response $response
     * @param callable $next
     * @return mixed
     */
    public function __invoke(Request $request, Response $response, callable $next)
    {
        $user = $this->container->user;
        /** @var User $user */

        if (isset($_SESSION['userSession'])) {
            if ($user->connect(['session' => $_SESSION['userSession']])) {
                return $next($request, $response);
            }

            unset($_SESSION['userSession']);
        }

        if (isset($_COOKIE['userSession'])) {
            if ($user->connect(['session' => $_COOKIE['userSession']])) {
                return $next($request, $response);
            }

            unset($_COOKIE['userSession']);
        }

        return $next($request, $response);
    }
}
