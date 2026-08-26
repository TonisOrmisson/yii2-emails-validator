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

    /** @return array<string, string> */
    public function apiBasePath(): string
    {
        $baseUrl = rtrim(Yii::$app->request->getBaseUrl(), '/');

        return $baseUrl . '/' . trim($this->getUniqueId(), '/') . '/api/v1/email-validations';
    }

    /** @return array<string, string> */
    public static function apiRouteRules(string $moduleId = 'emailsvalidator'): array
    {
        $moduleId = trim($moduleId, '/');
        $base = $moduleId . '/api/v1/email-validations';
        $controller = $moduleId . '/api/email-validation';

        // Keep the legacy static declaration for hosts that still merge it directly.
        if (func_num_args() === 0) {
            return ['POST api/v1/email-validations' => 'emailsvalidator/api/email-validation/index'];
        }

        return ["POST {$base}" => "{$controller}/index"];
    }

    public function getValidationService(): EmailValidationService
    {
        if (!is_int($this->maxInputKB) || $this->maxInputKB <= 0) {
            throw new \InvalidArgumentException('The maximum input KB must be positive.');
        }

        return new EmailValidationService($this->maxInputKB * 1024);
    }
}
