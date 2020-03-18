<?php
namespace app\home\controller;

class Organize extends Base
{
    public function index()
    {
        $id       = $this->request->param('id/d');
        $mem_info = session("member");
        //检查UID是否登录
        if(empty($mem_info)){
            $url  = "/home/pub/loading";
            $jump = "/home/organize/index/id/".$id;
            header("Location: $url"."?jump=".$jump);
        }
        $uid   = session("member")['uid'];
        $user  = model("member")->getMemberById($uid);
        
        $model = model("Organize");
        $info  = $model->getOrganizeInfoById($id);
       
        if($user['userPhone']!=$info['contact_tel']){
            //检查机构是否可在平台展示
            if($info['status'] !=2){
                $url  = "/home/project/loading";
                header("Location: $url");
            }
            
            //检查机构是否可在平台展示
            if($info['flag'] !=1){
                $url  = "/home/organize/loading";
                $jump = "/home/organize/index/id/".$id;
                header("Location: $url");
            }
        }
        
        //查看人数+1
        $white  = model("whiteUid");
        if($white->getWhite($uid)){
            $model->where(['org_id'=>$id])->setInc('view_num');
        }
        $this->assign('info' , $info);
        $this->assign('title' , $info['org_short_name']."｜FA財-一站式智能信息投融交互平台");
        
        return view();
    }
    
    
    /**
     * 检查机构是否可平台显示的跳转页面
     * @return unknown
     */
    public function loading(){
        $this->assign('title' , "FA財-一站式智能投融信息交互平台");
        return view();
    }
    
    
    public function add()
    {
        $mem_info = session("member");
        //检查UID是否登录
        if(empty($mem_info)){
            $url  = "/home/pub/loading";
            $jump = "/home/organize/add";
            header("Location: $url"."?jump=".$jump);
        }
        
        $uid      = session("member")['uid'];
        $model_m  = model("Member");
        $mem      = $model_m->getMemberById($uid);
        $this->assign('mem', $mem);
        
        $model_d  = model("Dict");
        $industry = $model_d->getIndustry();
        $stage    = $model_d->getStage();
        $area     = $model_d->getArea();
        $type     = $model_d->getType();
        
        $id       = $this->request->param('id/d');
        $model    = model("Organize");
        $info     = $model->getOrganizeInfoById($id);
        $this->assign('info' , $info);
        
        foreach ($industry as $key => $item) {
            foreach ($item['sub'] as $key => $it) {
                $count = substr_count($info['inc_industry'],$it['id']);
                if($count==1){
                    $it['checked'] = 1;
                } else{
                    $it['checked'] = 0;
                }
            }
            
        }
        
        foreach ($type as $key => $item) {
            $count = substr_count($info['inc_type'],$item['id']);
            if($count==1){
                $item['checked'] = 1;
            } else{
                $item['checked'] = 0;
            }
        }
        
        foreach ($stage as $key => $item) {
            $count = substr_count($info['invest_stage'],$item['id']);
            if($count==1){
                $item['checked'] = 1;
            } else{
                $item['checked'] = 0;
            }
        }
        
        foreach ($area as $key => $item) {
            $count = substr_count($info['inc_area'],$item['id']);
            if($count==1){
                $item['checked'] = 1;
            } else{
                $item['checked'] = 0;
            }
        }
        
        
        $this->assign('industryList' , $industry);
        $this->assign('stageList' , $stage);
        $this->assign('areaList' , $area);
        $this->assign('typeList' , $type);
        $this->assign('title' , "上传机构｜FA財-一站式智能信息投融交互平台");
        return view();
    }
    
    
    public function edit(){
        $id       = $this->request->param('id/d');
        
        $mem_info = session("member");
        //检查UID是否登录
        if(empty($mem_info)){
            $url  = "/home/pub/loading";
            $jump   = "/home/organize/edit/id/".$id;
            header("Location: $url"."?jump=".$jump);
        }
        
        $model    = model("Organize");
        $info     = $model->getOrganizeInfoById($id);
        
        if($info['contact_tel'] !== $mem_info['userPhone']){
            $url   = "/home/organize/index/id/".$id;
            header("Location: $url");
        }else{
            if($info['is_confirm']==1){
                $url   = "/home/organize/index/id/".$id;
                header("Location: $url");
            }
        }
        
        $model_d  = model("Dict");
        $industry = $model_d->getIndustry();
        $stage    = $model_d->getStage();
        $area     = $model_d->getArea();
        $type     = $model_d->getType();
        foreach ($industry as $key => $item) {
            foreach ($item['sub'] as $key => $it) {
                $count = substr_count($info['inc_industry'],$it['id']);
                if($count==1){
                    $it['checked'] = 1;
                } else{
                    $it['checked'] = 0;
                }
            }
            
        }
        
        foreach ($type as $key => $item) {
            $count = substr_count($info['inc_type'],$item['id']);
            if($count==1){
                $item['checked'] = 1;
            } else{
                $item['checked'] = 0;
            }
        }
        
        foreach ($stage as $key => $item) {
            $count = substr_count($info['invest_stage'],$item['id']);
            if($count==1){
                $item['checked'] = 1;
            } else{
                $item['checked'] = 0;
            }
        }
        
        foreach ($area as $key => $item) {
            $count = substr_count($info['inc_area'],$item['id']);
            if($count==1){
                $item['checked'] = 1;
            } else{
                $item['checked'] = 0;
            }
        }
        
        
        $this->assign('industryList' , $industry);
        $this->assign('stageList' , $stage);
        $this->assign('areaList' , $area);
        $this->assign('typeList' , $type);
        $this->assign('info' , $info);
        
        
        $this->assign('title' , $info['org_name']."｜FA財-一站式智能信息投融交互平台");
        return view();
    } 
    
