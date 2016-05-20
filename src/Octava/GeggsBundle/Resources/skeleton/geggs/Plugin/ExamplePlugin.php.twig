<?php
namespace Project\Geggs\Plugin;

use Octava\GeggsBundle\Helper\RepositoryList;
use Octava\GeggsBundle\Model\RepositoryModel;
use Octava\GeggsBundle\Plugin\AbstractPlugin;

/**
 * Class StatusPlugin
 * @package Octava\GeggsBundle\Plugin
 */
class ExamplePlugin extends AbstractPlugin
{
    /**
     * @param RepositoryList $repositories
     */
    public function execute(RepositoryList $repositories)
    {
        $this->getLogger()->debug('Run plugin', [get_called_class()]);

        foreach ($repositories->getAll() as $model) {
            $path = $model->getPath();
            if ($model->getType() === RepositoryModel::TYPE_ROOT) {
                $path = '.';
            }

            $this->getSymfonyStyle()->writeln(sprintf('<info>%s</info> [%s]', $path, $model->getBranch()));
        }

        $this->getLogger()->debug('End plugin', [get_called_class()]);
    }
}
