<?php


namespace App\Middlewares;

use App\Controllers\Controller;
use Slim\Container;
use Slim\Http\Request;
use Slim\Http\Response;

class PermissionMiddleware
{
    /**
     * @var Container
     */
    private $container;

    /** @var array */
    private $level_map = [
        'default' => 'public',
        '-1' => 'admin',
        '0' => 'employee',
        '1' => 'company_manager'
    ];

    /** @var array */
    private $routes = [
        'all' => [
            'disconnect',
            'allQuestions'
        ],
        'public' => [
            'overview',
            'login',
            'register',
            'postLogin',
            'postRegister',
            'modifyAccount'
        ],
        'admin' => [
            'admin',
            'adminAddLicense',
            'adminEditLicense',
            'adminAbortLicense',
            'account'
        ],
        'employee' => [
            'home',
            'addQuestion',
            'voteQuestion',
            'unvoteQuestion',
            'account',
            'pools',
            'pool'
        ],
        'company_manager' => [
            'home',
            'activate',
            'license',
            'employees',
            'companyManagerActivate',
            'companyManagerAddEmployee',
            'companyManagerRemoveEmployee',
            'answerQuestion',
            'account',
            'pools',
            'pool',
            'modifyAnswer',
            'addPromotion',
            'addPeriode',
            'removePeriode'
        ]
    ];

    /**
     * PermissionMiddleware constructor.
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
        $route = $request->getAttribute('route');
        $routeName = $route->getName();

        $account_type = $this->container->user->account_type;
        $level = (is_null($account_type) || !array_key_exists($account_type, $this->level_map)) ? $this->level_map['default'] : $this->level_map[$account_type];

        if (!in_array($routeName, $this->routes['all']) && !in_array($routeName, $this->routes[$level])) {
            // Flash an error only for non public routes
            if (!in_array($routeName, $this->routes['public'])) {
                Controller::flash('Vous ne pouvez pas accéder à cette page', 'error');
            }

            // Return to the first route of the current level allowed routes
            $response = $response->withRedirect($this->container->router->pathFor(current($this->routes[$level])));
        }

        return $next($request, $response);
    }
}
