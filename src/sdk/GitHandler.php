<?php


namespace Daemon\Sdk;


class GitHandler
{
    /**
     * @var string
     */
    private string $branch;
    private $debug = true;

    public function __construct(string $branchName, bool $debug = false){
        $this->debug = $debug;
        $this->branch = $branchName;
        $status = [];
        exec("git status 2>&1", $status);
        if ($status[0] === "On branch " . $branchName) {
        }else{
            throw new \RangeException("Please, run git init");
        }
    }

    public function getDiff(array  $config, $beforeHandler, $afterHandler)
    {
        $diff = "git diff origin/".$this->branch."..".$this->branch." --name-only 2>&1";
        $output = [];
        $filesMap = [];

        exec($diff, $output);
        $files = $output;
        if (count($output) == 0) {
            return false;
        }

        $filesMap= $this->getDifFiles($files);
        return $this->handlePullData($filesMap, $beforeHandler, $afterHandler);
    }

    private function getDifFiles(array $files): array
    {
        $filesMap = [];
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
        return $filesMap;
    }

    private function handlePullData($filesMap, $beforeHandler, $afterHandler) {
        if (count($filesMap) > 1 || count($filesMap["./"]) > 1) {
            $resultBefore = $beforeHandler($filesMap);
            if ($resultBefore) {
                $pullResult = $this->execPull();
                if ($pullResult[0] === "Already up to date!" || $pullResult[0] === "Already up to date.") {
                    return false;
                }
            }
            $resultAfter = $afterHandler($filesMap);
        }
        return true;
    }

    private function execPull() {
        $result = [];
        exec("git pull origin " . $this->branch ." 2>&1", $result);
        return $result;
    }

    private function logger(array $info) {
        if ($this->debug)
            echo implode("\n",$info);
    }
}