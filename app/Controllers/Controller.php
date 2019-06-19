<?php


namespace App\Controllers;

use Slim\Container;
use Slim\Http\Response;

class Controller
{
    /**
     * @var Container
     */
    protected $container;

    /**
     * Controller constructor.
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * @param $content
     * @param string $type
     */
    public static function flash($content, $type = 'success')
    {
        if (!isset($_SESSION['flash'])) {
            $_SESSION['flash'] = [];
        }

        switch ($type) {
            case 'error':
                $content = '<span class="font-weight-bold">Erreur:</span> ' . $content;
                break;
            case 'success':
                $content = '<span class="font-weight-bold">Succès:</span> ' . $content;
                break;
        }

        $_SESSION['flash'][$type] = $content;
    }

    /**
     * @param Response $response
     * @param $name
     * @param int $status
     * @return Response
     */
    protected function redirect(Response $response, $name, $status = 302)
    {
        return $response->withRedirect($this->router->pathFor($name), $status);
    }

    /**
     * @param $name
     * @return mixed
     * @throws \Interop\Container\Exception\ContainerException
     */
    public function __get($name)
    {
        return $this->container->get($name);
    }
}
