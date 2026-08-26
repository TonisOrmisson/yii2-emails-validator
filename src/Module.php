<?php

declare(strict_types=1);

namespace andmemasin\emailsvalidator;

use andmemasin\emailsvalidator\validation\EmailValidationService;
use Yii;

class Module extends \yii\base\Module
{
    public $accessPermissionName = 'access::emailsValidator';
    public $controllerNamespace = 'andmemasin\\emailsvalidator\\controllers';
    public $defaultRoute = 'site/index';
    /** @var int Limit input to this many kilobytes. */
    public $maxInputKB = 128;
    public $displayFlashMessages = true;

    public function init(): void
    {
        parent::init();

        if (Yii::$app instanceof \yii\console\Application) {
            $this->controllerNamespace = 'andmemasin\\emailsvalidator\\commands';
        }
    }

    public function apiBasePath(): string
    {
        $urlManager = Yii::$app->getUrlManager();
        $route = $urlManager->enablePrettyUrl
            ? $this->getUniqueId() . '/api/v1/email-validations'
            : $this->getUniqueId() . '/api/email-validation/index';

        return $urlManager->createUrl([$route]);
    }

    /** @return array<string, string> */
    public static function apiRouteRules(string $moduleId = 'emailsvalidator'): array
    {
        $moduleId = trim($moduleId, '/');

        return [
            "POST {$moduleId}/api/v1/email-validations" => "{$moduleId}/api/email-validation/index",
        ];
    }

    public function getValidationService(): EmailValidationService
    {
        if (!is_int($this->maxInputKB) || $this->maxInputKB <= 0) {
            throw new \InvalidArgumentException('The maximum input KB must be positive.');
        }

        return new EmailValidationService($this->maxInputKB * 1024);
    }
}