    /**
     * 机构添加
     * @return unknown
     */
    public function doAdd(){
        $data       = [];
        $post       = input();
        $mem_info = session("member");
        //检查UID是否登录
        if(empty($mem_info)){
            $jump   = "/home/organize/edit/id/".$post['id'];
            header("Location: $url"."?jump=".$jump);
        }
        
        $uid = session("member")['uid'];
        
        $data['org_name']                   = $post['org_name'];
        $data['org_short_name']             = $post['org_short_name'];
        $data['invest_stage']               = $post['stage'];
        $data['inc_target']                 = $post['inc_target'];
        $data['inc_industry']               = $post['industry'];
        $data['inc_area']                   = $post['area'];
        $data['invest_area']                = $post['area'];
        $data['contacts']                   = $post['contacts'];
        $data['contact_tel']                = $post['contact_tel'];
        $data['position']                   = $post['position'];
        $data['inc_type']                   = $post['type'];
        $data['create_uid']                 = $uid;
        $data['id']                         = $post['id'];
        
        $org    = model("Organize");
        $new    = $org->add($data);
        
        if($new==!false){
            $ddd    = model("OrganizeDict");
            $aaa    = $ddd->add($new,$post['industry']);
            $this->result('' ,'200', '上传成功' , 'json');
        }else{
            $this->result('' ,'201', '上传失败' , 'json');
        }
    }
    
    public function doEdit(){
        $data       = [];
        $post       = input();
        //检查UID是否登录
        $mem_info = session("member");
        //检查UID是否登录
        if(empty($mem_info)){
            $jump   = "/home/organize/edit/id/".$post['id'];
            header("Location: $url"."?jump=".$jump);
        }
        $uid = session("member")['uid'];
        $mem = model("Member")->getMemberById($uid);
        if($post['contact_tel']==$mem['userPhone']){
            $data['org_name']               = $post['org_name'];
            $data['org_short_name']         = $post['org_short_name'];
            $data['invest_stage']           = $post['stage'];
            $data['inc_target']             = $post['inc_target'];
            $data['inc_industry']           = $post['industry'];
            $data['inc_area']               = $post['area'];
            $data['invest_area']            = $post['area'];
            $data['contacts']               = $post['contacts'];
            $data['position']               = $post['position'];
            $data['inc_type']               = $post['type'];
            $data['id']                     = $post['id'];
            if($post['check']==1){
                $data['status']             = 2;
                $data['flag']               = 1;
                $data['is_confirm']         = 1;
            }
            $org    = model("Organize");
            $info   = $org->getOrganizeInfoById($post['id']);
            $edit   = $org->edit($data);
            if($edit==!false){
                if($info['inc_industry']!=$post['industry']){
                    $res = model("OrganizeDict")->add($post['id'],$post['industry']);
                }
                $this->result('' ,'200', '编辑成功' , 'json');
            }else{
                $this->result('' ,'201', '编辑失败' , 'json');
            }
        }else{
            $this->result('' ,'201', '手机号码的修改请去个人中心操作' , 'json');
        }
    }
}
