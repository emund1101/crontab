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

class CrontabManager
{
    /**
     * @var Crontab[]
     */
    protected $crontabs = [];

    /**
     * @var Parser
     */
    protected $parser;

    public function __construct(Parser $parser)
    {
        $this->parser = $parser;
    }

    public function register(Crontab $crontab): bool
    {
        if (! $this->isValidCrontab($crontab)) {
            return false;
        }
        $this->crontabs[$crontab->getName()] = $crontab;
        return true;
    }

    public function parse($first=false): array
    {
        $result = [];
        $crontabs = $this->getCrontabs();
//          $last = time();
//替换成当前
         $now  =time();
         $last=$first ? time() - date('s') :$now;
         
// echo date('Y-m-d H:i:s',$last).PHP_EOL;
        foreach ($crontabs ?? [] as $key => $crontab) {
            if (! $crontab instanceof Crontab) {
                unset($this->crontabs[$key]);
                continue;
            }
            $time = $this->parser->parse($crontab->getRule(), $last,$first);
            if ($time) {
                foreach ($time as $t) {
                    if(($first&&$t->unix() >=$now) ||!$first)
                        $result[] = clone $crontab->setExecuteTime($t);
                }
            }
        }
        return $result;
    }

    public function getCrontabs(): array
    {
        return $this->crontabs;
    }

    private function isValidCrontab(Crontab $crontab): bool
    {
        return $crontab->getName() && $crontab->getRule() && $crontab->getCallback() && $this->parser->isValid($crontab->getRule());
    }
}
