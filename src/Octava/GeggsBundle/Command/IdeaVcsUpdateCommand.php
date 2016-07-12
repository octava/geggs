<?php
namespace Octava\GeggsBundle\Command;

use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\Output;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Yaml\Yaml;

class IdeaVcsUpdateCommand extends AbstractCommand
{
    /**
     * Configure
     */
    protected function configure()
    {
        $this
            ->setName('idea-vcs-update')
            ->setDescription('Added vendors to .idea/vcs.xml file');
    }

    /**
     * @param InputInterface         $input
     * @param OutputInterface|Output $output
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->getLogger()->debug(
            'Start',
            [
                'command_name' => $this->getName(),
                'args' => $input->getArguments(),
                'opts' => $input->getOptions(),
            ]
        );
        $optionDryRun = $input->getOption('dry-run');

        $repositories = $this->getRepositoryModelList();
        $projectModel = $repositories->getProjectModel();
        $xmlFilename = implode(DIRECTORY_SEPARATOR, [$projectModel->getAbsolutePath(), '.idea', 'vcs.xml']);
        $fileSystem = new Filesystem();
        if (!$fileSystem->exists($xmlFilename)) {
            $this->getSymfonyStyle()->error(sprintf('Config file "%s" not found', $xmlFilename));
        }

        $existsMap = [];
        $simpleXml = simplexml_load_file($xmlFilename);
        foreach ($simpleXml->component->children() as $child) {
            /** @var \SimpleXMLElement $child */
            $existsMap[] = (string)$child->attributes()['directory'];
        }
        $vendors = [];
        foreach ($this->getRepositoryModelList()->getAll() as $model) {
            $tmp = '$PROJECT_DIR$';
            if ($model->getPath()) {
                $tmp .= DIRECTORY_SEPARATOR.$model->getPath();
            }
            $vendors[] = $tmp;
        }

        $symfonyStyle = new SymfonyStyle($input, $output);
        $newDirs = array_diff($vendors, $existsMap);
        if (count($newDirs)) {
            $question = sprintf('Script will add %d new dirs, would you like to continue?', count($newDirs));
            if ($symfonyStyle->confirm($question, true)) {
                foreach ($newDirs as $dir) {
                    $mapping = $simpleXml->component->addChild("mapping", "");
                    $mapping->addAttribute('directory', $dir);
                    $mapping->addAttribute('vcs', 'Git');
                }

                $dom = dom_import_simplexml($simpleXml)->ownerDocument;
                $dom->preserveWhiteSpace = false;
                $dom->formatOutput = true;

                if (!$optionDryRun) {
                    $fileSystem->dumpFile($xmlFilename, $dom->saveXML());
                }

                $symfonyStyle->success(sprintf('File "%s" updated', $xmlFilename));
            }
        } else {
            $symfonyStyle->note('Changes not found');
        }

        $this->getLogger()->debug('Finish', ['command_name' => $this->getName()]);
    }
}
