<?php
namespace app\home\controller;

class Project extends Base
{
    public function index()
    {
        $id       = $this->request->param('id/d');
        $mem_info = session("member");
        //检查UID是否登录
        if(empty($mem_info)){
            $url  = "/home/pub/loading";
            $jump = "/home/project/index/id/".$id;
            header("Location: $url"."?jump=".$jump);
        }
        $uid   = session("member")['uid'];
        
        $model = model("Project");
        $info  = $model->getProjectInfoById($id,$uid);
        
        if($info==false){
            $url  = "/home/project/loading";
            $jump = "/home/project/index/id/".$id;
            header("Location: $url");
        }
        
        if($info['create_uid']!=$uid){
            //检查项目是否可在平台展示
            if($info['status'] !=2){
                $url  = "/home/project/loading";
                header("Location: $url");
            }
            
            //检查项目是否可在平台展示
            if($info['flag'] !=1){
                $url  = "/home/project/loading";
                $jump = "/home/project/index/id/".$id;
                header("Location: $url");
            }
        }
        
        
        
        $org_id      = 0;
        $org_flg     = 1;
        $org_model   = model("Organize");
        $org         = $org_model->getOrganizeInfoByTel($mem_info['userPhone']);
        if($org==false){
            $org_flg = 0;
        }else{
            if($org['is_confirm']==1){
                $org_flg = 0;
            }else{
                $org_id = $org['org_id'];
            }
        }
        $this->assign('org_flg' , $org_flg);
        $this->assign('org_id' , $org_id);
        
        //查看人数+1
        $white  = model("whiteUid");
        if($white->getWhite($uid)){
            $model->where(['pro_id'=>$id])->setInc('view_num');
            //浏览记录+1
            model("ProjectView")->add($id,$uid);
        }
        
        $this->assign('info' , $info);
        
        $keywords       = config('keywords');
        $description    = config('description');
        $this->assign('keywords' , $keywords);
        $this->assign('description' , $description);
        $this->assign('title' , $info['pro_name']."｜FA財-一站式智能信息投融交互平台");
        
        return view();
    }
    
    /**
     * 检查项目是否可平台显示的跳转页面
     * @return unknown
     */
    public function loading(){
        $keywords       = config('keywords');
        $description    = config('description');
        $this->assign('keywords' , $keywords);
        $this->assign('description' , $description);
        $this->assign('title' , "FA財-一站式智能投融信息交互平台");
        return view();
    }
    
    
    public function add()
    {
        $mem_info = session("member");
        //检查UID是否登录
        if(empty($mem_info)){
            $url  = "/home/pub/loading";
            $jump = "/home/project/add";
            header("Location: $url"."?jump=".$jump);
        }
        $uid   = session("member")['uid'];
        $user  = model("member")->getMemberById($uid);
        
        //非项目方无法上传
        if($user['type']>1){
            $url  = "/home/pub/loading";
            $jump = "/home/project/add";
            header("Location: $url"."?jump=".$jump);
        }
        
        //资料信息不完整
        if($user['realName']=="" || $user['company']=="" || $user['position']=="" || $user['userPhoto']==""){
            $url  = "/home/member/userInfo";
            header("Location: $url");
        }
        
        $model  = model("Dict");
        $stage  = $model->getStage();
        $this->assign('stage' , $stage);
        
        $keywords       = config('keywords');
        $description    = config('description');
        $this->assign('keywords' , $keywords);
        $this->assign('description' , $description);
        $this->assign('title' , "发布项目｜FA財-一站式智能信息投融交互平台");
        return view();
    }
    
    public function edit()
    {
        $mem_info = session("member");
        //检查UID是否登录
        if(empty($mem_info)){
            $url  = "/home/pub/loading";
            $jump = "/home/project/add";
            header("Location: $url"."?jump=".$jump);
        }
        $uid   = session("member")['uid'];
        $user  = model("member")->getMemberById($uid);
        
        //非项目方无法编辑
        if($user['type']>1){
            $url  = "/home/pub/loading";
            $jump = "/home/project/add";
            header("Location: $url"."?jump=".$jump);
        }
        
        //资料信息不完整
        if($user['realName']=="" || $user['company']=="" || $user['position']=="" || $user['userPhoto']==""){
            $url  = "/home/member/userInfo";
            header("Location: $url");
        }
        
        $id     = $this->request->param('id/d');
        
        $model  = model("Project");
        $info   = $model->getProjectInfoById($id,$uid);
        $this->assign('info' , $info);
        
        $model  = model("Dict");
        $stage  = $model->getStage();
        $this->assign('stage' , $stage);
        
        $keywords       = config('keywords');
        $description    = config('description');
        $this->assign('keywords' , $keywords);
        $this->assign('description' , $description);
        $this->assign('title' , $info['pro_name']."｜FA財-一站式智能信息投融交互平台");
        return view();
    }

