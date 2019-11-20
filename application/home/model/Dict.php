<?php

namespace app\home\model;

use think\Model;

class Dict extends Model
{
    //获取业务类型一级和二级标签
    public function getService()
    {
        //一级
        $dict    = $this->alias('d')
                        ->join('DictType dt','d.dt_id=dt.dt_id')
                        ->field('d.id,d.fid,d.value')
                        ->where(['d.fid'=>0,'d.status'=>1,'dt.sign'=>'service_type'])
                        ->order(['list_order'=>'desc'])
                        ->select();
        //二级
        foreach ($dict as $key => $value) {
            $dict2   = $this->alias('d')
                            ->join('DictType dt','d.dt_id=dt.dt_id')
                            ->field('d.id,d.fid,d.value')
                            ->where(['d.fid'=>$value['id'],'d.status'=>1,'dt.sign'=>'service_type'])
                            ->order(['list_order'=>'desc'])
                            ->select();
            if (!empty($dict2)) {
                $dict[$key]['sub'] = $dict2;
            }
        }
        return $dict;
    }
    
    
    //获取所属行业一级和二级标签
    public function getIndustry()
    {
        $dict    = $this->alias('d')
                        ->join('DictType dt','d.dt_id=dt.dt_id')
                        ->field('d.id,d.fid,d.value')
                        ->where(['d.fid'=>0,'d.status'=>1,'dt.sign'=>'industry'])
                        ->order(['list_order'=>'desc'])
                        ->select();
 
        //二级
        foreach ($dict as $key => $value) {
            $dict2   = $this->alias('d')
                            ->join('DictType dt','d.dt_id=dt.dt_id')
                            ->field('d.id,d.fid,d.value')
                            ->where(['d.fid'=>$value['id'],'d.status'=>1,'dt.sign'=>'industry'])
                            ->order(['list_order'=>'desc'])
                            ->select();
            if (!empty($dict2)) {
                $dict[$key]['sub'] = $dict2;
            }
        }
        return $dict;
    }
    
    //获取投融规模的标签
    public function getSize()
    {
        $dict    = $this->alias('d')
                        ->join('DictType dt','d.dt_id=dt.dt_id')
                        ->field('d.id,d.value')
                        ->where(['d.status'=>1,'dt.sign'=>'size'])
                        ->order(['list_order'=>'desc'])
                        ->select();
        return $dict;
    }
    
    //获取所在省份的标签
    public function getProvince()
    {
        $dict    = $this->alias('d')
                        ->join('DictType dt','d.dt_id=dt.dt_id')
                        ->field('d.id,d.value')
                        ->where(['d.status'=>1,'dt.sign'=>'to_province'])
                        ->order(['list_order'=>'desc'])
                        ->select();

        return $dict;
    }
    
    /**
     * 获取标签一级集合
     * @param string 标签二级字符串以逗号分割
     * @return string|unknown
     */
    public function getTopDictList($id = '')
    {
        $sql   = "SELECT  id,`value` as dict_name from fic_dict where id in ( SELECT distinct  fid  from fic_dict where id in (". $id ."))";
        $list  = $this->query($sql);

        return $list;
    }
    
    /**
     * 获取标签二级集合
     * @param string 行业二级字符串以逗号分割
     * @return string|unknown
     */
    public function getSecDictList($id = '')
    {
        $sql   = "SELECT  id,`value` as dict_name from fic_dict where id in  (". $id .")";
        $list  = $this->query($sql);
        
        return $list;
    }
    
    /**
     * 获取标签名称字符串
     * @param string $id
     * @return string|unknown
     */
    public function getDictStr($id = '')
    {
        $result  = $this->alias("d")
                        ->field('d.id,d.value')
                        ->where(" id in (" .$id." ) ")
                        ->select();
        $str = '';
        foreach ($result as $key => $item) {
            if($str==''){
                $str  = $item['value'];
            }else{
                $str  = $str."、".$item['value'];
            }
        }
        return $str;
    }
    
    
    /**
     * 获取标签二级集合
     * @param string 行业二级字符串以逗号分割
     * @return string|unknown
     */
    public function getDictList($id = '')
    {
        
        $result  = $this->alias("d")
                        ->field('d.id ,d.value as dict_name')
                        ->where(" id = " .$id)
                        ->find();
        return $result;
    }
    
}