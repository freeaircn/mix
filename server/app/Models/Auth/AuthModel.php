<?php
/*
 * @Description:
 * @Author: freeair
 * @Date: 2021-06-27 20:47:50
 * @LastEditors: freeair
 * @LastEditTime: 2022-04-01 10:43:13
 */

namespace App\Models\Auth;

use CodeIgniter\Model;

class AuthModel extends Model
{
    protected $DBGroup = 'mix';

    protected $table         = 'app_login_attempts';
    protected $primaryKey    = 'id';
    protected $allowedFields = ['ip_address', 'identity', 'time'];

    protected $useAutoIncrement = true;

    protected $returnType     = 'array';
    protected $useSoftDeletes = false;

    protected $useTimestamps = false;
    // protected $createdField  = 'created_at';
    // protected $updatedField  = 'updated_at';
    // protected $deletedField  = 'deleted_at';

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
