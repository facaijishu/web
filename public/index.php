<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

// [ 应用入口文件 ]
// 定义__ROOT__
if (!defined('__ROOT__')) {
    $_root = rtrim(dirname(rtrim($_SERVER['SCRIPT_NAME'], '/')), '/');
    define('__ROOT__', ((DIRECTORY_SEPARATOR == $_root) ? '' : $_root));
}
// 定义应用目录
define('APP_PATH', __DIR__ . '/../application/');
// 定义运行时目录
define('RUNTIME_PATH', APP_PATH . '/runtime/');

// 加载框架引导文件
require __DIR__ . '/../thinkphp/start.php';