<?php
namespace app\home\controller;

class Organize extends Base
{
    public function index()
    {
        $id    = $this->request->param('id/d');
        //检查UID是否登录
        if(empty(session("member"))){
            $url  = "/home/pub/loading";
            $jump = "/home/organize/index/id/".$id;
            header("Location: $url"."?jump=".$jump);
        }
        $uid = session("member")['uid'];
        
        $model = model("Organize");
        $info  = $model->getOrganizeInfoById($id);
       

        $this->assign('info' , $info);
        $this->assign('title' , $info['org_short_name']."｜FA財-一站式智能信息投融交互平台");
        
        return view();
    }
    
    
    
    public function add()
    {
        //检查UID是否登录
        if(empty(session("member"))){
            $url  = "/home/pub/loading";
            $jump = "/home/project/add";
            header("Location: $url"."?jump=".$jump);
        }
        
        $id       = $this->request->param('id/d');
        $model    = model("Dict");
        $service  = $model->getService();
        $this->assign('info' , $service);
        $this->assign('title' , "发布项目｜FA財-一站式智能信息投融交互平台");
        return view();
    }
    
    /**
     * 会员注册
     * @return unknown
     */
    public function doAdd(){
        
        //检查UID是否登录
        if(empty(session("member"))){
            $url  = "/home/pub/loading";
            $jump = "/home/project/add";
            header("Location: $url"."?jump=".$jump);
        }
        
        $res = [];
        //上传图片
        $base64   = $_POST['com_file'];
        faLog(empty($base64));
        if(empty($base64)){
            $res['code'] = "201";
            $res['msg']  = "项目图片没有上传";
            $this->result('' ,'201', '项目图片没有上传' , 'json');
        }else{
            $base64   = explode(',',$base64);
            $tmp      = base64_decode($base64[1]);//base64解码
            $path     = ROOT_PATH . 'public' . DS . 'uploads' . DS . 'project'. DS . 'img'. DS;
            if(!is_dir($path)){
                mkdir($path , 0777, true);
            }
            $filename = "pro_".".png";
            file_put_contents($path.$filename,$tmp);
        }
        
        //上传BP
        $base64bp   = $_POST['com_bp_file'];
        if(empty($base64bp)){
            $res['code'] = "201";
            $res['msg']  = "项目BP没有上传";
            $this->result('' ,'201', '项目BP没有上传' , 'json');
        }else{
            $base64bp   = explode(',',$base64bp);
            $tmpbp      = base64_decode($base64bp[1]);//base64解码
            $pathbp     = ROOT_PATH . 'public' . DS . 'uploads' . DS . 'project'. DS . 'pdf'. DS;
            if(!is_dir($pathbp)){
                mkdir($pathbp , 0777, true);
            }
            $filenamebp = "pro_bp_".".pdf";
            file_put_contents($pathbp.$filenamebp,$tmpbp);
        }
        
        $this->result('' ,'200', '上传成功' , 'json');
        
  
        
        //检查UID是否登录
        /*
        $post    = input();

        $data = [];
        $data['pro_name']           = $post['pro_name'];
        $data['company_name']       = $post['company_name'];
        $data['introduction']       = $post['introduction'];
        $data['financing_amount']   = $post['financing_amount'];
        $data['analysis_des']       = $post['analysis_des'];
        $data['product_des']        = $post['product_des'];
        $data['enterprise_des']     = $post['enterprise_des'];
        $data['contacts_uid']       = session("member")['uid'];
        
        $model   = model("Project");
        $res     = $model->add($data);
        return $this->result('', $res['code'],$res['msg'], 'json');*/
           
        
    }
}
