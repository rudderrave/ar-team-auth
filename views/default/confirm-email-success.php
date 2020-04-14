<?php

/**
 * @var yii\web\View $this
 * @var arteam\models\User $user
 */

$this->title = Yii::t('arteam/auth', 'E-mail confirmed');
?>
<div class="change-own-password-success">

    <div class="alert alert-success text-center">
        <?= Yii::t('arteam/auth', 'E-mail confirmed') ?> - <b><?= $user->email ?></b>
    </div>

</div>
