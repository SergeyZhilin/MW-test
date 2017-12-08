<?php

namespace console\models;
use common\models\MessageForm;
use common\models\User;
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

class SocketServer implements MessageComponentInterface
{
    protected $clients;

    public function __construct() {
        $this->clients = new \SplObjectStorage;
    }

    public function onOpen(ConnectionInterface $conn) {
        $token = $conn->httpRequest->getUri()->getQuery();

        $user = User::findOne(['auth_key' => $token]);
        $user->online_status = 'on';
        $user->update();
//        var_dump($user->ban == 1);
        // если пользователь забанен - выкинули из чата
        if ($user->ban == 1){
            $conn->close();
        }

        $conn->user = $user;
        $this->sendAll([
            'type' => 'new',
            'text' => 'Enter in chat',
            'name' => $user->username,
            'id' => $user->id,
            'color' => $user->color,
        ]);


        // отправить список всех пользователей в сети только что зашедшему
        $this->sendUserList($conn);

        // Store the new connection to send messages to later
        $this->clients->attach($conn);
        echo "New connection! ({$conn->resourceId})\n";
    }

    public function onMessage(ConnectionInterface $from, $msg) {

        // декодируем полученное сообщение от клиента
        $data = json_decode($msg);

        // разбираем тип сообщения
        switch ($data->type){
            case 'message':
                // если пользователь не в режиме "только для чтения", разрешаем пересылку его сообщения всем остальным
                if (!$from->user->mute == 1 ) {

                    $messages = MessageForm::find()
                        ->where(['user_id' => $from->user->id])
                        ->orderBy('time')
                        ->all();

                    $rev_mes = array_reverse($messages);
                    $mes_time = array_shift($rev_mes);

                    if(time() - strtotime($mes_time->time) > 15) {
                        ( new MessageForm([
                            'user_id'=>$from->user->id,
                            'message'=>$data->text,
                            'time'=> date("Y-m-d H:i:s")
                        ]))->save();

                        // рассылка всем
                        $this->sendAll([
                            'type' => 'message',
                            'name' => $from->user->username,
                            'text' => $data->text,
                            'color' => $from->user->color,
                            'id' => $from->user->id,
                        ]);
                    }

                }
                break;

            case 'ban':
                // проверка на админа и id пользователя, для которого выполняем действие
                if ($from->user->isAdmin && $data->id){
                    $bannedUser = $this->ban($data->id);

                    // записываем в историю бд
                    ( new MessageForm([
                        'user_id'=>$from->user->id,
                        'message'=>$data->text,
                        'time'=> date("Y-m-d H:i:s")
                    ]))->save();

                    $this->sendAll([
                        'type' => 'ban',
                        'text' => $data->text,
                        'name' => $bannedUser->username,
                        'id'=> $bannedUser->id
                    ]);
                }
                break;

            case 'mute':
                // проверка на админа и id пользователя, для которого выполняем действие
                if ($from->user->isAdmin && $data->id){
                    $muttedUser = $this->mute($data->id);

                    // записываем в историю бд
                    ( new MessageForm([
                        'user_id'=>$from->user->id,
                        'message'=>$data->text,
                        'time'=> date("Y-m-d H:i:s")
                    ]))->save();

                    $this->sendAll([
                        'type' => 'mute',
                        'text' => $data->text,
                        'name' => $muttedUser->username,
                        'id'=> $muttedUser->id
                    ]);
                }
                break;

            case 'unban':
                // проверка на админа и id пользователя, для которого выполняем действие
                if ($from->user->isAdmin && $data->id){
                    $bannedUser = $this->ban($data->id, 0);
                    $this->sendAll([
                        'type' => 'unban',
                        'text' => $data->text,
                        'name' => $bannedUser->username,
                        'id'=> $bannedUser->id
                    ]);
                }
                break;

            case 'unmute':
                // проверка на админа и id пользователя, для которого выполняем действие
                if ($from->user->isAdmin && $data->id){
                    $muttedUser = $this->mute($data->id, 0);
                    $this->sendAll([
                        'type' => 'unmute',
                        'text' => $data->text,
                        'name' => $muttedUser->username,
                        'id'=> $muttedUser->id
                    ]);
                }
                break;

        }
    }

    public function onClose(ConnectionInterface $conn) {

        $conn->user->online_status = 'off';
        $conn->user->update();

        $this->sendAll([
            'type' => 'exit',
            'id' => $conn->user->id,
            'text' => 'Leave chat',
            'color' => $conn->user->color,
            'name' => $conn->user->username,
        ],$conn);

        // The connection is closed, remove it, as we can no longer send it messages
        $this->clients->detach($conn);

        echo "Connection {$conn->resourceId} has disconnected\n";
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
        echo "An error has occurred: {$e->getMessage()}\n";

        $conn->close();
    }

    /**
     * Отправить информацию всем клиентам
     *
     * @param mixed $data данные для отправки
     * @param null|mixed $current текущее подключение, которому не нужно отправлять данные
     */
    public function sendAll($data,$current=null){
        foreach ($this->clients as $client) {
            if (!$current || $current->resourceId != $client->resourceId) {
                $client->send(json_encode($data));
            }
        }
    }

    /**
     * Бан/анбан пользователя по id
     *
     * @param int $userId id пользователя
     * @param bool $state состояние нового статуса
     * @return bool|User
     */
    public function ban($userId, $state = true){
        foreach ($this->clients as $client) {
            if ($client->user->id == $userId){
                $client->user->ban = $state;
                $client->user->save();

                $client->close();
                return $client->user;
            }
        }

        return false;
    }

    /**
     * Мут/анмут пользователя по id
     *
     * @param int $userId id пользователя
     * @param bool $state состояние нового статуса
     * @return bool|User
     */
    public function mute($userId, $state = true){
        foreach ($this->clients as $client) {
            if ($client->user->id == $userId){
                $client->user->mute = $state;

                $client->user->save();
                return $client->user;
            }
        }

        return false;
    }

    /**
     * Отправить клиенту список всех текущих пользователей
     *
     * @param $conn
     */
    public function sendUserList($conn){
        $list = User::find()
            ->select(['id','username','color','mute'])
            ->where(['online_status' => 'on'])
            ->orderBy('username')
            ->asArray()
            ->all();

        $conn->send(json_encode([
            'type'=>'users',
            'list'=>$list
        ]));
    }
}