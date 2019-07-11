<?php


namespace App\Controllers;

use App\Models\Employee;
use App\Models\License;
use App\Models\Answer;
use Slim\Http\Request;
use Slim\Http\Response;
use App\Models\Promotion;
use App\Models\QuestionPool;
use DateTime;

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

    
    
    public function addPeriode(Request $request, Response $response)
    {
        $user = $this->container->user;
        $owner_id=$user->id;
        $period_start=DateTime::createFromFormat('Y-m-d',$request->getParam('inputStartDate'));
        $period_end=DateTime::createFromFormat('Y-m-d',$request->getParam('inputEndDate'));
        $reponse_date=DateTime::createFromFormat('Y-m-d',$request->getParam('inputEndDateR'));
        $bool1=(date_diff($period_end,$reponse_date))->format('%R');
        $bool2=(date_diff($period_start,$period_end))->format('%R');
        $diff_end=true;
        $diff_start=true;
        if($bool1=="+"){
            $diff_end=false;
        }
        if($bool2=="+"){
            $diff_start=false;
        }
        $array = [
            'owner_id' => $owner_id,
            'period_start' => $request->getParam('inputStartDate'),
            'period_end' => $request->getParam('inputEndDate'),
            'reponse' => $request->getParam('inputEndDateR')
        ];
        $pool=new QuestionPool($this->container,$array,"create_manual",false);
       if(!$diff_start){  
            if(!$diff_end){
                if (!$pool->add()) {
                    self::flash("Impossible d'ajouter cette période à la liste", 'error');
                }
            }
            else{
                self::flash("La période de fin de Réponse doit être supérieur à la période de fin Question", 'error');
            }
       }
       else{
        self::flash("La période de Début de Question doit être inférieur à la période de fin Question", 'error');
       }
        return $this->redirect($response, 'pools');
    }

    public function removePeriode(Request $request, Response $response)
    {
        $user = $this->container->user;
        $id = $request->getParam('inputId');

        $pool = new QuestionPool($this->container, ['id' => $id, 'owner_id' => $user->id],'create_manual');

        if (!$pool->remove()) {
            self::flash("Impossible de supprimer cette période de la liste", 'error');
        }

        return $this->redirect($response, 'pools');
    }

    /**
     * @param Request $request
     * @param Response $response
     * @return Response
     */
    public function editPeriode(Request $request, Response $response)
    {
        $id = $request->getParam('inputEditId');
        $user = $this->container->user;
        $owner_id=$user->id;
        /* Verification des valeurs entrer */
        $period_start=DateTime::createFromFormat('Y-m-d',$request->getParam('inputEditStartDate'));
        $period_end=DateTime::createFromFormat('Y-m-d',$request->getParam('inputEditEndDate'));
        $reponse_date=DateTime::createFromFormat('Y-m-d',$request->getParam('inputEditEndDateR'));
        $bool1=(date_diff($period_end,$reponse_date))->format('%R');
        $bool2=(date_diff($period_start,$period_end))->format('%R');
        $diff_end=true;
        $diff_start=true;
        if($bool1=="+"){
            $diff_end=false;
        }
        if($bool2=="+"){
            $diff_start=false;
        }
        /* ------------------------------------- */
        $array = [
            'id' => $id,
            'period_start' => $request->getParam('inputEditStartDate'),
            'period_end' => $request->getParam('inputEditEndDate'),
            'owner_id' => $owner_id,
            'reponse' =>$request->getParam('inputEditEndDateR')
        ];
        $pools=new QuestionPool($this->container,$array,'create_manual',null);
       if(!$diff_start){
           if(!$diff_end){
                if ($pools->edit($array,['id' => $id])) {
                    self::flash("Période correctement modifiée", 'success');
                } else {
                    self::flash("Impossible de modifier la période".$pools->getValues(), 'error');
                }
           }
           else{
                   self::flash("La période de fin de Réponse doit être supérieur à la période de fin Question", 'error');
               }
        }
        else{
                self::flash("La période de Début de Question doit être inférieur à la période de fin Question", 'error');
        }
        return $this->redirect($response, 'pools');
    }


}
