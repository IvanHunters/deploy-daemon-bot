<?php

require "vendor/autoload.php";
use Daemon\Sdk\Init;

$afterHandler = $beforeHandler = function (array $filesMap, string $type_pull, array $config) {
    $dirs = $config['dirs'];
    foreach ($filesMap as $path=>$file) {
        if (isset($dirs[$path])) {
            $dirInfo = $dirs[$path];
            if (isset($dirInfo[$type_pull])) {
                $beforePullExecution = $dirInfo[$type_pull];
                if (count($beforePullExecution) > 0) {
                    $resultExecution = [];
                    exec($beforePullExecution, $resultExecution);
                    $this->logger($resultExecution);
                }
            }
        }
    }
    return true;
};
$deploy = new Init($argv, $beforeHandler, $afterHandler, true);

?>