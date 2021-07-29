<?php
/*
 * @Description:
 * @Author: freeair
 * @Date: 2021-06-25 11:16:41
 * @LastEditors: freeair
 * @LastEditTime: 2021-07-29 23:35:52
 */

namespace App\Controllers;

use CodeIgniter\API\ResponseTrait;

class GeneratorEvent extends BaseController
{
    use ResponseTrait;

    // 机组事件
    public function getGeneratorEvent()
    {

        $res['code'] = EXIT_SUCCESS;
        // $res['data'] = ['data' => $result];

        return $this->respond($res);
    }

    public function newGeneratorEvent()
    {
        // 检查请求数据
        if (!$this->validate('GeneratorEventNew')) {
            $res['error'] = $this->validator->getErrors();

            $res['code'] = EXIT_ERROR;
            $res['msg']  = '请求数据无效！';
            return $this->respond($res);
        }

        $client = $this->request->getJSON(true);

        // 取出检验后的数据
        $eventData = [
            'station_id'   => $client['station_id'],
            'generator_id' => $client['generator_id'],
            'event'        => $client['event'],
            'timestamp'    => $client['timestamp'],
            'creator'      => $client['creator'],
        ];

        // $client = $this->request->getJSON(true);

        // $model  = new RoleMode();
        // $result = $model->insert($client);

        // if (is_numeric($result)) {
        //     $res['code'] = EXIT_SUCCESS;
        //     $res['msg']  = '完成添加！';
        //     $res['data'] = ['id' => $result];
        // } else {
        //     $res['code'] = EXIT_ERROR;
        //     $res['msg']  = '添加失败，稍后再试！';
        // }

        $res['code']      = EXIT_SUCCESS;
        $res['eventData'] = $eventData;
        return $this->respond($res);
    }

    // public function updateRole()
    // {
    //     $client = $this->request->getJSON(true);

    //     $model  = new RoleMode();
    //     $result = $model->save($client);

    //     if ($result) {
    //         $res['code'] = EXIT_SUCCESS;
    //         $res['msg']  = '完成修改！';
    //     } else {
    //         $res['code'] = EXIT_ERROR;
    //         $res['msg']  = '修改失败，稍后再试！';
    //     }

    //     return $this->respond($res);
    // }

    // public function delRole()
    // {
    //     $client = $this->request->getJSON(true);

    //     $model  = new RoleMode();
    //     $result = $model->delete($client['id']);

    //     if ($result === true) {
    //         $res['code'] = EXIT_SUCCESS;
    //         $res['msg']  = '完成删除！';
    //     } else {
    //         $res['code'] = EXIT_ERROR;
    //         $res['msg']  = '删除失败，稍后再试！';
    //     }

    //     return $this->respond($res);
    // }
}
