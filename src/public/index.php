<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

require '../../vendor/autoload.php';

$app = new \Slim\App;
$app->get('/hello/{name}', function (Request $request, Response $response) {
    $name = $request->getAttribute('name');
    $response->getBody()->write("Hello, $name");

    return $response;
});
$app->get('/', function (Request $request, Response $response) {
    $response->getBody()->write("lol");
    return $response;
});

$app->get('/room', 'getAllRoom');
$app->post('/room', 'addRoom');

$app->run();

function getConnection() {
    $dbhost = "localhost";
    $dbuser = "root";
    $dbpass = "";
    $dbname = "wirchat";
    $dbh = new PDO("mysql:host=$dbhost;dbname=$dbname", $dbuser, $dbpass);
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    return $dbh;
}

function getAllRoom(Request $request, Response $response) {
    $sql = "SELECT * FROM room";
    try {
        $db = getConnection();
        $stmt = $db->query($sql);
        $rooms = $stmt->fetchAll(PDO::FETCH_OBJ);
        $db = null;
        // echo '{"rooms": ' . json_encode($rooms) . '}';
        $newResponse = $response->withJson($rooms);
        return $newResponse;
    } catch (PDOException $e) {
        echo '{"error":{"text":' . $e->getMessage() . '}}';
    }
}

function addRoom(Request $request, Response $response) {
    $room = json_decode($request->getBody());
    $sql = "INSERT INTO room (id, name) VALUES (NULL, :name)";
    try {
        $db = getConnection();
        $stmt = $db->prepare($sql);
        $stmt->bindParam("name", $room->name);
        $stmt->execute();
        $room->id = $db->lastInsertId();
        $db = null;
        $newResponse = $response->withJson($room);
        return $newResponse;
    } catch (PDOException $e) {
        echo '{"error":{"text":"' . $e->getMessage() . '"}}';
        // $error = '{"error":{"text":"' . $e->getMessage() . '"}}';
        // $newResponse = $response->withJson($error);
        // return $newResponse;
    }
}
