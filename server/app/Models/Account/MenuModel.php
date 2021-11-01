<?php
/*
 * @Description:
 * @Author: freeair
 * @Date: 2021-06-27 20:47:50
 * @LastEditors: freeair
 * @LastEditTime: 2021-11-01 20:34:48
 */

namespace App\Models\Account;

use CodeIgniter\Model;
use Config\Services;

class MenuModel extends Model
{
    protected $DBGroup = 'mix';

    protected $table      = 'app_menu';
    protected $primaryKey = 'id';

    protected $useAutoIncrement = true;

    protected $returnType     = 'array';
    protected $useSoftDeletes = false;

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    public function getMenu($queryParam = [])
    {
        $selectSQL = 'id, pid, name, path, component, redirect, hideChildrenInMenu, title, icon, keepAlive, meta_hidden, hiddenHeaderContent, hidden, permission, target';
        $builder   = $this->select($selectSQL);

        if (isset($queryParam['type']) && $queryParam['type'] === '1') {
            $builder->where('type', '1');
        }
        if (isset($queryParam['pageId']) && !empty($queryParam['pageId'])) {
            $builder->whereIn('id', $queryParam['pageId']);
        }
        $builder->orderBy('id', 'ASC');
        $data = $builder->findAll();

        $menus = [];
        if (!empty($data)) {
            foreach ($data as $item) {
                $menu = [];
                $meta = [];
                //
                $menu['id']   = $item['id'];
                $menu['pid']  = $item['pid'];
                $menu['name'] = $item['name'];
                $menu['path'] = $item['path'];
                if ($item['component'] !== '') {
                    $menu['component'] = $item['component'];
                }
                if ($item['redirect'] !== '') {
                    $menu['redirect'] = $item['redirect'];
                }
                $menu['hidden']             = $item['hidden'];
                $menu['hideChildrenInMenu'] = $item['hideChildrenInMenu'];
                // if ($item['hidden'] === '1') {
                //     $menu['hidden'] = '1';
                // }
                // if ($item['hideChildrenInMenu'] === '1') {
                //     $menu['hideChildrenInMenu'] = '1';
                // }
                //
                $meta['title'] = $item['title'];
                if ($item['icon'] !== '') {
                    $meta['icon'] = $item['icon'];
                }
                if ($item['keepAlive'] === '1') {
                    $meta['keepAlive'] = '1';
                }
                if ($item['meta_hidden'] === '1') {
                    $meta['hidden'] = '1';
                }
                if ($item['hiddenHeaderContent'] === '1') {
                    $meta['hiddenHeaderContent'] = '1';
                }
                if ($item['permission'] !== '') {
                    $meta['permission'] = [];
                }
                if ($item['target'] !== '') {
                    $meta['target'] = $item['target'];
                }
                //
                $menu['meta'] = $meta;
                $menus[]      = $menu;
            }
        }

        $utils = Services::mixUtils();
        $res   = $utils->arr2tree($menus);

        return $res;
    }

    // public function getMenuByColumnName($columnName = [])
    // {
    //     $selectSQL = '';
    //     foreach ($columnName as $name) {
    //         $selectSQL = $selectSQL . $name . ', ';
    //     }

    //     $builder = $this->select($selectSQL)->orderBy('id', 'ASC');
    //     $data    = $builder->findAll();

    //     // $utils = Services::mixUtils();
    //     // $res   = $utils->arr2tree($data);

    //     return $data;
    // }

    // public function getApiAclByMenuId(array $menuId = null)
    // {
    //     if (!is_array($menuId) || empty($menuId)) {
    //         return [];
    //     }

    //     $temp = $this->select('authority')
    //         ->where('type', '2')
    //         ->whereIn('id', $menuId)
    //         ->orderBy('id', 'ASC')
    //         ->findAll();

    //     $res = [];
    //     foreach ($temp as $value) {
    //         if (!empty($value['authority'])) {
    //             $res[] = $value['authority'];
    //         }
    //     }

    //     return $res;
    // }

    // public function getPageIdByMenuId(array $menuId = null)
    // {
    //     if (!is_array($menuId) || empty($menuId)) {
    //         return [];
    //     }

    //     $temp = $this->select('id')
    //         ->where('type', '1')
    //         ->whereIn('id', $menuId)
    //         ->orderBy('id', 'ASC')
    //         ->findAll();

    //     $res = [];
    //     foreach ($temp as $value) {
    //         $res[] = $value['id'];
    //     }

    //     return $res;
    // }
}
