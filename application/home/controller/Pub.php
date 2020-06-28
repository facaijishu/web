<?php
namespace app\home\controller;
use think\Controller;

class Pub extends Base
{
    
    function changePhone(){
        $post    = input();
        $mobile  = $post['phone'];
        $partten = '/^(0|86|17951)?(13[0-9]|15[012356789]|17[0-9]|18[0-9]|14[57]|16[0-9])[0-9]{8}$/';
        if(empty($mobile) || preg_match($partten, $mobile) == 0){
            return $this->result('', 201, '手机号码不正确', 'json');
        }
        $model = model("Member");
        $info  = $model->getMemberByPhone($mobile);
        if(!empty($info)){
            return $this->result('', '201','手机号码已经被注册了', 'json');
        }else{
            return $this->result('', '200','可以注册', 'json');
        }
        
    }
    
    /**
     * 会员注册页面
     * @return view
     */
    public function register(){
        
        $superior = (int) $this->request->param('superior/d', 0);
        session('Superior' , $superior);
        
        $roleList   =  array();
        $roleList[] = [
                        "id"   => "1",
                        "name"   => "项目方"
                      ];
        $roleList[] = [
                        "id"   => "2",
                        "name"   => "资金方"
                      ];
        $roleList[] = [
                        "id"   => "3",
                        "name"   => "合伙人"
                      ];
        
        $this->assign('roleList' , $roleList);
        
        $keywords       = config('keywords');
        $description    = config('description');
        $this->assign('keywords' , $keywords);
        $this->assign('description' , $description);
        $this->assign('title' , "FA財-一站式智能投融信息交互平台");
        return view();
    }
    
    /**
     * 会员注册操作
     * @return view
     */
    public function doRegister(){
        $post    = input();
        faLog(json_encode($post));
        
        /*检测验证码  */
        /*if(!captcha_check($post['imgcode'])){
            return $this->result('','201','图形验证码输入错误', 'json');
        }
        */
        $partten = '/^(0|86|17951)?(13[0-9]|15[012356789]|17[0-9]|18[0-9]|14[57]|16[0-9])[0-9]{8}$/';
        if(empty($post['phone']) || preg_match($partten, $post['phone']) == 0){
            return $this->result('', 201, '手机号码不正确', 'json');
        }
        
        $model   = model("Member");
        $info    = $model->getMemberByPhone($post['phone']);
        if(!empty($info)){
            return $this->result('', '201','手机号码已经被注册了', 'json');
        }else{
            //check手机验证码
            $modelCode   = model("MemberCode");
            $resCode     = $modelCode->isValidCode($post['phone'],$post['regcode']);
          
            if($resCode['code']==200){
                $data = [];
                $data['userPhone']      = $post['phone'];
                $data['uidPwd']         = pwd_encrypt($post['password']);
                $data['type']           = $post['roleType'];
                if($post['userName']!==""){
                    $data['realName']       = $post['userName'];
                }
                if($post['company']!==""){
                    $data['company']        = $post['company'];
                }
                if($post['position']!==""){
                    $data['position']       = $post['position'];
                }
                if($post['userName']!=="" && $post['company']!=="" && $post['position']!==""){
                    $data['userType'] = 2;
                }else{
                    $data['userType'] = 1;
                }
                $model   = model("Member");
                $res     = $model->add($data);
                $info    = $model->getMemberById($res);
                session("member",$info);
                return $this->result(200, $resCode['code'],'', 'json');
            }else{
                return $this->result('', $resCode['code'],$resCode['msg'], 'json');
            }
        }
    }
    
    /**
     * 会员登录页面
     */
    public function login(){
        $jump = session("jump_url");
        //$info   = session("member");
        //faLog("NEW----".json_encode($info));
        $this->assign('jump' , $jump);
        
        $keywords       = config('keywords');
        $description    = config('description');
        $this->assign('keywords' , $keywords);
        $this->assign('description' , $description);
        $this->assign('title' , "FA財-一站式智能投融信息交互平台");
        return view();
    }
    
    /**
     * 验证码登录操作
     */
    public function dologin(){
        $data  = input();
        /*检测验证码  */
        if(!captcha_check($data['imgcode'])){
            return $this->result('','201','图形验证码输入错误', 'json');
        }
        $model = model("Member");
        $res   = $model->codeLogin($data);
        if($res['code']==200){
            session("member",$res['data']);
        }
        return $this->result('',$res['code'],$res['msg'], 'json');
    }
    
