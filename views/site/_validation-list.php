<?php

use andmemasin\emailsvalidator\models\EmailAddress;
use andmemasin\emailsvalidator\models\EmailsValidationForm;

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
                    [
                        'attribute'=> 'isValidRFC',
                        'format'=>'boolean',
                        'contentOptions' => function ($model) {
                            /** @var EmailAddress $model */
                            return ['class' => ($model->isValidRFC ? 'success' : 'danger')];
                        },
                    ],
                    [
                        'attribute'=> 'isNoRFCWarnings',
                        'format'=>'boolean',
                        'contentOptions' => function ($model) {
                            /** @var EmailAddress $model */
                            return ['class' => ($model->isNoRFCWarnings ? 'success' : 'danger')];
                        },
                    ],
                    [
                        'attribute'=> 'isValidDNS',
                        'format'=>'boolean',
                        'contentOptions' => function ($model) {
                            /** @var EmailAddress $model */
                            return ['class' => ($model->isValidDNS ? 'success' : 'danger')];
                        },
                    ],
                    [
                        'attribute'=> 'isValidSpoofCheck',
                        'format'=>'boolean',
                        'contentOptions' => function ($model) {
                            /** @var EmailAddress $model */
                            return ['class' => ($model->isValidSpoofCheck ? 'success' : 'danger')];
                        },
                    ],
                ],
            ]); ?>

        </div>
    </div>
<?php endif;?>
