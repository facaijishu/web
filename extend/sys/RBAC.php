<?php

// +----------------------------------------------------------------------
// | RBAC后台权限控制
// +----------------------------------------------------------------------

namespace sys;

use app\console\service\User;
use think\Request;

class RBAC {

    /**
     * 当前登录下权限检查
     * @param type $map [模块/控制器/方法]，没有时，自动获取当前进行判断
     * @return boolean
     */
    static public function authenticate($map = '') {
        if (self::checkLogin() == false) {
            return false;
        }
        //是否超级管理员
        if (User::getInstance()->isAdmin() === true) {
            return true;
        }
        //查询是否有权限
        return D('Admin/Access')->isCompetence($map);
    }

    //用于检测用户权限的方法,并保存到Session中，登陆成功以后，注册有权限
    static function saveAccessList($authId = null) {
        if (null === $authId){
            $authId = User::getInstance()->id;
        }
        // 如果使用普通权限模式，保存当前用户的访问权限列表
        // 对管理员开发所有权限
        if (config('user_auth_type') != 2 && User::getInstance()->isAdmin($authId) !== true){
            session("_ACCESS_LIST", RBAC::getAccessList($authId));
        }
        return;
    }

    //检查当前操作是否需要认证 第二步
    static function checkAccess() {
        //如果项目要求认证，并且当前模块需要认证，则进行权限认证
        if (config('user_auth_on')) {
            //控制器
            $controller = array();
            //动作
            $action = array();
            if ("" != config('require_auth_module')) {
                //需要认证的模块
                $controller['yes'] = explode(',', strtoupper(config('require_auth_module')));
            } else {
                //无需认证的模块
                $controller['no'] = explode(',', strtoupper(config('not_auth_module')));
            }
            //检查当前模块是否需要认证
            if ((!empty($controller['no']) && !in_array(strtoupper(Request::instance()->controller()), $controller['no'])) || (!empty($controller['yes']) && in_array(strtoupper(Request::instance()->controller()), $controller['yes']))) {
                if ("" != config('require_auth_action')) {
                    //需要认证的操作
                    $action['yes'] = explode(',', strtoupper(config('require_auth_action')));
                } else {
                    //无需认证的操作
                    $action['no'] = explode(',', strtoupper(config('not_auth_action')));
                }
                //检查当前操作是否需要认证
                if ((!empty($action['no']) && !in_array(strtoupper(Request::instance()->action()), $action['no'])) || (!empty($action['yes']) && in_array(strtoupper(Request::instance()->action()), $action['yes']))) {
                    return true;
                } else {
                    return false;
                }
            } else {
                return false;
            }
        }
        return false;
    }

    // 登录检查
    static public function checkLogin() {
        //检查当前操作是否需要认证
        if (RBAC::checkAccess()) {
            //检查认证识别号
            if (User::getInstance()->isLogin() == false) {
                return false;
            }
        }
        return true;
    }

    //权限认证的过滤器方法 第一步
    static public function AccessDecision($access_id) {
        //检查是否需要认证
        if (RBAC::checkAccess()) {
            //存在认证识别号，则进行进一步的访问决策
            $accessGuid = md5('access_guid' . $access_id);
            //判断是否超级管理员，是无需进行权限认证
            if (User::getInstance()->isAdmin() !== true) {
                //认证类型 1 登录认证 2 实时认证
                if (config('user_auth_type') == 2) {
                    //加强验证和即时验证模式 更加安全 后台权限修改可以即时生效
                    //通过数据库进行访问检查
                    $access_list = RBAC::getAccessList(User::getInstance()->uid);
                } else {
                    // 如果是管理员或者当前操作已经认证过，无需再次认证
                    if (session($accessGuid)) {
                        return true;
                    }
                    //登录验证模式，登录后保存的可访问权限列表
                    $access_list = session("_ACCESS_LIST");
                }
                //判断是否为组件化模式，如果是，验证其全模块名
                if (!in_array($access_id, $access_list)) {
                    //验证登录
                    if (self::checkLogin() == true) {
                        //做例外处理，只要有管理员帐号，都有该项权限
                        if (Request::instance()->module() == "console" && in_array(Request::instance()->controller(), array("Index")) && in_array(Request::instance()->action(), array("index"))) {
                            session($accessGuid, true);
                            return true;
                        }
                        //如果是public_开头的验证通过。
                        if (substr(Request::instance()->action(), 0, 6) == 'public') {
                            session($accessGuid, true);
                            return true;
                        }
                        //内容模块特殊处理
                        /*if ($access_id == 'Content' && Request::instance()->controller() == 'Content') {
                            session($accessGuid, true);
                            return true;
                        }*/
                    }
                    session($accessGuid, false);
                    return false;
                } else {
                    session($accessGuid, true);
                }
            } else {
                //超级管理员直接验证通过，且检查是否登录
                if (self::checkLogin()) {
                    return true;
                }
                return false;
            }
        }
        return true;
    }

    /**
      +----------------------------------------------------------
     * 取得当前认证号的所有权限列表
      +----------------------------------------------------------
     * @param integer $authId 用户ID
      +----------------------------------------------------------
     * @access public
      +----------------------------------------------------------
     */
    static public function getAccessList($authId) {
        $access_list = [];
        //用户信息
        $user = User::getInstance()->getInfo();
        if (empty($user)) {
            return $access_list;
        }
        //角色ID
        $role_id = $user->role_id;
        //检查角色
        $role_info = model('Role')->getRole($role_id);
        if (empty($role_info) || empty($role_info->status)) {
            return $access_list;
        }
        //该角色全部权限
        $access = model('RoleAccess')->getAccessList($role_id);
        $access_list = array();
        foreach ($access as $item) {
            $access_list[] = $item->menu_id;
        }
        return $access_list;
    }

}
