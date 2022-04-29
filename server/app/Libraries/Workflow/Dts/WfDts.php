<?php
/*
 * @Description:
 * @Author: freeair
 * @Date: 2019-12-29 14:06:12
 * @LastEditors: freeair
 * @LastEditTime: 2022-04-29 15:29:15
 */

namespace App\Libraries\Workflow\Dts;

use App\Libraries\Workflow\Core;
use App\Libraries\Workflow\Ticket;

class WfDts extends Core
{
    public function __construct(string $place = null)
    {
        if (!is_null($place)) {
            $ticket = new Ticket($place);
            $this->bindTicket($ticket);
        }
        $config = rtrim(APPPATH, '\\/ ') . DIRECTORY_SEPARATOR . config('MyGlobalConfig')->wfDtsConfigFile;
        parent::__construct($config);
    }

    public function getPlaceLine(): array
    {
        $temp = $this->placesMetadata;
        $res  = [];

        foreach ($temp as $key => $value) {
            $res[] = [
                'name'  => $value['name'],
                'alias' => $key,
            ];
        }

        return $res;
    }

    public function getPlaceAllowOp(): array
    {
        $place = $this->ticket->getCurrentPlace();
        if (empty($place)) {
            return [];
        }

        $meta = $this->placesMetadata[$place];

        if (isset($meta['allow'])) {
            return $meta['allow'];
        } else {
            return [];
        }
    }

    public function getWorkingPlace()
    {
        return 'working';
    }

    public function canWorking()
    {
        try {
            if ($this->workflow->can($this->ticket, 'to_working')) {
                return true;
            } else {
                return false;
            }
        } catch (LogicException $exception) {
            // print($exception);
            return false;
        }
    }

    public function toWorking()
    {
        try {
            if ($this->workflow->can($this->ticket, 'to_working')) {
                $this->workflow->apply($this->ticket, 'to_working');
                return true;
            } else {
                return false;
            }
        } catch (LogicException $exception) {
            // print($exception);
            return false;
        }
    }

    public function canResolve()
    {
        try {
            if ($this->workflow->can($this->ticket, 'to_resolve')) {
                return true;
            } else {
                return false;
            }
        } catch (LogicException $exception) {
            // print($exception);
            return false;
        }
    }

    public function toResolve()
    {
        try {
            if ($this->workflow->can($this->ticket, 'to_resolve')) {
                $this->workflow->apply($this->ticket, 'to_resolve');
                return true;
            } else {
                return false;
            }
        } catch (LogicException $exception) {
            // print($exception);
            return false;
        }
    }

    public function canBackWorking()
    {
        try {
            if ($this->workflow->can($this->ticket, 'to_working')) {
                return true;
            } else {
                return false;
            }
        } catch (LogicException $exception) {
            // print($exception);
            return false;
        }
    }

    public function toBackWorking()
    {
        try {
            if ($this->workflow->can($this->ticket, 'to_working')) {
                $this->workflow->apply($this->ticket, 'to_working');
                return true;
            } else {
                return false;
            }
        } catch (LogicException $exception) {
            // print($exception);
            return false;
        }
    }

    public function canClose()
    {
        try {
            if ($this->workflow->can($this->ticket, 'to_close')) {
                return true;
            } else {
                return false;
            }
        } catch (LogicException $exception) {
            // print($exception);
            return false;
        }
    }

    public function toClose()
    {
        try {
            if ($this->workflow->can($this->ticket, 'to_close')) {
                $this->workflow->apply($this->ticket, 'to_close');
                return true;
            } else {
                return false;
            }
        } catch (LogicException $exception) {
            // print($exception);
            return false;
        }
    }

}
