<?php
/*
 * @Description:
 * @Author: freeair
 * @Date: 2021-06-27 20:47:50
 * @LastEditors: freeair
 * @LastEditTime: 2021-09-26 20:04:17
 */

namespace App\Models\Admin;

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

    public function newDefaultAvatarBySex(string $sex = '')
    {
        if (empty($sex)) {
            return false;
        }

        $config = config('MixUtils');
        if ($sex == '男') {
            $data = [
                'path' => $config->defaultAvatarPath,
                'name' => $config->defaultAvatarMale,
            ];
        } else {
            $data = [
                'path' => $config->defaultAvatarPath,
                'name' => $config->defaultAvatarFemale,
            ];
        }

        return $this->insert($data);

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

    public function deleteAvatarById($id = null)
    {
        if (!is_numeric($id)) {
            return false;
        }

        return $this->delete($id);
    }
}
