<?php

namespace App\Libraries;

class MixUtils
{
    public $maxPwdSizeBytes     = 254;
    public $hashMethod          = 'argon2'; // bcrypt or argon2
    public $bcryptDefaultCost   = 12; // Set cost according to your server benchmark - but no lower than 10 (default PHP value)
    public $argon2DefaultParams = [
        'memory_cost' => 1 << 14, // 16MB
        'time_cost'   => 4,
        'threads'     => PASSWORD_ARGON2_DEFAULT_THREADS,
    ];

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
     * @param array|\Config\MixUtils $config
     *
     * @return MixUtils
     */
    public function initialize($config)
    {
        // $this->clear();

        if ($config instanceof \Config\MixUtils) {
            $config = get_object_vars($config);
        }

        foreach (array_keys(get_class_vars(get_class($this))) as $key) {
            if (property_exists($this, $key) && isset($config[$key])) {
                $method = 'set' . ucfirst($key);

                if (method_exists($this, $method)) {
                    $this->$method($config[$key]);
                } else {
                    $this->$key = $config[$key];
                }
            }
        }

        return $this;
    }

    /**
     * @Description: 一维数组转换为树结构
     * @Author:
     * @Date:
     * @param array 一维数组
     * @param string ID Key
     * @param string 父ID Key
     * @param string 子数据Key
     * @return array
     */
    public function arr2tree($arr, $id = 'id', $pid = 'pid', $son = 'children')
    {
        list($tree, $map) = [[], []];
        foreach ($arr as $item) {
            $map[$item[$id]] = $item;
        }
        foreach ($arr as $item) {
            if (isset($item[$pid]) && isset($map[$item[$pid]])) {
                $map[$item[$pid]][$son][] = &$map[$item[$id]];
            } else {
                $tree[] = &$map[$item[$id]];
            }
        }
        unset($map);
        return $tree;
    }

    /**
     * Hashes the password to be stored in the database.
     *
     * @param string $password
     * @param string $identity
     *
     * @return false|string
     * @author Mathew
     */
    public function hash_password($password)
    {
        // $MAX_PASSWORD_SIZE_BYTES = $this->config->item('MAX_PASSWORD_SIZE_BYTES', 'app_config');
        // Check for empty password, or password containing null char, or password above limit
        // Null char may pose issue: http://php.net/manual/en/function.password-hash.php#118603
        // Long password may pose DOS issue (note: strlen gives size in bytes and not in multibyte symbol)
        if (empty($password) || strpos($password, "\0") !== false ||
            strlen($password) > $MAX_PASSWORD_SIZE_BYTES) {
            $this->app_log('error', "input of func hash_password checked failed.");
            return false;
        }

        $algo   = $this->get_hash_algo();
        $params = $this->get_hash_parameters();

        if ($algo !== false && $params !== false) {
            return password_hash($password, $algo, $params);
        }

        return false;
    }

    /** Retrieve hash algorithm according to options
     *
     * @return string|bool
     */
    public function get_hash_algo()
    {
        // $algo        = false;
        // $hash_method = $this->config->item('hash_method', 'app_config');
        // switch ($hash_method) {
        //     case 'bcrypt':
        //         $algo = PASSWORD_BCRYPT;
        //         break;

        //     case 'argon2':
        //         $algo = PASSWORD_ARGON2I;
        //         break;

        //     default:
        //         // Do nothing
        // }

        // return $algo;
    }

    /** Retrieve hash parameter according to options
     * @return array|bool
     */
    public function get_hash_parameters()
    {
        // $params      = false;
        // $hash_method = $this->config->item('hash_method', 'app_config');
        // switch ($hash_method) {
        //     case 'bcrypt':
        //         $params = [
        //             $this->config->item('bcrypt_default_cost', 'app_config'),
        //         ];
        //         break;

        //     case 'argon2':
        //         $params = $this->config->item('argon2_default_params', 'app_config');
        //         break;

        //     default:
        //         // Do nothing
        // }

        // return $params;
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
    public function resize_avatar_img($source_img, $new_image)
    {
        // if (empty($source_img) || empty($new_image)) {
        //     return false;
        // }

        // $config['image_library'] = 'gd2';
        // $config['source_image']  = $source_img;
        // $config['new_image']     = $new_image;
        // // $config['create_thumb']   = true;
        // $config['maintain_ratio'] = true;
        // $config['width']          = 200;
        // $config['height']         = 200;

        // $this->load->library('image_lib', $config);

        // if (!$this->image_lib->resize()) {
        //     // echo $this->image_lib->display_errors();
        //     return false;
        // } else {
        //     return true;
        // }

    }

    public function update_user_prop_in_session($data = [])
    {
        // if (empty($data)) {
        //     return false;
        // }

        // // 1 读取当前session数据
        // $current = $this->session->userdata();
        // if (empty($current['acl'])) {
        //     return false;
        // }

        // // 2 查找并更改
        // foreach ($data as $i => $i_value) {
        //     foreach ($current as $j => $j_value) {
        //         if ($i === $j) {
        //             $this->session->set_userdata($i, $i_value);
        //         }
        //     }
        // }

        // return true;
    }
}
