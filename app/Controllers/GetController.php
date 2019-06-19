<?php


namespace App\Controllers;

use App\Models\EmployeeCollection;
use App\Models\License;
use App\Models\LicenseCollection;
use App\Models\QuestionCollection;
use App\Models\QuestionPool;
use App\Models\QuestionPoolCollection;
use Slim\Http\Request;
use Slim\Http\Response;

class GetController extends Controller
{
    /**
     * @param Response $response
     * @param $file
     * @param array $params
     * @return mixed
     */
    protected function render(Response $response, $file, $params = [])
    {
        $mode = $this->container->settings['mode'];

        $params = array_merge($params, ['APP_MODE' => $mode ? $mode : 'production']);

        return $this->view->render($response, $file, $params);
    }

    /**
     * @param Request $request
     * @param Response $response
     * @return mixed
     */
    public function overview(Request $request, Response $response)
    {
        return $this->render($response, 'public/overview.twig');
    }

    /**
     * @param Request $request
     * @param Response $response
     * @return mixed
     */
    public function login(Request $request, Response $response)
    {
        return $this->render($response, 'public/login.twig');
    }

    /**
     * @param Request $request
     * @param Response $response
     * @return mixed
     */
    public function register(Request $request, Response $response)
    {
        return $this->render($response, 'public/register.twig');
    }

    /**
     * @param Request $request
     * @param Response $response
     * @return mixed
     */
    public function admin(Request $request, Response $response)
    {
        $licenses = new LicenseCollection($this->container, 'all');

        return $this->render($response, 'admin/home.twig', ['licenses' => $licenses->categorize()]);
    }

    public function pools(Request $request, Response $response){
      $user = $this->container->user;

      if($user->account_type == '1'){
        return $this->companyManagerPools($request, $response);
      }

      return $this->employeePools($request, $response);
    }

    public function companyManagerPools(Request $request, Response $response){
      $user = $this->container->user;
      $pools = new QuestionPoolCollection($this->container, $user->id);

      $this->render(
          $response,
          'company_manager/pools.twig',
          [
            'pools' => $pools->toArray()
          ]
      );
    }

    public function employeePools(Request $request, Response $response){
      $user = $this->container->user;
      $pools = new QuestionPoolCollection($this->container, $user->getOwnerId());

      $this->render(
          $response,
          'employee/pools.twig',
          [
            'pools' => $pools->toArray()
          ]
      );
    }

    public function pool(Request $request, Response $response){
      $user = $this->container->user;

      if ($user->account_type == '1'){
        return $this->companyManagerPool($request, $response);
      }

      return $this->employeePool($request, $response);
    }

    public function companyManagerPool(Request $request, Response $response){
      $pool_id = $request->getAttribute('route')->getArgument('id');

      $pool = new QuestionPool($this->container, ['owner_id' => $this->container->user->id, 'id' => $pool_id], 'fetch');

      if($pool->isActive()){
        return $this->redirect($response, 'home');
      }

      $questions = $pool->upvoted();

      $this->render(
          $response,
          'company_manager/pool.twig',
          [
            'pool' => $pool->toArray(),
            'questions' => $questions->toArray(),
            'answers' => $questions->getAnswers()->indexize()
          ]
      );
    }

    public function employeePool(Request $request, Response $response){
      $pool_id = $request->getAttribute('route')->getArgument('id');

      $pool = new QuestionPool($this->container, ['owner_id' => $this->container->user->getOwnerId(), 'id' => $pool_id], 'fetch');

      if($pool->isActive()){
        return $this->redirect($response, 'home');
      }

      $questions = $pool->upvoted();

      $this->render(
          $response,
          'employee/pool.twig',
          [
            'pool' => $pool->toArray(),
            'questions' => $questions->toArray(),
            'answers' => $questions->getAnswers()->indexize()
          ]
      );
    }

    /**
     * @param Request $request
     * @param Response $response
     * @return mixed|Response
     */
    public function home(Request $request, Response $response)
    {
        $user = $this->container->user;

        if ($user->account_type == '1') {
            return $this->companyManagerHome($request, $response);
        }
		//return $response->withRedirect('/pool/20');
        return $this->employeeHome($request, $response);
    }

