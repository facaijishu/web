<?php

namespace app\home\model;

use think\Model;

class IndustryReport extends Model
{
    
    /**
     * 获取研报详细信息
     * @param 研报 $id
     * @return 数据集合
     */
    public function getReportInfoById($id,$type=1){
        $result = $this->where(['id' => $id])->find();
        
        $result['report_date'] = substr($result['create_time'],0,10);
        
        $other = $this->field("id,title")->where('id <> '.$result['id'].' and industry_pro in ('.$result['industry_pro'].')')->select();
        $result['other_list'] = $other;
        
        $type = 1;
        if($type==1){
            $model   = model("dict");
            $list    = $model->getSecDictList($result['industry_pro']);
            $result['dict_list']     = $list;
            
            $modelP  = model("Project");
            $proList = $modelP->getRelatedOne($result['industry_pro']);
            if(empty($proList)){
                $result['related_id']     = 0;
                $result['related_title']  = '';
                $result['related_url']    = '';
            }else{
                $result['related_id']     = $proList['id'];
                $result['related_title']  = $proList['name'];
                $result['related_url']    = "/home/project/index/id/".$proList['id'];
            }
            
            $proOther = $modelP->getRelatedList($result['industry_pro'],3);
            $result['related_pro_list'] = $proOther;
            $result['related_org_list'] = [];
        }else{
            $model  = model("dict");
            $list   = $model->getTopDictList($result['industry_org']);
            $result['dict_list']     = $list;
            
            $modelO  = model("Organize");
            $orgList = $modelO->getRelatedOne($result['industry_org']);
            if(empty($orgList)){
                $result['related_id']     = 0;
                $result['related_title']  = '';
                $result['related_url']    = '';
            }else{
                $result['related_id']     = $orgList['id'];
                $result['related_title']  = $orgList['name'];
                $result['related_url']    = "/home/organize/index/id/".$orgList['id'];
            }
            
            $orgOther = $modelO->getRelatedList($result['industry_org'],3);
            $result['related_org_list'] = $orgOther;
            $result['related_pro_list'] = [];
            
        }
        
        return $result;
    }
    
    
    /*public function getOtherList($length = 4,$type){
        
    }*/
    
    /**
     * 或缺研报卡片列表
     * @param number $length
     * @param 关联属性       $type 1：显示项目  2：显示机构
     * @return unknown
     */
    public function getReportList($length = 4,$type){
        if($length === 0){
            $result = $this->alias('r')
                    ->where('r.status = 1' )
                    ->order("is_hot desc,id desc")
                    ->field("r.*")
                    ->select();
        } else {
            $result = $this->alias('r')
                    ->where('r.status = 1')
                    ->order("is_hot desc,id desc")
                    ->field("r.*")
                    ->limit($length)
                    ->select();
        }
        foreach ($result as $key => $value) {
            if($type==1){
                $model  = model("dict");
                $list   = $model->getTopDictList($value['industry_pro']);
                $result[$key]['dict_list']     = $list;
                
                $modelP  = model("Project");
                $proList = $modelP->getRelatedOne($value['industry_pro']);
                if(empty($proList)){
                    $result[$key]['related_id']     = 0;
                    $result[$key]['related_title']  = '';
                    $result[$key]['related_url']    = '';
                }else{
                    $result[$key]['related_id']     = $proList['id'];
                    $result[$key]['related_title']  = $proList['name'];
                    $result[$key]['related_url']    = "/home/project/index/id/".$proList['id'];
                }
            }else{
                $model  = model("dict");
                $list   = $model->getTopDictList($value['industry_org']);
                $result[$key]['dict_list']     = $list;
                
                $modelO  = model("Organize");
                $orgList = $modelO->getRelatedOne($value['industry_org']);
                if(empty($orgList)){
                    $result[$key]['related_id']     = 0;
                    $result[$key]['related_title']  = '';
                    $result[$key]['related_url']    = '';
                }else{
                    $result[$key]['related_id']     = $orgList['id'];
                    $result[$key]['related_title']  = $orgList['name'];
                    $result[$key]['related_url']    = "/home/organize/index/id/".$orgList['id'];
                }
                
            }
            $result[$key]['report_date'] = substr($result[$key]['create_time'],0,10);
        }
        return $result;
    }
    
    
    /**
     * 获取项目列表
     * @param 当前页数 $page
     * @return number[]|string[]|NULL[]|unknown[]
     */
    public function getList($page,$type=1){
        $return       = [];
        $length       = config('paginate.list_rows');
        if($page==1){
            $start    = 0;
        }else{
            $start    = ($page-1)*$length;
        }
        $year    = date( "Y",time())-1;
        $month   = date( "M",time());
        $day     = date( "d",time());
        $time    = strtotime( $year."-"."$month"."-".$day." 00:00:00");
        $list    = $this->alias('i')
                        ->where(' i.status = 1 and i.create_time > '.$time)
                        ->field("SQL_CALC_FOUND_ROWS i.*")
                        ->order(" i.is_hot desc ,i.id desc ")
                        ->limit($start, $length)->select();
        
        $result = $this->query('SELECT FOUND_ROWS() as count');
        $total  = $result[0]['count'];
        
        foreach ($list as $key => $value) {
            if($type==1){
                $model      = model("dict");
                $dictList   = $model->getTopDictList($value['industry_pro']);
                $list[$key]['dict_list']        = $dictList;
                
                $modelP     = model("Project");
                $proList    = $modelP->getRelatedOne($value['industry_pro']);
                if(empty($proList)){
                    $list[$key]['related_id']     = 0;
                    $list[$key]['related_title']  = '';
                    $list[$key]['related_url']    = '';
                }else{
                    $list[$key]['related_id']     = $proList['id'];
                    $list[$key]['related_title']  = $proList['name'];
                    $list[$key]['related_url']    = "/home/project/index/id/".$proList['id'];
                }
            }else{
                $model      = model("dict");
                $dictList   = $model->getTopDictList($value['industry_org']);
                $list[$key]['dict_list']     = $dictList;
                
                $modelO     = model("Organize");
                $orgList    = $modelO->getRelatedOne($value['industry_org']);
                
                if(empty($orgList)){
                    $list[$key]['related_id']     = 0;
                    $list[$key]['related_title']  = '';
                    $list[$key]['related_url']    = '';
                }else{
                    $list[$key]['related_id']     = $orgList['id'];
                    $list[$key]['related_title']  = $orgList['name'];
                    $list[$key]['related_url']    = "/home/organize/index/id/".$orgList['id'];
                }
                
            }
            
            $list[$key]['short_des']    = subStrLen($value['des'],50);
            $list[$key]['item_url']     = "/home/industry_report/index/id/".$value['id'];
            $list[$key]['news_date']    = substr($value['create_time'],0,10);
        }
        if(empty($list)){
            $return['code']            = 201;
            $return['msg']             = "列表没有数据";
            $return['data']            = "";
            $return['page']            = 0;
            $return['ptotal']          = 0;
        }else{
            
            $return['code']            = 200;
            $return['msg']             = "";
            $return['data']            = $list;
            $return['page']            = ceil($total/$length);
            $return['ptotal']          = $total;
        }
        faLog($return['page']);
        return $return;
    }
}