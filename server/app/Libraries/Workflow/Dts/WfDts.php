<?php
/*
 * @Description:
 * @Author: freeair
 * @Date: 2019-12-29 14:06:12
 * @LastEditors: freeair
 * @LastEditTime: 2022-04-27 23:54:32
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

    public function getPlaceLine(string $line = ''): array
    {
        if (empty($line)) {
            return [];
        }
        $temp = $this->placesMetadata;
        $res  = [];

        foreach ($temp as $key => $value) {
            if ($value['line'] === $line) {
                $res[] = [
                    'name'  => $value['name'],
                    'alias' => $key,
                ];
            }
        }

        return $res;
    }

    public function getPlaceMetaOfAllow(string $place = ''): array
    {
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

    public function isReviewPlace(string $place = '')
    {
        if ($place === 'review') {
            return true;
        } else {
            return false;
        }
    }

    public function getWorkingPlace()
    {
        return 'working';
    }

    public function getReviewPlace()
    {
        // $meta = $this->placesMetadata['review'];

        return 'review';
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

    public function toReview()
    {
        try {
            if ($this->workflow->can($this->ticket, 'to_review')) {
                $this->workflow->apply($this->ticket, 'to_review');
                return true;
            } else {
                return false;
            }
        } catch (LogicException $exception) {
            // print($exception);
            return false;
        }
    }

    public function toCancel()
    {
        try {
            $this->workflow->apply($this->ticket, 'to_cancel');
        } catch (LogicException $exception) {
            print($exception);
        }
    }

    public function canUpdateProgress(string $place_at = '')
    {
        $allow = $this->getPlaceMetaOfAllow($place_at);
        if (isset($allow['updateProgress'])) {
            return $allow['updateProgress'];
        } else {
            return false;
        }

    }

}
