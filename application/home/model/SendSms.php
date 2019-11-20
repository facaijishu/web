<?php

namespace app\home\model;

use think\Model;

class SendSms extends Model{
    
    
    public function addSms($mobile,$content,$status=0,$error=null){
        $data['mobile']      = $mobile;
        $data['content']     = $content;
        $data['status']      = $status;
        if($status==2){
            $data['error_code']      = $error;
        }
        $res =  $this->createSms($data);
        return $res;
    }
    
    public function createSms($data){
        if(empty($data)){
            $this->error = '数据为空，无法插入表';
            return false;
        }
        //验证数据信息
        $validate   = validate('SendSms');
        $scene      = 'add';
        if(!$validate->scene($scene)->check($data)){
            $error  = $validate->getError();
            $this->error = $error;
            return false;
        }

        //开启事务
        $this->startTrans();
        try{
            $arr                = [];
            $arr['mobile']      = $data['mobile'];
            $arr['content']     = $data['content'];
            $arr['create_time'] = time();
            $arr['status']      = $data['status'];
            if($data['status']==2){
                $arr['error_code']  = $data['error_code'] ;
            }
            $this->allowField(true)->save($arr);
            $id = $this->getLastInsID();
            
            // 提交事务
            $this->commit();
            return $id;
        } catch (\Exception $e) {
            // 回滚事务
            $this->rollback();
            return false;
        }
    }
    
    
    /**
     * 更新短信发送状态
     * @param 发送短信记录的ID $id
     * @param 发送状态  $status 0：等待发送  1：发送提交成功 2：发送提交失败
     * @param 错误编码  $errorCode
     * @return boolean
     */
    public function setStatus($id,$status,$errorCode){
        $this->startTrans();
        try {
            $this->where(['id'=>$id])->update(['status'=>$status],['error_code'=>$error_code]);
            $this->commit();
            return true;
        } catch (\Exception $e) {
            $this->rollback();
            return false;
        }  
    }
}