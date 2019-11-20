<?php

namespace app\home\controller;
use think\Controller;

class Base extends Controller
{
    //�û���Ϣ
    protected $jsGlobal = array();
    
    public function __construct(){
        parent::__construct();
        $this->getinfo();
        $this->getAddress();
    }
    
    /**
     * ��ȡ������Ļ�Ա��Ϣ
     */
    public function getinfo(){
        if(empty(session("member"))){
            if(!empty(cookie("uname")) && !empty(cookie("pwd"))){
                $model = model("Member");
                $res   = $model->normalLogin(cookie("uname"),cookie("pwd"));
                if($res['code']==200){
                    //���뻺��
                    session("member",$res['data']);
                }
            }
        }
    }
    
    public function getAddress(){
        $this->assign('root_path' , $_SERVER['SERVER_NAME']);
    }
}