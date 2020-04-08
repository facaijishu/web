<?php

namespace app\home\model;
use think\Model;

class Trilateral extends Model
{   
	public function getInfo($id){
        if(empty($id)){
            $this->error = '该项目ID不存在';
            return false;
        }
        $info = $this->where(['id'=>$id])->find();
        
        
        $info['top_img_url']    = "http://fin.jrfacai.com/uploads/".$info['top_img_url'];
        $info['index_img_url']  = "http://fin.jrfacai.com/uploads/".$info['index_img_url'];
        
        return $info;
    }
    
    /**
     * 获取列表
     * @param number $length
     * @return 数据集合
     */
    public function getList($length = 0){
        $result_info = [];
        if($length === 0){
            $result  = $this->alias('t')
                            ->where(['t.status' => 1])
                            ->order("t.list_order desc")
                            ->select();
        }else{
            $result  = $this->alias('t')
                            ->where(['t.status' => 1])
                            ->order("t.list_order desc")
                            ->limit($length)
                            ->select();
        }
        
        foreach ($result as &$v) {
            $v['top_url']    = "http://fin.jrfacai.com/uploads/".$v['top_img_url'];
            $v['index_url']  = "http://fin.jrfacai.com/uploads/".$v['index_img_url'];
        }
        
        $result_info = $result;
        return $result_info;
    }
    
    /**
     * 获取列表
     * @param number $length
     * @return 数据集合
     */
    public function getNearList($id,$length = 0){
        $result_info = [];
        if($length === 0){
            $result  = $this->alias('t')
                            ->where(['t.status' => 1,'t.id' => array('neq',$id)])
                            ->order("t.list_order desc")
                            ->select();
        }else{
            $result  = $this->alias('t')
                            ->where(['t.status' => 1,'t.id' => array('neq',$id)])
                            ->order("t.list_order desc")
                            ->limit($length)
                            ->select();
        }
        
        foreach ($result as &$v) {
            $v['top_url']    = "http://fin.jrfacai.com/uploads/".$v['top_img_url'];
            $v['index_url']  = "http://fin.jrfacai.com/uploads/".$v['index_img_url'];
        }
        
        $result_info = $result;
        return $result_info;
    }

}