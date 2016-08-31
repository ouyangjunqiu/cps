<?php
/**
 * Application,模块及分发控制器
 * @package cloud.core.components
 * @author oShine <oshine.ouyang@da-mai.com>
 */

namespace cloud\core\web;

use cloud\Cloud;
use cloud\core\components\Browser;
use cloud\core\components\Setting;
use cloud\core\utils\Module;
use yii\helpers\ArrayHelper;

class Application extends \yii\web\Application {

    /**
     * 已安装的模块
     * @var array 
     */
    private $_enabledModule = array();

    /**
     * @param array $config
     * @throws \yii\base\InvalidConfigException
     */
    public function preInit(&$config) {

        $config["basePath"] = PATH_ROOT . DIRECTORY_SEPARATOR . 'system';
        $config["vendorPath"] = PATH_ROOT . DIRECTORY_SEPARATOR . 'vendor';
        $config["defaultRoute"] = 'main/default/index';
        $config["timeZone"] = 'PRC';
        $config["charset"] = "utf-8";
        $config["id"] = 'cloudframework';
        $config["bootstrap"] = ['cache','log'];

        $config = ArrayHelper::merge($config,Cloud::engine()->getConfig());

        $this->_enabledModule = Module::fetchAllModule();
        foreach ($this->_enabledModule as $name => $mc) {

            if (isset($mc['config'])) {
                $config = ArrayHelper::merge($config,$mc['config']);
            }
        }
        $config["components"]["log"]["targets"]["file"] = [
            'class' => 'yii\log\FileTarget',
            'levels' => ['error'],
            'categories' => ['*'],
            'logFile' => '@runtime/logs/webapp.'.date("Y-m-d").".log"

        ];

        $config["components"]["assetManager"] = [
            'basePath' => '@webroot/static',
            'baseUrl' => '@web/static'
        ];

        $config["components"]["errorHandler"] = [
            'errorAction' => 'main/default/error',
        ];

        parent::preInit($config);
    }


    public function bootstrap()
    {
        $this->on(self::EVENT_BEFORE_REQUEST,['cloud\core\web\InitEnv','handle']);
        Cloud::engine()->bootstrap();
        parent::bootstrap(); // TODO: Change the autogenerated stub

        \Yii::setAlias('@theme',\Yii::getAlias('@app/theme'));
    }

    /**
     * 返回可用的模块数组
     * @return array
     */
    public function getEnabledModule() {
        return (array) $this->_enabledModule;
    }

    /**
     * @inheritdoc
     */
    public function coreComponents()
    {
        return array_merge(parent::coreComponents(), [
            'browser' => ['class' => 'cloud\core\components\Browser'],
            'setting' => ['class' => 'cloud\core\components\Setting'],
            'cache' => ['class'=>'yii\caching\FileCache']
        ]);
    }

    /**
     * Returns the setting component.
     * @return Setting the error handler application component.
     */
    public function getSetting()
    {
        return $this->get('setting');
    }

    /**
     * Returns the browser component.
     * @return Browser the error handler application component.
     */
    public function getBrowser()
    {
        return $this->get('browser');
    }

}
