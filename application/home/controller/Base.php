<?php

namespace app\home\controller;
use think\Controller;

class Base extends Controller
{
    public function __construct(){
        parent::__construct();
        $this->getAddress();
    }
    public function getAddress(){
        $this->assign('root_path' , $_SERVER['SERVER_NAME']);
    }
}