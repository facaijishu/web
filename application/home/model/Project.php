<?php

namespace app\home\model;

use think\Model;

class Project extends Model
{
    
    /**
     * 获取完整的项目信息
     * @param 项目编号 $id
     * @param 会员编号 $uid
     * @return boolean|unknown
     */
    public function getProjectInfoById($id,$uid){
        if(empty($id)){
            $this->error = '该项目ID不存在';
            return false;
        }
        $info                           = $this->where(['pro_id' => $id])->find();
        if(empty($info)){
            return false;
        }
        $model    = model("QrcodeDirection");
        $result   = $model->getQrcodeDirectionById($info['qr_code']);
        $info['qr_path']                = "http://fin.jrfacai.com/uploads/project/code/".$result['qrcode_path'];
        
        
        $model = model("dict");
        //所属行业
        $result                         = $model->getDictList($info['inc_industry']);
        $info['industry_name']          = $result['dict_name'];
        
        //所属业务
        //$result                         = $model->getDictList($info['inc_sign']);
        $result                         = $model->getDictList($info['invest_stage']);
        $info['sign_name']              = $result['dict_name'];
        
        //所属地区
        $result                         = $model->getDictList($info['inc_area']);
        $info['area_name']              = $result['dict_name'];
        
        //推荐
        $proOther = $this->getOtherList($info['inc_industry'],3,$id);
        if(empty($proOther)){
            $proOther = $this->getOtherTopList($info['inc_top_industry'],3,$id);
        }
        
        $info['related_pro_list']       = $proOther;
        
        //商业计划书
        $model                          = model("material_library");
        $business_plan                  = $model->getMaterialInfoById($info['business_plan']);
        $info['business_plan']          = $business_plan['name'];
        if($info['up_type']==1){
            $info['business_plan_url']      = "http://fin.jrfacai.com/uploads/".$business_plan['url'];
        }else{
            $info['business_plan_url']      = "http://www.jrfacai.com/uploads/".$business_plan['url'];
        }
        
        return $info;
    }
    
    /**
     * 
     * @param 页数 $page
     * @param 长度 $length
     * @param 上传者 $uid
     * @param 状态 ￥status 1：审核中 2：上架中 3:已下架
     * @return number[]|string[]|NULL[]|unknown[]
     */
    public function getListByUid($page, $length=0, $uid,$status=0){
        $return       = [];
        if($length==0){
            $length   = config('paginate.list_rows');
        }
        if($page==1){
            $start    = 0;
        }else{
            $start    = ($page-1)*$length;
        }
        
        $project = $this->alias("p")
                        ->join('Dict d','p.inc_top_industry = d.id')
                        ->join('Dict d2','p.invest_stage = d2.id')
                        ->field("SQL_CALC_FOUND_ROWS p.pro_id as id,p.pro_name,p.company_name,p.introduction,p.status,p.flag,p.view_num,p.type,d.value as label1,d2.value as label2");
        $project_sql = ' p.create_uid = '.$uid;
        
        if($status==1){
            $project_sql .= ' and p.status = 1 and p.flag = 0';
        }else if($status==2){
            $project_sql .= ' and p.status = 2 and p.flag = 1';
        }else if($status==3){
            $project_sql .= ' and p.status = 2 and p.flag = 0';
        }
        
        $list   = $project->where($project_sql)
                            ->order(" p.pro_id desc ")
                            ->limit($start, $length)->select();
        
        $result = $this->query('SELECT FOUND_ROWS() as count');
        $total  = $result[0]['count'];
        if(empty($list)){
            $return['code']           = 201;
            $return['msg']            = "列表没有数据";
            $return['data']           = "";
            $return['page']           = 0;
            $return['total']          = 0;
        }else{
            foreach ($list as $key => $item) {
                $list[$key]['id']     = $item['id'];
                $list[$key]['name']   = $item['pro_name'];
                $list[$key]['bottom'] = subStrLen($item['introduction'],40);
                $list[$key]['link']   = "/home/project/index/id/".$item['id'];
                
                if($item['status']==2){
                    if($item['flag']==1){
                        $list[$key]['status_name']   = "已上架";
                    }else{
                        $list[$key]['status_name']   = "已下架";
                    }
                }else{
                    $list[$key]['status_name']   = "审核中";
                    $list[$key]['link']   = "/home/project/edit/id/".$item['id'];
                } 
            }
            $return['code']           = 200;
            $return['msg']            = "";
            $return['data']           = $list;
            $return['page']           = ceil($total/$length);
            $return['total']          = $total;
        }
        return $return;
    }
    