    /**
     * 密码登录操作
     */
    public function dologinpsw(){
        $data  = input();
        $model = model("Member");
        $res   = $model->normalLogin($data['phone_psw'],$data['password']);
        if($res['code']==200){
            session("member",$res['data']);
            if($data['rember']==1){
                cookie("uname",$data['phone_psw']);
                cookie("pwd", $data['password']);
            }     
        }
        return $this->result('',$res['code'],$res['msg'], 'json');
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
        if(!captcha_check($data['imgcode'])){
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
            //if(1==1){
            if($array['Code'] == "OK"){
                //插入短信发送表
                $text    = "您的验证码".$number."，该验证码5分钟内有效，请勿泄漏于他人！";
                $model   = model("SendSms");
                $smsId   = $model->addSms($mobile,$text,1);
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

    /**
     * 退出登录
     * @return unknown
     */
    public function logout(){
        //会员缓存清理
        session(null);
        $url = "/";
        header("Location: $url");
    }
    
    /**
     * 检查会员是否登录的跳转页面
     * @return unknown
     */
    public function loading(){
        $jump = $this->request->param('jump')?$this->request->param('jump'):'';
        session("jump_url",$jump);
        
        $keywords       = config('keywords');
        $description    = config('description');
        $this->assign('keywords' , $keywords);
        $this->assign('description' , $description);
        $this->assign('title' , "FA財-一站式智能投融信息交互平台");
        return view();
    }
    
    public function forgetPwd(){
        
        $keywords       = config('keywords');
        $description    = config('description');
        $this->assign('keywords' , $keywords);
        $this->assign('description' , $description);
        $this->assign('title' , "FA財-一站式智能投融信息交互平台");
        return view();
    }
    
    public function doForgetPwd(){
        
        $data  = input();
        //check手机号是否为会员
        $model = model("Member");
        $info  = $model->getMemberByPhone($data['phone']);
        if(empty($info)){
            return $this->result('', '201', '该手机号还没有注册', 'json');
        }else{
            //检测图形验证码
            if(!captcha_check($data['imgcode'])){
                return $this->result('','201','图形验证码输入错误', 'json');
            }else{
                //check手机验证码
                $modelc  = model("MemberCode");
                $isCode  = $modelc->isValidCode($data['phone'],$data['regcode']);
                if($isCode['code']== 200){
                    $updata = array();
                    $updata['uidPwd']     = pwd_encrypt($data['password']);
                    $model = model("Member");
                    $model->where(['uid' => $info['uid']])->update($updata);
                    return $this->result('', 200, '新密码设置成功', 'json');
                }else{
                    return $this->result('', $isCode['code'], $isCode['msg'], 'json');
                }
            }
        }
    }
    
    /**
     * 获取手机验证码(忘记密码)
     * @return unknown
     */
    public function getNumber(){
        
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
            $tem_id = "SMS_174809993";
            $array  = $sms->sendSms($mobile,$number,$tem_id);
            $array  = json_decode(json_encode($array) , true);
            //if(1==1){
            if($array['Code'] == "OK"){
                //插入短信发送表
                $text    = "您正在重置您的FA财密码，验证码是：".$number."，该验证码5分钟内有效，请勿泄漏于他人！";
                $model   = model("SendSms");
                $smsId   = $model->addSms($mobile,$text,1);
                return $this->result('', 200, '发送成功', 'json');
            }else{
                //插入短信发送表
                $text    = "您正在重置您的FA财密码，验证码是：".$number."，该验证码5分钟内有效，请勿泄漏于他人！";
                $model   = model("SendSms");
                $smsId   = $model->addSms($mobile,$text,2,$array['Message']);
                return $this->result('', 201, "验证码短信异常，请稍后再试", 'json');
            }
        }else{
            return $this->result('', 201, '验证码获取失败，请重新获取', 'json');
        }
    }
    
    
    public function  sendSmsApi(){
        
        $sms    = new \common\Sms();
        $tem_id = "SMS_185842599";
        $array  = $sms->sendSms($mobile,$number,$tem_id);
        $array  = json_decode(json_encode($array) , true);
        //if(1==1){
        if($array['Code'] == "OK"){
            //插入短信发送表
            $text    = "您正在重置您的FA财密码，验证码是：".$number."，您的信息已发布，请登陆FA財浏览项目资料。！";
            $model   = model("SendSms");
            $smsId   = $model->addSms($mobile,$text,1);
            return $this->result('', 200, '发送成功', 'json');
        }else{
            //插入短信发送表
            $text    = "您正在重置您的FA财密码，验证码是：".$number."，该验证码5分钟内有效，请勿泄漏于他人！";
            $model   = model("SendSms");
            $smsId   = $model->addSms($mobile,$text,2,$array['Message']);
            return $this->result('', 201, "验证码短信异常，请稍后再试", 'json');
        }
        
    }
    
    
    
}
