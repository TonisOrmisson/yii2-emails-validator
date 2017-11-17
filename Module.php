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

    /** @var int $maxInputKB Limit the input string to KB (default = 128) */
    public $maxInputKB = 128;

}
