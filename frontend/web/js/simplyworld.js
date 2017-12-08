socket = new WebSocket('ws://localhost:8080?'+ AuthKey);//помните про порт: он должен совпадать с тем, который использовался при запуске серверной части
socket.onopen = function(e) {

    // $(".list-group.simplyUser").last().append(
    //     '<li class=\"list-group-item\"><span style="color: ' + UserColor+ '">' + UserName + '</span><span class=\'simply-mute\'><button class=\'mute\'>&#128276;</button></span><span class=\'simply-ban\'><button class=\'ban\'>&#128275;</button></span></li></li>'
    // );
    console.log("Connection established!");

};
socket.onmessage = function(e) {

    var data = JSON.parse(e.data);

    switch (data.type){
        case 'new':
            $(".list-group.simplyUser").last().append(
                data.role == 'user' ?
                '<li class=\"list-group-item\" id="'+data.id+'" style="color: ' + data.color+ '"><span>' + data.name + '</span>' +
                '<span class=\'simply-mute\'><button id="mute'+ data.id +'">&#128276;</button></span><span class=\'simply-ban\'>' +
                '<button id="ban' + data.id + '">&#128275;</button></span></li></li>' :
                    '<li class=\"list-group-item\" id="'+data.id+'" style="color: ' + data.color+ '"><span>' + data.name + '</span>'
            );
            // data.mute == 1 ? socket.close() : '';
            console.log('new user in chat: ' + data.name);
            break;

        case 'exit':
            $(".list-group.simplyUser").find('li#'+data.id).remove();
            console.log('user ' + data.name + ' leave chat');
            break;

        case 'message':
            data.ban == 1 ? onclose() : '';
            console.log('message: '+data.msg);
            break;
    }

    $("#ban" +data.id).click(function () {

        $("#ban" +data.id).attr('banned','true');
        $("#ban" +data.id).text('2222');
        var msg = {
            banned:  $(this).attr('banned'),
            user: data.name,
            id: data.id,
            connid: data.connid,
            msg: '<li class="list-group-item list-group-item-danger simply-chat">'+ data.name + ' : Was Banned!</li>',
        };
        socket.send(JSON.stringify(msg));

        // console.log($(this).attr('banned'));
    });

    $("#mute" +data.id).click(function () {

        $("#mute" +data.id).attr('mutted','true');
        $("#mute" +data.id).text('11111');
        var msg = {
            mutted:  $(this).attr('mutted'),
            user: data.name,
            id: data.id,
            connid: data.connid,
            msg: '<li class="list-group-item list-group-item-warning simply-chat">'+ data.name + ' : Was Mutted!</li>',
        };
        socket.send(JSON.stringify(msg));
    });

    $(".list-group.simplymsg").last().append(
        '<li class=\"list-group-item simply-chat\"><span style="color: ' + data.color + '">' + data.name + ' : </span>'+ data.text + '</li>'
    );

    // $("div.simply-content ul").append(
    //     '<li class=\"list-group-item\"><span>'+data.name+' : </span>'+ data.message + '</li>'
    // );
    console.log(data);


};



$('#simplysend').on('click', function(e){
    var input = $('#content').val();
    var userId = $('#messageform-user_id').val();
    e.preventDefault(e);

    if (input === '') {
        return;
    }

    var msg = {
        type:'message',
        msg:  input,
        userAuthKey: userId
    };
    socket.send(JSON.stringify(msg));
    $('#content').val('');
});
