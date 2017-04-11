<?php
namespace WirChat;
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

require_once 'db.php';

class Chat implements MessageComponentInterface {
    protected $clients;
    protected $rooms;
    protected $users_in_room;
    protected $clients_in_room;

    public function __construct() {
        $this->clients = new \SplObjectStorage;
        $this->rooms = array();
        $rooms = getAllRoom();
        $this->users_in_room = array();
        $this->clients_in_room = array();
    }

    public function onOpen(ConnectionInterface $conn) {
        // Store the new connection to send messages to later
        $this->clients->attach($conn);

        echo "New connection! ({$conn->resourceId})\n";
    }

    public function onMessage(ConnectionInterface $from, $msg) {
        $msg = json_decode($msg);

        if ($msg->type === "changeRoom") {
            $uname = $msg->username;
            $rid = $msg->toRoom;
            $crid = $msg->currentRoom;

            // init room
            if (!array_key_exists($rid, $this->users_in_room)) {
                $this->users_in_room[$rid] = array();
                $this->clients_in_room[$rid] = array();
            }

            // exit current room
            if (!empty($crid) && $rid !== $crid) {
                $key = array_search($uname, $this->users_in_room[$crid]);
                unset($this->users_in_room[$crid][$key]);
                unset($this->clients_in_room[$crid][$key]);

                // broadcast new userlist
                $bmsg = array('type'=>'userlist', 'users'=>$this->getUserList($crid), 'text'=>$uname . ' leave this room');
                $bmsg = json_encode($bmsg);
                foreach ($this->clients_in_room[$crid] as $client) {
                    $client->send($bmsg);
                }
            }

            // when username in use, then reject to enter in this room
            if (in_array($uname, $this->users_in_room[$rid])) {
                $errorMsg = array('type'=>'rejectusername', 'text'=>'the name you chose is in use');
                $errorMsg = json_encode($errorMsg);
                $from->send($errorMsg);
                return;
            }
            // add user to room
            array_push($this->users_in_room[$rid], $uname);
            array_push($this->clients_in_room[$rid], $from);

            // send enter room succeed
            $nmsg = array('type'=>'enterroom');
            $nmsg = json_encode($nmsg);
            $from->send($nmsg);

            // broadcast new userlist
            $userlist = $this->getUserList($rid);
            $bmsg = array('type'=>'userlist', 'users'=>$userlist, 'text'=>$uname . ' join this room');
            $bmsg = json_encode($bmsg);
            foreach ($this->clients_in_room[$rid] as $client) {
                $client->send($bmsg);
            }
        } elseif ($msg->type === "message") {
            $uname = $msg->username;
            $rid = $msg->roomId;
            $text = $msg->text;

            $key = array_search($from, $this->clients_in_room[$rid]);
            if ($this->users_in_room[$rid][$key] !== $uname) {
                if (in_array($uname, $this->users_in_room[$rid])) {
                    $errorMsg = array('type'=>'rejectusername', 'text'=>'the name you chose is in use');
                    $errorMsg = json_encode($errorMsg);
                    $from->send($errorMsg);
                    return;
                } else {
                    $this->users_in_room[$rid][$key] = $uname;
                }
            }

            // send message to other user in the room
            $nmsg = array('type'=>'message', 'username'=>$uname, 'text'=>$text);
            $nmsg = json_encode($nmsg);
            foreach ($this->clients_in_room[$rid] as $client) {
                if ($from !== $client) {
                    $client->send($nmsg);
                }
            }
        }
    }

    private function getUserList($rid) {
        $userlist = array();
        foreach ($this->users_in_room[$rid] as $user) {
            array_push($userlist, $user);
        }
        return $userlist;
    }

    public function onClose(ConnectionInterface $conn) {
        // The connection is closed, remove it, as we can no longer send it messages
        $this->clients->detach($conn);

        echo "Connection {$conn->resourceId} has disconnected\n";
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
        echo "An error has occurred: {$e->getMessage()}\n";

        $conn->close();
    }
}