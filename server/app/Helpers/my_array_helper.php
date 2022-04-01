<?php
/*
 * @Description:
 * @Author: freeair
 * @Date: 2022-03-31 19:43:41
 * @LastEditors: freeair
 * @LastEditTime: 2022-04-01 18:23:41
 */
/**
 * Array Helpers
 */

if (!function_exists('my_arr2tree')) {
    /**
     * 一维数组转换为树结构
     *
     * @author freeair
     * @DateTime 2022-03-31
     * @param array $arr 一维数组
     * @param string $id
     * @param string $pid
     * @param string $son
     * @return void
     */
    function my_arr2tree(array $arr, string $id = 'id', string $pid = 'pid', string $son = 'children')
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
}

if (!function_exists('my_get_all_children')) {

    function my_get_all_children(array $arr = [], int $pid = 0)
    {
        $res    = [];
        $keys[] = $pid;
        do {
            $mid_keys = [];
            foreach ($keys as $key) {
                foreach ($arr as $value) {
                    if ($value['pid'] == $key) {
                        $res[]      = $value;
                        $mid_keys[] = $value['id'];
                    }
                }
            }
            unset($keys);
            $keys = $mid_keys;
            unset($mid_keys);
        } while (!empty($keys));

        return $res;
    }
}
