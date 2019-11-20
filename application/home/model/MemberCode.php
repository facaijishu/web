<?php
namespace app\home\model;

use think\Model;

class MemberCode extends Model
{
    /**
     * 插入会员获取验证码code表
     * @param 数据集合 $data
     * @return boolean
     */
    public function createCode($data){
        
        //手机号码是否请求过验证码
        $re = $this->getMemberCode($data['phone']);
        //首次生成验证码
        if (empty($re)) {
            //开启事务
            $this->startTrans();
            try{
                $arr                = [];
                $arr['phone']       = $data['phone'];
                $arr['reg_code']    = $data['reg_code'];
                $arr['reg_time']    = time();
                $this->allowField(true)->save($arr);
                // 提交事务
                $this->commit();
                return true;
            } catch (\Exception $e) {
                // 回滚事务
                $this->rollback();
                return false;
            }
        }else{
            $now = time();
            $this->startTrans();
            try {
                $this->where(['phone'=>$re['phone']])->update(['reg_code'=>$data['reg_code'],'reg_time'=>$now]);
                $this->commit();
                return true;
            } catch (\Exception $e) {
                $this->rollback();
                return false;
            }
        }
    }
    
    /**
     * 依据客户编号UID查询code表的数据
     * @param unknown $uid
     * @return boolean|unknown
     */
    public function getMemberCode($phone)
    {
        if(empty($phone)){
            $this->error = '数据缺失';
            return false;
        }
        $re  = $this->where(['phone'=>$phone])->find();
        
        return $re;
    }
    
    /**
     * 验证会员的验证码是否一致
     * @param 会员编号 $uid
     * @param 要验证的验证码Code $code
     */
    public function isValidCode($phone,$code)
    {
        $res = [];
        $codeList = $this->getMemberCode($phone);
        if (empty($codeList)) {
            $res['code'] = 201;
            $res['msg'] = '手机验证码获取有误，请重新点击获取！';
        }else{
            $timea = $codeList['reg_time']+15*60;
            $now   = time();
            if($now<=$timea){
                if($codeList['reg_code']==$code){
                    $res['code'] = 200;
                    $res['msg'] = '手机验证码验证成功！';
                }else{
                    $res['code'] = 201;
                    $res['msg'] = '手机验证码输入有误，请重新输入！';
                } 
            }else{
                $res['code'] = 201;
                $res['msg'] = '手机验证码超时，请重新点击获取！';
            }
        }
        return $res;  
    }
    
}