    /**
     * 插入新数据
     * @param unknown $data
     */
    public function add($data){
        if(empty($data)){
            $this->error = '项目信息不存在';
            return false;
        }
        
        //开启事务
        $this->startTrans();
        try{
            $data['status']         = 1;
            $data['flag']           = 0;
            $data['inc_top_sign']   = 0;
            $data['inc_sign']       = 0;
            $data['is_display']     = 1;
            $data['create_time']    = time();
            $data['last_time']      = time();
            $this->allowField(true)->save($data);
            $id     = $this->getLastInsID();
            
            /*$system = model("SystemConfig")->getSystemConfigInfo();
            $result = createProEditQrcodeDirection($system['current'] , 'project' , $id , '' , '');
            if(!$result){
                $this->error = '创建二维码指向失败';
                return false;
            } else {
                $this->where(['pro_id' => $id])->update(['qr_code' => $result]);
            }*/
        
            // 提交事务
            $this->commit();
            return $id;
        } catch (\Exception $e) {
            // 回滚事务
            $this->rollback();
            return false;
        }
    }
    
    public function edit($data){
        if(empty($data)){
            $this->error = '项目信息不存在';
            return false;
        }
        
        //开启事务
        $this->startTrans();
        try{
            $data['last_time']      = time();
            faLog(json_encode($data));
            $this->allowField(true)->save($save,['pro_id' => $data['id']]);
            // 提交事务
            $this->commit();
            return $id;
        } catch (\Exception $e) {
            // 回滚事务
            $this->rollback();
            return false;
        }
    }
    
    /**
     * 创建产品二维码
     * @param unknown $id
     * @return boolean
     */
    function addQrcode($id){
        $system = model("SystemConfig")->getSystemConfigInfo();
        faLog(json_encode($system));
        $result = createProEditQrcodeDirection($system['current'] , 'project' , $id , '' , '');
        if(!$result){
            $this->error = '创建二维码指向失败';
            return false;
        } else {
            $this->where(['pro_id' => $id])->update(['qr_code' => $result]);
        }
    }
    
