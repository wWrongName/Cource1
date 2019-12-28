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
        exit;
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
        let exit = function(){
            let promise = new Promise(function (resolve, reject) {
                        let xmlRequest = new XMLHttpRequest();
                        xmlRequest.open("DELETE", 'commandsAllocator/deleteToken/<?php if(isset($_COOKIE["token"])) echo $_COOKIE["token"];?>');
                        xmlRequest.send();
                        xmlRequest.onload = function() {
                            if (this.status === 200){
                                window.location.href = 'authPage';
                            } else {
                                alert('Ops, something wrong.');
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
                            tableHtml += `<tr><td width="10%"><img src=${tableData.photo[i]} alt="photo" width="35%" height="35%"></td><td><h3 style="margin-top: 1%;">${tableData.user[i]}</h3></td><td>${tableData.number[i]}</td><td><button class="btn btn-secondary" style="width: 30%; margin-top: 3%; margin-left: 50%;">visit</button></td></tr>`;
                        }
                        tableHtml += '</tbody></table>';
                        insideFrame.contentDocument.write(tableHtml);
                    } else {
                        alert('Oops, something wrong.');
                    }
                };
                setTimeout(() => resolve(), 1000);
            });
            promise.then(() => promise, () => alert('bad'));
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
            });
            frame.getElementById("user").value = "";
            frame.getElementById("exampleTextarea").value = "";
            promise.then(() => console.log('ok'), () => alert('bad'));
        };
        let messages = function() {
            let promise = new Promise(function (resolve, reject) {
                let xmlRequest = new XMLHttpRequest();
                xmlRequest.open("GET", 'commandsAllocator/getMessages/<?php if(isset($_COOKIE["token"])) echo $_COOKIE["token"];?>');
                xmlRequest.send();
                xmlRequest.onload = function() {
                    let insideFrame = document.getElementById("frame");
                    let tableHtml = `<link href="https://stackpath.bootstrapcdn.com/bootswatch/4.4.1/superhero/bootstrap.min.css" rel="stylesheet" integrity="sha384-R/oa7KS0iDoHwdh4Gyl3/fU7pgvSCt7oyuQ79pkw+e+bMWD9dzJJa+Zqd+XJS0AD" crossorigin="anonymous">`
                    insideFrame.contentDocument.open();
                    if (this.status === 200) {
                        let messages = JSON.parse(xmlRequest.responseText);
                        tableHtml += '<table class="table table-hover"><tbody>';
                        for (let i = 0; i < messages.length; i++) {
                            tableHtml += `<tr><td>${messages[i].talker}</td><td><button onclick="readMsgs(${messages[i].msgs});" class="btn btn-secondary" style="width: 30%; margin-top: 3%; margin-left: 50%;">read</button></td></tr>`;
                        }
                        tableHtml += '</tbody></table>';
                    } else {
                        tableHtml += `<h3 style="position: fixed; top: 40%; left: 45%; margin-left: -40px">No messages</h3>`;
                    }
                    tableHtml += `<div style="position: fixed; bottom: 0; left: 45%; margin-left: -200px; width: 500px">
                                     <input class="form-control mr-sm-2" id="user" type="text" placeholder="User">
                                     <div class="form-group">
                                        <textarea class="form-control" id="exampleTextarea" rows="3" style="height: 79px;" placeholder="Text"></textarea>
                                     </div>
                                  </div>`;
                    insideFrame.contentDocument.write(tableHtml);
                    insideFrame.contentDocument.addEventListener('keyup', function (event) {
                        if (event.key === "Enter") {
                            sendMsg();
                        }
                    });
                }
            });
            promise.then(() => console.log('ok'), () => alert('bad'));
        };
    </script>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <a href="#" class="navbar-brand">THE DIARY</a>
        <button class="navbar-toggler" data-toggle="collapse" data-target="#menu">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="menu">
            <ul class="navbar-nav mr-auto">
                <li class="nav-items"><a href="#" class="nav-link">New deal</a></li>
            </ul>
            <form action="#" class="form-inline my-2 my-lg-0" style="width: 80%">
                <div class="btn-group">
                    <button type="button" class="btn btn-secondary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
                        Category
                    </button>
                    <div class="dropdown-menu">
                        <a class="dropdown-item" href="#">Title</a>
                        <a class="dropdown-item" href="#">Day</a>
                        <a class="dropdown-item" href="#">Month</a>
                        <a class="dropdown-item" href="#">Year</a>
                    </div>
                </div>
                <input style="width: 80%" class="form-control mr-sm-2" type="search" placeholder="Find your notes_" aria-label="Search">
                <button class="btn btn-outline-info" type="submit">Search</button>
            </form>
            <button class="btn btn-secondary" style="margin-left: 1%" onclick="exit();">exit</button>
        </div>
    </nav>
    <div style="position: fixed; height: 100%; width: 45px; background: #39536a">
        <button class="submenuButtons" onclick="messages();"> <img style="width: 100%" alt="messages" src="message.png" style="background-size: cover;"/></button>
        <button class="submenuButtons" onclick="findUsers();"> <img style="width: 100%" alt="messages" src="search.png" style="background-size: cover;"/></button>
        <a href="help.html" style="position: fixed; color: #e0e0e1; bottom: 5px; left: -5px;" class="btn btn-link">help</a>
    </div>
    <iframe id="frame" style="border:none; position: fixed; left:45px; width: 100%; height:94%"></iframe>
    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
  </body>
</html>