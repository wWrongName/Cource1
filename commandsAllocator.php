<?php
class User {
    public $user;
    public $pass;
    public $salt;
    public $token;
    public $photo;
    public $number;
    public $code;
    public function __construct($usr, $psw, $slt, $num) {
        $this->user = $usr;
        $this->pass = $psw;
        $this->salt = $slt;
        $this->token = array();
        $this->number = $num;
        $this->photo = "avatar.jpg";
        $this->code = "";
    }
}
class Message {
    public $listener;
    public $talkers;
    public function __construct($l, $t, $msg) {
        $this->listener = $l;
        $this->talkers = [array(
            "talker" => $t,
            "msgs" => ['('.$msg.')']
        )];
    }
}
class Note {
    public $user;
    public $deals;
    public function __construct($usr, $t, $txt, $d) {
        $this->user = $usr;
        $this->deals =[array(
            "title" => $t,
            "text" => $txt,
            "data" => $d
        )];
    }
}

function checkNote ($deal, $category, $data) {
    if ($category == 'title') {
        if ($deal->title == $data)
            return true;
        else
            return false;
    } else if ($category == 'data') {
        if ($deal->data == $data)
            return true;
        else
            return false;
    } else
        return false;
}
function findNote ($notes, $user) {
    for ($i = 0; $i < count($notes); $i++) {
        if ($notes[$i]->user == $user)
            return $i;
    }
    return -1;
}
function checkUserByName($users, $wanted) {
    for ($i = 0; $i < count($users); $i++) {
        if ($users[$i]->user == $wanted)
            return true;
    }
    return false;
}
function findTalker($talkers, $talker) {
    for ($i = 0; $i < count($talkers); $i++) {
        if ($talkers[$i]->talker == $talker)
            return $i;
    }
    return -1;
}
function findMessage($messages, $user) {
    for ($i = 0; $i < count($messages); $i++) {
        if ($messages[$i]->listener == $user)
            return $i;
    }
    return -1;
}
function findToken($user, $token) {
    for ($j = 0; $j < count($user->token); $j++)
        if ($user->token[$j] == $token)
            return $j;
    return -1;
}
function userExists($userName, $userList) {
    for ($i = 0; $i < count($userList); $i++)
        if ($userList[$i]->user == $userName)
            return $i;
    return -1;
}
function createToken($tokens, $newToken) {
    array_push($tokens, $newToken);
    setcookie("token", $newToken, time()+3600, "/");
    return $tokens;
}
function checkPass($user, $pass) {
    $psw = hash('md5', $pass.$user->salt);
        if ($psw == $user->pass)
            return 1;
    return 0;
}
function giveUsers($users) {
    $outJson = array(
        "user" => [],
        "photo" => [],
        "number" => []
    );
    for ($i = 0; $i < count($users); $i++) {
        array_push($outJson["user"], $users[$i]->user);
        array_push($outJson["photo"], $users[$i]->photo);
        array_push($outJson["number"], $users[$i]->number);
    }
    header('Content-Type: application/json');
    print(json_encode($outJson));
}

    $uriOpNameLocation = 4;
    if (isset($_SERVER['REQUEST_URI']))
        $vars = explode('/', $_SERVER['REQUEST_URI']);
    $structList = json_decode(file_get_contents('structures.json'));
    $reqData = json_decode(file_get_contents("php://input"));

    if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == 'DELETE') {
        if (isset($vars[$uriOpNameLocation])) {
            if ($vars[$uriOpNameLocation] == "deleteToken") {
                $deletedToken = $vars[$uriOpNameLocation + 1];
                $deleted = false;
                for ($i = 0; $i < count($structList->users); $i++) {
                    $j = findToken($structList->users[$i], $deletedToken);
                    if ($j != -1) {
                        array_splice($structList->users[$i]->token, $j, 1);
                        if (isset($_COOKIE["token"]))
                            setcookie("token", "", time() - 3600);
                        http_response_code(200);
                        $deleted = true;
                    }
                }
                if (!$deleted)
                    http_response_code(403);
            } else if ($vars[$uriOpNameLocation] == "deleteUser") {
                $userPlace = -1;
                for ($i = 0; $i < count($structList->users); $i++) {
                    $tokenPlace = findToken($structList->users[$i], $vars[$uriOpNameLocation+1]);
                    if ($tokenPlace != -1)
                        $userPlace = $i;
                }
                if ($userPlace != -1) {
                    $j = findMessage($structList->messages, $structList->users[$userPlace]->user);
                    $i = findNote($structList->notes, $structList->users[$userPlace]->user);
                    array_splice($structList->users, $userPlace, 1);
                    array_splice($structList->notes, $i, 1);
                    array_splice($structList->messages, $j, 1);
                    http_response_code(200);
                } else {
                    http_response_code(403);
                }
            } else if ($vars[$uriOpNameLocation] == "deleteNote") {
                $userPlace = -1;
                for ($i = 0; $i < count($structList->users); $i++) {
                    $tokenPlace = findToken($structList->users[$i], $vars[$uriOpNameLocation+1]);
                    if ($tokenPlace != -1)
                        $userPlace = $i;
                }
                if ($userPlace != -1) {
                    $j = findNote($structList->notes, $structList->users[$userPlace]->user);
                    if ($j != -1) {
                        $flag = false;
                        for ($i = 0; $i < count($structList->notes[$j]->deals); $i++) {
                            if ($structList->notes[$j]->deals[$i] == $vars[$uriOpNameLocation+2]) {
                                array_splice($structList->notes[$j]->deals, $i, 1);
                                $flag = true;
                                http_response_code(200);
                            }
                        }
                        if (!$flag)
                            http_response_code(403);
                    } else {
                        http_response_code(403);
                    }
                } else {
                    http_response_code(403);
                }
            } else if ($vars[$uriOpNameLocation] == "deleteMessage") {
                $userPlace = -1;
                for ($i = 0; $i < count($structList->users); $i++) {
                    $tokenPlace = findToken($structList->users[$i], $vars[$uriOpNameLocation+1]);
                    if ($tokenPlace != -1)
                        $userPlace = $i;
                }
                if ($userPlace != -1) {
                    $j = findMessage($structList->messages, $structList->users[$userPlace]->name);
                    if ($j != -1) {
                        $i = findTalker($structList->messages[$i]->talkers, $vars[$uriOpNameLocation+2]);
                        if ($i != -1) {
                            array_splice($structList->messages[$j]->talkers, $i, 1);
                            http_response_code(200);
                        } else {
                            http_response_code(403);
                        }
                    } else {
                        http_response_code(403);
                    }
                } else {
                    http_response_code(403);
                }
            } else {
                http_response_code(400);
            }
        } else {
            http_response_code(400);
        }
    } else if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == 'POST') {
        if (isset($vars[$uriOpNameLocation])) {
            if ($vars[$uriOpNameLocation] == "getToken") {
                $userLocation = userExists($reqData->user, $structList->users);
                if ($userLocation + 1) {
                    if(checkPass($structList->users[$userLocation], $reqData->pass)) {
                        $tkn = bin2hex(random_bytes(32));
                        $structList->users[$userLocation]->token = createToken($structList->users[$userLocation]->token, $tkn);
                        header('Content-Type: application/json');
                        print('{"token" : "'.$tkn.'"}');
                    } else
                        http_response_code(403);
                } else {
                    http_response_code(401);
                }
            } else if ($vars[$uriOpNameLocation] == "createUser") {
                $userLocation = userExists($reqData->user, $structList->users);
                if ($userLocation + 1) {
                    http_response_code(501);
                } else {
                    $salt = bin2hex(random_bytes(8));
                    $password = hash('md5', $reqData->pass.$salt);
                    array_push($structList->users, new User($reqData->user, $password, $salt, $reqData->number));
                    http_response_code(200);
                }
            } else if ($vars[$uriOpNameLocation] == "createNote") {
                $userPlace = -1;
                for ($i = 0; $i < count($structList->users); $i++) {
                    $tokenPlace = findToken($structList->users[$i], $reqData->token);
                    if ($tokenPlace != -1)
                        $userPlace = $i;
                }
                if ($userPlace != -1) {
                    $j = findNote($structList->notes, $structList->users[$userPlace]->user);
                    if ($j != -1) {
                        $newDeal = array(
                            "title" => $reqData->title,
                            "text" => $reqData->text,
                            "data" => date("d.m.y")
                        );
                        array_push($structList->notes[$j]->deals, $newDeal);
                    } else {
                        array_push($structList->notes, new Note($structList->users[$userPlace]->user, $reqData->title, $reqData->text, date("d.m.y")));
                    }
                    http_response_code(200);
                } else {
                    http_response_code(403);
                }
            } else if ($vars[$uriOpNameLocation] == "createMessage") {
                $userPlace = -1;
                for ($i = 0; $i < count($structList->users); $i++) {
                    $tokenPlace = findToken($structList->users[$i], $reqData->token);
                    if ($tokenPlace != -1)
                        $userPlace = $i;
                }
                if ($userPlace != -1) {
                    if (checkUserByName($structList->users, $reqData->user)) {
                        $j = findMessage($structList->messages, $reqData->user);
                        if ($j != -1) {
                            $i = findTalker($structList->messages[$j]->talkers, $structList->users[$userPlace]->user);
                            if ($i != -1) {
                                array_push($structList->messages[$j]->talkers[$i]->msgs, '('.$reqData->text.')');
                                http_response_code(200);
                            } else {
                                $talker = array(
                                    "talker" => $structList->users[$userPlace]->user,
                                    "msgs" => ['('.$reqData->text.')']
                                );
                                array_push($structList->messages[$j]->talkers, $talker);
                                http_response_code(200);
                            }
                        } else {
                            array_push($structList->messages, new Message($reqData->user, $structList->users[$userPlace]->user, $reqData->text));
                            http_response_code(200);
                        }
                    } else {
                        http_response_code(403);
                    }
                } else {
                    http_response_code(403);
                }
            } else {
                http_response_code(400);
            }
        } else {
            http_response_code(400);
        }
    } else if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == 'PUT') {
        if (isset($vars[$uriOpNameLocation])) {
            if ($vars[$uriOpNameLocation] == "resetPassword") {
                $j = -1;
                if (($j = userExists($reqData->user, $structList->users))+1) {
                    $structList->users[$j]->code = rand(1000, 9999);
                    if ($reqData->way == "sms") {
                        $phone = $structList->users[$j]->number;
                        echo $phone;
                        echo $structList->users[$j]->code;
                        $req = curl_init();
                        curl_setopt_array($req, [
                                CURLOPT_URL => 'https://smsc.ru/sys/send.php?login=1amJ0hn&psw=cfhfq2005&phones='.$phone.'&mes='.$structList->users[$j]->code,
                                CURLOPT_POST => true,
                                CURLOPT_RETURNTRANSFER => true
                            ]
                        );
                        curl_setopt($req, CURLOPT_SSL_VERIFYPEER, false);
                        curl_setopt($req, CURLOPT_SSL_VERIFYHOST, false);
                        curl_exec($req);
                    } elseif ($reqData->way == "telegram") {
                        $id = -1;
                        $teleList = json_decode(file_get_contents('users.json'));
                        for ($i = 0; $i < count($teleList); $i++)
                            if ($teleList[$i]->username == $structList->users[$j]->user) {
                                $id = $teleList[$i]->id;
                            }
                        if ($id != -1) {
                            $req = curl_init();
                            curl_setopt_array($req, [
                                    CURLOPT_URL => 'https://api.telegram.org/bot869456428:AAEIGO4PyJgzN9BH98DEDO6ah5ADSmBtN1Y/sendMessage',
                                    CURLOPT_POST => true,
                                    CURLOPT_RETURNTRANSFER => true,
                                    CURLOPT_TIMEOUT => 10,
                                    CURLOPT_POSTFIELDS => array(
                                        'chat_id' => $id,
                                        'text' => "Код сброса: " . $structList->users[$j]->code
                                    ),
                                    CURLOPT_PROXY => '51.158.123.35:8811',
                                    CURLOPT_PROXYTYPE => CURLPROXY_HTTP,
                                    CURLOPT_PROXYAUTH => CURLAUTH_BASIC
                                ]
                            );
                            curl_setopt($req, CURLOPT_SSL_VERIFYPEER, false);
                            curl_setopt($req, CURLOPT_SSL_VERIFYHOST, false);
                            curl_exec($req);
                            curl_close($req);
                            http_response_code(200);
                        } else {
                            http_response_code(403);
                        }
                    } else {
                        http_response_code(403);
                    }
                } else {
                    http_response_code(403);
                }
            } else if ($vars[$uriOpNameLocation] == "editNote") {
                $userPlace = -1;
                for ($i = 0; $i < count($structList->users); $i++) {
                    $tokenPlace = findToken($structList->users[$i], $reqData->token);
                    if ($tokenPlace != -1)
                        $userPlace = $i;
                }
                if ($userPlace != -1) {
                    $j = findNote($structList->notes, $structList->users[$userPlace]->user);
                    if ($j != -1) {
                        $flag = false;
                        for ($i = 0; $i < count($structList->notes[$j]->deals); $i++) {
                            if ($structList->notes[$j]->deals[$i]->title == $reqData->title) {
                                $structList->notes[$j]->deals[$i]->text = $reqData->text;
                                $flag = true;
                            }
                        }
                        if (!$flag)
                            http_response_code(403);
                        else
                            http_response_code(200);
                    } else {
                        http_response_code(403);
                    }
                } else {
                    http_response_code(403);
                }
            } else {
                http_response_code(400);
            }
        } else {
            http_response_code(400);
        }
    } else if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == 'GET') {
        if (isset($vars[$uriOpNameLocation])) {
            if ($vars[$uriOpNameLocation] == "confirmCode") {
                $userPlace = -1;
                $token = $vars[$uriOpNameLocation + 1];
                if(($j = userExists($vars[$uriOpNameLocation + 3], $structList->users)) + 1) {
                    if ($structList->users[$j]->code == $vars[$uriOpNameLocation + 1]) {
                        $structList->users[$j]->pass = hash('md5', $vars[$uriOpNameLocation + 2].$structList->users[$j]->salt);
                        $structList->users[$j]->code = "";
                        http_response_code(200);
                    } else {
                        http_response_code(403);
                    }
                } else {
                    http_response_code(403);
                }
            } else if ($vars[$uriOpNameLocation] == "getUsers") {
                 $inside = false;
                 $token = $vars[$uriOpNameLocation + 1];
                 for ($i = 0; $i < count($structList->users); $i++) {
                     $j = findToken($structList->users[$i], $token);
                     if ($j != -1) {
                         $inside = true;
                     }
                 }
                 if ($inside) {
                    giveUsers($structList->users);
                    http_response_code(200);
                 } else {
                    http_response_code(403);
                 }
            } else if ($vars[$uriOpNameLocation] == "getUser") {
                $inside = false;
                $token = $vars[$uriOpNameLocation + 1];
                for ($i = 0; $i < count($structList->users); $i++) {
                    $j = findToken($structList->users[$i], $token);
                    if ($j != -1) {
                        $inside = true;
                    }
                }
                if ($inside) {
                    $i = userExists($vars[$uriOpNameLocation + 2], $structList->users);
                    if ($i != -1) {
                        print(json_encode($structList->users[$i]));
                        http_response_code(200);
                    }
                } else {
                    http_response_code(403);
                }
            } else if ($vars[$uriOpNameLocation] == "getNote") {
                $userPlace = -1;
                $vars[$uriOpNameLocation+3] = str_replace('%20', ' ', $vars[$uriOpNameLocation+3]);
                $token = $vars[$uriOpNameLocation + 1];
                for ($i = 0; $i < count($structList->users); $i++) {
                    $tokenPlace = findToken($structList->users[$i], $token);
                    if ($tokenPlace != -1)
                        $userPlace = $i;
                }
                if ($userPlace != -1) {
                    $flag = false;
                    $i = findNote($structList->notes, $structList->users[$userPlace]->user);
                    for ($j = 0; $j < count($structList->notes[$i]->deals); $j++) {
                        if (checkNote($structList->notes[$i]->deals[$j], $vars[$uriOpNameLocation+2], $vars[$uriOpNameLocation+3])) {
                            header('Content-Type: application/json');
                            print(json_encode(array("text" => $structList->notes[$i]->deals[$j]->text)));
                            $flag = true;
                            break 1;
                        }
                    }
                    if (!$flag)
                        http_response_code(403);
                    else
                        http_response_code(200);
                } else {
                    http_response_code(403);
                }
            } else if ($vars[$uriOpNameLocation] == "getMessages") {
                $userPlace = -1;
                $token = $vars[$uriOpNameLocation + 1];
                for ($i = 0; $i < count($structList->users); $i++) {
                    $tokenPlace = findToken($structList->users[$i], $token);
                    if ($tokenPlace != -1)
                        $userPlace = $i;
                }
                $i = findMessage($structList->messages, $structList->users[$userPlace]->user);
                if ($i != -1) {
                    $msgs = $structList->messages[$i]->talkers;
                    header('Content-Type: application/json');
                    print(json_encode($msgs));
                } else {
                    http_response_code(403);
                }
            } else if ($vars[$uriOpNameLocation] == "getNotes") {
                $userPlace = -1;
                $token = $vars[$uriOpNameLocation + 1];
                for ($i = 0; $i < count($structList->users); $i++) {
                    $tokenPlace = findToken($structList->users[$i], $token);
                    if ($tokenPlace != -1)
                        $userPlace = $i;
                }
                $i = findNote($structList->notes, $structList->users[$userPlace]->user);
                if ($i != -1) {
                    $notes = $structList->notes[$i]->deals;
                    header('Content-Type: application/json');
                    print(json_encode($notes));
                } else {
                    http_response_code(403);
                }
            } else {
                http_response_code(400);
            }
        } else {
            http_response_code(400);
        }
    }
    file_put_contents('structures.json', json_encode($structList));
?>