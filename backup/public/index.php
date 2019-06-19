<?php

use App\Controllers\AdminPostController;
use App\Controllers\CompanyManagerPostController;
use App\Controllers\GetController;
use App\Controllers\PostController;
use App\Controllers\EmployeePostController;
use App\Middlewares\AuthMiddleware;
use App\Middlewares\FlashMiddleware;
use App\Middlewares\OldInputMiddleware;
use App\Middlewares\PermissionMiddleware;
use Respect\Validation\Validator as v;

// Assets fixing
if (PHP_SAPI === 'cli-server' && $_SERVER['SCRIPT_FILENAME'] !== __FILE__) {
    return false;
}

// Vendor autoload
require __DIR__ . '/../vendor/autoload.php';

// Session start
session_start();

// Slim app
$app = new \Slim\App([
    'settings' => require(__DIR__ . '/../config/SlimProductionSettings.php')
]);

// Container
require __DIR__ . '/../app/container.php';
/** @var Slim\Container $container */

// Middlewares
$app->add(new PermissionMiddleware($container));
$app->add(new AuthMiddleware($container));
$app->add(new FlashMiddleware($container->view->getEnvironment()));
$app->add(new OldInputMiddleware($container->view->getEnvironment()));

//Validations
v::with('App\\Validation\\Rules\\');

/****************** TEST ******************/



/****************** TEST ******************/

// Routes

//  public:get
$app->get('/', GetController::class . ':overview')->setName('overview');
$app->get('/login', GetController::class . ':login')->setName('login');
$app->get('/register', GetController::class . ':register')->setName('register');
$app->get('/disconnect', GetController::class . ':disconnect')->setName('disconnect');
//  public:post
$app->post('/login', PostController::class . ':login')->setName('postLogin');
$app->post('/register', PostController::class . ':register')->setName('postRegister');
$app->post('/modifyAccount', PostController::class . ':modifyAccount')->setName('modifyAccount');

//  user:get
$app->get('/home', GetController::class . ':home')->setName('home');
$app->get('/pools', GetController::class . ':pools')->setName('pools');
$app->get('/account', GetController::class . ':accountInformations')->setName('account');
$app->get('/pool/{id}', GetController::class . ':pool')->setName('pool');

//  employee:get
$app->get('/allQuestions', GetController::class . ':getAllQuestions')->setName('allQuestions');
//  employee:post
$app->post('/addQuestion', EmployeePostController::class . ':addQuestion')->setName('addQuestion');
$app->post('/voteQuestion', EmployeePostController::class . ':voteQuestion')->setName('voteQuestion');
$app->post('/unvoteQuestion', EmployeePostController::class . ':unvoteQuestion')->setName('unvoteQuestion');

//  company_manager:get
$app->get('/activate', GetController::class . ':activate')->setName('activate');
$app->get('/license', GetController::class . ':license')->setName('license');
$app->get('/employees', GetController::class . ':employees')->setName('employees');
//  company_manager:post
$app->post('/activate', CompanyManagerPostController::class . ':activate')->setName('companyManagerActivate');
$app->post('/addEmployee', CompanyManagerPostController::class . ':addEmployee')->setName('companyManagerAddEmployee');
$app->post('/removeEmployee', CompanyManagerPostController::class . ':removeEmployee')->setName('companyManagerRemoveEmployee');
$app->post('/answerQuestion', CompanyManagerPostController::class . ':answerQuestion')->setName('answerQuestion');
$app->post('/modifyAnswer', CompanyManagerPostController::class . ':modifyAnswer')->setName('modifyAnswer');

//  admin:get
$app->get('/admin', GetController::class . ':admin')->setName('admin');
//  admin:post
$app->post('/admin/addLicense', AdminPostController::class . ':addLicense')->setName('adminAddLicense');
$app->post('/admin/editLicense', AdminPostController::class . ':editLicense')->setName('adminEditLicense');
$app->post('/admin/abortLicense', AdminPostController::class . ':abortLicense')->setName('adminAbortLicense');

$app->run();
