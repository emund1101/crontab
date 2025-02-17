<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */

namespace Hyperf\Crontab;

class Scheduler
{
    /**
     * @var CrontabManager
     */
    protected $crontabManager;

    /**
     * @var \SplQueue
     */
    protected $schedules;

    public function __construct(CrontabManager $crontabManager)
    {
        $this->schedules = new \SplQueue();
        $this->crontabManager = $crontabManager;
    }

    public function schedule($first=false): \SplQueue
    {
        foreach ($this->getSchedules($first) ?? [] as $schedule) {
            $this->schedules->enqueue($schedule);
        }
        return $this->schedules;
    }

    public function getSchedules($first): array
    {
        return $this->crontabManager->parse($first);
    }
}
