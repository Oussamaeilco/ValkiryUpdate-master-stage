<?php


namespace App\Controllers;

use Slim\Http\Request;
use Slim\Http\Response;
use Respect\Validation\Validator as v;

class PostController extends Controller
{
    /**
     * @param Request $request
     * @param Response $response
     * @return Response
     */
    public function login(Request $request, Response $response)
    {
        $user = $this->user;

        $email = $request->getParam('inputEmail');
        $password = md5($request->getParam('inputPassword'));
        $remember = $request->getParam('remember') ? true : false;

        if ($user->connect(['email' => $email, 'password' => $password], $remember)) {
            if ($this->user->account_type == '-1') {
                return $this->redirect($response, 'admin');
            }

            return $this->redirect($response, 'home');
        } else {
            self::flash('Identifiants invalides ou compte inexistant', 'error');

            return $this->redirect($response, 'login');
        }
    }

    /**
     * @param Request $request
     * @param Response $response
     * @return Response
     */
    public function register(Request $request, Response $response)
    {
        $user = $this->container->user;
        $mode = $this->container->settings['mode'];

        $validation = $this->container->validator->validate($request, array(
            'inputEmail' => v::noWhitespace()->notEmpty()->email()->emailAvailable($this->container->user),
            'inputPassword' => v::noWhitespace()->notEmpty(),
            'accountType' => v::noWhitespace()->intVal()
        ));

        $account_type = $request->getParam('accountType');

        if (!in_array($account_type, ["0", "1"]) && $mode != 'development') {
            self::flash('Type de compte inconnu', 'error');
            return $this->redirect($response, 'register');
        }

        if ($account_type == 0) {
            $emailRegisteredValidation = $this->container->validator->validate($request, array(
                'inputEmail' => v::emailRegistered($this->container)
            ));

            if ($emailRegisteredValidation->failed()) {
                self::flash('Impossible de créer le compte', 'error');
                return $this->redirect($response, 'register');
            }
        }

        if ($validation->failed()) {
            self::flash('Impossible de créer le compte', 'error');
            return $this->redirect($response, 'register');
        }

        $email = $request->getParam('inputEmail');
        $password = md5($request->getParam('inputPassword'));

        if ($user->register($email, $password, $account_type)) {
            return $this->redirect($response, 'home');
        }

        self::flash('Impossible de créer le compte', 'error');
        return $this->redirect($response, 'register');
    }
}
