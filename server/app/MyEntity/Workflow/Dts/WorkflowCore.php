<?php
/*
 * @Description:
 * @Author: freeair
 * @Date: 2019-12-29 14:06:12
 * @LastEditors: freeair
 * @LastEditTime: 2022-03-24 20:41:56
 */

namespace App\MyEntity\Workflow\Dts;

use App\MyEntity\Workflow\Dts\Ticket;
use Symfony\Component\Workflow\DefinitionBuilder;
use Symfony\Component\Workflow\Exception\LogicException;
use Symfony\Component\Workflow\MarkingStore\MethodMarkingStore;
use Symfony\Component\Workflow\Transition;
use Symfony\Component\Workflow\Workflow;
use Symfony\Component\Yaml\Yaml;

class WorkflowCore
{
    private $ticket;
    private $workflow;
    private $workflowMetadata;
    private $placesMetadata;
    private $transitionsMetadata;
    private $event;

    // public function __construct(Ticket $ticket)
    public function __construct()
    {
        // $this->ticket = $ticket;

        // $paths  = new Config\Paths();
        $config = rtrim(APPPATH, '\\/ ') . DIRECTORY_SEPARATOR . 'MyEntity/Workflow/Dts/config.yaml';
        $this->init($config);
    }

    private function init(string $configFile = '')
    {
        $config = Yaml::parseFile($configFile);

        $workflows = $config['workflows'];
        $workflow  = $workflows['dts'];
        $places    = array_keys($workflow['places']);

        $transitions = [];
        foreach ($workflow['transitions'] as $x => $value) {
            if (is_array($value['from'])) {
                foreach ($value['from'] as $a) {
                    if (is_array($value['to'])) {
                        foreach ($value['to'] as $b) {
                            $transitions[] = ['name' => $x, 'from' => $a, 'to' => b];
                        }
                    } else {
                        $transitions[] = ['name' => $x, 'from' => $a, 'to' => $value['to']];
                    }
                }
            } else {
                if (is_array($value['to'])) {
                    foreach ($value['to'] as $c) {
                        $transitions[] = ['name' => $x, 'from' => $value['from'], 'to' => $c];
                    }
                } else {
                    $transitions[] = ['name' => $x, 'from' => $value['from'], 'to' => $value['to']];
                }
            }
        }

        $definitionBuilder = new DefinitionBuilder();
        $definitionBuilder->addPlaces($places);
        foreach ($transitions as $transition) {
            $definitionBuilder->addTransition(new Transition($transition['name'], $transition['from'], $transition['to']));
        }
        $definition = $definitionBuilder->build();

        $singleState = true; // true if the subject can be in only one state at a given time
        $property    = 'currentPlace'; // subject property name where the state is stored
        $marking     = new MethodMarkingStore($singleState, $property);

        $this->workflow = new Workflow($definition, $marking, null, 'DTSWorkflow');

        if ($workflow['metadata']) {
            $this->workflowMetadata = $workflow['metadata'];
        }

        $placesMetadata = [];
        foreach ($workflow['places'] as $name => $place) {
            if (isset($place['metadata'])) {
                $placesMetadata[$name] = $place['metadata'];
            }
        }
        if ($placesMetadata) {
            $this->placesMetadata = $placesMetadata;
        }

        $transitionsMetadata = [];
        foreach ($workflow['transitions'] as $name => $transition) {
            if (isset($transition['metadata'])) {
                $transitionsMetadata[$name] = $transition['metadata'];
            }
        }
        if ($transitionsMetadata) {
            $this->transitionsMetadata = $transitionsMetadata;
        }
    }

    public function bindTicket(Ticket $ticket)
    {
        $this->ticket = $ticket;
    }

    public function getTicket()
    {
        return $this->ticket;
    }

    public function getWorkflowMetadata(): array
    {
        return $this->workflowMetadata;
    }

    public function getPlaceMetadata(string $place): array
    {
        return $this->placesMetadata[$place] ?? [];
    }

    public function getTransitionMetadata(Transition $transition): array
    {
        return $this->transitionsMetadata[$transition] ?? [];
    }

    //
    public function getFirstPlace()
    {
        $temp = $this->placesMetadata;

        foreach ($temp as $key => $value) {
            return $key;
        }
    }

    public function getNextPlace(string $place = '')
    {
        if (empty($place)) {
            return false;
        }

        $temp = $this->placesMetadata;
        $next = false;

        foreach ($temp as $key => $value) {
            if ($next) {
                return $key;
            }
            if ($key === $place) {
                $next = true;
            }
        }
        return false;
    }

    public function getPlaceMetaOfName(string $branch = 'branchA'): array
    {
        $temp = $this->placesMetadata;
        $res  = [];

        if (!empty($branch)) {
            foreach ($temp as $key => $value) {
                if (isset($value[$branch])) {
                    $res[] = [
                        'name'  => $value['name'],
                        'alias' => $key,
                    ];
                }
            }
        } else {
            foreach ($temp as $key => $value) {
                $res[] = [
                    'name'  => $value['name'],
                    'alias' => $key,
                ];
            }
        }

        return $res;
    }

    public function getPlaceMetaOfView(string $place = ''): array
    {
        if (empty($place)) {
            return [];
        }

        $meta = $this->placesMetadata[$place];

        if (isset($meta['view'])) {
            return $meta['view'];
        } else {
            return [];
        }
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
