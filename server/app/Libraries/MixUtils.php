<?php

namespace App\Libraries;

use CodeIgniter\HTTP\RequestInterface;

class MixUtils
{
    public $maxPwdSizeBytes     = 254;
    public $argon2DefaultParams = [
        'memory_cost' => 1 << 14, // 16MB
        'time_cost'   => 4,
        'threads'     => PASSWORD_ARGON2_DEFAULT_THREADS,
    ];

    /**
     * 登录
     */
    public $maxAttempts = 5;
    public $lockoutTime = 600; // 秒

    /**
     * 验证码
     */
    public $smsCodeTimeout = 600; // 秒

    /**
     * 默认头像
     */
    public $defaultAvatarPath   = 'avatar/default/';
    public $defaultAvatarMale   = 'male.jpg';
    public $defaultAvatarFemale = 'female.jpg';

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
    public function hashPassword($password)
    {
        // $MAX_PASSWORD_SIZE_BYTES = $this->config->item('MAX_PASSWORD_SIZE_BYTES', 'app_config');
        // Check for empty password, or password containing null char, or password above limit
        // Null char may pose issue: http://php.net/manual/en/function.password-hash.php#118603
        // Long password may pose DOS issue (note: strlen gives size in bytes and not in multibyte symbol)
        if (empty($password) || strpos($password, "\0") !== false ||
            strlen($password) > $this->maxPwdSizeBytes) {
            return false;
        }

        $algo   = PASSWORD_ARGON2I;
        $params = $this->argon2DefaultParams;

        if ($algo !== '') {
            return password_hash($password, $algo, $params);
        }

        return false;
    }

    public function verifyPassword($password, $hashPassword)
    {
        // $MAX_PASSWORD_SIZE_BYTES = $this->config->item('MAX_PASSWORD_SIZE_BYTES', 'app_config');
        // Check for empty password, or password containing null char, or password above limit
        // Null char may pose issue: http://php.net/manual/en/function.password-hash.php#118603
        // Long password may pose DOS issue (note: strlen gives size in bytes and not in multibyte symbol)
        if (empty($password) || empty($hashPassword) || strpos($password, "\0") !== false
            || strlen($password) > $this->maxPwdSizeBytes) {
            return false;
        }

        return password_verify($password, $hashPassword);
    }

    public function accessAuth(RequestInterface $request)
    {
        $url = $request->uri->getSegments();

        // 方法：登录，登出，重置密码，返回true
        if (isset($url[0]) && $url[0] === 'api' && isset($url[1]) && $url[1] === 'auth') {
            return true;
        }

        // 检查是否存在session数据。存在 - 已登录；不存在 - 未登录。
        $phone = session('phone');
        if (is_null($phone)) {
            return '用户未登录';
        }

        // ！！！超级用户，仅测试使用。
        // if ($phone == '13812345679') {
        //     return true;
        // }

        // API鉴权
        $acl = session('acl');
        if (is_null($acl) || empty($acl)) {
            return '用户没有权限';
        }
        $wanted = '';
        $method = $request->getMethod();

        if (isset($url[0]) && $url[0] === 'api') {
            for ($i = 1; $i < count($url); $i++) {
                $wanted = $wanted . $url[$i] . '/';
            }
            $wanted = rtrim($wanted, "/") . ':' . $method;
        }

        if (in_array($wanted, $acl) === false) {
            return '用户没有权限';
        }

        // 数据
        $station_in_session = session('belongToDeptId');
        $station_in_request = isset($_POST['station_id']) ? $_POST['station_id'] : null;
        if ($station_in_request !== null) {
            if ($station_in_request != $station_in_session) {
                return '没有访问数据的权限';
            }
        }
        $station_in_request = isset($_GET['station_id']) ? $_GET['station_id'] : null;
        if ($station_in_request !== null) {
            if ($station_in_request != $station_in_session) {
                return '没有访问数据的权限';
            }
        }

        return true;
    }

    public function isTimeout(string $timeName = null, string $time)
    {
        if (empty($timeName) || empty($time)) {
            return false;
        }

        // 验证码
        if ($timeName === 'SMS_CODE') {
            $timeout = $this->smsCodeTimeout;
            $now     = date("Y-m-d H:i:s");
            if (strtotime($now) - strtotime($time) > $timeout) {
                // 超期
                return true;
            } else {
                return false;
            }
        }

        return false;
    }

    public function getPlusOffsetDay(string $date = '', $offset = 0)
    {
        return date("Y-m-d", strtotime($offset . " days", strtotime($date)));
    }

    public function getDayOfPreviousWeek(string $date = '')
    {
        return date('Y-m-d', strtotime($date . ' -1 week'));
    }

    public function getFirstDayOfMonth(string $date = '', $offset = 0)
    {
        return date("Y-m-d", strtotime("first day of" . $offset . " month", strtotime($date)));
    }

    public function getLastDayOfMonth(string $date = '', $offset = 0)
    {
        return date("Y-m-d", strtotime("last day of" . $offset . " month", strtotime($date)));
    }

    public function getFirstDayOfQuarter(string $date = '', $offset = 0)
    {
        $year    = date('Y', strtotime($date));
        $quarter = ceil((date('n', strtotime($date))) / 3) + $offset;
        return date('Y-m-d', mktime(0, 0, 0, $quarter * 3 - 3 + 1, 1, $year));
    }

    public function getLastDayOfQuarter(string $date = '', $offset = 0)
    {
        $year    = date('Y', strtotime($date));
        $quarter = ceil((date('n', strtotime($date))) / 3) + $offset;
        return date('Y-m-d', mktime(23, 59, 59, $quarter * 3, date('t', mktime(0, 0, 0, $quarter * 3, 1, $year)), $year));
    }

    public function getLastDayOfYear(string $date = '', $offset = 0)
    {
        $year = date('Y', strtotime($date)) + $offset;
        return $year . '-12-31';
    }

    public function getFirstDayOfYear(string $date = '', $offset = 0, $include_time = false)
    {
        $year = date('Y', strtotime($date)) + $offset;
        if ($include_time) {
            return date('Y-m-d H:i:s', mktime(0, 0, 0, 1, 1, $year));
        } else {
            return date('Y-m-d', mktime(0, 0, 0, 1, 1, $year));
        }
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
