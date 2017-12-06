socket = new WebSocket('ws://localhost:8080?'+ AuthKey);//помните про порт: он должен совпадать с тем, который использовался при запуске серверной части
socket.onopen = function(e) {
    console.log("Connection established!");
    // socket.send('{"idUser":'+yiiConfig["idUser"]+'}'); //часть моего кода. Сюда вставлять любой валидный json.
};
socket.onmessage = function(e) {

    var data = JSON.parse(e.data);
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

});