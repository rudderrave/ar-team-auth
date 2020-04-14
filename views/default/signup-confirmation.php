<?php

/**
 * @var yii\web\View $this
 * @var arteam\models\User $user
 */

$this->title = Yii::t('arteam/auth', 'Registration - confirm your e-mail');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="registration-wait-for-confirmation">

    <div class="alert alert-info text-center">
        <?= Yii::t('arteam/auth', 'Check your e-mail {email} for instructions to activate account', [
            'email' => '<b>' . $user->email . '</b>'
        ]) ?>
    </div>

</div>
