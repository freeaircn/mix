<?php
/*
 * @Description:
 * @Author: freeair
 * @Date: 2019-12-29 14:06:12
 * @LastEditors: freeair
 * @LastEditTime: 2022-04-14 16:38:27
 */

namespace App\Libraries\Workflow\Dts;

use App\Libraries\Workflow\Core;

class WfDts extends Core
{
    public function __construct()
    {
        $config = rtrim(APPPATH, '\\/ ') . DIRECTORY_SEPARATOR . config('MyGlobalConfig')->wfDtsConfigFile;
        parent::__construct($config);
    }

    public function isHandlingPlace(string $place = '')
    {
        if ($place === 'handling') {
            return true;
        } else {
            return false;
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

}
