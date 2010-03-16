<?php
// Variable $configuration is defined inside cli-config.php
require __DIR__ . '/cli-config.php';

$cli = new \Doctrine\Common\Cli\CliController($configuration);
$cli->run($_SERVER['argv']);