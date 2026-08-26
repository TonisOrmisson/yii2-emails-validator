<?php

declare(strict_types=1);

namespace andmemasin\emailsvalidator;

use yii\base\BootstrapInterface;
use yii\web\Application;

final class Bootstrap implements BootstrapInterface
{
    public function bootstrap($app): void
    {
        if (!$app instanceof Application) {
            return;
        }

        foreach ($app->getModules() as $id => $definition) {
            $class = is_array($definition) ? ($definition['class'] ?? null) : null;
            if ($definition instanceof Module || (is_string($class) && is_a($class, Module::class, true))) {
                $rules = Module::apiRouteRules((string) $id);
                $app->urlManager->addRules($rules, false);
                foreach ($rules as $rule => $target) {
                    $app->urlManager->addRules([
                        preg_replace('/^POST /', 'GET ', $rule) => $target,
                    ], false);
                }
            }
        }
    }
}
