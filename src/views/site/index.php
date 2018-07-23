<?php

use andmemasin\emailsvalidator\models\EmailsValidationForm;
use yii\widgets\ActiveForm;
use yii\helpers\Html;
use yii\bootstrap\Alert;

/* @var $this yii\web\View */
/* @var EmailsValidationForm $model */
/* @var \yii\data\ArrayDataProvider $dataProvider */

$this->title = Yii::t('app', 'Bulk e-mail validation');
$this->params['breadcrumbs'][] = Yii::t('app', 'E-mail validation');
?>
<div id="bulk-email-validation">
    <?php if($model->module->displayFlashMessages):?>
    <div class="row">
        <div class="col-xs-12">
            <?php foreach (Yii::$app->session->allFlashes as $type => $message): ?>
                <?php if (in_array($type, ['success', 'danger', 'warning', 'info'])): ?>
                    <?= Alert::widget([
                        'options' => ['class' => 'alert-dismissible alert-' . $type],
                        'body' => $message
                    ]) ?>
                <?php endif ?>
            <?php endforeach ?>
        </div>
    </div>
    <?php endif;?>


    <div class="panel panel-default email-validation-results" id="email-validation-results">
        <div class="panel-heading">
            <?= Yii::t('app','Input')?>
            <a class="pull-right btn btn-default btn-xs" data-toggle="collapse" href="#emails-validation-input"><?=Yii::t('app','Show/hide')?></a>
        </div>

        <div id="emails-validation-input" class="panel-collapse collapse <?=(count($model->emailAddresses)>0 ? null:'in')?>">
            <div class="panel-body ">
                <?php $form = ActiveForm::begin() ?>
                <?= $form->field($model, 'textInput')->textarea(['rows'=>10]); ?>

                <div class="container">
                    <div class="row">
                        <div class="col col-lg-2  col-md-2 col-sm-4 col-xs-4">
                            <?= $form->field($model, 'displayOnlyProblems')->checkbox() ?>
                        </div>
                        <div class="col col-lg-2 col-md-2 col-sm-4 col-xs-4">
                            <?= $form->field($model, 'checkDNS')->checkbox() ?>
                        </div>
                        <div class="col col-lg-2  col-md-2 col-sm-4 col-xs-4">
                            <?= $form->field($model, 'checkSpoof')->checkbox() ?>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <?= Html::submitButton(Yii::t('app', 'Validate'), ['class' => 'btn btn-primary']) ?>
                </div>
            </div>

        </div>
    </div>

    <?php $form = ActiveForm::end() ?>

    <?= $this->render('_validation-list',['model'=>$model,'dataProvider'=>$dataProvider])?>
</div>