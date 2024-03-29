<?php
/*
 * @Description:
 * @Author: freeair
 * @Date: 2021-09-06 01:17:02
 * @LastEditors: freeair
 * @LastEditTime: 2023-02-26 21:45:56
 */

namespace App\Libraries;

class MyUtils
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

        if ($config instanceof \Config\MyUtils) {
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

    // public function getContentType(string $ext = null)
    // {
    //     $contentType = '';
    //     switch ($ext) {
    //         case 'jpeg':
    //             $contentType = 'image/jpeg';
    //             break;
    //         case 'jpg':
    //             $contentType = 'image/jpeg';
    //             break;
    //         case 'png':
    //             $contentType = 'image/png';
    //             break;

    //         default:
    //             # code...
    //             break;
    //     }
    // }

    public function getAllChild($array, $pid = 0, $level = 0)
    {
        if (empty($array)) {
            return [];
        }

        static $result = [];
        //
        $level++;
        foreach ($array as $key => $value) {
            if ($value['pid'] == $pid) {
                $result[] = $value;
                unset($array[$key]);
                $this->getAllChild($array, $value['id']);
            }
        }
        return $result;
    }
}