    /**
     * 获取项目列表
     * @param 当前页数 $page
     * @param 搜索关键字 $keyword
     * @return number[]|string[]|NULL[]|unknown[]
     */
    public function getList($page, $length=0, $status=2, $flag=1, $keyword='', $key_sign='', $key_industry='', $key_size='', $key_area=''){
        $return       = [];
        if($length==0){
            $length   = config('paginate.list_rows');
        }
        if($page==1){
            $start    = 0;
        }else{
            $start    = ($page-1)*$length;
        }
        
        $project = $this->alias("p")
                        ->join('Dict d','p.inc_top_industry = d.id')
                        ->join('Dict d2','p.invest_stage = d2.id')
                        ->field("SQL_CALC_FOUND_ROWS p.pro_id as id,p.pro_name,p.company_name,p.introduction,p.view_num,p.type,d.value as label1,d2.value as label2");
  
        //可显示的融资中项目
        $project_sql = 'p.status = '.$status.' and p.flag = '.$flag;
        
        //业务细分
        if($key_sign!==''){
            $project_sql .= ' and p.inc_sign = '.$key_sign;
        }
        
        //行业细分
        if($key_industry!==''){
            $project_sql .= ' and p.inc_industry = '.$key_industry;
        }
        
        //投资阶段
        if($key_size!==''){
            $project_sql .= ' and p.invest_stage  = '.$key_size;
        }
        
        
        //投融规模
        /*if($key_size!==''){
            //500万以下
            if ($key_size == 288) {
                $project_sql .= ' and p.financing_amount < 500 ';
            }
            
            //500-1999万
            if ($key_size == 289) {
                $project_sql .= ' and p.financing_amount >= 500 and p.financing_amount <= 1999 ';
            }
            
            //2000-3999万
            if ($key_size == 290) {
                $project_sql .= ' and p.financing_amount >= 2000 and p.financing_amount <= 3999 ';
            }
            
            //4000-6999万
            if ($key_size == 291) {
                $project_sql .= ' and p.financing_amount >= 4000 and p.financing_amount <= 6999 ';
            }
            
            //7000-9999万
            if ($key_size == 292) {
                $project_sql .= ' and p.financing_amount >= 7000 and p.financing_amount <= 9999 ';
            }
            
            //1亿以上
            if ($key_size == 293) {
                $project_sql .= ' and p.financing_amount >= 10000';
            }
        }*/
        //地区
        if($key_area!==''){
            $project_sql .= ' and p.inc_area = '.$key_area;
        }
        
        //关键字
        if($keyword!==''){
            $project_sql .= ' and ( p.pro_name like "%'.$keyword.'%" or p.company_name like "%'.$keyword.'%" or p.introduction like "%'.$keyword.'%" or p.investment_lights like "%'.$keyword.'%" or p.introduction like "%'.$keyword.'%" or ' ;
            $project_sql .= ' d.value like "%'.$keyword.'%" or d2.value like "%'.$keyword.'%" ) ' ;
        }
        
        $list   = $project->where($project_sql)
                          ->order(" p.list_order desc ,p.pro_id desc ")
                          ->limit($start, $length)->select();
        
        $result = $this->query('SELECT FOUND_ROWS() as count');
        $total  = $result[0]['count'];
        if(empty($list)){
            $return['code']           = 201;
            $return['msg']            = "列表没有数据";
            $return['data']           = "";
            $return['page']           = 0;
            $return['total']          = 0;
        }else{
            foreach ($list as $key => $item) {
                $list[$key]['id']     = $item['id'];
                $list[$key]['name']   = $item['pro_name'];
                $list[$key]['bottom'] = subStrLen($item['introduction'],40);
                $list[$key]['link']   = "/home/project/index/id/".$item['id'];
            }
            $return['code']           = 200;
            $return['msg']            = "";
            $return['data']           = $list;
            $return['page']           = ceil($total/$length);
            $return['total']          = $total;
        }
        return $return;
    }
    
    
    public function getProjectList($length = 0){
        $result_info = [];
        if($length === 0){
            $result  = $this->alias('p')
                            ->join('Dict d','p.inc_industry = d.id')
                            ->join('Dict d2','p.inc_sign = d2.id')
                            ->where(['p.status' => 2 , 'p.flag' => 1])
                            ->order("p.list_order desc")
                            ->field([
                                'p.pro_id'      =>'id',
                                'p.pro_name'    =>'name',
                                'p.company_name',
                                'd.value'       =>'label1',
                                'd2.value'      =>'label2',
                                'p.introduction',
                                'p.view_num',
                                'p.type',
                            ])
                            ->select();
        } else {
            $result  = $this->alias('p')
                            ->join('Dict d','p.inc_industry = d.id')
                            ->join('Dict d2','p.inc_sign = d2.id')
                            ->where(['p.status' => 2 , 'p.flag' => 1])
                            ->order("p.list_order desc")
                            ->field([
                                'p.pro_id'      =>'id',
                                'p.pro_name'    =>'name',
                                'p.company_name',
                                'd.value'       =>'label1',
                                'd2.value'      =>'label2',
                                'p.introduction',
                                'p.view_num',
                                'p.type',
                            ])
                            ->limit($length)
                            ->select();
        }
        
        foreach ($result as &$v) {
            $v['bottom']     = subStrLen($v['introduction'],40);
        }
        
        $result_info = $result;

        return $result_info;
        
    }
    
