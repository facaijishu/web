<?php
namespace app\home\controller;

class Index extends Base
{
    public function index()
    {
        $model = model("PcConsultation");
        $list = $model->getPcConsultationList(4);
        $this->assign('list' , $list);
        return view();
    }
}
