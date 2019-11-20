<?php
namespace app\home\controller;

class Index extends Base
{
    public function index()
    {
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
        
        //获取研报
        $modelR       = model("IndustryReport");
        $list_report  = $modelR->getReportList(2,$login);
        $this->assign('list_report' , $list_report);
        
        //获取资讯
        $modelN       = model("News");
        $list_news    = $modelN->getNewsList(2,$login);
        $this->assign('list_news' , $list_news);
        
        $this->assign('title' , "FA財-一站式智能投融信息交互平台");
        
        return view();
    }
    
    public function index2()
    {
        
        $modelD = model("Dict");
        $service = $modelD->getService();
        $this->assign('service' , $service);
        
        $industry = $modelD->getIndustry();
        $this->assign('industry' , $industry);
        
        $size = $modelD->getSize();
        $this->assign('size' , $size);
        
        $province = $modelD->getProvince();
        $this->assign('province' , $province);
        
        $model = model("PcConsultation");
        $list = $model->getPcConsultationList(8);
        $this->assign('list' , $list);
        
        $model = model("Project");
        $plist = $model->getProjectList(8);
        $this->assign('plist' , $plist);
        
        return view();
    }
    
    /**
     * 搜索View
     */
    public function search()
    {
        $keyword    = $this->request->param('keyword')?$this->request->param('keyword'):'';
        session("search_key",$keyword);
        $url = "/home/index/listview";
        header("Location: $url");
    }
    
    /**
     * 搜索一览view
     */
    public function listView()
    {
        $keyword    = session("search_key");
        $this->assign('v_keyword' , $keyword);
        $this->assign('title' , "找项目、找资金、找人脉、找FA財-一站式智能信息投融交互平台");
        session("search_key","");
        return view();
    }
    
    /**
     * 搜索关键字请求
     */
    public function listViewApi()
    {
        $pageP      = $this->request->param('pageP')?$this->request->param('pageP'):'1';
        $pageO      = $this->request->param('pageO')?$this->request->param('pageO'):'1';
        $pagePub    = $this->request->param('pagePub')?$this->request->param('pagePub'):'1';
        $keyword    = $this->request->param('keyword')?$this->request->param('keyword'):'';
        
        if(session("member")['uid']>0){
            $uid = session("member")['uid'];
        }else{
            $uid = 0;
        }
        
        if($keyword!=''){
            //保存用户搜索内容
            $data = [];
            $data['uid']      = $uid;
            $data['keyword']  = $keyword;
            $data['add_time'] = time();
            $data['type']     = 1;
            model('SearchKeywords')->createSearchKeywords($data);
        }
        
        $modelP     = model("Project");
        $plist      = $modelP->getList($pageP,$keyword);
        $modelO     = model("Organize");
        $olist      = $modelO->getList($pageO,$keyword);
        
        //$modelPub   = model("Publish");
        //$publist    = $modelPub->getList($pagePub,$keyword);
  
        
        $data =  [
            'data_p'    => $plist['data'],
            'code_p'    => $plist['code'],
            'page_p'    => $plist['page'],
            'total_P'   => $plist['total'],
            'data_o'    => $olist['data'],
            'code_o'    => $olist['code'],
            'total_o'   => $olist['total'],
            'page_o'    => $olist['page'],
        ];
        
        if($plist['code']==201 && $olist['code']==201){
            $code = "201";
            $msg  = "列表没有数据";
        }else{
            $code = "200";
            $msg  = "";
        }
        $this->result($data,$code,$msg,'json');
        
    }
    
    /**
     * 找项目view
     */
    public function listproject()
    {
        $modelD = model("Dict");
        $service = $modelD->getService();
        $this->assign('service' , $service);
        
        $industry = $modelD->getIndustry();
        $this->assign('industry' , $industry);
        
        $size = $modelD->getSize();
        $this->assign('size' , $size);
        
        $province = $modelD->getProvince();
        $this->assign('province' , $province);
        
        $this->assign('title' , "找项目｜FA財-一站式智能信息投融交互平台");
        
        return view();
    }
    
    /**
     * 项目一览List请求（包含关键字）
     */
    public function listprojectApi()
    {
        $page         = $this->request->param('page')?$this->request->param('page'):'';
        $keyword      = $this->request->param('keyword')?$this->request->param('keyword'):'';
        $key_sign     = $this->request->param('key_sign')?$this->request->param('key_sign'):'';
        $key_industry = $this->request->param('key_industry')?$this->request->param('key_industry'):'';
        $key_size     = $this->request->param('key_size')?$this->request->param('key_size'):'';
        $key_area     = $this->request->param('key_area')?$this->request->param('key_area'):'';
        
        
        if(session("member")['uid']>0){
            $uid = session("member")['uid'];
        }else{
            $uid = 0;
        }
        
        if($keyword!=''){
            //保存用户搜索内容
            $data = [];
            $data['uid']      = $uid;
            $data['keyword']  = $keyword;
            $data['add_time'] = time();
            $data['type']     = 3;
            model('SearchKeywords')->createSearchKeywords($data);
        }
        
        $model        = model("Project");
        $plist        = $model->getList($page,0,$keyword,$key_sign,$key_industry,$key_size,$key_area);
        $this->result($plist,$plist['code'],$plist['msg'],'json'); 
    }

    /**
     * 资金一览View
     * @return unknown
     */
    public function listorganize()
    {
        
        $modelD = model("Dict");
        $service = $modelD->getService();
        $this->assign('service' , $service);
        
        $industry = $modelD->getIndustry();
        $this->assign('industry' , $industry);
        
        $size = $modelD->getSize();
        $this->assign('size' , $size);
        
        $province = $modelD->getProvince();
        $this->assign('province' , $province);
        $this->assign('title' , "找资金｜FA財-一站式智能信息投融交互平台");
        
        return view();
    }
    
    /**
     * 资金一览List请求（包含关键字）
     */
    public function listorganizetApi()
    {
        $page         = $this->request->param('page')?$this->request->param('page'):'';
        $keyword      = $this->request->param('keyword')?$this->request->param('keyword'):'';
        $key_sign     = $this->request->param('key_sign')?$this->request->param('key_sign'):'';
        $key_industry = $this->request->param('key_industry')?$this->request->param('key_industry'):'';
        $key_size     = $this->request->param('key_size')?$this->request->param('key_size'):'';
        $key_area     = $this->request->param('key_area')?$this->request->param('key_area'):'';
        
        if(session("member")['uid']>0){
            $uid = session("member")['uid'];
        }else{
            $uid = 0;
        }
        
        if($keyword!=''){
            //保存用户搜索内容
            $data = [];
            $data['uid']      = $uid;
            $data['keyword']  = $keyword;
            $data['add_time'] = time();
            $data['type']     = 4;
            model('SearchKeywords')->createSearchKeywords($data);
        }
        
        $model        = model("Organize");
        $olist        = $model->getList($page,0,$keyword,$key_sign,$key_industry,$key_size,$key_area);
        $this->result($olist,$olist['code'],$olist['msg'],'json'); 
    }
    
}
