<?php

namespace app\home\model;

use think\Model;


class Member extends Model
{
    
    /**
     * 获取会员信息
     * @param 会员编号 $uid
     * @return 会员信息集合
     */
    public function getMemberById($uid){
        $result = $this->where(['uid' => $uid])->find();
        return $result;
    }
    
    /**
     * 获取会员信息
     * @param 手机号码 $userPhone
     * @return 会员信息集合
     */
    public function getMemberByPhone($userPhone){
        $result = $this->where(['userPhone' => $userPhone])->find();
        return $result;
    }
    
    
    /**
     * 注册新会员
     * @param unknown $data
     */
    public function add($data){
        
        /*
        $data['userName']        = '';
        $data['userSex']         = '';
        $data['realName']        = '';
        $data['company']         = '';
        $data['company_jc']      = '';
        $data['position']        = '';
        $data['weixin']          = '';
        $data['email']           = '';
        $data['company_address'] = '';
        $data['website']         = '';
        $data['userPhone']       = '';
        $data['qr_code']         = '';
        $data['userPhoto']       = '';
        $data['openId']          = '';
        */
        $data['superior']        = session('Superior');
        session('Superior' , 0);
        
        $data['service_num']     = 0;
        $data['require_num']     = 0;
        $data['createTime']      = time();
        $data['CREATE_TIME']     = date('Y-m-d H:i:s' , time());
        $data['lastTime']        = time();
        $data['lastIP']          = get_client_ip();
        $data['status']          = 1;
        $result = $this->allowField(true)->save($data);
        
        return $result;
    }
    
    /**
     * 用户登录验证（手机+验证码）
     * @param 参数集 $post
     * @return number[]|string[]
     */
    public function codeLogin($post){
        $res   = [];
        $user  = $this->getMemberByPhone($post['phone']);
        if(!empty($user)){
            if($user['pullback']==1){
                $res['code'] = 203;
                $res['msg']  = "登录异常";
            }else{
                $model  = model("MemberCode");
                //手机验证码
                $isCode = $model->isValidCode($post['phone'],$post['regcode']);
                if($isCode['code']== 200){
                    $data = array();
                    $data['lastTime']   = time();
                    $data['lastIP']     = get_client_ip();
                    $this->where(['uid' => $user['uid']])->update($data);
                    
                    $info['uid']       = $user['uid'];
                    $info['realName']  = $user['realName'];
                    $info['company']   = $user['company'];
                    $info['position']  = $user['position'];
                    $info['userPhone'] = $user['userPhone'];
                    $info['type']      = $user['type'];
                    
                    $res['data'] = $info;
                    $res['code'] = 200;
                    $res['msg']  = "登录成功";
                }else{
                    $res['code'] = $isCode['code'];
                    $res['msg']  = $isCode['msg'];
                }
            }
        }else{
            $res['code'] = 201;
            $res['msg']  = "手机号不存在，请先去注册";
        }
        return $res;
    }

    /**
     * 用户登录验证（手机+密码）
     * @param 手机号 $phone
     * @param 密码     $password
     * @return number[]|string[]
     */
    public function normalLogin($phone,$password){
	    $res   = [];
	    $user  = $this->getMemberByPhone($phone);
	    if(!empty($user)){
	        if($user['pullback']==1){
	            $res['code'] = 203;
	            $res['msg']  = "登录异常";
	        }else{
	            if($user['uidPwd']==''){
	                $res['code'] = 201;
	                $res['msg']  = "初始密码还没有设置，请用手机验证码登录";
	            }else{
	                //密码一致
	                if(pwd_encrypt($password)==$user['uidPwd']){
	                    $data = array();
	                    $data['lastTime']   = time();
	                    $data['lastIP']     = get_client_ip();
	                    $this->where(['uid' => $user['uid']])->update($data);
	                    
	                    $info['uid']        = $user['uid'];
	                    $info['realName']   = $user['realName'];
	                    $info['company']    = $user['company'];
	                    $info['position']   = $user['position'];
	                    $info['userPhone']  = $user['userPhone'];
	                    $info['type']       = $user['type'];
	                    
	                    $res['data'] = $info;
	                    $res['code'] = 200;
	                    $res['msg']  = "登录成功";
	                }else{
	                    $res['code'] = 201;
	                    $res['msg']  = "密码输入有误";
	                }
	            }
	        }
	    }else{
	        $res['code'] = 201;
	        $res['msg']  = "手机号不存在，请先去注册";
	    }
	    
	    return $res;
	}
	
	/**
	 * 检查新密码
	 * @param 会员编号 $uid
	 * @param 新密码 $newPwd
	 * 
	 */
	public function checkPassword($uid,$newPwd){
	    $res = [];
	    if($newPwd==""){
	        $res['code'] = 201;
	        $res['msg']  = "新密码输入为空";
	    }else{
	        $uer = $this->getMemberById($uid);
	        if(!empty($uer)){
	            if(pwd_encrypt($newPwd)==$user['uidPwd']){
	                $res['code'] = 201;
	                $res['msg']  = "新密码与旧密码一样";
	            }else{
	                $res['code'] = 200;
	                $res['msg']  = "可以修改";
	            }
	        }else{
	            $res['code'] = 203;
	            $res['msg']  = "会员异常，请登录";
	        }
	    }
	    return $res;
	}
	
	/**
	 * 修改密码
	 * @param 会员编号 $uid
	 * @param 新密码 $newPwd
	 * 
	 */
	public function modifyPassword($uid,$newPwd){
	    $res = [];
	    if($newPwd==""){
	        $res['code'] = 201;
	        $res['msg']  = "新密码输入为空";
	    }else{
	        $uer = $this->getMemberById($uid);
	        if(!empty($uer)){
	            if(pwd_encrypt($newPwd)==$user['uidPwd']){
	                $res['code'] = 201;
	                $res['msg']  = "新密码与旧密码一样";
	            }else{
	                $data = array();
	                $data['uidPwd']     = pwd_encrypt($newPwd);
	                $this->where(['uid' => $user['uid']])->update($data);
	                
	                $res['code'] = 200;
	                $res['msg']  = "密码修改成功";
	            }
	        }else{
	            $res['code'] = 203;
	            $res['msg']  = "会员异常，请登录";
	        }
	    }
	    
	    return $res;
	}
	
	/**
	 * 忘记密码
	 * @param 提交数据基 $post
	 * @return number[]|string[]|unknown[]
	 */
	public function forgetPassword($post){
	    
	    $res   = [];
	    $user  = $this->getMemberByPhone($post['userPhone']);
	    if(!empty($user)){
	        if($user['pullback']==1){
	            $res['code'] = 203;
	            $res['msg']  = "黑名单用户";
	        }else{
	            
	            $model = model("MemberCode");
	            //手机验证码
	            $isCode = $model->isValidCode($user['uid'],$post['reg_code']);
	            if($isCode['code']== 200){
	                $data = array();
	                $data['lastTime']   = time();
	                $data['lastIP']     = get_client_ip();
	                $this->where(['uid' => $user['uid']])->update($data);
	                
	                //放入缓存
	                $this->jsGlobal['member']       = $user;
	                
	                $res['code'] = 200;
	                $res['msg']  = "登录成功";
	            }else{
	                $res['code'] = $isCode['code'];
	                $res['msg']  = $isCode['msg'];
	            }
	        }
	    }else{
	        $res['code'] = 201;
	        $res['msg']  = "手机号不存在，请先去注册";
	    }
	    return $res;
	}
    
  
}