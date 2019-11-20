<?php

namespace app\home\model;

use think\Model;

class Publish extends Model{
    
    /**
     * 获取项目列表
     * @param 当前页数 $page
     * @param 搜索关键字 $keyword
     * @return number[]|string[]|NULL[]|unknown[]
     */
    public function getList($page, $keyword='', $key_sign='', $key_industry='', $key_size='', $key_area=''){
        
        
        
        
        
        $return       = [];
        $length       = config('paginate.list_rows');
        if($page==1){
            $start    = 0;
        }else{
            $start    = ($page-1)*$length;
        }
        
        $pb     =  $this->alias('pb')
                        ->join('Member m','pb.uid=m.uid')
                        ->field([
                            'pb.id',
                            'm.uid',
                            'm.userPhoto',
                            'm.realName',
                            'm.company_jc',
                            'm.position',
                            'pb.content',
                            'pb.img_id',
                            'pb.create_time',
                            'pb.point_num',
                            'pb.comment_num',
                            'pb.type',
                        ]);
        
        
        //可显示的融资中项目
        $publish_sql = 'pb.is_del = 2 and pb.status = 1';
        
        
        if($keyword!==''){
            $publish_sql .= ' and ( m.realName like "%'.$keyword.'%" or m.company_jc like "%'.$keyword.'%" or m.position like "%'.$keyword.'%" or pb.content like "%'.$keyword.'%" ) ' ;
        }
        
        
        $list      = $pb->where($publish_sql)
        ->order(" pb.rank desc,pb.id desc ")
        ->limit($start, $length)->select();
        $result = $this->query('SELECT FOUND_ROWS() as count');
        $total  = $result[0]['count'];
        if(empty($list)){
            $return['code']            = 201;
            $return['msg']             = "列表没有数据";
            $return['data']            = "";
            $return['page']            = 0;
            $return['total']           = 0;
        }else{
            foreach ($list as $key => $item) {
                $list[$key]['id']           = $item['id'];
                //$list[$key]['name']         = $item['contacts']."|".$item['position'];
                //$list[$key]['bottom']       = $item['scale_min']." - ".$item['scale_max']."亿元";
                //$list[$key]['style_bg_url'] = "background:url('".config('material_img.upload_root').$item['url']."')  center no-repeat;";
            }
            $return['code']            = 200;
            $return['msg']             = "";
            $return['data']            = $list;
            $return['page']            = ceil($total/$length);
            $return['total']           = $total;
        }
        return $return;
    }
    
}