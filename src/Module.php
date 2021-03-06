<?php

namespace andmemasin\emailsvalidator;

use Yii;


class Module extends \yii\base\Module
{
    /** @var string  */
    public $accessPermissionName = 'access::emailsValidator';

    public $controllerNamespace = 'andmemasin\emailsvalidator\controllers';

    public $defaultRoute = 'site/index';

    /** @var int $maxInputKB Limit the input string to KB (default = 128) */
    public $maxInputKB = 128;

    /** @var boolean $displayFlashMessages whether the flash messages will be rendered ny the module. You can disable this if your app has a separate flash displaying system.  */
    public $displayFlashMessages = true;

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();

        if (Yii::$app instanceof \yii\console\Application) {
            $this->controllerNamespace = 'andmemasin\emailsvalidator\commands';
        }
    }



}
