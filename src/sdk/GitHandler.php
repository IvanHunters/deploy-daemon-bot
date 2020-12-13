<?php


namespace Daemon\Sdk;


class GitHandler
{
    /**
     * @var string
     */
    private string $branch;

    public function __construct(string $branchName){
        $this->branch = $branchName;
        $status = [];
        exec("git status 2>&1", $status);
        if ($status[0] === "On branch main") {

        }
        $a = 10;
    }
}