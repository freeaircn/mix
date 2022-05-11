<?php
/*
 * @Description:
 * @Author: freeair
 * @Date: 2021-06-27 20:47:50
 * @LastEditors: freeair
 * @LastEditTime: 2022-05-11 10:02:22
 */

namespace App\Models\Auth;

use CodeIgniter\Model;

class AuthModel extends Model
{
    protected $DBGroup;
    protected $table;

    protected $primaryKey    = 'id';
    protected $allowedFields = ['ip_address', 'identity', 'time'];

    protected $useAutoIncrement = true;

    protected $returnType     = 'array';
    protected $useSoftDeletes = false;

    protected $useTimestamps = false;
    // protected $createdField  = 'created_at';
    // protected $updatedField  = 'updated_at';
    // protected $deletedField  = 'deleted_at';

    public function __construct()
    {
        $config        = config('MyGlobalConfig');
        $this->DBGroup = $config->dbName;
        $this->table   = $config->dbPrefix . 'login_attempts';
        parent::__construct();
    }

    public function isMaxLoginAttemptsExceeded(string $phone = null, string $ip_address = null)
    {
        if (!is_numeric($phone) || !is_string($ip_address)) {
            return true;
        }

        $config      = config('MyGlobalConfig');
        $maxAttempts = $config->maxAttempts;
        $lockoutTime = $config->lockoutTime;

        if ($maxAttempts > 0) {
            $builder = $this->where('identity', $phone)->where('ip_address', $ip_address)->where('time >', time() - $lockoutTime, false);

            $attempts = $builder->countAllResults();

            return $attempts >= $maxAttempts;
        }

        return false;
    }

    public function increaseLoginAttempts(string $phone = null, string $ip_address = null)
    {
        if (!is_numeric($phone) || !is_string($ip_address) || empty($ip_address)) {
            return false;
        }

        $data = [
            'ip_address' => $ip_address,
            'identity'   => $phone,
            'time'       => time(),
        ];

        $result = $this->insert($data);
        if (is_numeric($result)) {
            return true;
        } else {
            return false;
        }
    }

    public function clearLoginAttempts(string $phone = null, string $ip_address = null)
    {
        if (!is_numeric($phone) || !is_string($ip_address) || empty($ip_address)) {
            return false;
        }

        return $this->where('identity', $phone)->where('ip_address', $ip_address)->delete();
    }
}
