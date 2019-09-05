<?php
namespace app\home\controller;

class Consultation extends Base
{
    public function index()
    {
        $id = $this->request->param('id/d');
        $model = model("PcConsultation");
        $info = $model->getPcConsultationInfoById($id);
        $this->assign('info' , $info);
        return view();
    }
    public function lists(){
        $model = model("PcConsultation");
        $list = $model->getPcConsultationList();
        $this->assign('list' , $list);
        return view();
    }
}
