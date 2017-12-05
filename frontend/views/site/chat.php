<?php

/* @var $this yii\web\View */
/* @var $form_model \common\models\MessageForm */

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use common\models\User;

$this->title = 'Chat';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="site-chat">
    <div class="simply-sidebar">
<?php
    $users = User::find()->orderBy('username')->all();
    echo "<ul class=\"list-group\">";
    foreach ($users as $user){
        echo "<li class=\"list-group-item\" style='color: $user->color'>" . $user->username . "</li>";
    }
    echo "</ul>";
?>
    </div>
    <div class="simply-content angular-chat">
        <ul class="list-group">
            <li class="list-group-item list-group-item-success">Сообщение 432432</li>
            <li class="list-group-item list-group-item-danger">This is a danger list group item</li>
            <li class="list-group-item list-group-item-warning">This is a warning list group item</li>
            <li class="list-group-item list-group-item-info">This is a info list group item</li>
        </ul>
    </div>
    <div class="simply-input">
        <div class="col-lg-12 simply-col-size">

            <div class="input-group">
                <?php $form = ActiveForm::begin(['id' => 'message-form', 'options' => ['class' => 'form-control']]) ?>
                <?= $form->field($form_model, 'message') ?>
                <?= Html::submitButton('Send', ['class' => 'btn btn-success']) ?>
                <?php ActiveForm::end() ?>
                    <input type="text" class="form-control" name="content" id="content"  placeholder="Insert message..." aria-label="Insert message...">
                    <span class="input-group-btn">
                        <input class="btn btn-info" type="submit" value="Go!">
                    </span>
            </div>

        </div>
        <td id="simply-word-count">Количество символов: <span class="word-count"></span></td>
    </div>
</div>

