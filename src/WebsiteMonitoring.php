<?php
/**
 * Website monitoring plugin for Craft CMS 3.x
 *
 * Plugin to monitor pending updates on websites
 *
 * @link      https://solvr.no
 * @copyright Copyright (c) 2020 Solvr
 */

namespace solvras\websitemonitoring;

use solvras\websitemonitoring\services\WebsiteMonitoringService as WebsiteMonitoringServiceService;

use Craft;
use craft\base\Plugin;
use craft\services\Plugins;
use craft\events\PluginEvent;
use craft\web\UrlManager;
use craft\events\RegisterUrlRulesEvent;

use yii\base\Event;

/**
 * Class WebsiteMonitoring
 *
 * @author    Solvr
 * @package   WebsiteMonitoring
 * @since     1.0.0
 *
 * @property  WebsiteMonitoringServiceService $websiteMonitoringService
 */
class WebsiteMonitoring extends Plugin
{
    // Static Properties
    // =========================================================================

    /**
     * @var WebsiteMonitoring
     */
    public static $plugin;

    // Public Properties
    // =========================================================================

    /**
     * @var string
     */
    public string $schemaVersion = '1.0.0';

    /**
     * @var bool
     */
    public bool $hasCpSettings = false;

    /**
     * @var bool
     */
    public bool $hasCpSection = true;

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        self::$plugin = $this;

        Event::on(
            UrlManager::class,
            UrlManager::EVENT_REGISTER_SITE_URL_RULES,
            function (RegisterUrlRulesEvent $event) {
                $event->rules['siteActionTrigger1'] = 'website-monitoring/api';
            }
        );

        Event::on(
            Plugins::class,
            Plugins::EVENT_AFTER_INSTALL_PLUGIN,
            function (PluginEvent $event) {
                if ($event->plugin === $this) {
                }
            }
        );

        Craft::info(
            Craft::t(
                'website-monitoring',
                '{name} plugin loaded',
                ['name' => $this->name]
            ),
            __METHOD__
        );
    }

    public function getCpNavItem(): ?array
    {
        $item = parent::getCpNavItem();
        $item['subnav'] = [
            'token' => ['label' => 'Create token', 'url' => 'website-monitoring']
        ];
        return $item;
    }

    // Protected Methods
    // =========================================================================

}
