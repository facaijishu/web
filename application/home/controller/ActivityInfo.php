<?php
namespace app\home\controller;

class ActivityInfo extends Base
{
    public function index(){
        $id = $this->request->param('id');
        $RequestAddress = [
            'controller' => $this->Request['controller'] , 
            'action'     => $this->Request['action'] , 
            'id'         => $id
                ];
        session('RequestAddress' , $RequestAddress);
        $model  = model("Activity");
        $info   = $model->getActivityInfo($id);
        $this->assign('info' , $info);
        
        $model  = model("ActivityComment");
        $list   = $model->getActivityCommentListByAcID($id);
        $this->assign('list' , $list);
        
        $user   = session('FINANCE_USER');
        if(in_array($id, explode(',', $user['collection_activity']))){
            $this->assign('sign' , 'true');
        } else {
            $this->assign('sign' , 'false');
        }
        
        $modelT = model("through");
        $sult   = $modelT->where("uid = ".$user['uid'])->find();
        $this->assign('sult' , $sult);
        
        $this->assign('title' , '- '.$info['act_name']);
        $this->assign('img' , $info['top_img_url']);
        $this->assign('des' , $info['introduce']);
        return view();
    }
}
