<?php
/*
 * @Description:
 * @Author: freeair
 * @Date: 2021-06-27 20:47:50
 * @LastEditors: freeair
 * @LastEditTime: 2022-05-22 17:39:39
 */

namespace App\Models;

use CodeIgniter\Model;

class SmsCodeModel extends Model
{
    protected $DBGroup;
    protected $table;

    protected $primaryKey    = 'id';
    protected $allowedFields = ['phone', 'code'];

    protected $useAutoIncrement = true;

    protected $returnType     = 'array';
    protected $useSoftDeletes = false;

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    public function __construct()
    {
        $config        = config('Config\\MyConfig\\MyDB');
        $this->DBGroup = $config->dbName;
        $this->table   = $config->dbPrefix . 'sms_code';
        parent::__construct();
    }

    public function newSmsCodeByPhone(string $phone = null)
    {
        if (!is_numeric($phone)) {
            return '';
        }

        $randNumber = (string) mt_rand(10000, 99999);

        $builder = $this->select('id')->where('phone', $phone);
        $total   = $builder->countAllResults(false);

        if ($total === 0) {
            $data = [
                'phone' => $phone,
                'code'  => $randNumber,
            ];
            $id = $this->insert($data);

            return (is_numeric($id)) ? $randNumber : '';

        } else if ($total === 1) {
            $data = [
                'code' => $randNumber,
            ];
            $res = $builder->findAll();
            $id  = $res[0]['id'];

            return ($this->update($id, $data)) ? $randNumber : '';
        }
    }

    public function validateSmsCodeByPhone(string $phone = null, string $code = null, $del = true)
    {
        if (!is_numeric($phone) || !is_numeric($code)) {
            return false;
        }

        $query = $this->select('id, phone, code, updated_at')
            ->where('phone', $phone)
            ->findAll();

        if (empty($query)) {
            return false;
        }

        $db = $query[0];
        // 比对验证码
        if ($code !== $db['code']) {
            return false;
        }

        // 超期
        helper('my_date');
        if (my_is_timeout($db['updated_at'])) {
            // 删除db记录
            $this->delete($db['id']);
            return false;
        }

        // 最后，删除db记录，通过后，清除验证码。
        if ($del) {
            $this->delete($db['id']);
        }

        return true;
    }
}
