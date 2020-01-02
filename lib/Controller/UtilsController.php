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
use OCP\IServerContainer;

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

    public function __construct($AppName,
                                IRequest $request,
                                IServerContainer $serverContainer,
                                IConfig $config,
                                IAppManager $appManager,
                                IAvatarManager $avatarManager,
                                $UserId) {
        parent::__construct($AppName, $request);
        $this->userId = $UserId;
        $this->avatarManager = $avatarManager;
        $this->serverContainer = $serverContainer;
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
    public function getAvatar($name, $color=null) {
        // no color given, we return the image generated by AvatarManager
        if (is_null($color)) {
            $av = $this->avatarManager->getGuestAvatar($name);
            $avatarContent = $av->getFile(64)->getContent();
            foreach ($this->serverContainer->getAppFolder()->getDirectoryListing() as $f) {
                error_log($f->getPath());
            }
            //error_log($this->serverContainer->getAppFolder()->getFullPath('.'));
            return new DataDisplayResponse($avatarContent);
        }
        else {
            // either we have it already or not
            $dataFolder = $this->serverContainer->getAppFolder();
            $letter = strtoupper($name[0]);
            if (!$dataFolder->nodeExists('cospend/'.$letter.$color.'.png')) {
                $size = 64;
                $backgroundColor = $this->hexToRgb($color);

                $im = imagecreatetruecolor($size, $size);
                $background = imagecolorallocate(
                        $im,
                        $backgroundColor['r'],
                        $backgroundColor['g'],
                        $backgroundColor['b']
                );
                $white = imagecolorallocate($im, 255, 255, 255);
                imagefilledrectangle($im, 0, 0, $size, $size, $background);

                $font = __DIR__ . '/../../../../core/fonts/NotoSans-Regular.ttf';

                $fontSize = $size * 0.4;
                list($x, $y) = $this->imageTTFCenter(
                        $im, $letter, $font, (int)$fontSize
                );

                imagettftext($im, $fontSize, 0, $x, $y, $white, $font, $letter);

                ob_start();
                imagepng($im);
                $data = ob_get_contents();
                ob_end_clean();

                // store it
                if (!$dataFolder->nodeExists('cospend')) {
                    $dataFolder->newFolder('cospend');
                }
                $cospendFolder = $dataFolder->get('cospend');
                $outFile = $cospendFolder->newFile($letter.$color.'.png');
                $outFile->putContent($data);
            }
            $cospendFolder = $dataFolder->get('cospend');
            $dataFile = $cospendFolder->get($letter.$color.'.png');
            $resultData = $dataFile->getContent();
            return new DataDisplayResponse($resultData);
        }
    }

    protected function imageTTFCenter(
        $image,
        string $text,
        string $font,
        int $size,
        $angle = 0
    ): array {
        // Image width & height
        $xi = imagesx($image);
        $yi = imagesy($image);

        // bounding box
        $box = imagettfbbox($size, $angle, $font, $text);

        // imagettfbbox can return negative int
        $xr = abs(max($box[2], $box[4]));
        $yr = abs(max($box[5], $box[7]));

        // calculate bottom left placement
        $x = intval(($xi - $xr) / 2);
        $y = intval(($yi + $yr) / 2);

        return array($x, $y);
    }

    private function hexToRgb($color) {
        $color = \str_replace('#', '', $color);
        $split_hex_color = str_split($color, 2);
        $r = hexdec($split_hex_color[0]);
        $g = hexdec($split_hex_color[1]);
        $b = hexdec($split_hex_color[2]);
        return ['r'=>$r, 'g'=>$g, 'b'=>$b];
    }

}
