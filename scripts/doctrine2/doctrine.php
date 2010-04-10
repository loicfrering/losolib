<?php
define('APPLICATION_ENV', 'development');

// Variable $configuration is defined inside cli-config.php
require __DIR__ . '/cli-config.php';

$cli = new \Doctrine\Common\Cli\CliController($configuration);
$cli->addNamespace('LoSo')
    ->addTask('build', 'LoSo\Doctrine\ORM\Tools\Cli\Tasks\BuildTask');
$cli->run($_SERVER['argv']);
