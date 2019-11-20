<?php

namespace app\home\model;

use think\Model;

class WhiteUid extends Model{
    
    /**
     * 判断是否为白名单库，
     * @param 用户编号 $uid
     * @return 若存在则返回false
     */
    public function getWhite($uid){
        if(empty($uid)){
            $this->error = 'ID不存在';
            return true;
        }
        $re  = $this->where(['uid'=>$uid])->find();
        if($re['uid']>0){
            return false;
        }else{
            return true;
        }
          
    }
}