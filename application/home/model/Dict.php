<?php

namespace app\home\model;

use think\Model;

class Dict extends Model
{
    //��ȡҵ������һ���Ͷ�����ǩ
    public function getService()
    {
        //һ��
        $dict    = $this->alias('d')
                        ->join('DictType dt','d.dt_id=dt.dt_id')
                        ->field('d.id,d.fid,d.value')
                        ->where(['d.fid'=>0,'d.status'=>1,'dt.sign'=>'service_type'])
                        ->order(['list_order'=>'desc'])
                        ->select();
        //����
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
    
    
    //��ȡ������ҵһ���Ͷ�����ǩ
    public function getIndustry()
    {
        $dict    = $this->alias('d')
                        ->join('DictType dt','d.dt_id=dt.dt_id')
                        ->field('d.id,d.fid,d.value')
                        ->where(['d.fid'=>0,'d.status'=>1,'dt.sign'=>'industry'])
                        ->order(['list_order'=>'desc'])
                        ->select();
 
        //����
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
    
    //��ȡͶ�ڹ�ģ�ı�ǩ
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
    
    //��ȡ����ʡ�ݵı�ǩ
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
     * ��ȡ��ǩһ������
     * @param string ��ǩ�����ַ����Զ��ŷָ�
     * @return string|unknown
     */
    public function getTopDictList($id = '')
    {
        $sql   = "SELECT  id,`value` as dict_name from fic_dict where id in ( SELECT distinct  fid  from fic_dict where id in (". $id ."))";
        $list  = $this->query($sql);

        return $list;
    }
    
    /**
     * ��ȡ��ǩ��������
     * @param string ��ҵ�����ַ����Զ��ŷָ�
     * @return string|unknown
     */
    public function getSecDictList($id = '')
    {
        $sql   = "SELECT  id,`value` as dict_name from fic_dict where id in  (". $id .")";
        $list  = $this->query($sql);
        
        return $list;
    }
    
    /**
     * ��ȡ��ǩ�����ַ���
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
                $str  = $str."��".$item['value'];
            }
        }
        return $str;
    }
    
    
    /**
     * ��ȡ��ǩ��������
     * @param string ��ҵ�����ַ����Զ��ŷָ�
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