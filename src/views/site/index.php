<?php

use andmemasin\emailsvalidator\EmailValidatorAsset;
use andmemasin\emailsvalidator\models\EmailsValidationForm;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var EmailsValidationForm $model */
/* @var \yii\data\ArrayDataProvider|null $dataProvider */

$this->title = Yii::t('app', 'Bulk e-mail validation');
$this->params['breadcrumbs'][] = Yii::t('app', 'E-mail validation');
$isPost = Yii::$app->request->getIsPost();

if (!$isPost) {
    $asset = EmailValidatorAsset::register($this);
    ?>
    <emails-validator
        api-base="<?= Html::encode(Url::to(['/api/v1/email-validations'])) ?>"
        csrf-token="<?= Html::encode(Yii::$app->request->getCsrfToken()) ?>"
        asset-base="<?= Html::encode((string) $asset->baseUrl) ?>"
    ></emails-validator>
    <?php
} else {
    ?>
    <div id="bulk-email-validation">
        <?php if ($model->module->displayFlashMessages): ?>
            <div class="row">
                <div class="col-xs-12">
                    <?php foreach (Yii::$app->session->getAllFlashes() as $type => $data): ?>
                        <?php if (in_array($type, ['success', 'danger', 'warning', 'info'], true)): ?>
                            <?php foreach ($data as $message): ?>
                                <div class="alert alert-<?= Html::encode($type) ?> alert-dismissible fade show" role="alert">
                                    <?= $message ?>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>
                            <?php endforeach ?>
                        <?php endif ?>
                    <?php endforeach ?>
                </div>
            </div>
        <?php endif; ?>

        <div class="card card-default email-validation-results" id="email-validation-results">
            <div class="card-header">
                <?= Yii::t('app', 'Input') ?>
                <div class="float-right btn btn-default btn-xs" data-bs-toggle="collapse" data-bs-target="#emails-validation-input">
                    <?= Yii::t('app', 'Show/hide') ?>
                </div>
            </div>
            <div id="emails-validation-input" class="card-collapse collapse <?= count($model->emailAddresses) > 0 ? null : 'in' ?>">
                <div class="card-body">
                    <?php $form = ActiveForm::begin() ?>
                    <?= $form->field($model, 'textInput')->textarea(['rows' => 10]) ?>
                    <div class="container">
                        <div class="row">
                            <div class="col col-lg-2 col-md-2 col-sm-4 col-xs-4">
                                <?= $form->field($model, 'displayOnlyProblems')->checkbox() ?>
                            </div>
                            <div class="col col-lg-2 col-md-2 col-sm-4 col-xs-4">
                                <?= $form->field($model, 'checkDNS')->checkbox() ?>
                            </div>
                            <div class="col col-lg-2 col-md-2 col-sm-4 col-xs-4">
                                <?= $form->field($model, 'checkSpoof')->checkbox() ?>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <?= Html::submitButton(Yii::t('app', 'Validate'), ['class' => 'btn btn-primary']) ?>
                    </div>
                    <?php ActiveForm::end() ?>
                </div>
            </div>
        </div>
        <?= $this->render('_validation-list', ['model' => $model, 'dataProvider' => $dataProvider]) ?>
    </div>
    <?php
}
