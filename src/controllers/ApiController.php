<?php
/**
 * Website monitoring plugin for Craft CMS 3.x
 *
 * Plugin to monitor pending updates on websites
 *
 * @link      https://solvr.no
 * @copyright Copyright (c) 2020 Kalle Pohjapelto
 */

namespace solvras\websitemonitoring\controllers;

use solvras\websitemonitoring\WebsiteMonitoring;

use Craft;
use craft\web\Controller;
use craft\helpers\DateTimeHelper;
use yii\web\ForbiddenHttpException;

/**
 * @author    Kalle Pohjapelto
 * @package   WebsiteMonitoring
 * @since     1.0.0
 */
class ApiController extends Controller
{

    // Protected Properties
    // =========================================================================

    /**
     * @var    bool|array Allows anonymous access to this controller's actions.
     *         The actions must be in 'kebab-case'
     * @access protected
     */
    protected $allowAnonymous = ['info'];

    // Public Methods
    // =========================================================================

    public function beforeAction($action): bool
    {
        // Disable CSRF validation POST requests
        if ($action->id === 'info') {
            $this->enableCsrfValidation = false;
        }

        return parent::beforeAction($action);
    }

     /**
     * @return mixed
     */
    public function actionCreateToken()
    {
        $this->requireAdmin(false);
        
        $currentTime = DateTimeHelper::currentUTCDateTime();
        $expiryDate = $currentTime->add(new \DateInterval('P10Y'));

        $token = Craft::$app->tokens->createToken('website-monitoring/api/info', null, $expiryDate);

        Craft::$app->session->setFlash('tokenCreated', "Token created: ".$token);
        Craft::$app->session->setNotice("Token created");

        return null;
    }

    /**
     * @return mixed
     */
    public function actionInfo()
    {   
        if($_SERVER['REMOTE_ADDR'] !== Craft::parseEnv('$MONITOR_TRUSTED_HOST')) {
            throw new ForbiddenHttpException();
        }

        $this->requirePostRequest();
        $this->requireToken();

        $result = [
            'general' => [
                'php' => [
                    'version' => craft\helpers\App::phpversion(),
                ],
                'cms' => [
                    'edition' => Craft::$app->getLicensedEditionName(), // Craft::$app->getEditionName()
                    'license' => Craft::$app->api->getLicenseInfo(),
                    'email' => craft\helpers\App::mailSettings()
                ],
                'plugins' => Craft::$app->plugins->getAllPluginInfo()
            ],
            'updates' => Craft::$app->api->getUpdates()
        ];

        return $this->asJson($result);
    }
}
