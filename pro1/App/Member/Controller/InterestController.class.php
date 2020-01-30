<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/6/12
 * Time: 12:18
 */

namespace Member\Controller;


class InterestController
{
    public function index(){
       HOOK('interest', "7");
    }
}