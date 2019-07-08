<?php


namespace App\Controllers;

use App\Models\Employee;
use App\Models\License;
use App\Models\Answer;
use Slim\Http\Request;
use Slim\Http\Response;
use App\Models\Promotion;
use App\Models\QuestionPool;

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
        $promotion=$request->getParam('promotion_id');
        $employee = new Employee($this->container, ['email' => $email, 'owner_id' => $user->id, 'promotion_id' =>$promotion ] , false);

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

    
    /**
     * @param Request $request
     * @param Response $response
     * @return Response
     */
    public function addLicense(Request $request, Response $response)
    {
        $array = [
            'user_email' => ($request->getParam('inputUserEmail')) ? $request->getParam('inputUserEmail') : null,
            'start_date' => $request->getParam('inputStartDate'),
            'end_date' => $request->getParam('inputEndDate')
        ];

        if ($request->getParam('customLicense')) {
            $key = $request->getParam('inputLicense');
            $array['license'] = $key;
        }

        $license = new License($this->container, $array, false);

        if ($license->exists(['license'])) {
            self::flash("La license <span class=\"font-weight-bold\">{$key}</span> est déjà attribuée", 'error');
            return $this->redirect($response, 'admin');
        }

        if ($license->add()) {
            self::flash("License correctement créée: <span class=\"font-weight-bold\">{$license->getKey()}</span>", 'success');
        } else {
            self::flash("Impossible d'attribuer la license: <span class=\"font-weight-bold\">{$license->getKey()}</span>", 'error');
        }

        return $this->redirect($response, 'admin');
    }

    
    /**
     * @param Request $request
     * @param Response $response
     * @return Response
     */
    public function editLicense(Request $request, Response $response)
    {
        $id = $request->getParam('inputEditId');
        $user = $this->container->user;
        $owner_id=$user->id;
        $period_end=strtotime($request->getParam('inputEditEndDate'));
        $reponse_date=strtotime($request->getParam('inputEditEndDateR'));
        $array = [
            'id' => $id,
            'period_start' => $request->getParam('inputEditStartDate'),
            'period_end' => $request->getParam('inputEditEndDate'),
            'owner_id' => $owner_id
        ];
        $rep=($period_end-$reponse_date)/86400;
        //$license = new License($this->container, $array, false, $regenerate);
        $pools=new QuestionPool($this->container,$array,false);
        $pools->getExpiration($rep);

        
        
        if ($pools->exists(['pool']) && $pools->idFor(['pool']) != $id) {
            self::flash("La période <span class=\"font-weight-bold\">{$pools->getKey()}</span> est déjà attribuée", 'error');
            return $this->redirect($response, 'admin');
        }

        if ($pools->edit()) {
            self::flash("License correctement modifiée: <span class=\"font-weight-bold\">{$pools->getKey()}</span>", 'success');
        } else {
            self::flash("Impossible de modifier la license: <span class=\"font-weight-bold\">{$pools->getKey()}</span>", 'error');
        }

        return $this->redirect($response, 'admin');
    }

    public function addPeriode(Request $request, Response $response)
    {
        $user = $this->container->user;
        $owner_id=$user->id;
        $period_end=strtotime($request->getParam('inputEndDate'));
        $reponse_date=strtotime($request->getParam('inputEndDateR'));
        $rep=($period_end-$reponse_date)/86400;

        $array = [
            'owner_id' => $owner_id,
            'period_start' => $request->getParam('inputStartDate'),
            'period_end' => $request->getParam('inputEndDate')
        ];
        $pool=new QuestionPool($this->container,$array,"create",false);
        $pool->getExpiration($rep);
        //if (!$pool->add()) {
          //  self::flash("Impossible d'ajouter période", 'error');
        //}

        return $this->redirect($response, 'pools');
    }

    public function removePeriode(Request $request, Response $response)
    {
        $user = $this->container->user;
        $id = $request->getParam('inputId');

        $pool = new QuestionPool($this->container, ['id' => $id, 'owner_id' => $user->id]);

        if (!$pool->remove()) {
            self::flash("Impossible de supprimer cette période de la liste", 'error');
        }

        return $this->redirect($response, 'pools');
    }

}
