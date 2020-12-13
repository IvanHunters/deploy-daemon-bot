<?php


namespace Daemon\Sdk;

use RuntimeException;
use Throwable;

class Init
{

    /**
     * @var string
     */
    private string $gitBranch;
    private $beforeHandler;
    private $afterHandler;

    public function __construct(array $argv, $beforeHandler, $afterHandler, bool $debug = false){
        $this->beforeHandler = $beforeHandler;
        $this->afterHandler = $afterHandler;
        $this->handleCommand($argv, $debug);
    }

    private function getConfig(){
        try {
            $configBody = file_get_contents("deploy-config.json");
        }catch (Throwable $e){
            throw new RuntimeException('Config file not found');
        }
        /** @var null|array<string> $config */
        $config = json_decode($configBody, true);
        if (is_null($config)) {
            throw new RuntimeException('Config file not found');
        }
        if (!isset($config['branch']) || empty($config['branch'])) {
            throw new RuntimeException('Branch is empty or not found');
        }
        $this->gitBranch = $config['branch'];
        return $config;
    }

    private function handleCommand(array $argv, bool $debug){
        if (!isset($argv[1])) {
            die("Command not found\n");
        }
        $this->getConfig();
        switch (mb_strtolower($argv[1])) {
            case 'init':
                $this->init();
                break;

            case 'start':
                $this->start($debug);
                break;
        }
    }

    private function init(){
        if(file_exists("deploy-config.json")){
            $configBody = file_get_contents("deploy-config.json");
            $config = json_decode($configBody, true);
        } else {
            $config = null;
        }

        if (is_null($config)) {
            $json_example = file_get_contents("http://bot-os.ru/example-deploy-bot.json");
            file_put_contents("deploy-config.json", $json_example);
            die("Deploy bot successful init\n");
        }else{
            die("deploy-config.json already exists successful init\n");
        }
    }

    private function start(bool $debug) {
        $fetch = "git fetch 2>&1";

        $output = [];
        $gitHandler = new GitHandler($this->gitBranch, $debug);
        echo sprintf("\n[%s] start monitoring", date('H:i d.m.Y'));

        while(true){
            $config = $this->getConfig();

            exec($fetch, $output);


            if ($gitHandler->getDiff($config, $this->beforeHandler, $this->afterHandler)) {
                echo sprintf("\n[%s] New pull was been successful handle", date('H:i d.m.Y'));
            }

            sleep(5);
        }
    }

}