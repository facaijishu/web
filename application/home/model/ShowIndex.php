<?php

namespace app\home\model;

use think\Model;

class ShowIndex extends Model
{

    
    /**
     * 获取广告位项目和机构
     * @return 数据集
     */
    public function getlist($login){ 
        $result = [];
        if($login==1){
            $res                 = $this->alias('s')
                                        ->join('Project p','p.pro_id = s.show_id')
                                        ->join('Dict d','p.inc_industry = d.id')
                                        ->join('Dict d2','p.inc_sign = d2.id')
                                        ->where(['p.status' => 2 , 'p.flag' => 1])
                                        ->order("s.list_order desc")
                                        ->field([
                                            'p.pro_id'      =>'id',
                                            'p.pro_name'    =>'name',
                                            'd.value'       =>'label1',
                                            'd2.value'      =>'label2',
                                            'p.inc_industry',
                                            'p.company_name',
                                            'p.introduction',
                                            'p.view_num',
                                            'p.type',
                                        ])
                                        ->select();
            foreach ($res as $key => $item) {
                $res[$key]['bottom'] = subStrLen($item['introduction'],40);
                $res[$key]['link']   = "/home/project/index/id/".$item['id'];
            }                             
           $result['pro-data']       = ['data'   => $res];
           $result['org-data']       = ['data'   => []];
       
        }else{
            
            $res                 = $this->alias('s')
                                        ->join('Organize o','o.org_id = s.show_id')
                                        ->join('MaterialLibrary ml','o.top_img=ml.ml_id')
                                        ->where(['o.status' => 2 , 'o.flag' => 1])
                                        ->order("s.list_order desc")
                                        ->field([
                                            'o.org_id'            =>'id',
                                            'o.org_short_name'    =>'name',
                                            'o.inc_industry',
                                            'o.contacts',
                                            'o.position',
                                            'o.view_num',
                                            'o.type',
                                            'ml.url',
                                        ])
                                        ->select();
            foreach ($res as $key => $item) {
                $res[$key]['bottom']        = $item['contacts']."   |   ".$item['position'];
                $res[$key]['link']          = "/home/organize/index/id/".$item['id'];
                $model   = model("dict");
                $dict    = $model->getTopDictList($item['inc_industry']);
                $res[$key]['dict_list']     = $dict;
                $res[$key]['style_bg_url']  = "background:url('".config('material_img.upload_root').$item['url']."')  center no-repeat;";
            } 
                                        
                                        
        
            $result['pro-data']  = ['data'   => []];
            $result['org-data']  = ['data'   => $res];
        }
        return $result;
    }
   
}
