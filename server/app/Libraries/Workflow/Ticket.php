<?php
/*
 * @Description:
 * @Author: freeair
 * @Date: 2019-12-29 14:06:12
 * @LastEditors: freeair
 * @LastEditTime: 2022-04-01 15:19:47
 */

namespace App\Libraries\Workflow;

class Ticket
{
    protected $currentPlace;

    public function __construct(string $val = null)
    {
        $this->currentPlace = $val;
    }

    // getter/setter methods must exist for property access by the marking store
    public function getCurrentPlace()
    {
        return $this->currentPlace;
    }

    public function setCurrentPlace($currentPlace, $context = [])
    {
        $this->currentPlace = $currentPlace;
    }
}
