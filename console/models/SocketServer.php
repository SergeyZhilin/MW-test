<?php

namespace console\models;
use common\models\MessageForm;
use common\models\User;
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

class SocketServer implements MessageComponentInterface
{
//    protected $clients;
//    public function __construct()
//    {
//        $this->clients = new \SplObjectStorage; // Для хранения технической информации об присоединившихся клиентах используется технология SplObjectStorage, встроенная в PHP
//    }
//
//    public function onOpen(ConnectionInterface $conn)
//    {
//        $this->clients->attach($conn);
//        echo "New connection! ({$conn->resourceId})\n";
//    }
//
//    public function onMessage(ConnectionInterface $from, $msg)
//    {
//        $data = json_decode($msg, true); //для приема сообщений в формате json
//        if (is_null($data))
//        {
//            echo "invalid data\n";
//            return $from->close();
//        }
//        echo $from->resourceId."\n";//id, присвоенное подключившемуся клиенту
//    }
//
//    public function onClose(ConnectionInterface $conn)
//    {
//        $this->clients->detach($conn);
//        echo "Connection {$conn->resourceId} has disconnected\n";
//    }
//
//    public function onError(ConnectionInterface $conn, \Exception $e)
//    {
//        echo "An error has occurred: {$e->getMessage()}\n";
//        $conn->close();
//    }
    protected $clients;

    public function __construct() {
        $this->clients = new \SplObjectStorage;
    }

    public function onOpen(ConnectionInterface $conn) {
        $token = $conn->httpRequest->getUri()->getQuery();

        $user = User::findOne(['auth_key' => $token]);
        $user->online_status = 'on';
        $user->update();
        $conn->user = $user;
        $allUser =  User::find()->orderBy('username')->all();

        foreach ($this->clients as $client) {
            if($conn->resourceId != $client->resourceId) {
                $client->send(json_encode([
                    'type' => 'new',
                    'name' => $user->username,
                    'id' => $user->id,
                    'color' => $user->color,
                    'text' => 'now online',
                    'ban'    => $user->ban,
                    'mute'    => $user->mute,
                    'all' => $allUser,
                    'online_status' => $user->online_status,
                ]));
            }
        }

        // отправить список всех пользователей в сети только что зашедшему


        // Store the new connection to send messages to later
        $this->clients->attach($conn);
        echo "New connection! ({$conn->resourceId})\n";
    }

    public function onMessage(ConnectionInterface $from, $msg) {

        $numRecv = count($this->clients) - 1;
        echo sprintf('Connection %d sending message "%s" to %d other connection%s' . "\n"
            , $from->resourceId, $msg, $numRecv, $numRecv == 1 ? '' : 's');

        $messages = json_decode($msg);
        var_dump($messages);
        foreach ($this->clients as $client) {
            $client->send(json_encode([
                'type'=>'message',
                'name'   => $from->user->username,
                'text' => $messages->msg,
                'color' => $from->user->color,
                'all'    => 'all user',
                'ban'    => $from->user->ban,
                'mute'    => $from->user->mute,
            ]));
        }

        $msg_chat = new MessageForm();
        $msg_chat->user_id = $from->user->id;
        $msg_chat->message = $messages->msg;
        $msg_chat->save();

        if (isset($messages->mutted) && $messages->mutted == true)
        {
            $user = User::findIdentity($messages->id);
            $user->mute = 1;
            $user->update();
        }

        if (isset($messages->banned) && $messages->banned == true)
        {
            $user = User::findIdentity($messages->id);
            $user->ban = 1;
            $user->update();
        }

    }

    public function onClose(ConnectionInterface $conn) {

//        $token = $conn->httpRequest->getUri()->getQuery();

//        $user = User::findOne(['auth_key' => $token]);
        $conn->user->online_status = 'off';
        $conn->user->update();

        foreach ($this->clients as $client) {
            if ($client->resourceId != $conn->resourceId) {
                $client->send(json_encode([
                    'type' => 'exit',
                    'id' => $conn->user->id,
//                    'id' => $conn->resourceId,
                    'text' => 'exit from chat',
                    'color' => $conn->user->color,
                    'name' => $conn->user->username,
                ]));
            }
        }

        // The connection is closed, remove it, as we can no longer send it messages
        $this->clients->detach($conn);

        echo "Connection {$conn->resourceId} has disconnected\n";
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
        echo "An error has occurred: {$e->getMessage()}\n";

        $conn->close();
    }

}