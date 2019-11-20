<?php
namespace app\home\controller;
use think\Controller;

class Member extends Base
{
    
    /**
     * 我的页面
     * @return unknown
     */
    public function index()
    {
        //检查UID是否登录
        if(empty(session("member"))){
            $url  = "/home/pub/loading";
            $jump = "/home/member/index";
            header("Location: $url"."?jump=".$jump);
        }else{
            $model  = model("Member");
            $info   = $model->getMemberById(session("member")['uid']);
            if($info['userPhoto'] ==''){
                $info['userPhoto'] = "/static/common/img/member_default.png";
            }
            $this->assign('info' , $info);
            $this->assign('title' , "FA財-一站式智能投融信息交互平台");
            return view();
        }
    }
    
    /**
     * 获取手机验证码
     * @return unknown
     */
    public function getNewNumber(){
        $data    = input();
        $mobile  = $data['phone'];
        $partten = '/^(0|86|17951)?(13[0-9]|15[012356789]|17[0-9]|18[0-9]|14[57]|16[0-9])[0-9]{8}$/';
        if(empty($mobile) || preg_match($partten, $mobile) == 0){
            return $this->result('', 201, '手机号码不正确', 'json');
        }
        
        /*检测验证码  */
        if(!captcha_checkSms($data['imgcode'])){
            return $this->result('','201','图形验证码输入错误', 'json');
        }
        $model = model("Member");
        $info  = $model->getMemberByPhone($data['phone']);
        //注册
        if($data['type']==1){
            if(!empty($info)){
                return $this->result('', 201, '该手机号码已经注册', 'json');
            }
        }
        
        //登录
        if($data['type']==2){
            if(empty($info)){
                return $this->result('', 201, '该手机号码还没有注册', 'json');
            }
        }
        $number = rand(100000, 999999);
        //验证码插入数据库验证表中
        $modelM             = model("MemberCode");
        $dsms['phone']      = $data['phone'];
        $dsms['reg_code']   = $number;
        $res = $modelM->createCode($dsms);
        if($res){
            $sms    = new \common\Sms();
            $tem_id = "SMS_173190063";
            $array  = $sms->sendSms($mobile,$number,$tem_id);
            $array  = json_decode(json_encode($array) , true);
            if($array['Code'] == "OK"){
                //插入短信发送表
                $text    = "您的验证码".$number."，该验证码5分钟内有效，请勿泄漏于他人！";
                $model   = model("SendSms");
                $smsId   = $model->addSms($mobile,$text,1,'');
                return $this->result('', 200, '发送成功', 'json');
            }else{
                //插入短信发送表
                $text    = "您的验证码".$number."，该验证码5分钟内有效，请勿泄漏于他人！";
                $model   = model("SendSms");
                $smsId   = $model->addSms($mobile,$text,2,$array['Message']);
                return $this->result('', 201, "验证码短信异常，请稍后再试", 'json');
            }
        }else{
            return $this->result('', 201, '验证码获取失败，请重新获取', 'json');
        }
    }
    
    
    
    public function modifyPwd(){
        //检查UID是否登录
        if(empty(session("member"))){
            $url  = "/home/pub/login";
            $jump = "/home/member/modifyPwd";
            header("Location: $url"."?jump=".$jump);
        }
        $this->assign('title' , "FA財-一站式智能投融信息交互平台");
        return view();
    }
    
    public function doModifyPwd(){
        
        //检查UID是否登录
        if(empty(session("member"))){
            $url  = "/home/pub/loading";
            $jump = "/home/member/modifyPwd";
            header("Location: $url"."?jump=".$jump);
        }
        
        $data  = input();
        $uid   = session("member")['uid'];
 
        //check原密码是否正确
        $model = model("Member");
        $info  = $model->getMemberById($uid);
        if(pwd_encrypt($data['oldpassword'])==$info['uidPwd']){
            if($data['newpassword']!==$data['newpassword']){
                return $this->result('', 201, '新密码两次输入不一致', 'json');
            }else{
                $data = array();
                $data['uidPwd']     = pwd_encrypt($data['newpassword']);
                $this->where(['uid' => $uid])->update($data);
                return $this->result('', 200, '密码修改成功', 'json');
            }
        }else{
            return $this->result('', 201, '原密码不正确', 'json');
        }
    }
}
