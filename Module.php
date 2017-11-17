<?php

namespace andmemasin\emailsvalidator;

use Yii;

class Module extends \yii\base\Module
{

    public $accessPermissionName = 'access::emailsValidator';

    /**
     * @inheritdoc
     */
    public $controllerNamespace = 'andmemasin\emailsvalidator\controllers';

    /** @inheritdoc  */
    public $defaultRoute = 'site/index';

}
