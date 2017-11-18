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

                    [
                        'attribute'=> 'address',
                        'contentOptions' => function ($model) {
                            /** @var EmailAddress $model */
                            return ['class' => (!$model->isValid ? 'danger': null)];
                        },
                    ],
                    [
                        'attribute'=> 'needsTrimming',
                        'format'=>'boolean',
                        'contentOptions' => function ($model) {
                            /** @var EmailAddress $model */
                            return ['class' => ($model->needsTrimming ? 'warning': null)];
                        },
                    ],
                    [
                        'attribute'=> 'isValid',
                        'format'=>'boolean',
                        'contentOptions' => function ($model) {
                            /** @var EmailAddress $model */
                            return ['class' => (!$model->isValid ? 'danger': null)];
                        },
                    ],
                    [
                        'attribute'=> 'isValidRFC',
                        'format'=>'boolean',
                        'contentOptions' => function ($model) {
                            /** @var EmailAddress $model */
                            return ['class' => (!$model->isValidRFC ? 'danger': null)];
                        },
                    ],
                    [
                        'attribute'=> 'isNoRFCWarnings',
                        'format'=>'boolean',
                        'contentOptions' => function ($model) {
                            /** @var EmailAddress $model */
                            return ['class' => (!$model->isNoRFCWarnings ? 'danger': null)];
                        },
                    ],
                    [
                        'attribute'=> 'isValidDNS',
                        'format'=>'boolean',
                        'contentOptions' => function ($model) {
                            /** @var EmailAddress $model */
                            return ['class' => (!$model->isValidDNS ? 'danger': null)];
                        },
                    ],
                    [
                        'attribute'=> 'isValidSpoofCheck',
                        'format'=>'boolean',
                        'contentOptions' => function ($model) {
                            /** @var EmailAddress $model */
                            return ['class' => (!$model->isValidSpoofCheck ? 'danger': null)];
                        },
                    ],
                ],
            ]); ?>

        </div>
    </div>
<?php endif;?>
