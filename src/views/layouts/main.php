<?php
use yii\helpers\Html;

/* @var $this \yii\web\View */
/* @var $content string */

/* @var $this yii\web\View */

$session = Yii::$app->session;

    ?>
    <?php $this->beginPage() ?>
    <!DOCTYPE html>
    <html lang="<?= Yii::$app->language ?>">
    <head>
        <meta charset="<?= Yii::$app->charset ?>"/>
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <?= Html::csrfMetaTags() ?>
        <title><?= Html::encode($this->title) ?></title>
        <?php $this->head() ?>
    </head>
    <body class="hold-transition  sidebar-mini">
    <?php $this->beginBody() ?>


    <div id="content" class="wrapper">
        <?= $this->render('content.php',['content' => $content]) ?>
    </div>

    <?php $this->endBody() ?>
    </body>
    </html>
    <?php $this->endPage() ?>
