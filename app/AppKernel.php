<?php
use Octava\Geggs\Command\StatusCommand;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class AppKernel
{
    public function run()
    {
        $application = new Application('geggs', trim(file_get_contents(dirname(__DIR__).'/version')));
        $application->getDefinition()->addOption(
            new InputOption(
                'config-file',
                'c',
                InputOption::VALUE_REQUIRED,
                'Geggs config file',
                __DIR__.'/config/config.yml'
            )
        );

        $container = new ContainerBuilder();
        $application->add(new StatusCommand($container));
        $application->run();
    }
}
