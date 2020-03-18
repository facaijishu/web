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
        $mem      = session("member");
        $uid      = session("member")['uid'];
        $type     = session("member")['type'];
        
        //检查UID是否登录
        if(empty($mem)){
            $url  = "/home/pub/loading";
            $jump = "/home/member/index";
            header("Location: $url"."?jump=".$jump);
        }else{
            
            $sid     = $this->request->param('sid/d');
            $this->assign('sid' , $sid);
            
            $model  = model("Member");
            $info   = $model->getMemberById($uid);
            if($info['userPhoto'] ==''){
                $info['userPhoto'] = "/static/common/img/member_default.png";
            }
            $this->assign('info' , $info);
            
            $model_p  = model("Project");
            $pro      = $model_p->getListByUid(1,3,$uid,0);
            $this->assign('project' , $pro);
            
            $modelO  = model("Organize");
            $org     = $modelO->getOrganizeInfoByTel($info['userPhone']);
            if($org==false){
                $org['total']= 0;
            }else{
                $org['total']= 1;
                if($org['is_confirm']==1){
                    $org['status_name'] = "已认领";
                    $org['link']        = "/home/organize/index/id/".$org['org_id'];
                }else{
                    $org['status_name'] = "待认领";
                    $org['link']        = "/home/organize/edit/id/".$org['org_id'];
                }
                $model_d            = model("Dict");
                $result             = $model_d->getSecDictList($org['inc_industry']);
                $org['dict_list']   = $result;
                
            }
            $this->assign('org' , $org);
            
            $this->assign('title' , "个人中心-FA財");
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
        $mem      = session("member");
        //检查UID是否登录
        if(empty($mem)){
            $url  = "/home/pub/login";
            $jump = "/home/member/modifyPwd";
            header("Location: $url"."?jump=".$jump);
        }
        $this->assign('title' , "FA財-一站式智能投融信息交互平台");
        return view();
    }
    
    public function doModifyPwd(){
        
        $mem      = session("member");
        //检查UID是否登录
        if(empty($mem)){
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
    
    public function userInfo()
    {
        $mem   = session("member");
        $uid   = session("member")['uid'];
        //检查UID是否登录
        if(empty($mem)){
            $url  = "/home/pub/loading";
            $jump = "/home/member/index";
            header("Location: $url"."?jump=".$jump);
        }else{
            $model  = model("Member");
            $info   = $model->getMemberById($uid);
            if($info['userPhoto'] ==''){
                $info['userPhoto'] = "/static/common/img/member_default.png";
            }
            $this->assign('info' , $info);
            $this->assign('title' , "个人中心-FA財");
            return view();
        }
    }
    
    public function doEdit(){
        
        $mem      = session("member");
        //检查UID是否登录
        if(empty($mem)){
            $url  = "/home/pub/loading";
            $jump = "/home/member/modifyPwd";
            header("Location: $url"."?jump=".$jump);
        }
        
        $data  = input();
        $uid   = session("member")['uid'];
        
        //check原密码是否正确
        $model = model("Member");
        $info  = $model->getMemberById($uid);
        if($info['uid']>0){
            $save   = [];
            $save['realName']       = $data['realName'];
            $save['company']        = $data['company'];
            $save['company_jc']     = $data['company_jc'];
            $save['position']       = $data['position'];
            
            if($info['userPhone']!=$data['userPhone']){
                $model_code  = model("MemberCode");
                //手机验证码
                $isCode = $model_code->isValidCode($data['userPhone'],$data['regcode']);
                if($isCode['code']== 200){
                    $save['userPhone'] = $data['userPhone'];
                    $res = $model->allowField(true)->save($save,['uid' => $uid]);
                    if($res){
                        //更新用户对应的机构的联络手机号码
                        $modelO = model("Organize");
                        $org    = $modelO->getOrganizeInfoByTel();
                        if($org==false){                                                    
                            
                            
                        }else{
                            $org_save = [];
                            $org_save['contact_tel'] = $data['userPhone'];
                            $res = $modelO->allowField(true)->save($org_save,['org_id' => $org_save['org_id']]);
                        }
                        return $this->result('', 200, '信息修改成功', 'json');
                    }else{
                        return $this->result('', 201, '信息修改失败，请确认重新提交', 'json');
                    }
                }else{
                    return $this->result('', 201, '手机验证码输入有误', 'json');
                }
            }else{
                $res = $model->allowField(true)->save($save,['uid' => $uid]);
                if($res){
                    return $this->result('', 200, '信息修改成功', 'json');
                }else{
                    return $this->result('', 201, '信息修改失败，请确认重新提交', 'json');
                }
            }
        }else{
            return $this->result('', 201, '会员信息异常', 'json');
        }
    }
    
    /**
     * 更改头像
     */
    public function upImg(){
        $mem      = session("member");
        //检查UID是否登录
        if(empty($mem)){
            $url  = "/home/pub/loading";
            $jump = "/home/member/userInfo";
            header("Location: $url"."?jump=".$jump);
        }
        
        $data  = input();
        $uid   = session("member")['uid'];
        
        $res = [];
        //上传图片
        $base64   = $data['user_img'];
        if(empty($base64)){
            $res['code'] = "201";
            $res['msg']  = "头像更改失败";
            $this->result('' ,'201', '头像更改失败' , 'json');
        }else{
            $base64   = explode(',',$base64);
            $tmp      = base64_decode($base64[1]);//base64解码
            $path     = ROOT_PATH . 'public' . DS . 'uploads' . DS . 'member'. DS . 'img'. DS;
            if(!is_dir($path)){
                mkdir($path , 0777, true);
            }
            $type     = substr($base64[0],11,strlen($base64[0])-18);
            $fileName = pwd_encrypt("uid_".time().$uid).".".$type;
            file_put_contents($path.$fileName,$tmp);
            $save['userPhoto'] = "http://www.jrfacai.com/uploads/member/img/".$fileName;
            $model = model("Member");
            $model->allowField(true)->save($save,['uid' => $uid]);
            
            $res['code'] = "200";
            $res['msg']  = "";
            $this->result('' ,'200', '', 'json');
        }
        
    }
    
    public function myProject()
    {
        $this->assign('title' , "我的项目-FA財");
        return view();
    }
    
    
    /**
     * 查看我的项目请求
     */
    public function myProjectApi()
    {
        $mem = session("member");
        //检查UID是否登录
        if(empty($mem)){
            $url  = "/home/pub/loading";
            $jump = "/home/member/myProject";
            header("Location: $url"."?jump=".$jump);
        }
        $uid     = session("member")['uid'];
        $page    = $this->request->param('page')?$this->request->param('page'):'1';
        $status  = $this->request->param('status')?$this->request->param('status'):'0';
        
        $modelP  = model("Project");
        $data    = $modelP->getListByUid($page,6,$uid,$status);
        $this->result($data,$data['code'],$data['msg'],'json');
     
    }
    
    /**
     * 查看我的业务请求
     */
    public function myOrganize(){
        $this->assign('title' , "我的业务-FA財");
        return view();
    }
    
    /**
     * 
     */
    public function myOrganizeApi(){
        $mem = session("member");
        //检查UID是否登录
        if(empty($mem)){
            $url  = "/home/pub/loading";
            $jump = "/home/member/myOrganize";
            header("Location: $url"."?jump=".$jump);
        }
        $uid     = session("member")['uid'];
        $model   = model("Member");
        $info    = $model->getMemberById($uid);
        
        $page    = $this->request->param('page')?$this->request->param('page'):'1';
        $status  = $this->request->param('status')?$this->request->param('status'):'0';
   
        $modelO  = model("Organize");
        $data    = $modelO->getListByUid($page,6, $uid,$status);
        $this->result($data,$data['code'],$data['msg'],'json');
    }
}
