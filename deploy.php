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

    $host = Context::get()->getHost();
    $user = 'forge';
    $hostname = $host->getHostname();
    $localHostname = str_replace('.se', '.app', $hostname);

    $actions = [
        "ssh deploy@{$hostname} 'cd {{deploy_path}}/current && mysqldump --user='swerob' --password='Arkitekt17!' --skip-lock-tables --hex-blob --single-transaction swerob | gzip' > db.sql.gz",
        "gzip -df db.sql.gz",
        "wp db import db.sql",
        "rm -f db.sql",
        "wp search-replace 'grp.elseif.se' 'grp.swerob.app' --all-tables",
        "rsync --exclude .cache -re ssh " .
            "deploy@{$hostname}:{{deploy_path}}/shared/web/app/uploads web/app",
        "wp plugin activate query-monitor",
        "wp plugin deactivate nginx-cache --network",
        "wp rewrite flush"
    ];

    foreach ($actions as $action) {
        writeln("{$action}");
        writeln(runLocally($action));
    }
});