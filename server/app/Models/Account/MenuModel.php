<?php
/*
 * @Description:
 * @Author: freeair
 * @Date: 2021-06-27 20:47:50
 * @LastEditors: freeair
 * @LastEditTime: 2022-05-07 22:26:08
 */

namespace App\Models\Account;

use CodeIgniter\Model;

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

    public function getByPageIds(array $fields = null, array $pageIds = null)
    {
        if (empty($pageIds)) {
            return [];
        }

        $selectSql = '';
        if (empty($fields)) {
            $selectSql = 'id, pid, name, path, component, redirect, hideChildrenInMenu, title, icon, keepAlive, meta_hidden, hiddenHeaderContent, hidden, permission, target';
        } else {
            foreach ($fields as $f) {
                $selectSql = $selectSql . $f . ', ';
            }
        }

        $builder = $this->select($selectSql);
        $builder->whereIn('id', $pageIds);
        $builder->orderBy('id', 'ASC');

        $db = $builder->findAll();

        return $db;
    }
}
