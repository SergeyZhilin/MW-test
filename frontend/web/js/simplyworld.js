socket = new WebSocket('ws://localhost:8080');//помните про порт: он должен совпадать с тем, который использовался при запуске серверной части
socket.onopen = function(e) {
    console.log("Connection established!");
    // socket.send('{"idUser":'+yiiConfig["idUser"]+'}'); //часть моего кода. Сюда вставлять любой валидный json.
};
socket.onmessage = function(e) {
    // var aDate = new Date();
    // var $dateString = '';
    //
    // var $h = aDate.getHours();
    // var $m = aDate.getMinutes();
    // var $s = aDate.getSeconds();
    //
    // if ($h < 10) $h = '0' + $h;
    // if ($m < 10) $m = '0' + $m;
    // if ($s < 10) $s = '0' + $s;
    //
    // $dateString = $h + ':' + $m + ':' + $s;
    // var data = JSON.parse(e.data);
};

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