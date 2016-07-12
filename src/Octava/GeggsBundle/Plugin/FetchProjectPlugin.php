<?php
namespace Octava\GeggsBundle\Plugin;

use Octava\GeggsBundle\Helper\RepositoryList;

class FetchProjectPlugin extends AbstractPlugin
{
    /**
     * @param RepositoryList $repositories
     */
    public function execute(RepositoryList $repositories)
    {
        $this->getLogger()->debug('Run plugin', [get_called_class()]);

        $all = $this->getInput()->hasOption('all') && $this->getInput()->getOption('all');
        $prune = $this->getInput()->hasOption('prune') && $this->getInput()->getOption('prune');

        $arguments = [];
        if ($all) {
            $arguments[] = '--all';
        }
        if ($prune) {
            $arguments[] = '--prune';
        }

        $model = $repositories->getProjectModel();
        $model->getProvider()->run('fetch', $arguments, $this->isDryRun(), true);

        $this->getLogger()->debug('End plugin', [get_called_class()]);
    }
}
