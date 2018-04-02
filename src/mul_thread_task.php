<?php
/**
 * 多进程启动任务脚本
 * php MulThreadTask.php 2 (2为开启进程数,范围是1-100)
 */


global $argv;

function executeTask($startTable, $endTable)
{
    $subTask = array('/usr/local/webserver/php/bin/php');

    $subTask[] = dirname(__FILE__) . '/work_task.php';

    $subTask[] = $startTable . ' ' . $endTable;

    // 后台执行
    $subTask[] = '> /dev/null 2>&1 &';

    $subTask = implode(' ', $subTask);

    shell_exec($subTask).PHP_EOL;

    echo '执行子任务:' . $subTask . PHP_EOL;
}

$threadNum = $argv[1];

if (is_numeric($threadNum) && $threadNum > 0 && $threadNum <= 100) {

    $nfield = floor(100 / $threadNum);
    $startTable = 0;

    while ($startTable + ($nfield - 1) < 100) {
        $endTable = $startTable + ($nfield - 1);
        executeTask($startTable, $endTable);
        $startTable = $endTable + 1;
    }

    if ($endTable != 99 && $startTable < 100) {
        executeTask($startTable, 99);
    }
} else {
    echo '进程数必须为数字且小于100';
}






