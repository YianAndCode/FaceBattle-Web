<?php
date_default_timezone_set("Asia/Shanghai");
/*require_once("../config.php");
require_once(APP_ROOT."/lib/medoo.php");
require_once(APP_ROOT."/lib/PasswordHash.php");*/
/**
* 
*/
class User
{
    private $database;
    private $hasher;
    
    function __construct()
    {
        $this->database = new medoo();
        $this->hasher = new PasswordHash(8, false);
    }

    public function login($username, $password)
    {
        $user = $this->database->get(DB_PREFIX.'users', '*', ['username' => $username]);
        if($user == null)
        {
            return "Failed";
        }
        if($this->hasher->CheckPassword($password, $user['password']))
        {
            $AuthCode = $this->generateAuthCode($user['uid'], $password);
            return ['uid' => $user['uid'], 'username' => $user['username'], 'authcode' => $AuthCode];
        }
        return "Failed";
    }

    public function signup($username, $password)
    {
        if($this->database->has(DB_PREFIX.'users', ['username' => $username]))
        {
            return "username_exist";
        }
        $pwd = $this->hasher->HashPassword($password);
        $uid = $this->database->insert(DB_PREFIX.'users', ['username' => $username, 'password' => $pwd]);
        $AuthCode = $this->generateAuthCode($uid, $password);
        return ['uid' => $uid,'username' => $username, 'authcode' => $AuthCode];
    }

    private function generateAuthCode($uid, $password)
    {
        $AuthCode = substr(md5($uid + $password + time()), 8, 8);
        $this->database->update(DB_PREFIX.'users', ['authcode' => $AuthCode], ['uid' => $uid]);
        return $AuthCode;
    }
}