    /**
     * 
     * @param number $length
     */
    public function getRelatedOne($industry){
        $result  = $this->alias('p')
                        ->field('p.pro_id as id , p.pro_name as name ')
                        ->where('p.status = 2 and p.flag = 1 and p.inc_industry in ( '.$industry.' ) ')
                        ->order("p.list_order desc")
                        //->limit(1)
                        ->find();
       return $result;
        
    }
    
    public function getOtherList($industry,$length,$id){
        $result  = $this->alias('p')
        ->join('Dict d','p.inc_top_industry = d.id')
        ->join('Dict d2','p.invest_stage = d2.id')
        //->where('p.status = 2 and p.flag = 1 and p.pro_id <> '.$id)
        ->where('p.status = 2 and p.flag = 1 and p.pro_id <> '.$id.' and p.inc_industry = '.$industry)
        ->order("p.list_order desc")
        ->field([
            'p.pro_id'      =>'id',
            'p.pro_name'    =>'name',
            'p.company_name',
            'd.value'       =>'label1',
            'd2.value'      =>'label2',
            'p.introduction',
            'p.view_num',
            'p.type',
        ])
        ->limit($length)
        ->select();
        foreach ($result as &$v) {
            $v['bottom']     = subStrLen($v['introduction'],40);
        }
        return $result;
    }
    
    public function getOtherTopList($industry,$length,$id){
        $result  = $this->alias('p')
        ->join('Dict d','p.inc_top_industry = d.id')
        ->join('Dict d2','p.invest_stage = d2.id')
        //->where('p.status = 2 and p.flag = 1 and p.pro_id <> '.$id)
        ->where('p.status = 2 and p.flag = 1 and p.pro_id <> '.$id.' and p.inc_top_industry = '.$industry)
        ->order("p.list_order desc")
        ->field([
            'p.pro_id'      =>'id',
            'p.pro_name'    =>'name',
            'p.company_name',
            'd.value'       =>'label1',
            'd2.value'      =>'label2',
            'p.introduction',
            'p.view_num',
            'p.type',
        ])
        ->limit($length)
        ->select();
        foreach ($result as &$v) {
            $v['bottom']     = subStrLen($v['introduction'],40);
        }
        return $result;
    }
    
    /**
     * 获取这些行业其他项目
     * @param unknown $industry
     * @param unknown $length
     * @return unknown
     */
    public function getRelatedList($industry,$length){
        $result  = $this->alias('p')
                        ->join('Dict d','p.inc_top_industry = d.id')
                        ->join('Dict d2','p.invest_stage = d2.id')
                        ->where('p.status = 2 and p.flag = 1 and p.inc_industry in ( '.$industry.' ) ')
                        ->order("p.list_order desc")
                        ->field([
                            'p.pro_id'      =>'id',
                            'p.pro_name'    =>'name',
                            'p.company_name',
                            'd.value'       =>'label1',
                            'd2.value'      =>'label2',
                            'p.introduction',
                            'p.view_num',
                            'p.type',
                        ])
                        ->limit(1,$length)
                        ->select();
        foreach ($result as &$v) {
            $v['bottom']     = subStrLen($v['introduction'],40);
        }
        return $result;
    }
    
    /**
     * 首页轮播广告位的项目列表页面
     * @param number $idStr
     * @return 集合
     */
    public function getProjectShowDs($idStr)
    {
        $data    = $this->alias('p')
                        ->join('Dict d','p.inc_industry = d.id')
                        ->join('Dict d2','p.invest_stage = d2.id')
                        ->field([
                            'p.pro_id'      => 'id',
                            'p.pro_name'    => 'top',
                            'p.company_name'=> 'name',
                            'd.value'       => 'label1',
                            'd2.value'      => 'label2',
                            'p.introduction',
                            'p.view_num',
                            'p.type',
                        ])
                        ->where(['p.status' => 2 , 'p.flag' => 1, 'p.pro_id' => array("in",$idStr)])
                        ->order("p.list_order desc,p.pro_id desc")
                        ->select();
        foreach ($data as &$v) {
            $v['type_label'] = '精选项目';
            $v['bottom']     = subStrLen($v['introduction'],23);
        }
        return $data;
        
    }
}