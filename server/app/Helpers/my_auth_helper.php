<?php
/*
 * @Description:
 * @Author: freeair
 * @Date: 2022-04-01 09:35:05
 * @LastEditors: freeair
 * @LastEditTime: 2022-04-01 10:10:10
 */

/**
 * Auth Helpers
 */

if (!function_exists('my_hash_password')) {
    /**
     * hash密码
     *
     * @author freeair
     * @DateTime 2022-04-01
     * @param string $password
     * @param integer $max_bytes
     * @return mixed bool or string
     */
    function my_hash_password(string $password = '', int $max_bytes = 254)
    {
        if ($max_bytes < 8 || $max_bytes > 254) {
            return false;
        }

        // $MAX_PASSWORD_SIZE_BYTES = $this->config->item('MAX_PASSWORD_SIZE_BYTES', 'app_config');
        // Check for empty password, or password containing null char, or password above limit
        // Null char may pose issue: http://php.net/manual/en/function.password-hash.php#118603
        // Long password may pose DOS issue (note: strlen gives size in bytes and not in multibyte symbol)
        if (empty($password) || strpos($password, "\0") !== false ||
            strlen($password) > $max_bytes) {
            return false;
        }

        $algo   = PASSWORD_ARGON2I;
        $params = [
            'memory_cost' => 1 << 14, // 16MB
            'time_cost'   => 4,
            'threads'     => PASSWORD_ARGON2_DEFAULT_THREADS,
        ];

        return password_hash($password, $algo, $params);
    }
}

if (!function_exists('my_verify_password')) {
    /**
     * 检查密码
     *
     * @author freeair
     * @DateTime 2022-04-01
     * @param string $password
     * @param string $hashPassword
     * @param integer $max_bytes
     * @return bool
     */
    function my_verify_password(string $password = '', string $hashPassword = '', int $max_bytes = 254)
    {
        if ($max_bytes < 8 || $max_bytes > 254) {
            return false;
        }

        // $MAX_PASSWORD_SIZE_BYTES = $this->config->item('MAX_PASSWORD_SIZE_BYTES', 'app_config');
        // Check for empty password, or password containing null char, or password above limit
        // Null char may pose issue: http://php.net/manual/en/function.password-hash.php#118603
        // Long password may pose DOS issue (note: strlen gives size in bytes and not in multibyte symbol)
        if (empty($password) || empty($hashPassword) || strpos($password, "\0") !== false
            || strlen($password) > $max_bytes) {
            return false;
        }

        return password_verify($password, $hashPassword);
    }
}
