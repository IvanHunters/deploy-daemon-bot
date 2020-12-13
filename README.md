# deploy_daemon_bot

# require class
```
require "vendor/autoload.php";
use Daemon\Sdk\Init;
```

# make anon-s functions before and after pull data
```
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
```

# Run Init
```
$deploy = new Init($argv, $beforeHandler, $afterHandler, false);
```

# CLI-Commands:
```
php script init -- init config file
php script start -- start handle pull-requests
```


# Config-file: deploy-config.json

```
{
  "branch": "main",
  "dirs":{
    "./":{
      "before_pull":[
        'pm2 stop 0'
      ],
      "after_pull":[
       'pm2 start 0'
      ]
    }
  }
}
```

