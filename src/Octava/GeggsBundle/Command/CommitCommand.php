<?php
namespace Octava\GeggsBundle\Command;

use Octava\GeggsBundle\Helper\RepositoryFactory;
use Octava\GeggsBundle\Plugin\BranchPlugin;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Class CommitCommand
 * @package Octava\GeggsBundle\Command
 */
class CommitCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('commit')
            ->setDescription('Commit command');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $config = $this->getContainer()->get('octava_geggs.config');
        $factory = new RepositoryFactory($this->getContainer()->get('octava_geggs.config'));
        $list = $factory->buildRepositoryModelList();

        // проверить измененные файлы в директории
        // если есть измененные - проверяем название ветки
        // берем название проектной ветки
        // собираем вендоры для которых нужно создать ветки
        // если ветки есть - запрашиваем подстверждение
        // запрашиваем комментарий, если не указан
        // для каждого измененного репозитория создаем ветку (git checkout -b branch_name)
        // коммитим
        // пук
        // вносим правки в composer.json
        // коммитим проектный репозиторий
        $branchPlugin = new BranchPlugin($config, $io);
        $branchPlugin->execute($list);
    }
}
