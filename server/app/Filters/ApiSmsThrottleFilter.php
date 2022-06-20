<?php
/*
 * @Description:
 * @Author: freeair
 * @Date: 2021-07-16 23:08:38
 * @LastEditors: freeair
 * @LastEditTime: 2022-06-20 23:15:37
 */

/**
 * This file is part of the CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Filters;

use CodeIgniter\API\ResponseTrait;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\Response;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Security\Exceptions\SecurityException;
use Config\Services;

class ApiSmsThrottleFilter implements FilterInterface
{
    protected $response;
    use ResponseTrait;
    /**
     * Do whatever processing this filter needs to do.
     * By default it should not return anything during
     * normal execution. However, when an abnormal state
     * is found, it should return an instance of
     * CodeIgniter\HTTP\Response. If it does, script
     * execution will end and that Response will be
     * sent back to the client, allowing for error pages,
     * redirects, etc.
     *
     * @param RequestInterface|IncomingRequest $request
     * @param array|null                       $arguments
     *
     * @return mixed
     * @throws SecurityException
     */
    public function before(RequestInterface $request, $arguments = null)
    {
        $throttler = Services::throttler();
        $config    = config('Config\\MyConfig\\Throttler');

        if ($throttler->check('sms_' . $request->getIPAddress(), $config->api_sms_throttle['capacity'], $config->api_sms_throttle['seconds']) === false) {
            $this->response = Services::response();
            $res['error']   = '系统忙，一分钟后再试';
            return $this->fail($res, 429);
        }

    }

    //--------------------------------------------------------------------
    /**
     * We don't have anything to do here.
     *
     * @param RequestInterface|IncomingRequest             $request
     * @param ResponseInterface|Response $response
     * @param array|null                                   $arguments
     *
     * @return mixed
     */
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
    }

    //--------------------------------------------------------------------
}
