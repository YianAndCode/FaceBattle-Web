<?php
date_default_timezone_set("Asia/Shanghai");
/**
* 
*/
class Battle
{
    private $database;
    function __construct()
    {
        $this->database = new medoo();
    }

    public function createBattle($type, $uid, $pid, $score)
    {
        $bid = $this->database->insert(DB_PREFIX.'battle_list', ['type' => $type, 'uid' => $uid, 'status' => 0]);
        $battle = ['bid' => $bid, 'uid' => $uid, 'pid' => $pid, 'time' => date('Y-m-d H:i:s')];
        $this->database->insert(DB_PREFIX.'battles', $battle);
        $this->database->update(DB_PREFIX.'pics', ['score' => $score], ['pid' => $pid]);
        return $battle;
    }

    public function joinBattle($bid, $uid, $pid, $score)
    {
        $battle = $this->getBattleInfo($bid);
        if($battle == null)
        {
            // 不存在的 battle
            return "Battle not exists";
        }
        if($battle['status'] == 1)
        {
            // battle 已经被完成了
            return "Battle had finished";
        }
        if($battle['stater']['uid'] == $uid)
        {
            // 不能自己和自己 battle
            return "Can not battle with yourself";
        }
        $joinBattle = ['bid' => $bid, 'uid' => $uid, 'pid' => $pid, 'time' => date('Y-m-d H:i:s')];
        $this->database->insert(DB_PREFIX.'battles', $joinBattle);
        $this->database->update(DB_PREFIX.'battle_list', ['status' => 1], ['bid' => $bid]);
        $this->database->update(DB_PREFIX.'pics', ['score' => $score], ['pid' => $pid]);
        return $joinBattle;
    }

    public function getAvailableBattle()
    {
        $battles = $this->database->select(DB_PREFIX.'battle_list', '*', ['status' => 0]);
        if($battles == null)
        {
            return null;
        }
        foreach ($battles as $key => $battle)
        {
            $stater = $this->database->get(DB_PREFIX.'battles', '*', ['AND' => ['bid' => $battle['bid'], 'uid' => $battle['uid']]]);
            $battles[$key]['stater'] = $stater;
            $battles[$key]['stater']['username'] = $this->database->get(DB_PREFIX.'users', 'username', ['uid' => $battles[$key]['stater']['uid']]);
        }
        return $battles;
    }

    public function getFinishedBattle($uid)
    {
        /* 获取自己发起的 */
        $battles = $this->database->select(DB_PREFIX.'battle_list', '*', ['AND' => ['status' => 1, 'uid' => $uid] ]);
        /* 获取参加的 */
        $joinBattleList = $this->database->select(DB_PREFIX.'battles', 'bid', ['uid' => $uid]);
        foreach ($joinBattleList as $key => $joinBattle)
        {
            $temp_battles[] = $this->database->get(DB_PREFIX.'battle_list', '*', ['AND' => ['status' => 1, 'bid' => $joinBattle] ]);
        }
        if(is_array($temp_battles))
        {
            $battles = array_merge($battles, $temp_battles);
        }

        if($battles == null)
        {
            return null;
        }
        foreach ($battles as $battles_key => $battle)
        {
            $participators = $this->database->select(DB_PREFIX.'battles', '*', ['bid' => $battle['bid']]);
            foreach ($participators as $key => $participator)
            {
                if($participator['uid'] == $battle['uid'])
                {
                    $battles[$battles_key]['stater'] = $participator;
                    $battles[$battles_key]['stater']['username'] = $this->database->get(DB_PREFIX.'users', 'username', ['uid' => $battle['uid']]);
                }
                else
                {
                    $participator['username'] = $this->database->get(DB_PREFIX.'users', 'username', ['uid' => $participator['uid']]);
                    $battles[$battles_key]['participator'][] = $participator;
                }
            }
        }
        return $battles;
    }

    public function getMyBattle($uid)
    {
        $myBattles = $this->database->select(DB_PREFIX.'battle_list', '*', ['uid' => $uid]);
        $battles = [];
        if($myBattles == null)
        {
            return null;
        }
        foreach ($myBattles as $key => $myBattle)
        {
            $battles[] = $this->getBattleInfo($myBattle['bid']);
        }
        return $battles;
    }

    public function getBattleInfo($bid)
    {
        $battle = $this->database->get(DB_PREFIX.'battle_list', '*', ['bid' => $bid]);
        if($battle == null)
        {
            return null;
        }
        $participators = $this->database->select(DB_PREFIX.'battles', '*', ['bid' => $bid]);
        foreach ($participators as $key => $participator)
        {
            $temp_score = json_decode($this->database->get(DB_PREFIX.'pics', 'score', ['pid' => $participator['pid']]),true);
            $participator['score'] = $temp_score[0]['scores'];
            $participator['username'] = $this->database->get(DB_PREFIX.'users', 'username', ['uid' => $participator['uid']]);
            if($participator['uid'] == $battle['uid'])
            {
                $battle['stater'] = $participator;
            }
            else
            {
                $battle['participator'] = $participator;
            }
        }
        return $battle;
    }
}