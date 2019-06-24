<?php


namespace App\Controllers;

use App\Models\Employee;
use App\Models\License;
use App\Models\Answer;
use Slim\Http\Request;
use Slim\Http\Response;
use App\Models\Promotion;

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

    public function modifyAnswer(Request $request, Response $response)
    {
        $question_id = $request->getParam('inputQuestionId');
        $answerText = $request->getParam('inputAnswerText');

        $answer = new Answer($this->container, ['question_id' => $question_id, 'answer' => $answerText], false);

        if (!$answer->modifyAnswer()) {
            self::flash('Impossible de modifier cette réponse.', 'error');
        }

        return $this->redirect($response, 'home');
    }

    public function addPromotion(Request $request, Response $response)
    {
        $user = $this->container->user;
        //$promotion_id = $request->getParam('promotion_id');
        $description = $request->getParam('promotion_name');

        $promotion = new Promotion($this->container, ['description' => $description,'owner_id' => $user->id], false);
        if (!$promotion->add()) {
            self::flash("Impossible d\'ajouter cette promotion.", 'error');
        }

        return $this->redirect($response, 'home');
    }

    public function modifyPromotion(Request $request, Response $response)
    {
        $user = $this->container->user;
        $promotion_id = $request->getParam('id');
        $description = $request->getParam('promotion_name');
        
        $promotion = new Promotion($this->container, ['id' => $promotion_id], false);

        if (!$promotion->modifyAnswer()) {
            self::flash('Impossible de modifier cette promotion.', 'error');
        }

        return $this->redirect($response, 'home');
    }

    public function PromotionHome(Request $request, Response $response)
    {
        $user = $this->container->user;
        $license = new License($this->container, ['user_email' => $user->email]);
        $pool = new QuestionPool($this->container, ['owner_id' => $user->id]);
        $promotions=new PromotionCollection($this->container,['owner_id' => $user->id]);

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


        return $this->redirect($response, 'home');
    }
}
