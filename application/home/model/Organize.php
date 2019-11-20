<?php

namespace app\home\model;

use think\Model;

class Organize extends Model
{
    
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
        
        //所属区域
        $area               = $model->getDictStr($info['inc_area']);
        $info['area']  = $area;
        
        //投资企业+所属行业
        $arr_target = [];
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
        $info['target_list'] = $arr_target;
        
        
        $orgOther = $this->getOtherList($info['inc_industry'],3,$id);
        $info['related_org_list'] = $orgOther;
        
        return $info;
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
                            //->join('Dict d','o.unit = d.id')
                            ->join('MaterialLibrary ml','o.top_img=ml.ml_id')
                            ->field("SQL_CALC_FOUND_ROWS o.org_id as id,o.org_short_name,o.contacts,o.position,o.view_num,o.type,o.scale_min,o.scale_max,o.inc_industry,ml.url");

        //可显示的融资中项目
        $organize_sql = 'o.status = 2 and o.flag = 1';
        
        
        //业务细分
        //if($key_sign!==''){
        //    $project_sql .= ' and o.inc_sign = '.$key_sign;
        //}
        
        //行业细分
        if($key_industry!==''){
            $organize_sql .= ' and o.inc_industry in ('.$key_industry.') ';
        }
        
        //投融规模
       if($key_size!==''){
            //500万以下
            if ($key_size == 288) {
                $organize_sql .= ' and o.scale_max < 500 ';
            }
            
            //500-1999万
            if ($key_size == 289) {
                $organize_sql .= ' and o.scale_min >= 500 and o.scale_max <= 1999 ';
            }
            
            //2000-3999万
            if ($key_size == 290) {
                $organize_sql .= ' and o.scale_min >= 2000 and o.scale_max <= 3999 ';
            }
            
            //4000-6999万
            if ($key_size == 291) {
                $organize_sql .= ' and o.scale_min >= 4000 and o.scale_max <= 6999 ';
            }
            
            //7000-9999万
            if ($key_size == 292) {
                $organize_sql .= ' and o.scale_min >= 7000 and o.scale_max <= 9999 ';
            }
            
            //1亿以上
            if ($key_size == 293) {
                $organize_sql .= ' and o.scale_min >= 10000';
            }
        }
        
        //地区
        if($key_area!==''){
            $organize_sql .= ' and o.inc_area in ('.$key_area.') ';
        }
        
        
        if($keyword!==''){
            $organize_sql .= ' and ( o.org_short_name like "%'.$keyword.'%" or o.org_name like "%'.$keyword.'%" or o.contacts like "%'.$keyword.'%" or o.position like "%'.$keyword.'%" ) ' ;
            //$organize_sql .= ' d.value like "%'.$keyword.'%" or d2.value like "%'.$keyword.'%" ) ' ;
        }
        
        $list      = $organize->where($organize_sql)
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
                $list[$key]['style_bg_url'] = "background:url('".config('material_img.upload_root').$item['url']."')  center no-repeat;";
                
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