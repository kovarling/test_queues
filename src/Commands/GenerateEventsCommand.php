<?php
/**
 * ОПИСАНИЕ
 *
 * @link http://m1-call.ru/
 * @author Лютович Георгий <darkbober@gmail.com>
 * @copyright M1 Shop <m1-shop.ru>
 */

namespace App\Commands;


use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class GenerateEventsCommand extends Command
{
    CONST maxEvents = 10000;

    private $redisHost;
    private $redisPort;

    protected static $defaultName = 'test:generate_events';

    /**
     * @param $redisHost string
     * @param $redisPort string
     */
    public function __construct($redisHost, $redisPort)
    {
        parent::__construct();
        $this->redisHost = $redisHost;
        $this->redisPort = $redisPort;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $redis = new \Redis();
        $redis->connect($this->redisHost, $this->redisPort);

        $redis->flushAll(); //очищаем весь кэш для исключения конфликтов. все запущенные обработчики сами закроются.

        $eventsCount = 0;
        while (true){
            $numUsers = random_int(1, 20);

            for($i = 0; $i < $numUsers; $i++){
                $userId = random_int(1, 1000);
                $numEvents = random_int(1, 10);
                $eventsArray = [];
                for($eventNum = 0; $eventNum < $numEvents; $eventNum++){
                    $eventsArray[] = 'TestEvent'.$eventNum;
                    $eventsCount++;
                    if($eventsCount >= self::maxEvents){
                        break 3;
                    }
                }
                $redis->lPush('group_'.substr($userId, 0, 1), $userId.':'.json_encode($eventsArray)); // разбиваем ивенты на группы по первой цифре из id юзера
            }
        }
        $output->writeln('в очередь помещено '.self::maxEvents.' ивентов');

        $redis->close();
        return Command::SUCCESS;
    }

}