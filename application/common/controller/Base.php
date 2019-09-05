<?php
namespace app\common\controller;

use think\Controller;

class Base extends Controller
{
    protected $lang;

    public function __construct()
    {
        parent::__construct();
    }

    public function _initialize() {

    }
}
