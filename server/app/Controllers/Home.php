<?php
/*
 * @Description:
 * @Author: freeair
 * @Date: 2021-06-25 11:16:41
 * @LastEditors: freeair
 * @LastEditTime: 2023-05-16 20:23:42
 */

namespace App\Controllers;

// use CodeIgniter\API\ResponseTrait;

class Home extends BaseController
{
    // use ResponseTrait;

    public function index()
    {
        // return view('welcome_message');
        return view('home.html');
    }
}
