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
        
        $keywords       = config('keywords_news');
        $description    = config('description_news');
        $this->assign('keywords' , $keywords."，资讯中心，".$info['type_name']);
        $this->assign('description' , $info['title'].",".$description);
        $this->assign('title' , $info['title']."｜FA財-一站式智能信息投融交互平台");
        return view();
        
    }
    
    
    public function lists(){
        $uid      = 0;
        $showflg  = 1;
        $search   = [];
        $mem      = session("member");
        $uid      = session("member")['uid'];
        $type     = session("member")['type'];
        
        $type_id     = $this->request->param('id')?$this->request->param('id'):0;
        $this->assign('type_id' , $type_id);
        
        //检查UID是否登录
        if(empty($mem)){
            $login      = 1;//显示项目
            $showflg    = 0;
        }else{
            if($uid>0){
                if($type==1 || $type==2){
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
        
        //广告位项目机构显示
        $modelDict = model("Dict");
        $type      = $modelDict->getNewsType();
        $this->assign('newsType' , $type);
        
        $keywords       = config('keywords_news');
        $description    = config('description_news');
        $this->assign('keywords' , "资讯中心,".$keywords);
        $this->assign('description' , $description);
        $this->assign('title' , "资讯中心-FA財");
        return view();
    }
    
    public function listApi(){
        $page       = $this->request->param('page')?$this->request->param('page'):'';
        $newsType   = $this->request->param('newsType')?$this->request->param('newsType'):0;
        $type       = 1;
        $model      = model("News");
        $list       = $model->getList($page,$type,$newsType);
        
        $this->result($list,$list['code'],$list['msg'],'json');
    }
   
}
