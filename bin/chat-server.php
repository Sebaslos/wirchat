<?php
use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use WirChat\Chat;

    require dirname(__DIR__) . '/vendor/autoload.php';
    require dirname(__DIR__) . '/api/Chat.php';

    $server = IoServer::factory(
        new HttpServer(
            new WsServer(
                new Chat()
            )
        ),
        8090
    );

    $server->run();