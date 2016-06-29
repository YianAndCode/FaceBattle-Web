<?php
date_default_timezone_set("Asia/Shanghai");
/*require_once("../config.php");
require_once(APP_ROOT."/lib/medoo.php");*/
/**
* 
*/
class Auth
{
    private $database;
    function __construct()
    {
        $this->database = new medoo();
    }

    function checkToken($uid, $token)
    {
        if(strlen($token) != 26)
        {
            return "Invalid Token";
        }
        $tokenTime = (int)substr($token, 16);
        $currentTime = time();
        if($currentTime - 10 * 60 > $tokenTime || $currentTime + 30 * 60 < $tokenTime)
        {
            return "Token Expire";
        }
        if($token == $this->generateToken($uid, $tokenTime))
        {
            return true;
        }
        return "Invalid Token";
    }

    function generateToken($uid, $timestamp = null)
    {
        $AuthCode = $this->database->get(DB_PREFIX."users", 'authcode', array("uid" => $uid));
        if(!$AuthCode)
        {
            return false;
        }
        if(!$timestamp)
        {
            $timestamp = time();
        }
        $token = substr(md5($uid . $AuthCode . $timestamp), 8, 16) . $timestamp;
        return $token;
    }
}

// DEBUG
/*$test = new Auth();
var_dump($test->generateToken(1));*/