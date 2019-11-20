<?php

namespace app\home\model;

use think\Model;

class QrcodeDirection extends Model
{
    public function getQrcodeDirectionById($id){
        $info = $this->where(['id' => $id])->find();
        return $info;
    }
}