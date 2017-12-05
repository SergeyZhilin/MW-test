socket = new WebSocket('ws://localhost:8080');//помните про порт: он должен совпадать с тем, который использовался при запуске серверной части
socket.onopen = function(e) {
    console.log("Connection established!");
    // socket.send('{"idUser":'+yiiConfig["idUser"]+'}'); //часть моего кода. Сюда вставлять любой валидный json.
};
socket.onmessage = function(e) {
    var aDate = new Date();
    var $dateString = '';

    var $h = aDate.getHours();
    var $m = aDate.getMinutes();
    var $s = aDate.getSeconds();

    if ($h < 10) $h = '0' + $h;
    if ($m < 10) $m = '0' + $m;
    if ($s < 10) $s = '0' + $s;

    $dateString = $h + ':' + $m + ':' + $s;
    var data   = JSON.parse(e.data);
    $.each(data.all, function (index, value) {
        if ($('.user-name-' + value.name).length === 0) {
            $('ul.list-unstyled:nth-child(1)').append(
                '<li class="user-name-' + value.name + ' offline" style="color:' + value.color_body + '">' + value.name + '</li>'
            );
        }
    })

    if (data.status === 'on') {
        $('.user-name-' + data.name).removeClass('offline').addClass('online');
    } else if (data.status === 'off') {
        $('.user-name-' + data.name).removeClass('online').addClass('offline');
    } else if (data.msg) {
        var $ul = $("ul.list-message");
        $ul.append(
            '<li class="msg-list" style="background:' + data.user.color_body + '">' +
            '<span class="msg-author col-md-1 col-sm-1" style="background:' + data.user.color_avatar + '">'
            + data.user.name[0] + '' +
            '</span>' + data.msg +
            '<span class="msg-time">' + $dateString + '</span>' +
            '</li>'
        );
    }

    $input.val('');
};

socket.onclose = function(e) {
    console.log("Connection lost!");
};

$('form[name="send-message"]').on('submit',function(e){
    $input.focus();
    e.preventDefault(e);
    if ($input.val() === '') {
        return;
    }

    var msg = {
        msg:  $input.val(),
        user: userId
    };
    socket.send(JSON.stringify(msg));
});

// Работа с ангуляром
// var app = angular.module("MyChatApplication", []);
//
// app.controller("MyChatControl", function () {
//     this.messagess = [1,2,'hello','fdsfsd'];
//     console.log('-------');
//     this.sendMessage = function () {
//         console.log('+++++++');
//         this.messagess.push(this.newMessage);
//     };
// });