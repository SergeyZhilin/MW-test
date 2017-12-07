<?php

/* @var $this yii\web\View */
/* @var $form_model common\models\MessageForm */

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use common\models\User;
use common\models\MessageForm;

$this->title = 'Chat';
$this->params['breadcrumbs'][] = $this->title;

if(Yii::$app->user->isGuest) {

    return Yii::$app->response->redirect('login');
}
?>

<div class="site-chat">
    <div class="simply-sidebar">
<?php
    $users = User::find()->orderBy('username')->all();
    echo "<ul class=\"list-group simplyUser\">";
    foreach ($users as $user){
        echo "<li class=\"list-group-item\" style='color: $user->color'>" . $user->username . "<span class='simply-mute'><button class='mute'>&#128276;</button></span><span class='simply-ban'><button class='ban'>&#128275;</button></span></li>";
    }
    echo "</ul>";
?>
    </div>
    <div class="simply-content angular-chat">
        <ul class="list-group simplymsg">
            <?php
                $messages = MessageForm::find()->all();
                foreach ($messages as $message) {
                    $user = User::findIdentity($message->user_id);
                    echo "<li class=\"list-group-item simply-chat\"><span style='color: $user->color'>$user->username : </span>$message->message</li>";
                }
            ?>
        </ul>
    </div>
    <div class="simply-input">
        <div class="col-lg-12 simply-col-size">
            <?php
            $currentUserId = Yii::$app->user->id;
            $currentUser = User::findIdentity($currentUserId);
            ?>
                <?php $form = ActiveForm::begin(['id' => 'message-form', 'options' => ['class' => 'form-control']]) ?>
                <?= $form->field($form_model, 'message')->textInput(['maxlength' => '200', 'id' => 'content', 'class' => 'form-control']) ?>
                <?= $form->field($form_model, 'user_id')->hiddenInput(['value' => $currentUser->auth_key]) ?>
                <?= Html::submitButton('Send', ['class' => 'btn btn-success', 'id' => 'simplysend']) ?>
                <?php ActiveForm::end() ?>

        </div>
<!--        <td id="simply-word-count">Количество символов: <span class="word-count"></span></td>-->
    </div>
</div>

<script>
    var AuthKey = '<?= $currentUser->auth_key; ?>';
    var UserName = '<?= $currentUser->username; ?>';
    var UserColor = '<?= $currentUser->color; ?>';
</script>