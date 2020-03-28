<?php


namespace App\Command;


use Psr\Cache\InvalidArgumentException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

class StepInfoCommand extends Command
{
    private ?CacheInterface $cache;

    public function __construct(CacheInterface $cache)
    {
        $this->cache = $cache;
        parent::__construct();
    }

    protected static $defaultName = 'app:step:info';

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     * @return int
     * @throws InvalidArgumentException
     */
    protected function execute(InputInterface $input, OutputInterface $output) :int
    {

        $step = $this->cache->get('app.current_step', static function (ItemInterface $item) {
            $process = new Process([
                                       'git', 'tag', '-1', '--points-at', 'HEAD'
                                   ]);
            $process->mustRun();
            $item->expiresAfter(30);
            return $process->getOutput();
        });

        $output->write($step);

        return 0;
    }
}
