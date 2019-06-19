<?php


namespace App\Controllers;

use App\Models\License;
use Slim\Http\Request;
use Slim\Http\Response;

class AdminPostController extends PostController
{
    /**
     * Flashing the current tab in session
     * @param Request $request
     * @param string $name
     */
    private function flashTab(Request $request, $name)
    {
        if ($tab = $request->getParam('flashTab')) {
            $this::flash($tab, $name);
        }
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
        $this->flashTab($request, 'licenseTab');

        $regenerate = $request->getParam('checkboxEditRegenerate');

        $array = [
            'id' => $id,
            'user_email' => ($request->getParam('inputEditUserEmail')) ? $request->getParam('inputEditUserEmail') : null,
            'license' => $regenerate ? null : $request->getParam('inputEditLicense'),
            'start_date' => $request->getParam('inputEditStartDate'),
            'end_date' => $request->getParam('inputEditEndDate')
        ];

        $license = new License($this->container, $array, false, $regenerate);

        if ($license->exists(['license']) && $license->idFor(['license']) != $id) {
            self::flash("La license <span class=\"font-weight-bold\">{$license->getKey()}</span> est déjà attribuée", 'error');
            return $this->redirect($response, 'admin');
        }

        if ($license->edit()) {
            self::flash("License correctement modifiée: <span class=\"font-weight-bold\">{$license->getKey()}</span>", 'success');
        } else {
            self::flash("Impossible de modifier la license: <span class=\"font-weight-bold\">{$license->getKey()}</span>", 'error');
        }

        return $this->redirect($response, 'admin');
    }

    /**
     * @param Request $request
     * @param Response $response
     * @return Response
     */
    public function abortLicense(Request $request, Response $response)
    {
        $id = $request->getParam('inputId');
        $this->flashTab($request, 'licenseTab');

        $license = new License($this->container, ['id' => $id]);

        if ($license->abort()) {
            self::flash("La license a été correctement supprimée", 'success');
        } else {
            self::flash("Une erreur est survenue lors de la suppression de la license", 'error');
        }

        return $this->redirect($response, 'admin');
    }
}
