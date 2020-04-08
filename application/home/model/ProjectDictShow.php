<?php

namespace app\home\model;
use think\Model;

class ProjectDictShow extends Model
{   
	public function getInfo($id){
        if(empty($id)){
            $this->error = '该项目ID不存在';
            return false;
        }
        $info = $this->where(['id'=>$id])->find();
        return $info;
    }

}