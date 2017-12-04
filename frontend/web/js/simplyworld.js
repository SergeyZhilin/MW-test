socket = new WebSocket('ws://localhost:8080');//помните про порт: он должен совпадать с тем, который использовался при запуске серверной части
socket.onopen = function(e) {
    socket.send('{"idUser":'+yiiConfig["idUser"]+'}'); //часть моего кода. Сюда вставлять любой валидный json.
};
socket.onmessage = function(e) {
    console.log(e.data);
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