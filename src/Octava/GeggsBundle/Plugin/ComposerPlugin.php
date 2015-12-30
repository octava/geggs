<?php
namespace Octava\GeggsBundle\Plugin;

use Octava\GeggsBundle\Config;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Class ComposerPlugin
 * @package Octava\GeggsBundle\Plugin
 */
class ComposerPlugin
{
    /**
     * @var Config
     */
    protected $config;
    /**
     * @var SymfonyStyle
     */
    protected $io;

    /**
     * ComposerPlugin constructor.
     * @param string       $type
     * @param Config       $config
     * @param SymfonyStyle $io
     */
    public function __construct($type, Config $config, SymfonyStyle $io)
    {
        $this->config = $config;
        $this->io = $io;
    }

    /**
     * Update composer
     * @return void
     */
    public function execute()
    {
        $composerData = $this->loadComposerJsonData();

        foreach ($this->config->getVendorDirs() as $dir) {
            $this->modifyRequire($dir, $composerData);
            $this->modifyrepositories($dir, $composerData);
        }

        $this->modifyComposerJson($composerData);
    }

    protected function loadComposerJsonData()
    {
        return [];
    }

    protected function modifyComposerJson($composerData)
    {
    }

    protected function modifyrepositories($dir, $composerData)
    {
    }

    protected function modifyRequire($dir, $composerData)
    {
    }
}
