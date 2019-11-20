<?php

namespace app\home\validate;

use think\Validate;

class SendSms extends Validate
{
    protected $rule = [
        ['mobile', 'require|length:11,11', '请输入手机号|手机号不正确'],
        ['content', 'require', '请输入发送的短信内容'],
        
    ];

    protected $scene = [
        'add'   => ['mobile','content'],
    ];
}