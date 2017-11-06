<?php

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

$console = new Application('My Silex Application', 'n/a');
$console->getDefinition()->addOption(new InputOption('--env', '-e', InputOption::VALUE_REQUIRED, 'The Environment name.', 'dev'));
$console->setDispatcher($app['dispatcher']);
$novoComando = new seed\Comando();
$novoComando->db($app['db']);
$console->add($novoComando);
$console
    ->register('my-command')
    ->setDefinition(array(
        // new InputOption('some-option', null, InputOption::VALUE_NONE, 'Some help'),
    ))
    ->setDescription('My command description')
    ->setCode(function (InputInterface $input, OutputInterface $output) use ($app) {
        // do something
    })
;


$app->register(
    new \Kurl\Silex\Provider\DoctrineMigrationsProvider($console),
    array(
        'migrations.directory' => __DIR__ . '/../db/migrations',
        'migrations.namespace' => 'Acme\Migrations',
    )
);
$app->boot();

return $console;
