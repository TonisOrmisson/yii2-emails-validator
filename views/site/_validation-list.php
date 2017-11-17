<?php

use andmemasin\emailsvalidator\models\EmailsValidationForm;

/* @var $this yii\web\View */
/* @var EmailsValidationForm $model */

?>
<?php if($model->emailAddresses):?>
    <div class="panel panel-primary email-validation-results" id="email-validation-results">
        <div class="panel-heading"><?= Yii::t('app','Results')?></div>

        <div class="panel-body">
            <?php foreach ($model->emailAddresses as $i=> $address):?>
                <?= $this->render('_validate-address',['address'])?>
            <?php endforeach;?>

        </div>
    </div>
<?php endif;?>
