<?php
namespace app\home\controller;

class Dict extends Base
{
    /**
     * 业务类型
     */
    public function serviceApi(){
        $model = model("Dict");
        $list  = $model->getService();
        $this->result($list,"200","",'json');
    }
    
    /**
     * 所属行业
     */
    public function industryApi(){
        $model = model("Dict");
        $list  = $model->getIndustry();
        $this->result($list,"200","",'json');
    }
    
    /**
     * 获取
     */
    public function dictApi(){
        
        
        $model   = model("Dict");
        $list_s  = $model->getService();
        
        
        $model   = model("Dict");
        $list_i  = $model->getIndustry();
        
        $list =  [
            'data_s'    => $list_s,
            'data_i'    => $list_i,
        ];
        
        $this->result($list,"200","",'json');
    }
}
