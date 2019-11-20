<?php

namespace app\home\controller;
use think\Controller;

class Base extends Controller
{
    //用户信息
    protected $jsGlobal = array();
    
    public function __construct(){
        parent::__construct();
        $this->getinfo();
        $this->getAddress();
    }
    
    /**
     * 获取缓存里的会员信息
     */
    public function getinfo(){
        if(empty(session("member"))){
            if(!empty(cookie("uname")) && !empty(cookie("pwd"))){
                $model = model("Member");
                $res   = $model->normalLogin(cookie("uname"),cookie("pwd"));
                if($res['code']==200){
                    //放入缓存
                    session("member",$res['data']);
                }
            }
        }
    }
    
    public function getAddress(){
        $this->assign('root_path' , $_SERVER['SERVER_NAME']);
    }
}