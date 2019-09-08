<?php
/**
 * Nextcloud - cospend
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Julien Veyssier <eneiluj@posteo.net>
 * @copyright Julien Veyssier 2019
 */

namespace OCA\Cospend\Controller;

use OCP\App\IAppManager;
use OCP\IAvatarManager;
use OCP\AppFramework\Http\DataDisplayResponse;

use OCP\IURLGenerator;
use OCP\IConfig;

use OCP\AppFramework\Http;
use OCP\AppFramework\Http\RedirectResponse;

use OCP\AppFramework\Http\ContentSecurityPolicy;

use OCP\IRequest;
use OCP\AppFramework\Http\DataResponse;
use OCP\AppFramework\Controller;

class UtilsController extends Controller {


    private $userId;
    private $config;
    private $dbconnection;
    private $dbtype;

    public function __construct($AppName, IRequest $request, $UserId,
                                $userfolder, $config, IAppManager $appManager,
                                IAvatarManager $avatarManager) {
        parent::__construct($AppName, $request);
        $this->userId = $UserId;
        $this->avatarManager = $avatarManager;
        $this->dbtype = $config->getSystemValue('dbtype');
        if ($this->dbtype === 'pgsql'){
            $this->dbdblquotes = '"';
        }
        else{
            $this->dbdblquotes = '';
        }
        // IConfig object
        $this->config = $config;
        $this->dbconnection = \OC::$server->getDatabaseConnection();
    }

    /*
     * quote and choose string escape function depending on database used
     */
    private function db_quote_escape_string($str){
        return $this->dbconnection->quote($str);
    }

    /**
     * set global point quota
     */
    public function setAllowAnonymousCreation($allow) {
        $this->config->setAppValue('cospend', 'allowAnonymousCreation', $allow);
        $response = new DataResponse(
            [
                'done'=>'1'
            ]
        );
        $csp = new ContentSecurityPolicy();
        $csp->addAllowedImageDomain('*')
            ->addAllowedMediaDomain('*')
            ->addAllowedConnectDomain('*');
        $response->setContentSecurityPolicy($csp);
        return $response;
    }

    /**
     * Delete user options
     * @NoAdminRequired
     */
    public function deleteOptionsValues() {
        $keys = $this->config->getUserKeys($this->userId, 'cospend');
        foreach ($keys as $key) {
            $this->config->deleteUserValue($this->userId, 'cospend', $key);
        }

        $response = new DataResponse(
            [
                'done'=>1
            ]
        );
        $csp = new ContentSecurityPolicy();
        $csp->addAllowedImageDomain('*')
            ->addAllowedMediaDomain('*')
            ->addAllowedConnectDomain('*');
        $response->setContentSecurityPolicy($csp);
        return $response;
    }

    /**
     * Save options values to the DB for current user
     * @NoAdminRequired
     */
    public function saveOptionValue($options) {
        foreach ($options as $key => $value) {
            $this->config->setUserValue($this->userId, 'cospend', $key, $value);
        }

        $response = new DataResponse(
            [
                'done'=>true
            ]
        );
        $csp = new ContentSecurityPolicy();
        $csp->addAllowedImageDomain('*')
            ->addAllowedMediaDomain('*')
            ->addAllowedConnectDomain('*');
        $response->setContentSecurityPolicy($csp);
        return $response;
    }

    /**
     * get options values from the config for current user
     * @NoAdminRequired
     */
    public function getOptionsValues() {
        $ov = array();
        $keys = $this->config->getUserKeys($this->userId, 'cospend');
        foreach ($keys as $key) {
            $value = $this->config->getUserValue($this->userId, 'cospend', $key);
            $ov[$key] = $value;
        }

        $response = new DataResponse(
            [
                'values'=>$ov
            ]
        );
        $csp = new ContentSecurityPolicy();
        $csp->addAllowedImageDomain('*')
            ->addAllowedMediaDomain('*')
            ->addAllowedConnectDomain('*');
        $response->setContentSecurityPolicy($csp);
        return $response;
    }

    /**
     *
     * @NoAdminRequired
     * @NoCSRFRequired
     * @PublicPage
     */
    public function getAvatar($name) {
        $av = $this->avatarManager->getGuestAvatar($name);
        $avatarContent = $av->getFile(64)->getContent();
        return new DataDisplayResponse($avatarContent);
    }

}
