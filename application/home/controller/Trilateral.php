<?php
namespace app\home\controller;

class Trilateral extends Base
{
    public function index()
    {
        $model = model("Trilateral");
        $list  = $model->getList(4);
        
        $this->assign('list' , $list);
        
        $keywords       = config('keywords_show');
        $description    = config('description_show');
        $this->assign('keywords' , $keywords);
        $this->assign('description' , $description);
        $this->assign('title' , "三方演绎——FA財");
        return view();
    }
    
    public function detail()
    {
        $id    = $this->request->param('id/d');
        
        $model = model("Trilateral");
        $info  = $model->getInfo($id);
        $nlist = $model->getNearList($id,4);
        
        $pro   = model("Project");
        $list  = $pro->getProjectShowDs($info['pro_ids']);
        
        
        
        $this->assign('project' , $list);
        $this->assign('nlist' , $nlist);
        $this->assign('info' , $info);
        
        $keywords       = config('keywords_show');
        $description    = config('description_show');
        $this->assign('keywords' , $keywords);
        $this->assign('description' , $description);
        $this->assign('title' , $info['title']."｜FA財-一站式智能信息投融交互平台");
        return view();
    }
}

