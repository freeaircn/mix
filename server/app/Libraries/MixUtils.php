<?php
/*
 * @Description:
 * @Author: freeair
 * @Date: 2021-09-06 01:17:02
 * @LastEditors: freeair
 * @LastEditTime: 2022-04-01 10:47:51
 */

namespace App\Libraries;

class MixUtils
{
    public function __construct($config = null)
    {
        $this->initialize($config);
        // if (!isset(static::$func_overload)) {
        //     static::$func_overload = (extension_loaded('mbstring') && ini_get('mbstring.func_overload'));
        // }
    }

    /**
     * Initialize preferences
     *
     * @param array|\Config\ $config
     *
     * @return
     */
    public function initialize($config)
    {
        // $this->clear();

        if ($config instanceof \Config\MixUtils) {
            $config = get_object_vars($config);
        }

        foreach (array_keys(get_class_vars(get_class($this))) as $key) {
            if (property_exists($this, $key) && isset($config[$key])) {
                // $method = 'set' . ucfirst($key);

                // if (method_exists($this, $method)) {
                //     $this->$method($config[$key]);
                // } else {
                //     $this->$key = $config[$key];
                // }
                $this->$key = $config[$key];
            }
        }

        return $this;
    }

    /**
     * 根据id便利数据表，输出包括输入id的所有子节点id
     * @param int $id
     * @return array string
     */
    // function get_all_children_ids($id)
    // {
    //     $array[] = (string)$id;
    //     $temp_arr[] = (string)$id;
    //     do
    //     {
    //         $this->db->select('id');
    //         $this->db->where_in('pid', $temp_arr);
    //         $query = $this->db->get($this->tables['dict']);
    //         $res = $query->result_array();
    //         unset($temp_arr);
    //         foreach ($res as $k=>$v)
    //         {
    //             $array[] = (string)$v['id'];
    //             $temp_arr[] = (string)$v['id'];
    //         }
    //     }
    //     while (!empty($res));

    //     return $array;
    // }

    /**
     * @Description: 修改用户上传的头像图片大小
     * @Author: freeair
     * @Date: 2020-11-14 10:48:48
     * @param {str}
     * @return {*}
     */
    // public function resize_avatar_img($source_img, $new_image)
    // {
    //     // if (empty($source_img) || empty($new_image)) {
    //     //     return false;
    //     // }

    //     // $config['image_library'] = 'gd2';
    //     // $config['source_image']  = $source_img;
    //     // $config['new_image']     = $new_image;
    //     // // $config['create_thumb']   = true;
    //     // $config['maintain_ratio'] = true;
    //     // $config['width']          = 200;
    //     // $config['height']         = 200;

    //     // $this->load->library('image_lib', $config);

    //     // if (!$this->image_lib->resize()) {
    //     //     // echo $this->image_lib->display_errors();
    //     //     return false;
    //     // } else {
    //     //     return true;
    //     // }

    // }

    // public function update_user_prop_in_session($data = [])
    // {
    //     // if (empty($data)) {
    //     //     return false;
    //     // }

    //     // // 1 读取当前session数据
    //     // $current = $this->session->userdata();
    //     // if (empty($current['acl'])) {
    //     //     return false;
    //     // }

    //     // // 2 查找并更改
    //     // foreach ($data as $i => $i_value) {
    //     //     foreach ($current as $j => $j_value) {
    //     //         if ($i === $j) {
    //     //             $this->session->set_userdata($i, $i_value);
    //     //         }
    //     //     }
    //     // }

    //     // return true;
    // }
}
