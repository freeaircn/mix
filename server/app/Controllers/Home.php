<?php
/*
 * @Description:
 * @Author: freeair
 * @Date: 2021-06-25 11:02:42
 * @LastEditors: freeair
 * @LastEditTime: 2021-06-28 22:42:47
 */

namespace App\Controllers;

class Home extends BaseController
{
    public function index()
    {
        return view('welcome_message');
    }
}
