#!/usr/bin/php
<?php
/**
* README
*
* データの集計期間をmuninの実行間隔と合わせる必要があるため、
* デフォルトと違う実行間隔の場合は設定ファイルを作成し、$time_rangeを変更する
*
* Usage: ln -s github_commits_.php /etc/munin/plugins/github_commits_{user_name}
*
* Optional:
*     env.time_range データの取得間隔
*     env.category カテゴリ
*/
preg_match("/github_commits_(.*)/",$argv[0],$matched);
$user_name = $matched[1];
$time_range = @getenv('time_range') ? @getenv('time_range') : 5;
$category   = @getenv('category')   ? @getenv('category') : 'web:services';

if (isset($argv[1]) && $argv[1] == 'config') {
    config();
} else {
    report();
}
exit;

function report()
{
    global $user_name,$time_range;

    $json = file_get_contents("https://api.github.com/users/{$user_name}/events");
    $hash = json_decode($json,true);

    $min_time = strtotime(date('Y-m-d H:i:00')) - ($time_range * 60);
    $push = 0;
    $commits = 0;
    foreach ($hash as $event) {
        if($event['type'] == 'PushEvent'
            && $min_time <= strtotime($event["created_at"])
        ){
            $push++;
            $commits += count($event["payload"]["commits"]);
        }
    }
    echo "commits.value $commits\n";
    echo "push.value $push\n";
}

function config()
{
    global $category;

    echo "graph_title github push/commits counts\n";
    echo "graph_info  \n";
    echo "graph_category $category\n";
    echo "graph yes\n";
    echo "graph_vlabel Counts\n";
    echo "graph_scale no\n";

    echo "commits.label commits\n";
    echo "commits.draw AREA\n";

    echo "push.label push\n";
    echo "push.draw AREA\n";
}
?>
