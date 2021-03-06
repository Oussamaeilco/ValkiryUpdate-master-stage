<?php


namespace App\Controllers;

use App\Models\Employee;
use App\Models\License;
use App\Models\Answer;
use Slim\Http\Request;
use Slim\Http\Response;

class CompanyManagerPostController extends Controller
{
    /**
     * @param Request $request
     * @param Response $response
     * @return Response
     */
    public function activate(Request $request, Response $response)
    {
        $user = $this->container->user;
        $license = new License($this->container, ['license' => $request->getParam('inputLicense')]);

        if ($license->apply($user->email)) {
            self::flash("License activée avec succés", 'success');
            return $this->redirect($response, 'home');
        } else {
            self::flash("Impossible d'activer cette license", 'error');
            return $this->redirect($response, 'activate');
        }
    }

    /**
     * @param Request $request
     * @param Response $response
     * @return Response
     */
    public function addEmployee(Request $request, Response $response)
    {
        $user = $this->container->user;
        $email = $request->getParam('inputEmployeeEmail');

        $employee = new Employee($this->container, ['email' => $email, 'owner_id' => $user->id], false);

        if (!$employee->add()) {
            self::flash("Impossible d'ajouter cette adresse e-mail à la liste des employés: {$email}", 'error');
        }

        return $this->redirect($response, 'employees');
    }

    /**
     * @param Request $request
     * @param Response $response
     * @return Response
     */
    public function removeEmployee(Request $request, Response $response)
    {
        $user = $this->container->user;
        $id = $request->getParam('inputEmployeeId');

        $employee = new Employee($this->container, ['id' => $id, 'owner_id' => $user->id]);

        if (!$employee->remove()) {
            self::flash("Impossible de retirer cet employé de la liste", 'error');
        }

        return $this->redirect($response, 'employees');
    }
    /**
     * @param Request $request
     * @param Response $response
     * @return Response
     */
    public function answerQuestion(Request $request, Response $response)
    {
        $question_id = $request->getParam('inputQuestionId');
        $answerText = $request->getParam('inputAnswerText');

        $answer = new Answer($this->container, ['question_id' => $question_id, 'answer' => $answerText], false);

        if (!$answer->add()) {
            self::flash('Impossible de répondre à cette question vous avez sûrement déjà répondu à cette question.', 'error');
        }

        return $this->redirect($response, 'home');
    }
}
