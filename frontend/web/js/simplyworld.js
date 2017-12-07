socket = new WebSocket('ws://localhost:8080?'+ AuthKey);//помните про порт: он должен совпадать с тем, который использовался при запуске серверной части
socket.onopen = function(e) {
    $(".list-group.simplyUser").last().append(
        '<li class=\"list-group-item\"><span style="color: ' + UserColor+ '">' + UserName + '</span></li>'
    );
    $(".list-group.simplymsg").last().append(
        '<li class=\"list-group-item simply-chat\"><span style="color: ' + UserColor+ '">' + UserName + ' : now online</span></li>'
    );
    console.log("Connection established!");
    // socket.send('{"idUser":'+yiiConfig["idUser"]+'}'); //часть моего кода. Сюда вставлять любой валидный json.
};
socket.onmessage = function(e) {

    var data = JSON.parse(e.data);
    if (!empty(UserName)){
        $(".list-group.simplyUser").last().append(
            '<li class=\"list-group-item\"><span style="color: ' + UserColor+ '">' + UserName + '</span></li>'
        );
    }
    $(".list-group.simplymsg").last().append(
        '<li class=\"list-group-item simply-chat\"><span style="color: ' + data.color + '">'+data.name+' : </span>'+ data.message + '</li>'
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
        msg:  input,
        userAuthKey: userId
    };
    socket.send(JSON.stringify(msg));
    $('#content').val('');
});
