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

namespace Hyperf\Crontab\Process;

use Hyperf\Contract\ConfigInterface;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Crontab\Event\CrontabDispatcherStarted;
use Hyperf\Crontab\Scheduler;
use Hyperf\Crontab\Strategy\StrategyInterface;
use Hyperf\Process\AbstractProcess;
use Psr\Container\ContainerInterface;
use Swoole\Server;

class CrontabDispatcherProcess extends AbstractProcess
{
    /**
     * @var string
     */
    public $name = 'crontab-dispatcher';

    /**
     * @var Server
     */
    private $server;

    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * @var Scheduler
     */
    private $scheduler;

    /**
     * @var StrategyInterface
     */
    private $strategy;

    /**
     * @var StdoutLoggerInterface
     */
    private $logger;

    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);
        $this->config = $container->get(ConfigInterface::class);
        $this->scheduler = $container->get(Scheduler::class);
        $this->strategy = $container->get(StrategyInterface::class);
        $this->logger = $container->get(StdoutLoggerInterface::class);
    }

    public function bind(Server $server): void
    {
        $this->server = $server;
        parent::bind($server);
    }

    public function isEnable(): bool
    {
        return $this->config->get('crontab.enable', false);
    }

    //修改后,自动根据当前分钟的间隔数，计算最近的执行时间，自动定时运行
    public function handle(): void
    {
        $i=0;
        while (true) {
            $first= $i ? false : true;
            $i=1;
            if(!$first)
                $this->sleep();
            
            $crontabs = $this->scheduler->schedule($first);
          
            while (! $crontabs->isEmpty()) {
                $crontab = $crontabs->dequeue();
                $this->strategy->dispatch($crontab);
            }
            
        }

    }

    private function sleep()
    {
        $current = date('s', time());
        $sleep = 60 - $current;
        $this->logger->debug('Crontab dispatcher sleep ' . $sleep . 's.');
        $sleep > 0 && \Swoole\Coroutine::sleep($sleep);
        
    }
}
