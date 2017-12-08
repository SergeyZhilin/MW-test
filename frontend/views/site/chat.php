<?php

/* @var $this yii\web\View */
/* @var $form_model common\models\MessageForm */

use common\models\MessageForm;

$this->title = 'Chat';
$this->params['breadcrumbs'][] = $this->title;
$messages = MessageForm::find()->all();
$currentUser = Yii::$app->user->identity;


?>

<div class="site-chat">
    <div class="simply-sidebar">
        <ul class="list-group simplyUser"></ul>

    </div>
</div>
    <div class="simply-content angular-chat">
        <ul class="list-group simplymsg">
            <?php
                foreach ($messages as $message) {
                    ?>
                    <li class="list-group-item simply-chat">
                        <span style='color: <?= $message->user->color ?>'><?= $message->user->username ?> : </span>
                        <?= $message->message ?>
                    </li>
                <?php
                }
            ?>
        </ul>
    </div>
    <div class="simply-input">
        <div class="col-lg-12 simply-col-size">

            <form id="message-form" class="form-control">
                <div class="form-group field-content">
                    <label class="control-label" for="content">Message</label>
                    <input id="content" class="form-control" maxlength="200" aria-required="true" aria-invalid="true" type="text">

<!--                    <div class="help-block">Message cannot be blank.</div>-->
                </div>

                <button type="submit" id="simplysend" class="btn btn-success">Send</button>
            </form>

        </div>
<!--        <td id="simply-word-count">Количество символов: <span class="word-count"></span></td>-->
    </div>
</div>

<script>
    $(function() {

        var userList = $(".list-group.simplyUser"),
            dialog = $(".list-group.simplymsg");

        //помните про порт: он должен совпадать с тем, который использовался при запуске серверной части
        socket = new WebSocket('ws://localhost:8080?<?= $currentUser->auth_key; ?>');

        socket.onopen = function (e) {
            console.log("Connection established!");

        };

        socket.onmessage = function (e) {

            var data = JSON.parse(e.data);


            // console.log(data);

            switch (data.type) {
                case 'users':
                    $.each(data.list, function (index, value) {
                        var li = $('<li/>').addClass('list-group-item').attr('id', 'user-' + value.id).html(value.username);
                        userList.append(li);
                    });
                    break;

                case 'new':

                    var li = $('<li>').addClass('list-group-item').attr('id', 'user-' + data.id).html(data.name);
                    console.log('user ' + data.name + ' enter in chat');

                <?php if ($currentUser->isAdmin){ ?>
                    li.append($('<span class="simply-mute"><button class="mute-button" rel="'+ data.id +'">Mute<!--&#128276;--></button></span>' +
                                '<span class="simply-ban"><button class="ban-button" rel="'+ data.id+'">Ban<!--&#128275;--></button></span>'));
                <?php } ?>

                    userList.append(li);

                    // console.log('new user in chat: ' + data.name);
                    break;

                case 'exit':
                    userList.find('li#user-' + data.id).remove();

                    console.log('user ' + data.name + ' leave chat');
                    break;

                case 'message':
                    // data.ban === 1 ? onclose() : '';
                    // dialog.append(
                        // $('<li>').addClass('list-group-item').html(data.name).html(data.text)
                    // );

                    // console.log('message: ' + data.msg);
                    break;
            }



            $(".list-group.simplymsg").last().append(
                '<li class=\"list-group-item simply-chat\"><span style="color: ' + data.color + '">' + data.name + ' : </span>'+ data.text + '</li>'
            );
            // console.log(data);
        };

        userList.on('click','.mute-button',function(){
            this.textContent = this.textContent === 'Mute' ? 'UnMute' : 'Mute';
            switch (this.textContent){
                case 'Mute':
                    $('.mute-button').attr('unmute','unmute');
                    socket.send(JSON.stringify({
                        type:'unmute',
                        text: 'UnMutted',
                        id:$(this).attr('rel')
                    }));
                break;
                case 'UnMute':
                    $('.mute-button').removeAttr('unmute');
                    socket.send(JSON.stringify({
                        type:'mute',
                        text: 'Mutted',
                        id:$(this).attr('rel')
                    }));
                break;
            }
            // socket.send(JSON.stringify({
            //     type:'mute',
            //     text: 'mutted',
            //     id:$(this).attr('rel')
            // }))
        });

        userList.on('click','.ban-button',function(){
            this.textContent = this.textContent === 'Ban' ? 'UnBan' : 'Ban';
            switch (this.textContent){
                case 'Ban':
                    $('.ban-button').attr('unban','unban');
                    socket.send(JSON.stringify({
                        type:'unban',
                        text: 'UnBanned',
                        id:$(this).attr('rel')
                    }));
                    break;
                case 'UnBan':
                    $('.ban-button').removeAttr('unban');
                    socket.send(JSON.stringify({
                        type:'ban',
                        text:'Banned',
                        id:$(this).attr('rel')
                    }));
                    break;
            }
            socket.send(JSON.stringify({
                type:'ban',
                text:'banned',
                id:$(this).attr('rel')
            }))
        });

        $('#simplysend').on('click', function (e) {
            e.preventDefault(e);

            // не хватает таймера 1 сообщение в 15 секунд

            var input = $('#content').val();
            var msg = {
                type: 'message',
                text: input
            };

            if (input === '') {
                return;
            }

            socket.send(JSON.stringify(msg));
            $('#content').val('');
        });

    });
</script>