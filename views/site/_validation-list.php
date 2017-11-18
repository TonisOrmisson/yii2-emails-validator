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
                        'format'=>'raw',
                        'value'=>function($model){
                            /** @var EmailAddress $model */
                            // hilight spaces
                            $word = " ";
                            $text = preg_replace ("/" . preg_quote($word, '/') . "/",
                                "<span class='bg-primary'>&nbsp;</span>",
                                $model->address);
                            return $text;

                        },
                        'contentOptions' => function ($model) {
                            /** @var EmailAddress $model */
                            return ['class' => (!$model->isValid ? 'danger text-danger': 'text-success')];
                        },
                    ],
                    [
                        'attribute'=> 'needsTrimming',
                        'format'=>'boolean',
                        'contentOptions' => function ($model) {
                            /** @var EmailAddress $model */
                            return ['class' => ($model->needsTrimming ? 'warning text-warning': 'text-success')];
                        },
                    ],
                    [
                        'attribute'=> 'isValid',
                        'format'=>'boolean',
                        'contentOptions' => function ($model) {
                            /** @var EmailAddress $model */
                            return ['class' => (!$model->isValid ? 'danger text-danger': 'text-success')];
                        },
                    ],
                    [
                        'attribute'=> 'isValidRFC',
                        'format'=>'boolean',
                        'contentOptions' => function ($model) {
                            /** @var EmailAddress $model */
                            return ['class' => (!$model->isValidRFC ? 'danger text-danger': 'text-success')];
                        },
                    ],
                    [
                        'attribute'=> 'isNoRFCWarnings',
                        'format'=>'boolean',
                        'contentOptions' => function ($model) {
                            /** @var EmailAddress $model */
                            return ['class' => (!$model->isNoRFCWarnings ? 'danger text-danger': 'text-success')];
                        },
                    ],
                    [
                        'attribute'=> 'isValidDNS',
                        'format'=>'boolean',
                        'contentOptions' => function ($model) {
                            /** @var EmailAddress $model */
                            return ['class' => (!$model->isValidDNS ? 'danger text-danger': 'text-success')];
                        },
                    ],
                    [
                        'attribute'=> 'isValidSpoofCheck',
                        'format'=>'boolean',
                        'contentOptions' => function ($model) {
                            /** @var EmailAddress $model */
                            return ['class' => (!$model->isValidSpoofCheck ? 'danger text-danger': 'text-success')];
                        },
                    ],
                ],
            ]); ?>

        </div>
    </div>
<?php endif;?>
