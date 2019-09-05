<?php

namespace app\home\model;

use think\Model;

class MaterialLibrary extends Model
{
    public function getImgInfoById($id){
        $info = $this->where(['ml_id' => $id])->find();
        return $info;
    }
}