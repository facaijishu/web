<?php
namespace app\home\controller;

class Consultation extends Base
{
    public function index()
    {
        $id    = $this->request->param('id/d');
        $model = model("PcConsultation");
        $info  = $model->getPcConsultationInfoById($id);
        $this->assign('info' , $info);
        $this->assign('title' , $info['title']."｜FA財-一站式智能信息投融交互平台");
        return view();
    }
    
    public function lists(){
        $model = model("PcConsultation");
        $list  = $model->getPcConsultationList();
        $this->assign('list' , $list);
        $this->assign('title' , "资讯中心-FA財");
        return view();
    }
    
    public function listApi(){
        $page       = $this->request->param('page')?$this->request->param('page'):'';
        $model      = model("PcConsultation");
        $list       = $model->getList($page);
        $this->result($list,$list['code'],$list['msg'],'json'); 
    }
}
