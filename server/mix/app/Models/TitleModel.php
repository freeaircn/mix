<?php
/*
 * @Description:
 * @Author: freeair
 * @Date: 2021-06-27 20:47:50
 * @LastEditors: freeair
 * @LastEditTime: 2021-07-04 22:22:25
 */

namespace App\Models;

use CodeIgniter\Model;

class TitleModel extends Model
{
    protected $DBGroup = 'mix';

    protected $table         = 'app_title';
    protected $primaryKey    = 'id';
    protected $allowedFields = ['name', 'status', 'description'];

    protected $useAutoIncrement = true;

    protected $returnType     = 'array';
    protected $useSoftDeletes = false;

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    public function getTitle()
    {
        $res = $this->select('id, name, status, description, updated_at')
            ->orderBy('id', 'ASC')
            ->findAll();

        return $res;
    }
}
