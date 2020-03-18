<?php

namespace app\home\model;

use think\Model;

class Organize extends Model
{
    
    /**
     * 编辑
     * @param unknown $data
     * @return boolean|unknown
     */
    public function add($data){
        if(empty($data)){
            $this->error = '项目信息不存在';
            return false;
        }
        //开启事务
        $this->startTrans();
        try{
            $data['create_time']      = time();
            $data['last_time']        = time();
            $this->allowField(true)->save($data);
            
            $id     = $this->getLastInsID();
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
     * 编辑
     * @param unknown $data
     * @return boolean|unknown
     */
    public function edit($data){
        if(empty($data)){
            $this->error = '项目信息不存在';
            return false;
        }
        //开启事务
        $this->startTrans();
        try{
            $data['last_time']      = time();
            
            $this->allowField(true)->save($data,['org_id' => $data['id']]);
            // 提交事务
            $this->commit();
            return true;
        } catch (\Exception $e) {
            // 回滚事务
            $this->rollback();
            return false;
        }
    }
    
    //获取简单的机构信息
    public function getOrganizeInfoById($id)
    {
        if(empty($id)){
            $this->error = '该机构ID不存在';
            return false;
        }
        $info               = $this->where(['org_id' => $id])->find();
        $erweima            = db("qrcode_direction")->where(['id' => $info['qr_code']])->find();
        $info['qr_path']    = $erweima['qrcode_path'];
        
        //投资方向
        $model              = model("Dict");
        $result             = $model->getSecDictList($info['inc_industry']);
        $info['dict_list']  = $result;
        $info['inc_list']   = explode(",", $info['inc_industry']);
        
        //所属区域
        $area               = $model->getDictStr($info['inc_area']);
        $info['area']       = $area;
        $info['area_list']  = explode(",", $info['inc_area']);
        
        //投资阶段
        $stage              = $model->getDictStr($info['invest_stage']);
        $info['stage']      = $stage;
        $info['stage_list'] = explode(",", $info['invest_stage']);
        
        //业务类型
        $type               = $model->getDictStr($info['inc_type']);
        $info['type']       = $type;
        $info['type_list']  = explode(",", $info['inc_type']);
        
        //投资企业+所属行业
        /*$arr_target = [];
        if(strpos($info['inc_target'], '-')){
            $inc_targets = explode('-', $info['inc_target']);
            foreach ($inc_targets as $key => $value) {
                
                $inc_targets2 = explode('+', $value);
                if(count($inc_targets2)==2){
                    $arr_target[] = array(
                        'target_company'=>$inc_targets2[0],
                        'target_industry'=>$inc_targets2[1]
                    );
                } 
            }
        }else{
            if(strpos($info['inc_target'],'+') !== false){
                $inc_targets2 = explode('+', $info['inc_target']);
                if(count($inc_targets2) == 2){
                    $arr_target[] = array(
                        'target_company' =>$inc_targets2[0],
                        'target_industry'=>$inc_targets2[1]
                    );
                }
                
                
            }
        }
        $info['target_list'] = $arr_target;*/
        
        
        $orgOther = $this->getOtherList($info['inc_industry'],3,$id);
        $info['related_org_list'] = $orgOther;
        
        return $info;
    }
    
    //获取简单的机构信息
    public function getOrganizeInfoByTel($tel)
    {
        if(empty($tel)){
            $this->error = '该机构ID不存在';
            return false;
        }
        
        $info = $this->where(['contact_tel' => $tel])->find();
        if(empty($info)){
            $this->error = '该机构ID不存在';
            return false;
        }else{
            return $info;
        }
    }
    
    
    /**
     * 获取项目列表
     * @param 当前页数 $page
     * @param 搜索关键字 $keyword
     * @return number[]|string[]|NULL[]|unknown[]
     */
    public function getList($page, $length=0,$keyword='', $key_sign='', $key_industry='', $key_size='', $key_area=''){
        
        $return       = [];
        if($length==0){
            $length       = config('paginate.list_rows');
        }
        
        if($page==1){
            $start    = 0;
        }else{
            $start    = ($page-1)*$length;
        }
        
        $organize    = $this->alias('o')
                            ->join('OrganizeDict od','od.org_id = o.org_id')
                            ->join('Dict d','od.id = d.id')
                            //->join('MaterialLibrary ml','o.top_img=ml.ml_id')
                            //->field("SQL_CALC_FOUND_ROWS o.org_id as id,o.org_short_name,o.contacts,o.position,o.view_num,o.type,o.scale_min,o.scale_max,o.inc_industry,ml.url");
                            ->field("SQL_CALC_FOUND_ROWS DISTINCT o.org_id as id,o.org_short_name,o.contacts,o.position,o.view_num,o.type,o.scale_min,o.scale_max,o.inc_industry");
                            
                            
        //可显示的融资中项目
        $organize_sql = 'o.status = 2 and o.flag = 1';
        
        //行业细分
        if($key_industry!==''){
            //$organize_sql .= ' and d.id = '.$key_industry;
            
            $organize_sql .= ' and o.inc_industry  in ('.$key_industry.') ';
        }
        
        //投资阶段
        if($key_size!==''){
            $organize_sql .= ' and o.invest_stage  in ('.$key_size.') ';
        }
        
        //地区
        if($key_area!==''){
            $organize_sql .= ' and o.inc_area = '.$key_area;
        }
        
        
        if($keyword!==''){
            $organize_sql .= ' and ( o.org_short_name like "%'.$keyword.'%" or o.org_name like "%'.$keyword.'%" or o.contacts like "%'.$keyword.'%" or o.position like "%'.$keyword.'%" or d.value like "%'.$keyword.'%") ' ;
        }
        $list   = $organize->where($organize_sql)
                              ->order(" o.list_order desc ,o.org_id desc ")
                              ->limit($start, $length)->select();
        $result = $this->query('SELECT FOUND_ROWS() as count');
        $total  = $result[0]['count'];
        
        if(empty($list)){
            $return['code']            = 201;
            $return['msg']             = "列表没有数据";
            $return['data']            = "";
            $return['page']            = 0;
            $return['total']           = 0;
        }else{
            foreach ($list as $key => $item) {
                $list[$key]['id']           = $item['id'];
                $list[$key]['bottom']       = $item['contacts']."|".$item['position'];
                $list[$key]['name']         = $item['org_short_name'];
                $list[$key]['link']         = "/home/organize/index/id/".$item['id'];
                //$list[$key]['style_bg_url'] = "background:url('".config('material_img.upload_root').$item['url']."')  center no-repeat;";
                $list[$key]['style_bg_url'] = "";
                $model   = model("dict");
                $dict    = $model->getTopDictList($item['inc_industry']);
                $list[$key]['dict_list']     = $dict;
                
            }
            $return['code']            = 200;
            $return['msg']             = "";
            $return['data']            = $list;
            $return['page']            = ceil($total/$length);
            $return['total']           = $total;
        }
        return $return;
    }
    
    
    public function getListByUid($page, $length=0, $uid,$status){
        
        $return       = [];
        
        if($length==0){
            $length   = config('paginate.list_rows');
        }
        
        if($page==1){
            $start    = 0;
        }else{
            $start    = ($page-1)*$length;
        }
        $organize     = $this->alias('o')
                            ->join('OrganizeDict od','od.org_id = o.org_id')
                            ->join('Dict d','od.id = d.id')
                            ->field("SQL_CALC_FOUND_ROWS DISTINCT o.org_id as id,o.org_short_name,o.status,o.contacts,o.position,o.view_num,o.type,o.scale_min,o.scale_max,o.inc_industry");
        
        //可显示的融资中项目
        $organize_sql = ' o.create_uid = '.$uid;
        if($status==1){
            $organize_sql .= ' and o.status = 0 and o.flag = 2';
        }else if($status==2){
            $organize_sql .= ' and o.status = 2 and o.flag = 1';
        }else if($status==3){
            $organize_sql .= ' and o.status = 2 and o.flag = 0';
        }
        $list    = $organize->where($organize_sql)
                            ->order(" o.list_order desc ,o.org_id desc ")
                            ->limit($start, $length)->select();
        $result  = $this->query('SELECT FOUND_ROWS() as count');
        $total   = $result[0]['count'];
        if(empty($list)){
            $return['code']            = 201;
            $return['msg']             = "列表没有数据";
            $return['data']            = "";
            $return['page']            = 0;
            $return['total']           = 0;
        }else{
            foreach ($list as $key => $item) {
                $list[$key]['id']           = $item['id'];
                $list[$key]['bottom']       = $item['contacts']."|".$item['position'];
                $list[$key]['name']         = $item['org_short_name'];
                $list[$key]['link']         = "/home/organize/index/id/".$item['id'];
                $list[$key]['style_bg_url'] = "";
                if($item['status']==2){
                    $list[$key]['status_name']   = "已确认";
                }else{
                    $list[$key]['status_name']   = "未确认";
                    $list[$key]['link']   = "/home/Organize/edit/id/".$item['id'];
                } 
                $model   = model("dict");
                $dict    = $model->getTopDictList($item['inc_industry']);
                $list[$key]['dict_list']     = $dict;
                
            }
            $return['code']            = 200;
            $return['msg']             = "";
            $return['data']            = $list;
            $return['page']            = ceil($total/$length);
            $return['total']           = $total;
        }
        return $return;
    }
    
    
    public function getOrganizeList($length = 0){
        $result_info = [];
        if($length === 0){
            $result = $this->alias('p')
            ->join('Dict d','p.inc_industry = d.id')
            ->join('Dict d2','p.inc_sign = d2.id')
            ->where(['p.status' => 2 , 'p.flag' => 1])
            ->order("p.list_order desc")
            ->field([
                'p.org_id'      =>'id',
                'p.org_name'    =>'name',
                'p.company_name',
                'd.value'       =>'label1',
                'd2.value'      =>'label2',
                'p.introduction',
                'p.view_num',
                'p.type',
            ])
            ->select();
        } else {
            $result = $this->alias('p')
            ->join('Dict d','p.inc_industry = d.id')
            ->join('Dict d2','p.inc_sign = d2.id')
            ->where(['p.status' => 2 , 'p.flag' => 1])
            ->order("p.list_order desc")
            ->field([
                'p.org_id'      =>'id',
                'p.org_name'    =>'name',
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
     *获取行业关联排序第1条记录
     * @param 行业 $industry
     * @return 集合
     */
    public function getRelatedOne($industry){
        
        $result  = $this->alias('o')
                        ->where('o.status = 2 and o.flag = 1 and o.inc_industry in ( '.$industry.' ) ')
                        ->order("o.list_order desc")
                        ->field([
                            'o.org_id'            =>'id',
                            'o.org_short_name'    =>'name',
                        ])
                        ->find();
        return $result;
    }
    
    /**
     * 获取行业关联排序第2条记录
     * @param 行业 $industry
     * @param 集合取得 $length
     * @return 集合
     */
    public function getRelatedList($industry,$length){
        $result  = $this->alias('o')
                        ->where('o.status = 2 and o.flag = 1 and o.inc_industry in ( '.$industry.' ) ')
                        ->order("o.list_order desc")
                        ->field([
                            'o.org_id'            =>'id',
                            'o.org_short_name'    =>'name',
                            'o.inc_industry',
                            'o.contacts',
                            'o.position',
                            'o.contact_tel',
                            'o.view_num',
                        ])
                        ->limit(1,$length)
                        ->select();
        foreach ($result as &$v) {
            $v['bottom'] = $v['contacts']." | ".$v['position'];
            $model   = model("dict");
            $list    = $model->getTopDictList($industry);
            $v['dict_list']     = $list;
        }             
        return $result;
    }
    
    public function getOtherList($industry,$length,$id){
        $result  = $this->alias('o')
                        ->where('o.status = 2 and o.flag = 1 and o.org_id <> '.$id.' and o.inc_industry in ('.$industry.')')
                        ->order("o.list_order desc")
                        ->field([
                            'o.org_id'              =>'id',
                            'o.org_short_name'      =>'name',
                            'o.org_name',
                            'o.inc_industry',
                            'o.contacts',
                            'o.position',
                            'o.view_num',
                            'o.type',
                        ])
                        ->limit($length)
                        ->select();
        foreach ($result as &$v) {
            $model   = model("Dict");
            $list    = $model->getTopDictList($v['inc_industry']);
            $v['dict_list']     = $list;
        }
        return $result;
    }
    
    
}