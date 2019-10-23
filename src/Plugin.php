<?php

namespace tde\craft\commerce\bundles;

use craft\events\RegisterUrlRulesEvent;
use craft\helpers\UrlHelper;
use craft\web\UrlManager;
use tde\craft\commerce\bundles\elements\ProductBundle;

use craft\events\RegisterComponentTypesEvent;
use craft\events\RegisterCpNavItemsEvent;
use craft\services\Elements;
use craft\web\twig\variables\Cp;
use tde\craft\commerce\bundles\models\Settings;
use tde\craft\commerce\bundles\services\ProductBundleService;
use yii\base\Event;

/**
 * Class Plugin
 *
 * @property ProductBundleService $productBundleService
 *
 * @package tde\craft\commerce\bundles
 */
class Plugin extends \craft\base\Plugin
{
    /**
     * @var self
     */
    public static $instance;

    /**
     * @var bool
     */
    public $hasCpSettings = true;

    /**
     * @inheritDoc
     */
    public function init()
    {
        parent::init();
        self::$instance = $this;

        $this->setComponents([
            'productBundleService' => ProductBundleService::class,
        ]);

        $this->_registerEvents();
        $this->_registerCpNavItem();
        $this->_registerCpRoutes();
    }

    /**
     * @inheritDoc
     */
    protected function createSettingsModel()
    {
        return new Settings();
    }

    /**
     * @inheritdoc
     */
    public function getSettingsResponse()
    {
        return \Craft::$app->getResponse()->redirect(UrlHelper::cpUrl('commerce-product-bundles/settings'));
    }

    /**
     * Register our Control Panel navigation item under 'Products' of the Commerce subnav
     */
    protected function _registerCpNavItem()
    {
        Event::on(
            Cp::class,
            Cp::EVENT_REGISTER_CP_NAV_ITEMS,
            function(RegisterCpNavItemsEvent $event) {
                if (isset($event->navItems['commerce'])) {
                    $keys = array_keys($event->navItems['commerce']['subnav']);
                    $pos = array_search('products', $keys) + 1;

                    $event->navItems['commerce']['subnav'] = array_merge(
                        array_slice(
                            $event->navItems['commerce']['subnav'],
                            0,
                            $pos
                        ),
                        [
                            'product-bundles' => [
                                'label' => 'Product bundles',
                                'url' => 'commerce/product-bundles',
                            ]
                        ],
                        array_slice($event->navItems['commerce']['subnav'], $pos)
                    );
                }
            }
        );
    }

    /**
     * Register events
     */
    protected function _registerEvents()
    {
        Event::on(
            Elements::class,
            Elements::EVENT_REGISTER_ELEMENT_TYPES,
            function (RegisterComponentTypesEvent $event) {
                $event->types[] = ProductBundle::class;
            }
        );
    }

    /**
     * Register Control Panel routes
     */
    protected function _registerCpRoutes()
    {
        Event::on(
            UrlManager::class,
            UrlManager::EVENT_REGISTER_CP_URL_RULES,
            function (RegisterUrlRulesEvent $event) {
                $event->rules = array_merge($event->rules, [
                    'commerce/product-bundles' => 'commerce-product-bundles/bundles/index',
                    'commerce/product-bundles/new' => 'commerce-product-bundles/bundles/edit',
                    'commerce/product-bundles/<productBundleId:\d+>' => 'commerce-product-bundles/bundles/edit',
                    'commerce/product-bundles/settings' => 'commerce-product-bundles/settings',
                ]);
            }
        );
    }
}