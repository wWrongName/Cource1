<?php
    $structList = json_decode(file_get_contents('structures.json'));
    $flag = false;
    if (isset($_COOKIE["token"]) && isset($_GET["user"])) {
        for ($i = 0; $i < count($structList->users); $i++)
            if ($structList->users[$i]->user == $_GET["user"])
                for ($j = 0; $j < count($structList->users[$i]->token); $j++)
                    if ($structList->users[$i]->token[$j] == $_COOKIE["token"])
                        $flag = true;
    }
    if (!$flag) {
        require('authPage.html');
        exit();
    }
?>
<!doctype html>
<html lang="en">
  <head>
      <style>
          .submenuButtons {
              background: #39536a;
              border: 0;
              padding: 10px;
          }
      </style>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
    <link href="https://stackpath.bootstrapcdn.com/bootswatch/4.4.1/superhero/bootstrap.min.css" rel="stylesheet" integrity="sha384-R/oa7KS0iDoHwdh4Gyl3/fU7pgvSCt7oyuQ79pkw+e+bMWD9dzJJa+Zqd+XJS0AD" crossorigin="anonymous">
  </head>
  <body>
    <script>
        let intervalId;
        let category = 'title';
        let sendNote = function () {
            let frame = document.getElementById("frame").contentDocument;
            let user = frame.getElementById("user").value;
            let text = frame.getElementById("exampleTextarea").value;
            let promise = new Promise(function (resolve, reject) {
                let xmlRequest = new XMLHttpRequest();
                xmlRequest.open("POST", 'commandsAllocator/createNote');
                xmlRequest.send(JSON.stringify({
                    "token": "<?php if(isset($_COOKIE["token"])) echo $_COOKIE["token"];?>",
                    "title" : user,
                    "text" : text
                }));
            });
            frame.getElementById("user").value = "";
            frame.getElementById("exampleTextarea").value = "";
        };
        let newNote = function() {
            let insideFrame = document.getElementById("frame");
            insideFrame.contentDocument.open();
            let htmlCode = `<link href="https://stackpath.bootstrapcdn.com/bootswatch/4.4.1/superhero/bootstrap.min.css" rel="stylesheet" integrity="sha384-R/oa7KS0iDoHwdh4Gyl3/fU7pgvSCt7oyuQ79pkw+e+bMWD9dzJJa+Zqd+XJS0AD" crossorigin="anonymous">`
            htmlCode += `<div style="position: fixed; top: 40%; left: 45%; margin-left: -200px; width: 500px">
                                     <input class="form-control mr-sm-2" id="user" type="text" placeholder="Title">
                                     <div class="form-group">
                                        <textarea class="form-control" id="exampleTextarea" rows="3" style="height: 79px;" placeholder="Text"></textarea>
                                     </div>
                                  </div>`;
            insideFrame.contentDocument.write(htmlCode);
            insideFrame.contentDocument.addEventListener('keyup', function (event) {
                if (event.key === "Enter") {
                    sendNote();
                }
            });
        };
        let getNotes = function() {
            promise = new Promise(function (resolve, reject) {
                let xmlRequest = new XMLHttpRequest();
                xmlRequest.open("GET", 'commandsAllocator/getNotes/<?php if(isset($_COOKIE["token"])) echo $_COOKIE["token"];?>');
                xmlRequest.send();
                let insideFrame = document.getElementById("frame");
                xmlRequest.onload = function() {
                    if (this.status === 200) {
                        let note = JSON.parse(xmlRequest.responseText).text;
                        let htmlCode = `<table class="table table-hover" style="width: 30%; top: 20%"><tbody>`;
                        for (let i = 0; i < tableData.user.length; i++) {
                            htmlCode += `<tr><td>${note.name}</td><td>${note.text}</td></tr>`;
                        }
                        htmlCode += '</tbody></table>';
                        insideFrame.contentDocument.write(htmlCode);
                    } else {
                        insideFrame.contentDocument.write(`<h4 style="position: fixed; top: 40%; left: 45%; margin-left: -40px"></h4>`);
                    }
                };
            });
        };
        let mainAccount = function() {
            let insideFrame = document.getElementById("frame");
            let htmlCode = `<link href="https://stackpath.bootstrapcdn.com/bootswatch/4.4.1/superhero/bootstrap.min.css" rel="stylesheet" integrity="sha384-R/oa7KS0iDoHwdh4Gyl3/fU7pgvSCt7oyuQ79pkw+e+bMWD9dzJJa+Zqd+XJS0AD" crossorigin="anonymous">`;
            let promise = new Promise(() => {
                let req = new XMLHttpRequest();
                req.open("GET", 'commandsAllocator/getUser/<?php if(isset($_COOKIE["token"])) echo $_COOKIE["token"];?>/<?php if(isset($_GET["user"])) echo $_GET["user"];?>');
                req.send();
                req.onload = function () {
                    let data = JSON.parse(req.responseText);
                    if (this.status == 200) {
                        insideFrame.contentDocument.write(`<h1 style="position: fixed; left: 5%; top: 10%;"><a style="font-size: 50%">USER:</a> ${data.user}</h1>`);
                        insideFrame.contentDocument.write(`<img style="position: fixed; left: 5%; top: 30%" src=${data.photo} alt="photo" width="24%" height="40%">`);
                        insideFrame.contentDocument.write(`<a style="position: fixed; left: 5%; top: 20%">NUMBER: ${data.number}</a>`);
                    } else {
                        alert("We can't find you");
                    }
                };
            });
            insideFrame.contentDocument.open();
            htmlCode += '<script>let deleteAcc = function() {\n' +
                '        let promise = new Promise(function (resolve, reject) {\n' +
                '                        let xmlRequest = new XMLHttpRequest();\n' +
                '                        xmlRequest.open("DELETE", \'commandsAllocator/deleteUser/<?php if (isset($_COOKIE["token"])) echo $_COOKIE["token"];?>\');\n' +
                '                        xmlRequest.send();\n' +
                '                        xmlRequest.onload = function() {\n' +
                '                            if (this.status === 200){\n' +
                '                                window.location.href = \'deletedAccount.html\';\n' +
                '                            } else {\n' +
                '                                alert(\'Ops, something wrong.\');\n' +
                '                            }\n' +
                '                        };\n' +
                '                    });};<';
            htmlCode += '/script>';
            htmlCode += '<script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"><';
            htmlCode += '/script>';
            htmlCode += '<a onclick="deleteAcc()" style="position: fixed; right: 1px; bottom: 0">delete your account</a>';
            insideFrame.contentDocument.write(htmlCode);
            let n_promise = new Promise(function (resolve, reject) {
                let xmlRequest = new XMLHttpRequest();
                xmlRequest.open("GET", 'commandsAllocator/getNotes/<?php if(isset($_COOKIE["token"])) echo $_COOKIE["token"];?>');
                xmlRequest.send();
                xmlRequest.onload = function() {
                    if (this.status === 200) {
                        let note = JSON.parse(xmlRequest.responseText);
                        htmlCode = `<table class="table table-hover" style="position: absolute; top: 20%; left: 50%; width: 30%"><tbody>`;
                        for (let i = 0; i < note.length; i++) {
                            htmlCode += `<tr><td>${note[i].title}</td><td>${note[i].text}</td><td>${note[i].data}</td><td><button class="btn btn-secondary">delete</button></td></tr>`;
                        }
                        htmlCode += '</tbody></table>';
                        insideFrame.contentDocument.write(htmlCode);
                    } else {
                        insideFrame.contentDocument.write(`<h4 style="position: fixed; top: 40%; left: 50%;">Your notes will be located here</h4>`);
                    }
                };
            });
        };
        let exit = function() {
            let promise = new Promise(function (resolve, reject) {
                        let xmlRequest = new XMLHttpRequest();
                        xmlRequest.open("DELETE", 'commandsAllocator/deleteToken/<?php if(isset($_COOKIE["token"])) echo $_COOKIE["token"];?>');
                        xmlRequest.send();
                        xmlRequest.onload = function() {
                            if (this.status === 200){
                                window.location.href = 'authPage';
                            } else {
                                alert('You have already leave that site.');
                            }
                        };
                    });
                    promise.then(() => console.log('ok'), () => alert('bad'));
        };
        let findUsers = function() {
            let promise = new Promise(function (resolve, reject) {
                let xmlRequest = new XMLHttpRequest();
                xmlRequest.open("GET", 'commandsAllocator/getUsers/<?php if(isset($_COOKIE["token"])) echo $_COOKIE["token"];?>');
                xmlRequest.send();
                xmlRequest.onload = function() {
                    let insideFrame = document.getElementById("frame");
                    insideFrame.contentDocument.open();
                    if (this.status === 200) {
                        let tableData = JSON.parse(xmlRequest.responseText);
                        let tableHtml = `<link href="https://stackpath.bootstrapcdn.com/bootswatch/4.4.1/superhero/bootstrap.min.css" rel="stylesheet" integrity="sha384-R/oa7KS0iDoHwdh4Gyl3/fU7pgvSCt7oyuQ79pkw+e+bMWD9dzJJa+Zqd+XJS0AD" crossorigin="anonymous">`
                        tableHtml += '<table class="table table-hover"><tbody>';
                        for (let i = 0; i < tableData.user.length; i++) {
                            tableHtml += `<tr><td width="10%" style="margin: auto"><img src=${tableData.photo[i]} alt="photo" width="35%" height="35%"></td><td><h3>${tableData.user[i]}</h3></td><td style="position: absolute; right: 0"><p style="margin-top: 1%">${tableData.number[i]}</p></td></tr>`;
                        }
                        tableHtml += '</tbody></table>';
                        insideFrame.contentDocument.write(tableHtml);
                    } else {
                        alert("We can't find users");
                    }
                };
                setTimeout(() => resolve(), 1000);
            });
            promise.then(() => promise, () => alert('bad'));
        };
        let getNote = function() {
            let promise = new Promise(function (resolve, reject) {
                let xmlRequest = new XMLHttpRequest();
                xmlRequest.open("GET", 'commandsAllocator/getNote/<?php if(isset($_COOKIE["token"])) echo $_COOKIE["token"];?>/' + category + '/' + document.getElementById("search").value);
                xmlRequest.send();
                xmlRequest.onload = function() {
                    if (this.status === 200) {
                        let note = JSON.parse(xmlRequest.responseText).text;
                        alert(note);
                    } else {
                        alert("We can't find note");
                    }
                };
            });
        };
        let sendMsg = function () {
            let frame = document.getElementById("frame").contentDocument;
            let user = frame.getElementById("user").value;
            let text = frame.getElementById("exampleTextarea").value;
            let promise = new Promise(function (resolve, reject) {
                let xmlRequest = new XMLHttpRequest();
                xmlRequest.open("POST", 'commandsAllocator/createMessage');
                xmlRequest.send(JSON.stringify({
                    "token": "<?php if(isset($_COOKIE["token"])) echo $_COOKIE["token"];?>",
                    "user" : user,
                    "text" : text
                }));
                xmlRequest.onload = function() {
                    if (this.status != 200) {
                        alert("We cannot find user called " + user);
                    }
                };
            });
            frame.getElementById("user").value = "";
            frame.getElementById("exampleTextarea").value = "";
            promise.then(() => console.log('ok'), () => alert('bad'));
        };
        let messages = function() {
            let insideFrame = document.getElementById("frame");
            let tableHtml = `<link href="https://stackpath.bootstrapcdn.com/bootswatch/4.4.1/superhero/bootstrap.min.css" rel="stylesheet" integrity="sha384-R/oa7KS0iDoHwdh4Gyl3/fU7pgvSCt7oyuQ79pkw+e+bMWD9dzJJa+Zqd+XJS0AD" crossorigin="anonymous">`;
            insideFrame.contentDocument.open();
            tableHtml += '<table class="table table-hover"><tbody id="table">';
            tableHtml += '</tbody></table>' +
                '<h3 style="position: fixed; top: 40%; left: 45%; margin-left: -40px" id="nomsg"></h3>';
            tableHtml += `<div style="position: fixed; bottom: 0; left: 45%; margin-left: -200px; width: 500px">
                                     <input class="form-control mr-sm-2" id="user" type="text" placeholder="User (click on the message to delete it)">
                                     <div class="form-group">
                                        <textarea class="form-control" id="exampleTextarea" rows="3" style="height: 79px;" placeholder="Text (only incoming messages are stored here)"></textarea>
                                     </div>
                                  </div>`;
            insideFrame.contentDocument.write(tableHtml);
            intervalId = setInterval(function(){
            let promise = new Promise(function (resolve, reject) {
                let table = "";
                let xmlRequest = new XMLHttpRequest();
                xmlRequest.open("GET", 'commandsAllocator/getMessages/<?php if(isset($_COOKIE["token"])) echo $_COOKIE["token"];?>');
                xmlRequest.send();
                xmlRequest.onload = function() {
                    if (this.status === 200) {
                        let messages = JSON.parse(xmlRequest.responseText);
                        for (let i = 0; i < messages.length; i++) {
                            table += `<tr><td>${messages[i].talker}</td><td>${messages[i].msgs}</td></tr>`;
                        }
                        insideFrame.contentDocument.getElementById("table").innerHTML = table;
                        insideFrame.contentDocument.getElementById("nomsg").innerHTML = "";
                    } else {
                        insideFrame.contentDocument.getElementById("nomsg").innerHTML = 'No messages';
                    }
                };
            });}, 1000);
            insideFrame.contentDocument.addEventListener('keyup', function (event) {
                if (event.key === "Enter") {
                    sendMsg();
                }
            });
        };
    </script>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <a class="navbar-brand" onclick="clearInterval(intervalId); mainAccount()">THE DIARY</a>
        <button class="navbar-toggler" data-toggle="collapse" data-target="#menu">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="menu">
            <ul class="navbar-nav mr-auto">
                <li class="nav-items"><a onclick="clearInterval(intervalId); newNote()" class="nav-link">New deal</a></li>
            </ul>
            <div class="form-inline my-2 my-lg-0" style="width: 80%">
                <div class="btn-group">
                    <button type="button" class="btn btn-secondary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
                        Category
                    </button>
                    <div class="dropdown-menu">
                        <a class="dropdown-item" onclick="category = 'title'">Title</a>
                        <a class="dropdown-item" onclick="category = 'data'">Data</a>
                    </div>
                </div>
                <input style="width: 80%" class="form-control mr-sm-2" type="text" placeholder="Find your notes_" id="search" aria-label="Search">
                <button onclick="getNote()" class="btn btn-outline-info" type="submit">Search</button>
            </div>
            <button class="btn btn-secondary" style="margin-left: 1%" onclick="clearInterval(intervalId); exit();">exit</button>
        </div>
    </nav>
    <div style="position: absolute; height: 100%; width: 3%; background: #39536a">
        <button class="submenuButtons" onclick="messages();"> <img style="width: 100%" alt="messages" src="message.png" style="background-size: cover;"/></button>
        <button class="submenuButtons" onclick="clearInterval(intervalId); findUsers();"> <img style="width: 100%" alt="messages" src="search.png" style="background-size: cover;"/></button>
        <a href="help.php?user=<?php if(isset($_GET['user'])) echo $_GET['user'];?>" onclick="clearInterval(intervalId);" style="position: fixed; color: #e0e0e1; bottom: 5px; left: -5px;" class="btn btn-link">help</a>
    </div>
    <iframe id="frame" style="border: none; position: absolute; width: 97%; margin-left: 3%; height: 100%"></iframe>
    <script>setTimeout(mainAccount, 100)</script>
    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
  </body>
</html>