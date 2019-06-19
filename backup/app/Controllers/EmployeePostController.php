<?php


namespace App\Controllers;

use Slim\Http\Request;
use Slim\Http\Response;
use App\Models\Question;
use App\Models\QuestionPool;
use Respect\Validation\Validator as v;

class EmployeePostController extends Controller
{
    /**
     * @param Request $request
     * @param Response $response
     * @return Response
     */
    public function addQuestion(Request $request, Response $response)
    {
        $user = $this->container->user;
        $owner_id = $user->getOwnerId();

        // TODO : Validator on inputSubject and inputBody
        $subject = $request->getParam('inputSubject');
        $questionMessage = $request->getParam('inputBody');

        $pool = new QuestionPool($this->container, ['owner_id' => $owner_id]);
        $question = new Question($this->container, ['employee_id' => $user->id, 'subject' => $subject, 'question' => $questionMessage], false);

        if (!$pool->addQuestion($question)) {
            self::flash('Impossible de poser cette question vous en avez sûrement déjà posé une', 'error');
        }

        return $this->redirect($response, 'home');
    }

    public function voteQuestion(Request $request, Response $response)
    {
        $user = $this->user;

        $question_id = $request->getParam('inputQuestionId');

        $question = new Question($this->container, ['id' => $question_id]);

        if (!$question->vote($user->id)) {
            self::flash('Impossible de voter pour cette question, vous avez probablement déjà voté', 'error');
        }

        return $this->redirect($response, 'home');
    }
}
