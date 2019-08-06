<?php


namespace App\Controllers;
use Slim\Http\UploadedFile;
use App\Models\Employee;
use App\Models\License;
use App\Models\Answer;
use Slim\Http\Request;
use Slim\Http\Response;
use App\Models\Promotion;
use App\Models\QuestionPool;
use DateTime;
use PhpOffice\PhpSpreadsheet\IOFactory;
use App\Models\QuestionPoolCollection;
use Respect\Validation\Rules\Equals;

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
    public function addEmployees(Request $request, Response $response)
    {
        $user = $this->container->user;
        $promotion=$request->getParam('promotion_id');
        $excel_path = $request->getUploadedFiles();
        $excel_path=$excel_path["fichier_excel"];
        if($excel_path->getError() === UPLOAD_ERR_OK){
            $extension = pathinfo($excel_path->getClientFilename(), PATHINFO_EXTENSION);
            $basename = bin2hex(random_bytes(8));
            $filename = sprintf('%s.%0.8s', $basename, $extension);
            $excel_path->moveTo(__DIR__ . DIRECTORY_SEPARATOR . $filename);
            $file_path=__DIR__ . DIRECTORY_SEPARATOR . $filename;
            $excel_reader=null;
            if($extension=="xls"){
                $excel_reader=IOFactory::createReader("Xls");
                $excel_reader=$excel_reader->load($file_path);
            }
            else if($extension=="xlsx"){
                $excel_reader=IOFactory::createReader("Xlsx");
                $excel_reader=$excel_reader->load($file_path);
            }
            if($excel_reader!=null){
                    foreach($excel_reader->getWorksheetIterator() as $worksheet)
                    {
                    $errors=[];
                    $highestRow = $worksheet->getHighestRow();
                    for($row=2; $row<=$highestRow; $row++)
                    {
                        $email = strval($worksheet->getCellByColumnAndRow(1, $row)->getValue());
                        $employee = new Employee($this->container, ['email' => $email, 'owner_id' => $user->id, 'promotion_id' =>$promotion ] , false);

                        if (!$employee->add()) {
                            $errors[]=$email;
                        }
                    }
                    $message="";
                    foreach($errors as $error){
                        $message.=$error."..";    
                    }
                    if(count($errors)!=0)
                    {
                        self::flash("Emails non ajouté :".$message,'error');
                    }
                }
           }else{
            self::flash("Impossible de charger le fichier non Excel", 'error');
           }
           unlink($file_path);
        }
        else{
            self::flash("Impossible de charger le fichier", 'error');
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

    public function sendEmail(Request $request, Response $response){
        
        $user = $this->container->user;
        $sujet=$request->getParam('sujet_input');
        $message=$request->getParam('question_input');
        $email=$request->getParam('email');
        
        mail($email, $sujet, $message, "From: ".$user->getEmail());
        
        return $this->redirect($response, 'home');
    }
    
    
    public function addPeriode(Request $request, Response $response)
    {
        $user = $this->container->user;
        $owner_id=$user->id;
        /*------------Liste des intervales-----------*/
        $pools = new QuestionPoolCollection($this->container,$owner_id);
        
        $intervales=[];
        $max=DateTime::createFromFormat('Y-m-d',date("Y-m-d"));
        foreach($pools->toArray() as $pool){
            $intervales[]=['period_start' => $pool['period_start'], 'reponse' => $pool['reponse'] ];
            $diff=(date_diff(DateTime::createFromFormat('Y-m-d',$pool['reponse']),$max))->format('%R');
            if($diff=="-"){
                $max=DateTime::createFromFormat('Y-m-d',$pool['reponse']);
            }
        }

        /*-------------------------------------------*/
       
        
        
        /* Verification des valeurs entrer */
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
        /*---------------------------------*/
        /* Test de supperposition des périodes */
        $test_su=true;        
        foreach($intervales as $int){
            $diff1=(date_diff(DateTime::createFromFormat('Y-m-d',$int['period_start']),$period_start))->format('%R');
            $diff2=(date_diff(DateTime::createFromFormat('Y-m-d',$int['reponse']),$reponse_date))->format('%R');
            $diff3=(DateTime::createFromFormat('Y-m-d',$int['reponse'])==$reponse_date);
            $diff4=(date_diff($max,$period_start))->format('%R');
            $diff5=(DateTime::createFromFormat('Y-m-d',$int['period_start'])==$max);
            if( $diff1=="-" || $diff2=="-" || $diff3 || $diff4=="-" || $diff5)
            {
                $test_su=false;
                break;
            }
        }
        /*-----------------Ajouter Aujourd'hui--------------------*/
        $today=new DateTime('now');
        $new_starting_today=date_diff($today,$period_start)->format('%R');
        /*--------------------------------------------------------*/
        $array = [
            'owner_id' => $owner_id,
            'period_start' => $request->getParam('inputStartDate'),
            'period_end' => $request->getParam('inputEndDate'),
            'reponse' => $request->getParam('inputEndDateR')
        ];
        $pool=new QuestionPool($this->container,$array,"create_manual",false);
        if($new_starting_today=='+'){
            if($test_su){
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
            }else{
                    self::flash("La période ne doit pas se supperposer avec une autre periode", 'error'); 
            }
        }else{
            self::flash("Une nouvelle période ne peut pas commencer avant aujourd'hui", 'error'); 
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
        /*------------Liste des intervales et date maximum-----------*/
        $pools = new QuestionPoolCollection($this->container,$owner_id);
        
        $intervales=[];
        $max=DateTime::createFromFormat('Y-m-d',date("Y-m-d"));
        foreach($pools->toArray() as $pool){
            if($pool['id']!=$id){
                $intervales[]=[ 'id' => $pool['id'] , 'period_start' => $pool['period_start'], 'reponse' => $pool['reponse'] ];
                $diff=(date_diff(DateTime::createFromFormat('Y-m-d',$pool['reponse']),$max))->format('%R');
                if($diff=="-"){
                    $max=DateTime::createFromFormat('Y-m-d',$pool['reponse']);
                }
            }
        }
        
        /*-------------------------------------------*/
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
        
        /* Test de supperposition des périodes */
        $test_su=true;
       
        foreach($intervales as $int){
                $diff1=(date_diff(DateTime::createFromFormat('Y-m-d',$int['period_start']),$period_start))->format('%R');
                $diff2=(date_diff(DateTime::createFromFormat('Y-m-d',$int['reponse']),$reponse_date))->format('%R');
                $diff3=(DateTime::createFromFormat('Y-m-d',$int['reponse'])==$reponse_date);
                $diff4=(date_diff($max,$period_start))->format('%R');
                $diff5=(DateTime::createFromFormat('Y-m-d',$int['period_start'])->format('Y-m-d'))==($max->format('Y-m-d'));
               
                if( $diff1=="-" || $diff2=="-" || $diff3 || $diff4=="-"  )
               {
                    $test_su=false;
                    break;
                }
        }
        /*-------------------------------------*/
        
        $array = [
            'id' => $id,
            'period_start' => $request->getParam('inputEditStartDate'),
            'period_end' => $request->getParam('inputEditEndDate'),
            'owner_id' => $owner_id,
            'reponse' =>$request->getParam('inputEditEndDateR')
        ];
        $pools=new QuestionPool($this->container,$array,'create_manual',null);
        if($test_su){
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
        }else{
            self::flash("La période ne doit pas se supperposer avec une autre periode", 'error');
        }
        return $this->redirect($response, 'pools');
    }


}
