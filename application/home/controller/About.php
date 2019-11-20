<?php
namespace app\home\controller;

class About extends Base
{
    public function index()
    {
        $this->assign('title' , "FA財-一站式智能投融信息交互平台");
        return view();
    }
}
