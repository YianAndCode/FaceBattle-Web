<?php
date_default_timezone_set("Asia/Shanghai");
/**
* 
*/
class Photo
{
    private $database;

    function __construct()
    {
        $this->database = new medoo();
    }

    function upload($uid)
    {
        $pid = md5($_FILES['photo']['tmp_name'] . $uid . time());
        move_uploaded_file($_FILES['photo']['tmp_name'], APP_ROOT.'/photos/'.$pid);
        $this->database->insert(DB_PREFIX.'pics', ['pid' => $pid, 'uid' => $uid]);
        return $pid;
    }
}