    /**
     * 会员注册
     * @return unknown
     */
    public function doAdd(){
        
        //检查UID是否登录
        $mem_info = session("member");
        //检查UID是否登录
        if(empty($mem_info)){
            $jump   = "/home/project/add";
            header("Location: $url"."?jump=".$jump);
        }
        $data       = [];
        $post       = input();
        if(empty($post)){
            $this->result('' ,'201', '提交错误' , 'json');
        }else{
            $uid        = session("member")['uid'];
            $mem        = model("Member")->getMemberById($uid);
            $stage      = isset($post["stage"])?$post["stage"]:0;
            $area       = isset($post["area"])?$post["area"]:0;
            $stageList  = model("Dict")->getByVal($stage,26);
            $areaList   = model("Dict")->getByVal($area,23);
            $industry_str      = isset($post["industry"])?$post["industry"]:0;
            if(strpos($industry_str,'其他') !== false){
                $result = array();
                preg_match_all("/(?:\()(.*)(?:\))/i",$post['industry'], $result);
                $inc_str = $result[1][0];
                $inc_top  = model("Dict")->getByVal($inc_str,1);
                $inc_sec  = model("Dict")->getByOther($inc_top['id']);
                $data['inc_industry']           = $inc_sec['id'];
                $data['inc_top_industry']       = $inc_top['id'];
                
            }else{
                $industry = model("Dict")->getByVal($industry_str,1);
                $data['inc_industry']           = $industry['id'];
                $data['inc_top_industry']       = $industry['fid'];
            }
            $data['pro_name']                   = $post['pro_name'];
            $data['company_name']               = $post['company_name'];
            $data['introduction']               = $post['introduction'];
            $data['financing_amount']           = $post['amount'];
            $data['analysis_des']               = $post['analysis_des'];
            $data['product_des']                = $post['product_des'];
            $data['enterprise_des']             = $post['enterprise_des'];
            $data['contacts_uid']               = session("member")['uid'];
            $data['create_uid']                 = session("member")['uid'];
            $data['contacts']                   = $mem['realName'];
            $data['contacts_tel']               = $mem['userPhone'];
            $data['invest_stage']               = $stageList['id'];
            $data['inc_area']                   = $areaList['id'];
            $data['up_type']                    = 2;
            
            $res = [];
            //上传图片
            /*$base64   = $post['com_file'];
             faLog("IMG---".$base64);
             if(empty($base64)){
             $res['code'] = "201";
             $res['msg']  = "项目图片没有上传";
             $this->result('' ,'201', '项目图片没有上传' , 'json');
             }else{
             $base64   = explode(',',$base64);
             $tmp      = base64_decode($base64[1]);//base64解码
             $path     = ROOT_PATH . 'public' . DS . 'uploads' . DS . 'project'. DS . 'img';
             if(!is_dir($path)){
             mkdir($path , 0777, true);
             }
             $type     = substr($base64[0],11,strlen($base64[0])-18);
             $fileName = pwd_encrypt("pro".time().$uid).".".$type;
             faLog("IMG-path---".$fileName);
             file_put_contents($path.$fileName,$tmp);
             $model_m  = model("MaterialLibrary");
             $mid      = $model_m->createMaterial($path,0,$type,$fileName,$uid);
             faLog("IMG-id---".$mid);
             $data['top_img'] = $mid;
             }*/
            
            //上传BP
            $base64bp        = $post['com_bp_file'];
            if(empty($base64bp)){
                $res['code'] = "201";
                $res['msg']  = "项目BP没有上传";
                $this->result('' ,'201', '项目BP没有上传' , 'json');
            }else{
                $base64bp    = explode(',',$base64bp);
                $tmpbp       = base64_decode($base64bp[1]);//base64解码
                $pathbp      = ROOT_PATH . 'public' . DS . 'uploads' . DS . 'project'. DS . 'pdf';
                //$pathbp      = "http://fin.jrfacai.com/uploads/project/pdf/";
                if(!is_dir($pathbp)){
                    mkdir($pathbp , 0777, true);
                }
                $typebp      = "pdf";
                $filenamebp  = pwd_encrypt("bp".time().$uid).".pdf";
                file_put_contents($pathbp.$filenamebp,$tmpbp);
                
                $model_tt     = model("MaterialLibrary");
                $path_m       = 'project'. DS . 'pdf';
                $mbp_id       = $model_tt->createMaterial($path_m,0,$typebp,$filenamebp,$uid);
                $data['business_plan'] = $mbp_id;
            }
            
            $project   = model("Project");
            $new       = $project->add($data);
            if($new==!false){
                $this->result('' ,'200', '发布成功' , 'json');
            }else{
                $this->result('' ,'201', '发布失败' , 'json');
            }
        }
    }
    
