<?php

namespace app\home\model;

use think\Model;

class SearchKeywords extends Model
{
    /**
     * 插入数据
     * @param 数据集合 $data
     * @return boolean
     */
    public function createSearchKeywords($data){
        //开启事务
        $this->startTrans();
        try{
            
            if($this->allowField(true)->save($data)){
                $this->commit();
                return true;
            }else{
                // 回滚事务
                $this->rollback();
                return false;
            }
            
        } catch (\Exception $e) {
            // 回滚事务
            $this->rollback();
            return false;
        }
    }
    
    /**
     * 获取最近搜索的关键字
     * @param 会员编号 $uid
     * @param 会员类型  $login 1，显示项目  2，显示机构
     * @return 最近搜索关键字
     */
    public function getByUid($uid,$login){
        $result = [];
        
        if($login==1){
            $type    = "1,3";
        }else if($login==2){
            $type  = "1,4";
        }
        $info    = $this->where('uid = '.$uid.' and type in ('.$type.')')
                        ->order(" add_time desc ")
                        ->find();
        
                        
        if(!empty($info)){
            if($login==1){
                $models              = model("Project");
                $result['pro-data']  = $models->getList(1,2, $info['keyword'], $key_sign='', $key_industry='', $key_size='', $key_area='');
                $result['org-data']  = ['data'   => []];
            }else if($login==2){
                $models              = model("Organize");
                $result['org-data']  = $models->getList(1,2, $info['keyword'], $key_sign='', $key_industry='', $key_size='', $key_area='');
                $result['pro-data']  = ['data'   => []];
            } 
        }else{
            $result['pro-data']      = ['data'   => []];
            $result['org-data']      = ['data'   => []];
        }
                      
        return $result;
    }
   
}
