<?php
require_once("config.php");
date_default_timezone_set("Asia/Shanghai");
$req = substr($_SERVER['REQUEST_URI'], (strlen($_SERVER['PHP_SELF']) - 9));
$params = explode('/', $req);
header('Content-type:application/json');
switch ($params[0])
{
    case 'user':
        $user = new User();
        switch ($params[1])
        {
            case 'login':
                $result = $user->login($_POST['username'], $_POST['password']);
                if($result === "Failed")
                {
                    PackResult(301, "Username does not match the password", null);
                }
                else
                {
                    PackResult(200, "Success", $result);
                }
                break;

            case 'signup':
                $result = $user->signup($_POST['username'], $_POST['password']);
                if($result === "username_exist")
                {
                    PackResult(302, "Username has existed", null);
                }
                else
                {
                    PackResult(200, "Success", $result);
                }
                break;

            case 'check':
                $auth = new Auth();
                $result = $auth->checkToken($_POST['uid'], $_POST['token']);
                if($result === true)
                {
                    PackResult(200, "Success", null);
                }
                else
                {
                    PackResult(403, $result, null);
                }
                break;
            
            default:
                PackResult(404, "API Not Found", null);
                break;
        }
        break;

    case 'battle':
        $auth = new Auth();
        $battle = new battle();
        $result = $auth->checkToken($_POST['uid'], $_POST['token']);
        if($result !== true)
        {
            if($result == "Invalid Token")
            {
                PackResult(403, "Invalid Token", null);
            }
            elseif($result == "Token Expire")
            {
                PackResult(405, "Token Expire", null);
            }
            exit();
        }
        switch ($params[1])
        {
            case 'create':
                $photo = new Photo();
                $emotion = new Emotion();
                $photoid = $photo->upload($_POST['uid']);
                $score = $emotion->getEmotionScore(PHOTOS_URL.$photoid);
                $result = $battle->createBattle($_POST['type'], $_POST['uid'], $photoid, $score);
                PackResult(200, "Success", $result);
                break;

            case 'join':
                $photo = new Photo();
                $emotion = new Emotion();
                $photoid = $photo->upload($_POST['uid']);                
                $score = $emotion->getEmotionScore(PHOTOS_URL.$photoid);
                $result = $battle->joinBattle($_POST['bid'], $_POST['uid'], $photoid, $score);
                if($result == "Battle not exists")
                {
                    PackResult(311, $result, null);
                }
                elseif($result == "Battle had finished")
                {
                    PackResult(312, $result, null);
                }
                elseif($result == "Can not battle with yourself")
                {
                    PackResult(313, $result, null);
                }
                else
                {
                    PackResult(200, "Success", $result);
                }
                break;

            // 获取全站当前可以挑战的 battle
            case 'available':
                $result = $battle->getAvailableBattle();
                if($result == null)
                {
                    PackResult(201, "Success", $result);
                }
                else
                {
                    PackResult(200, "Success", array_reverse($result));
                }
                break;

            // 获取全站的历史 battle
            case 'finished':
                $result = $battle->getFinishedBattle($_POST['uid']);
                if($result == null)
                {
                    PackResult(201, "Success", $result);
                }
                else
                {
                    PackResult(200, "Success", array_reverse($result));
                }
                break;

            //获取我的历史 battle
            case 'mine':
                $result = $battle->getMyBattle($_POST['uid']);
                if($result == null)
                {
                    PackResult(201, "Success", $result);
                }
                else
                {
                    PackResult(200, "Success", array_reverse($result));
                }
                break;

            case 'detail':
                $result = $battle->getBattleInfo($_POST['bid']);
                if($result == null)
                {
                    PackResult(314, "Battle not found", null);
                }
                else
                {
                    PackResult(200, "Success", $result);
                }
                break;
            
            default:
                PackResult(404, "API Not Found", null);
                break;
        }
        break;

    case 'photo':
        PackResult(-1, "301 Under Construction", null);
        break;

    case 'time':
        echo time();
        break;
    
    default:
        PackResult(404, "API Not Found", null);
        break;
}