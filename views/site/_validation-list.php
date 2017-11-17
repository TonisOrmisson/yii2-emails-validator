<?php

use andmemasin\emailsvalidator\models\EmailsValidationForm;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var EmailsValidationForm $model */
/* @var \yii\data\ArrayDataProvider $dataProvider */



?>
<?php if($model->emailAddresses):?>
    <div class="panel panel-primary email-validation-results" id="email-validation-results">
        <div class="panel-heading"><?= Yii::t('app','Results')?></div>

        <div class="panel-body">
            <?= \yii\grid\GridView::widget([
                'dataProvider' => $dataProvider,
                'columns' => [
                    ['class' => 'yii\grid\SerialColumn'],

                    'address',
                    'isValidRFC:boolean',
                    'isNoRFCWarnings:boolean',
                    'isValidDNS:boolean',
                    'isValidSpoofCheck:boolean',
                ],
            ]); ?>

        </div>
    </div>
<?php endif;?>
