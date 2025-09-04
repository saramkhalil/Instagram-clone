<?php

/** @var yii\web\View $this */
/** @var yii\bootstrap5\ActiveForm $form */
/** @var \frontend\models\SignupForm $model */

use yii\bootstrap5\Html;
use yii\bootstrap5\ActiveForm;

$this->title = 'Join Instagram Clone';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="site-signup">
    <div class="row justify-content-center">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-body">
                    <h1 class="text-center mb-4"><?= Html::encode($this->title) ?></h1>
                    <p class="text-center text-muted mb-4">Create your account to start sharing photos and connecting with friends</p>

                    <?php $form = ActiveForm::begin(['id' => 'form-signup']); ?>

                        <div class="row">
                            <div class="col-md-6">
                                <?= $form->field($model, 'first_name')->textInput(['autofocus' => true]) ?>
                            </div>
                            <div class="col-md-6">
                                <?= $form->field($model, 'last_name') ?>
                            </div>
                        </div>

                        <?= $form->field($model, 'username')->textInput() ?>

                        <?= $form->field($model, 'email') ?>

                        <?= $form->field($model, 'password')->passwordInput() ?>

                        <div class="row">
                            <div class="col-md-6">
                                <?= $form->field($model, 'phone') ?>
                            </div>
                            <div class="col-md-6">
                                <?= $form->field($model, 'gender')->dropDownList([
                                    '' => 'Select Gender',
                                    'male' => 'Male',
                                    'female' => 'Female',
                                    'other' => 'Other'
                                ]) ?>
                            </div>
                        </div>

                        <?= $form->field($model, 'timezone')->dropDownList([
                            'UTC' => 'UTC',
                            'America/New_York' => 'Eastern Time',
                            'America/Chicago' => 'Central Time',
                            'America/Denver' => 'Mountain Time',
                            'America/Los_Angeles' => 'Pacific Time',
                            'Europe/London' => 'London',
                            'Europe/Paris' => 'Paris',
                            'Asia/Tokyo' => 'Tokyo',
                            'Asia/Shanghai' => 'Shanghai',
                        ]) ?>

                        <div class="form-group">
                            <?= Html::submitButton('Create Account', ['class' => 'btn btn-primary btn-block w-100', 'name' => 'signup-button']) ?>
                        </div>

                        <div class="text-center mt-3">
                            <p>Already have an account? <?= Html::a('Sign in', ['login']) ?></p>
                        </div>

                    <?php ActiveForm::end(); ?>
                </div>
            </div>
        </div>
    </div>
</div>