    public function doEdit(){
        $data       = [];
        $post       = input();
 
        $mem_info = session("member");
        //检查UID是否登录
        if(empty($mem_info)){
            $jump   = "/home/project/edit/id/".$post['id'];
            header("Location: $url"."?jump=".$jump);
        }
        
        $uid        = session("member")['uid'];
        $mem        = model("Member")->getMemberById($uid);
        
        $model      = model("Dict");
        $stage      = $model->getByVal($post['stage'],26);
        $area       = $model->getByVal($post['area'],23);
        if(strpos($post['industry'],'其他') !== false){
            $result = array();
            preg_match_all("/(?:\()(.*)(?:\))/i",$post['industry'], $result);
            $inc_str = $result[1][0];
            $inc_top  = $model->getByVal($inc_str,1);
            $inc_sec  = $model->getByOther($inc_top['id']);
            $data['inc_industry']           = $inc_sec['id'];
            $data['inc_top_industry']       = $inc_top['id'];
            
        }else{
            $industry = $model->getByVal($post['industry'],1);
            $data['inc_industry']           = $industry['id'];
            $data['inc_top_industry']       = $industry['fid'];
        }
        $data['pro_name']                   = $post['pro_name'];
        $data['company_name']               = $post['company_name'];
        $data['introduction']               = $post['introduction'];
        $data['financing_amount']           = $post['amount'];
        $data['analysis_des']               = $post['analysis_des'];
        $data['product_des']                = $post['product_des'];
        $data['enterprise_des']             = $post['enterprise_des'];
        $data['invest_stage']               = $stage['id'];
        $data['inc_area']                   = $area['id'];
        $data['id']                         = $post['id'];
        
        
        $res = [];
        //更新BP
        $base64bp        = $post['com_bp_file'];
        if(!empty($base64bp)){
            $base64bp    = explode(',',$base64bp);
            $tmpbp       = base64_decode($base64bp[1]);//base64解码
            $pathbp      = ROOT_PATH . 'public' . DS . 'uploads' . DS . 'project'. DS . 'pdf';
            if(!is_dir($pathbp)){
                mkdir($pathbp , 0777, true);
            }
            $typebp      = "pdf";
            $filenamebp  = pwd_encrypt("bp".time().$uid).".pdf";
            file_put_contents($pathbp.$filenamebp,$tmpbp);
            
            $model_tt     = model("MaterialLibrary");
            $mbp_id       = $model_tt->createMaterial($pathbp,0,$typebp,$filenamebp,$uid);
            $data['business_plan'] = $mbp_id;
        }
        
        $project   = model("Project");
        $new       = $project->edit($data);
        if($new==!false){
            $this->result('' ,'200', '编辑成功' , 'json');
        }else{
            $this->result('' ,'201', '编辑失败' , 'json');
        }
    }
    
    
    public function prolist(){
        $id          = $this->request->param('id/d');
        $show        = model("ProjectDictShow")->getInfo($id);
        if(!empty($show)){
            $model   = model("Project");
            $project = $model->getProjectShowDs($show['pro_ids']);
        }else{
            $project = array();
        }
        
        $keywords       = config('keywords');
        $description    = config('description');
        $this->assign('keywords' , $keywords);
        $this->assign('description' , $description);
        $this->assign('title' , $show['title']."｜FA財-一站式智能信息投融交互平台");
        
        $this->assign('project',$project);
        $this->assign('show',$show);
        return view();
    }
}
