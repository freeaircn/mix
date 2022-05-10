<?php
/*
 * @Description:
 * @Author: freeair
 * @Date: 2021-06-27 20:47:50
 * @LastEditors: freeair
 * @LastEditTime: 2022-05-10 20:34:05
 */

namespace App\Models\Common;

use CodeIgniter\Model;

class AvatarModel extends Model
{
    protected $DBGroup;
    protected $table;

    protected $primaryKey    = 'id';
    protected $allowedFields = ['name', 'path', 'size'];

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
        $this->table   = $config->dbPrefix . 'user_avatar';
        parent::__construct();
    }

    public function getAvatarPathAndNameById(string $id = null)
    {
        if (empty($id)) {
            return [];
        }

        if (floor($id) != $id) {
            return [];
        }

        $db = $this->select('name, path')
            ->where('id', $id)
            ->findAll();

        return isset($db[0]) ? $db[0] : [];
    }

    public function newDefaultAvatarBySex(string $sex = null)
    {
        if (empty($sex)) {
            return false;
        }

        $config = config('MyGlobalConfig');
        if ($sex == '男') {
            $data = [
                'path' => $config->defaultAvatarPath,
                'name' => $config->defaultAvatarMale,
            ];
        } else if ($sex == '女') {
            $data = [
                'path' => $config->defaultAvatarPath,
                'name' => $config->defaultAvatarFemale,
            ];
        } else {
            return false;
        }

        return $this->insert($data);

    }

    public function updateAvatarById(string $path = null, string $name = null, string $id = null)
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

    public function deleteAvatarById(string $id = null)
    {
        if (!is_numeric($id)) {
            return false;
        }

        return $this->delete($id);
    }
}
