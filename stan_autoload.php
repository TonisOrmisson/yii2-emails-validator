<?php

// PHPStan fails to autoload Yii somehow; Yii of the vendor PHPStan runs on (the module's own, or the dev shell's root)
require \Composer\InstalledVersions::getInstallPath('yiisoft/yii2') . '/Yii.php';
