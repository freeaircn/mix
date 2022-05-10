<?php
/*
 * @Description:
 * @Author: freeair
 * @Date: 2021-06-27 20:47:50
 * @LastEditors: freeair
 * @LastEditTime: 2022-05-10 21:02:20
 */

namespace App\Models\Common;

use CodeIgniter\Model;

class MenuModel extends Model
{
    protected $DBGroup;
    protected $table;

    protected $primaryKey    = 'id';
    protected $allowedFields = ['pid', 'title', 'path', 'redirect', 'name', 'component', 'hidden', 'hideChildrenInMenu', 'meta_hidden', 'icon', 'keepAlive', 'hiddenHeaderContent', 'permission', 'target'];

    protected $useAutoIncrement = true;

    protected $returnType     = 'array';
    protected $useSoftDeletes = false;

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    public function __construct()
    {
        $config        = config('MyGlobalConfig');
        $this->DBGroup = $config->dbName;
        $this->table   = $config->dbPrefix . 'menu';
        parent::__construct();
    }

    public function getMenuAllRecords(array $fields = null)
    {
        $selectSql = '';
        if (empty($fields)) {
            $selectSql = 'id, pid, title, path, redirect, name, component, hidden, hideChildrenInMenu, meta_hidden, icon, keepAlive, hiddenHeaderContent, permission, target, updated_at';
        } else {
            foreach ($fields as $key) {
                $selectSql = $selectSql . $key . ', ';
            }
        }

        $builder = $this->select($selectSql)->orderBy('id', 'ASC');
        $db      = $builder->findAll();

        return $db;
    }

    public function getMenuRecordsByIds(array $fields = null, array $ids = null)
    {
        if (empty($ids)) {
            return [];
        }

        $selectSql = '';
        if (empty($fields)) {
            $selectSql = 'id, pid, title, path, redirect, name, component, hidden, hideChildrenInMenu, meta_hidden, icon, keepAlive, hiddenHeaderContent, permission, target, updated_at';
        } else {
            foreach ($fields as $key) {
                $selectSql = $selectSql . $key . ', ';
            }
        }

        $db = $this->select($selectSql)
            ->whereIn('id', $ids)
            ->orderBy('id', 'ASC')
            ->findAll();

        return $db;
    }

    // public function getMenu($queryParam = [])
    // {
    //     $selectSql = 'id, pid, name, path, component, redirect, hideChildrenInMenu, title, icon, keepAlive, meta_hidden, hiddenHeaderContent, hidden, permission, target';
    //     $builder   = $this->select($selectSql);

    //     if (isset($queryParam['type']) && $queryParam['type'] === '1') {
    //         $builder->where('type', '1');
    //     }
    //     if (isset($queryParam['pageId']) && !empty($queryParam['pageId'])) {
    //         $builder->whereIn('id', $queryParam['pageId']);
    //     }
    //     $builder->orderBy('id', 'ASC');
    //     $data = $builder->findAll();

    //     $menus = [];
    //     if (!empty($data)) {
    //         foreach ($data as $item) {
    //             $menu = [];
    //             $meta = [];
    //             //
    //             $menu['id']   = $item['id'];
    //             $menu['pid']  = $item['pid'];
    //             $menu['name'] = $item['name'];
    //             $menu['path'] = $item['path'];
    //             if ($item['component'] !== '') {
    //                 $menu['component'] = $item['component'];
    //             }
    //             if ($item['redirect'] !== '') {
    //                 $menu['redirect'] = $item['redirect'];
    //             }
    //             $menu['hidden']             = $item['hidden'];
    //             $menu['hideChildrenInMenu'] = $item['hideChildrenInMenu'];
    //             // if ($item['hidden'] === '1') {
    //             //     $menu['hidden'] = '1';
    //             // }
    //             // if ($item['hideChildrenInMenu'] === '1') {
    //             //     $menu['hideChildrenInMenu'] = '1';
    //             // }
    //             //
    //             $meta['title'] = $item['title'];
    //             if ($item['icon'] !== '') {
    //                 $meta['icon'] = $item['icon'];
    //             }
    //             if ($item['keepAlive'] === '1') {
    //                 $meta['keepAlive'] = '1';
    //             }
    //             if ($item['meta_hidden'] === '1') {
    //                 $meta['hidden'] = '1';
    //             }
    //             if ($item['hiddenHeaderContent'] === '1') {
    //                 $meta['hiddenHeaderContent'] = '1';
    //             }
    //             if ($item['permission'] !== '') {
    //                 $meta['permission'] = [];
    //             }
    //             if ($item['target'] !== '') {
    //                 $meta['target'] = $item['target'];
    //             }
    //             //
    //             $menu['meta'] = $meta;
    //             $menus[]      = $menu;
    //         }
    //     }

    //     helper('my_array');
    //     $res = my_arr2tree($menus);

    //     return $res;
    // }

    // public function getApiAclByMenuId(array $menuId = null)
    // {
    //     if (empty($menuId)) {
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
}
