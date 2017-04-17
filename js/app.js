var my_avatar_src;
my_avatar_src = "images/avatar/avatar" + getRandomNum(1, 9) + ".png";

var rooms = new Array();
$.getJSON(
    "api/room",
    function (data) {
        $.each(data, function (i, element) {
            // rooms[i] = element.name;
            rooms[i] = element;
        });
    }
);

var room = null;
var currentRoom = null;

var app = angular.module('wirchat', ['ngMaterial']);
app.controller('roomCtrl', function ($scope, $rootScope, $http, $mdDialog) {
    $http.get("api/room").then(function (response) {
        $rootScope.rooms = response.data;
    });
    $scope.showPrompt = function (ev) {
        var confirm = $mdDialog.prompt()
            .title('add new room')
            .placeholder('room name')
            .targetEvent(ev)
            .ok('add')
            .cancel('cancel');

        $mdDialog.show(confirm).then(function (result) {
            // alert("room name:" + result);
            $http.post("api/room", { 'id': null, 'name': result }).then(function (response) {
                var res = response.data;
                if (res.hasOwnProperty('error')) {
                    alert("error, Duplicate room name");
                } else {
                    alert("add room succeed");
                    $rootScope.rooms.push(res);
                }
            });
        });
    };
});

app.controller('tabCtrl', function ($scope) {
    $scope.tabSelected = "#room_list";
    $scope.tabChange = function (newTab) {
        $scope.tabSelected = newTab;
    }
});


// bind websocket
var conn = new WebSocket('ws://localhost:8090');
conn.onopen = function (e) {
    alert("Connection established!");
};

var scrolltoend = function () {
    var div = $(".box_bd");
    div.scrollTop(div[0].scrollHeight);
};

conn.onmessage = function (e) {

    var msg = $.parseJSON(e.data);
    var text = "";

    console.log(msg);

    switch (msg.type) {
        case "rejectusername":
            alert(msg.text);
            break;
        case "enterroom":
            setRoom(room);
            break;
        case "userlist":
            text = msg.text;
            var users = msg.users;
            $("#box_title").text(room.name + " (" + msg.users.length + ")");
            $(".message_box").append("<div class='message_info'><p class='message_system '><span class='content'>" + text + "</span></p></div>");
            // set userlist in left side
            $("#user_list").empty();
            var length = users.length;
            for (var i = 0; i < length; i++) {
                var name = users[i];
                $("#user_list").append("<div class='chat_item'><div class='avatar'><img class='img' src='images/logo.png' /></div><div class='info'><h3 class='nickname'><span class='nickname_text'>" + name + "</span></h3></div></div>");
            }
            break;
        case "message":
            text = msg.text;
            var nickname = msg.username;
            $(".message_box").append("<div class='message'><img class='avatar' src=" + getRandomAvatar() + " /><h4 class='nickname'>" + nickname + "</h4><div class='bubble bubble_default left'><div class='bubble_cont'><div class='plain'><pre>" + text + "</pre></div></div></div></div>");
            // $(".message_box").append("<div class='message'><img class='avatar' src='images/logo.png' /><div class='bubble bubble_default left'><div class='bubble_cont'><div class='plain'><pre>" + text + "</pre></div></div></div></div>");
            break;
    }

    scrolltoend();
};

function sendMessage() {
    var text = document.getElementById("chat_text").value;
    document.getElementById("chat_text").value = "";

    var msg = {
        type: "message",
        text: text,
        username: $("#username").val(),
        roomId: currentRoom.id
    };
    // alert(JSON.stringify(msg));
    conn.send(JSON.stringify(msg));
    $(".message_box").append("<div class='message me'><img class='avatar' src='" + my_avatar_src + "' /><div class='bubble bubble_primary right'><div class='bubble_cont'><div class='plain'><pre>" + text + "</pre></div></div></div></div>");
    scrolltoend();
}

function enterRoom(r) {
    room = $.parseJSON(r);
    if (currentRoom !== null && room.id == currentRoom.id) {
        alert("you have already in this room");
        return;
    }

    var cid = currentRoom === null ? null : currentRoom.id;
    var msg = {
        type: "changeRoom",
        username: $("#username").val(),
        toRoom: room.id,
        currentRoom: cid
    };
    conn.send(JSON.stringify(msg));
};

function setRoom(room) {
    // room = $.parseJSON(r);
    currentRoom = room;
    // $("#box_title").text(room.name);
    $(".message_box").text("");

    if ($("#box_content").is(":hidden"))
        $("#box_content").show();
};

$(document).ready(function () {
    $("textarea").keypress(function (e) {
        if (e.keyCode == 13) {
            $("button.btn_send").click();
        }
    });

    // chat box cant be seen when not in a room
    if (currentRoom === null) {
        $("#box_content").hide();
    }
});

function getRandomNum(Min, Max) {
    var Range = Max - Min;
    var Rand = Math.random();
    return Min + Math.round(Rand * Range);
}

function getRandomPicSrc() {
    avatar_src = "images/avatar/avatar" + getRandomNum(1, 9) + ".png";
    document.getElementById("myImage").src = avatar_src;
}

function getRandomAvatar() {
    return "images/avatar/avatar" + getRandomNum(1, 9) + ".png";
}

function getSelectRoom(index) {
    var selectRList = new Array();
    var i = 0;
    for (var item in rooms) {
        var variable = index.split("");
        var b = 0;
        for (var v in variable) {
            if (rooms[item].name.toUpperCase().indexOf(variable[v].toUpperCase()) >= 0 && b == 0) {
                b = 0;
            } else {
                b = 1;
            }
        }
        if (b == 0) {
            selectRList[i] = rooms[item];
            i++;
        }
    }
    return selectRList;
}

function onInput(event) {
    $(".contacts").empty();
    if (event.target.value.length != 0) {
        var selectRList = getSelectRoom(event.target.value);
        for (var item in selectRList) {
            var room = JSON.stringify(selectRList[item]);
            $(".contacts").append("<div class='contact_item' id='" + room + "' onclick='enterRoom(id)'><div class='avatar'><img class='img' src='images/logo.png' /></div><div class='info'><h4 class='nickname'>" + selectRList[item].name + "</h4></div></div>");
        }
    }
}

//        adapt to IE
function onPropChanged(event) {
    $(".contacts").empty();
    if (event.target.value.length != 0) {
        var selectRList = getSelectRoom(event.target.value);
        for (var item in selectRList) {
            var room = JSON.stringify(selectRList[item]);
            $(".contacts").append("<div class='contact_item on' id='" + room + "' onclick='enterRoom(id)'><div class='avatar'><img class='img' src='images/logo.png' /></div><div class='info'><h4 class='nickname'>" + selectRList[item] + "</h4></div></div>");
        }
    }
}