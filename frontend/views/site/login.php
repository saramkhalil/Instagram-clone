<?php

/** @var yii\web\View $this */
/** @var yii\bootstrap5\ActiveForm $form */
/** @var \common\models\LoginForm $model */

use yii\bootstrap5\Html;
use yii\bootstrap5\ActiveForm;

$this->title = 'Sign In to Instagram Clone';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="site-login">
    <div class="row justify-content-center">
        <div class="col-lg-4">
            <div class="card">
                <div class="card-body">
                    <h1 class="text-center mb-4"><?= Html::encode($this->title) ?></h1>

                    <?php $form = ActiveForm::begin(['id' => 'login-form']); ?>

                        <?= $form->field($model, 'username')->textInput(['autofocus' => true, 'placeholder' => 'Username or Email']) ?>

                        <?= $form->field($model, 'password')->passwordInput(['placeholder' => 'Password']) ?>

                        <?= $form->field($model, 'rememberMe')->checkbox() ?>

                        <div class="form-group">
                            <?= Html::submitButton('Sign In', ['class' => 'btn btn-primary btn-block w-100', 'name' => 'login-button']) ?>
                        </div>

                        <div class="text-center mt-3">
                            <p>Don't have an account? <?= Html::a('Sign up', ['signup']) ?></p>
                        </div>

                        <div class="text-center mt-3">
                            <small class="text-muted">
                                <?= Html::a('Forgot password?', ['request-password-reset']) ?> |
                                <?= Html::a('Resend verification email', ['resend-verification-email']) ?>
                            </small>
                        </div>

                    <?php ActiveForm::end(); ?>
                </div>
            </div>
        </div>
    </div>
</div>
