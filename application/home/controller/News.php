<?php
namespace app\home\controller;

class News extends Base
{
    public function index()
    {
        $id    = $this->request->param('id/d');
        $model = model("News");
        $info  = $model->getNewsInfoById($id);
        $this->assign('info' , $info);
        $this->assign('title' , $info['title']."｜FA財-一站式智能信息投融交互平台");
        return view();
        
    }
    
    
    public function lists(){
        $uid      = 0;
        $showflg  = 1;
        $search   = [];
        if(empty(session("member"))){
            $login      = 1;//显示项目
            $showflg    = 0;
        }else{
            if(session("member")['uid']>0){
                $uid = session("member")['uid'];
                if(session("member")['type']==1 || session("member")['type']==2){
                    $login = 2;//显示机构
                }else{
                    $login = 1;//显示项目
                }
                //获取右侧广告位
                $models    = model("SearchKeywords");
                $search    = $models->getByUid($uid,$login);
                if(!empty($search)){
                    if($login==1){
                        if(empty($search['pro-data']['data'])){
                            $showflg    = 0;
                        }
                    }else{
                        if(empty($search['org-data']['data'])){
                            $showflg    = 0;
                        }
                    }
                }else{
                    $showflg = 0;
                }
                
            }else{
                $showflg    = 0;
            }
        }
        
        if($showflg==0){
            //广告位项目机构显示
            $modelshow = model("ShowIndex");
            $show      = $modelshow->getlist($login);
        }else{
            $show      = $search;
        }
        
        $this->assign('show' , $show);
        
        $this->assign('title' , "资讯中心-FA財");
        return view();
    }
    
    public function listApi(){
        $page       = $this->request->param('page')?$this->request->param('page'):'';
        $type       = 1;
        $model      = model("News");
        $list       = $model->getList($page,$type);
        $this->result($list,$list['code'],$list['msg'],'json');
    }
   
}
