<?php

namespace app\home\model;

use think\Model;

class PcConsultation extends Model
{
    public function getPcConsultationList($length = 0){
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
        }
        return $result;
    }
    public function getPcConsultationInfoById($id){
        $result = $this->where(['con_id' => $id])->find();
        return $result;
    }
}