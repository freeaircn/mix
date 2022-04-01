<?php
/*
 * @Description:
 * @Author: freeair
 * @Date: 2021-06-27 20:47:50
 * @LastEditors: freeair
 * @LastEditTime: 2022-04-01 10:42:16
 */

namespace App\Models\Account;

use CodeIgniter\Model;

class AvatarModel extends Model
{
    protected $DBGroup = 'mix';

    protected $table         = 'app_user_avatar';
    protected $primaryKey    = 'id';
    protected $allowedFields = ['name', 'path', 'size'];

    protected $useAutoIncrement = true;

    protected $returnType     = 'array';
    protected $useSoftDeletes = false;

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    //
    public function getAvatarPathAndNameById($id = null)
    {
        if (!is_numeric($id)) {
            return [];
        }

        $res = $this->select('name, path')
            ->where('id', $id)
            ->findAll();

        return isset($res[0]) ? $res[0] : [];
    }

    public function updateAvatarById($id = null, string $path = '', string $name = '')
    {
        if (!is_numeric($id) || empty($path) || empty($name)) {
            return false;
        }

        $data = [
            'path' => $path,
            'name' => $name,
        ];

        return $this->update($id, $data);
    }

    // public function deleteAvatarById($id = null)
    // {
    //     if (!is_numeric($id)) {
    //         return false;
    //     }

    //     return $this->delete($id);
    // }
}