    /**
     * @param Request $request
     * @param Response $response
     * @return mixed|Response
     */
    public function companyManagerHome(Request $request, Response $response)
    {
        $user = $this->container->user;
        $license = new License($this->container, ['user_email' => $user->email]);
        $pool = new QuestionPool($this->container, ['owner_id' => $user->id]);

        switch ($license->getStatus()) {
            case License::$STATUS_EMPTY:
                return $this->redirect($response, 'activate');
                break;
            case License::$STATUS_EXPIRED:
                return $this->render($response, 'company_manager/expired.twig', $license->toArray());
                break;
            case License::$STATUS_UNSTARTED:
                return $this->render($response, 'company_manager/unstarted.twig', $license->toArray());
        }

        $questions = $pool->upvoted();

        return $this->render(
            $response,
            'company_manager/home.twig',
            [
                'route' => 'home',
                'license' => $license->toArray(),
                'pool' => $pool->toArray(),
                'poolExpiration' => $pool->getExpiration(),
                'questions' => $questions->toArray(),
                'answers' => $questions->getAnswers()->indexize()
            ]
        );
    }

    /**
     * @param Request $request
     * @param Response $response
     * @return mixed
     */
    public function activate(Request $request, Response $response)
    {
        return $this->render($response, 'company_manager/activate.twig');
    }

    /**
     * @param Request $request
     * @param Response $response
     * @return mixed
     */
    public function license(Request $request, Response $response)
    {
        $user = $this->container->user;
        $license = new License($this->container, ['user_email' => $user->email]);

        $data = [
            'license' => $license->toArray(),
            'status' => $license->getStatus()
        ];

        return $this->render($response, 'company_manager/license.twig', $data);
    }

    /**
     * @param Request $request
     * @param Response $response
     * @return mixed
     */
    public function employees(Request $request, Response $response)
    {
        $user = $this->container->user;
        $employees = new EmployeeCollection($this->container, $user->id);

        return $this->render($response, 'company_manager/employees.twig', [
            'route' => 'employees',
            'employees' => $employees->toArray()
        ]);
    }

    /**
     * @param Request $request
     * @param Response $response
     * @return mixed
     */
    public function employeeHome(Request $request, Response $response)
    {
        $user = $this->container->user;
        $owner = $user->getOwnerId();

        if (is_null($owner)) {
            return $this->render($response, 'employee/no_owner.twig');
        }

        $pool = new QuestionPool($this->container, ['owner_id' => $owner]);

        $questions['user'] = $pool->getQuestions(['employee_id' => $user->id]);
        $questions['voted'] = $pool->getVoted($user->id);
        $questions['upvoted'] = $pool->upvoted(3)->restrict();
        $questions['random'] = $pool->random(7)->except($questions['user'])->except($questions['upvoted'])->restrict();

        $all = new QuestionCollection($this->container);
        $all->merge($questions['user'])->merge($questions['upvoted'])->merge($questions['random'])->uniq();

        $answers = $all->getAnswers();

        return $this->render($response, 'employee/home.twig', [
            'pool' => $pool->toArray(),
            'questions' => [
                'user' => $questions['user']->toArray(),
                'voted' => $questions['voted']->indexize(),
                'upvoted' => $questions['upvoted']->toArray(),
                'random' => $questions['random']->toArray(),
                'canAsk' => (!$pool->hasAsked($user->id) && $pool->isOpen())
            ],
            'answers' => $answers->indexize()
        ]);
    }

    /**
     * @param Request $request
     * @param Response $response
     * @return Response
     */
    public function disconnect(Request $request, Response $response)
    {
        $this->user->disconnect();

        return $this->redirect($response, 'login');
    }

    public function accountInformations(Request $request, Response $response){
      $data = ['email' => $this->user->email];
      if($this->user->account_type == '1'){
        return $this->render($response, 'company_manager/account.twig', $data);
      }
      return $this->render($response, 'employee/account.twig', $data);
    }

    public function getAllQuestions(Request $request, Response $response){
      $user = $this->container->user;

      if(is_null($this->user->id)){
        return $this->redirect($response, 'login');
      }
      if($this->user->account_type == '1'){
        $pool = new QuestionPool($this->container, ['owner_id' => $this->user->id]);
      }
      else{
        $owner = $user->getOwnerId();
        if (is_null($owner)) {
          return $this->render($response, 'employee/no_owner.twig');
        }
        $pool = new QuestionPool($this->container, ['owner_id' => $owner]);
      }

      header('Content-Type: application/json');
      echo json_encode($pool->getQuestions()->toArray());
      exit;
    }
}
