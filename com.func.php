<?php
function PackResult($code, $msg, $data)
{
    $result = [
                "code" => $code,
                "msg" => $msg,
                "data" => $data];
    echo json_encode($result, true);
}