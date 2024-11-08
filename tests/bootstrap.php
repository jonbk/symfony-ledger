<?php

use Symfony\Component\Dotenv\Dotenv;

require dirname(__DIR__).'/vendor/autoload.php';

if (method_exists(Dotenv::class, 'bootEnv')) {
    (new Dotenv())->bootEnv(dirname(__DIR__).'/.env');
}

if ($_SERVER['APP_DEBUG']) {
    umask(0000);
}

passthru('php '.__DIR__.'/../bin/console cache:clear --env=test');
passthru('php '.__DIR__.'/../bin/console doctrine:database:drop --env=test --force');
passthru('php '.__DIR__.'/../bin/console doctrine:database:create --env=test');
passthru('php '.__DIR__.'/../bin/console doctrine:migration:migrate -n -q --env=test');
