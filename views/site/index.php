<?php

use andmemasin\emailsvalidator\models\EmailsValidationForm;
use yii\widgets\ActiveForm;
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var EmailsValidationForm $model */
/* @var \yii\data\ArrayDataProvider $dataProvider */

$this->title = Yii::t('app', 'Bulk e-mail validation');
$this->params['breadcrumbs'][] = Yii::t('app', 'E-mail validation');
?>
<div id="bulk-email-validation">
    <?php $form = ActiveForm::begin() ?>
    <?= $form->field($model, 'textInput')->textarea(); ?>

    <div class="form-group">
        <?= Html::submitButton(Yii::t('app', 'Validate'), ['class' => 'btn btn-primary']) ?>
    </div>

    <?php $form = ActiveForm::end() ?>

    <?= $this->render('_validation-list',['model'=>$model,'dataProvider'=>$dataProvider])?>
</div>