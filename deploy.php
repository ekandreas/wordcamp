<?php
namespace Deployer;

require 'vendor/deployer/deployer/recipe/composer.php';

set('repository', 'git@github.com:ekandreas/wordcamp.git');

set('shared_files', []);
set('shared_dirs', ['web/app/uploads']);
set('shared_files', ['.env']);

set('keep_releases', 5);

host('45.63.41.239')
    ->port(22)
    ->user('forge')
    ->set('branch', 'master')
    ->stage('production')
    ->set('deploy_path', '~/default')
    ->identityFile('~/.ssh/id_rsa');



task('pull', function () {

    $host = \Deployer\Task\Context::get()->getHost();
    $user = 'forge';
    $hostname = $host->getHostname();
    $localHostname = str_replace('.se', '.app', $hostname);

    $actions = [
        "ssh $user@{$hostname} 'cd {{deploy_path}}/current && mysqldump --user='forge' --password='biEcsLKXoYQwSZKashdG' --skip-lock-tables --hex-blob --single-transaction wordcamp | gzip' > db.sql.gz",
        "gzip -df db.sql.gz",
        "wp db import db.sql",
        "rm -f db.sql",
        "wp search-replace '45.63.41.239' 'wordcamp.app' --all-tables",
        "rsync --exclude .cache -re ssh " .
            "$user@{$hostname}:{{deploy_path}}/shared/web/app/uploads web/app",
        //"wp plugin activate query-monitor",
        "wp user update 1 --user_password='admin'",
        "wp rewrite flush"
    ];

    foreach ($actions as $action) {
        writeln("{$action}");
        writeln(runLocally($action));
    }
});