<?php


namespace Daemon\Sdk;


use Daemon\Sdk\GitHandler;
use \RuntimeException;

class Init
{

    /**
     * @var \Daemon\Sdk\GitHandler
     */
    private \Daemon\Sdk\GitHandler $gitHandler;
    /**
     * @var string
     */
    private string $gitBranch;

    public function __construct(array $argv){
        $this->handleCommand($argv);
    }

    private function getConfig(){
        try {
            $configBody = file_get_contents("deploy-config.json");
        }catch (\Throwable $e){
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
        $config1 = $config;
        return $config;
    }

    private function handleCommand(array $argv){
        if (!isset($argv[1])) {
            die("Command not found\n");
        }
        $this->getConfig();
        switch (mb_strtolower($argv[1])) {
            case 'init':
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
                break;

            case 'start':


                $fetch = "git fetch 2>&1";
                $diff = "git diff origin/".$config['branch']."..".$config['branch']." --name-only 2>&1";

                $output = [];
                $filesMap = [];
                $gitHandler = new GitHandler($this->gitBranch);
                echo sprintf("\n[%s] start monitoring", date('H:i d.m.Y'));

                while(true){
                    $config = $this->getConfig();

                    exec($fetch, $output);
                    if (count($output) > 0) {
                        $output = [];
                        exec($diff, $output);
                        $files = $output;
                        if (count($output) == 0) {
                            sleep(10);
                            continue;
                        }
                        foreach ($files as $file) {
                            $explodeFilePath = explode("/", $file);
                            $countExplode = count($explodeFilePath);
                            if ($countExplode > 0) {
                                $fileName = $explodeFilePath[$countExplode - 1];
                                unset($explodeFilePath[$countExplode - 1]);

                                $filePath = "./" . implode('/', $explodeFilePath);
                                $filesMap[$filePath][] = $fileName;
                            } else {
                                $filePath = "./";
                                $filesMap[$filePath][] = $file;
                            }
                        }
                        $filesMap["./"][] = 1;
                        for ($i=0; $i<3; $i++) {
                            foreach ($filesMap as $path=>$files) {
                                if (in_array($path, array_keys($config['dirs']))) {
                                    if ($i === 0) {
                                        foreach ($config['dirs'][$path]['before_push'] as $command) {
                                            exec($command);
                                        }
                                    } elseif ($i === 1) {
                                        exec("git pull");
                                    } else {
                                        foreach ($config['dirs'][$path]['after_push'] as $command) {
                                            exec($command);
                                        }
                                    }
                                }
                            }
                        }
                        echo sprintf("\n[%s] New pull was been successful handle", date('H:i d.m.Y'));
                    }
                    sleep(5);
                }
                break;
        }
    }

}