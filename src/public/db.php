<?php

function getConnection() {
    $dbhost = "localhost";
    $dbuser = "root";
    $dbpass = "";
    $dbname = "wirchat";
    $dbh = new PDO("mysql:host=$dbhost;dbname=$dbname", $dbuser, $dbpass);
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    return $dbh;
}

function getAllRoom() {
    $sql = "SELECT * FROM room";
    try {
        $db = getConnection();
        $stmt = $db->query($sql);
        $rooms = $stmt->fetchAll(PDO::FETCH_OBJ);
        return $rooms;
    } catch (PDOException $e) {
        echo '{"error":{"text":' . $e->getMessage() . '}}';
    }
}