<?php
namespace app\home\controller;

class Business extends Base
{
    public function index()
    {
        $this->assign('title' , "FA財-一站式智能投融信息交互平台");
        return view();
    }
}
