<?php

namespace app\home\model;

use think\Model;

class PcConsultation extends Model
{
    public function getPcConsultationList($length = 4){
        if($length === 0){
            $result = $this->alias('con')
                    ->join("dict d" , 'con.type = d.id')
                    ->where(['con.status' => 1 , 'd.des' => ''])
                    ->order("is_hot desc,con_id desc")
                    ->field("con.*,d.value")
                    ->select();
        } else {
            $result = $this->alias('con')
                    ->join("dict d" , 'con.type = d.id')
                    ->where(['con.status' => 1 , 'd.des' => ''])
                    ->order("is_hot desc,con_id desc")
                    ->field("con.*,d.value")
                    ->limit($length)
                    ->select();
        }
        $model = model("MaterialLibrary");
        foreach ($result as $key => $value) {
            $url = $model->getImgInfoById($value['top_img']);
            $result[$key]['top_img_url'] = 'http://fin.jrfacai.com/uploads/'.$url['url'];
            $result[$key]['short_des']   = subStrLen($value['des'],60);
        }
        return $result;
    }
    
    
    /**
     * 获取项目列表
     * @param 当前页数 $page
     * @return number[]|string[]|NULL[]|unknown[]
     */
    public function getList($page){
        $return       = [];
        $length       = 20;
        if($page==1){
            $start    = 0;
        }else{
            $start    = ($page-1)*$length;
        }
        
        $year    = date( "Y",time())-1;
        $month   = date( "M",time());
        $day     = date( "d",time());
        $time    = strtotime( $year."-"."$month"."-".$day." 00:00:00");
        $list    = $this->alias('con')
                        ->join("dict d" , 'con.type = d.id')
                        ->where(' con.status = 1 and con.create_time > '.$time)
                        ->field("SQL_CALC_FOUND_ROWS con.*,d.value")
                        ->order(" con.is_hot desc ,con.con_id desc ")
                        ->limit($start, $length)->select();
        
        $result = $this->query('SELECT FOUND_ROWS() as count');
        $total  = $result[0]['count'];
        
        $model = model("MaterialLibrary");
        foreach ($list as $key => $value) {
            $url = $model->getImgInfoById($value['top_img']);
            $list[$key]['top_img_url']  = config('material_img.upload_root').$url['url'];
            $list[$key]['short_des']    = subStrLen($value['des'],50);
            $list[$key]['item_url']     = "/home/consultation/index/id/".$value['con_id'];
            $list[$key]['style_bg_url'] = "background: url('".config('material_img.upload_root').$url['url']."')  center no-repeat;";
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
        return $return;
    }
    
    
    
    public function getPcConsultationInfoById($id){
        $result = $this->where(['con_id' => $id])->find();
        return $result;
    }
}