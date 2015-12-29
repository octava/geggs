<?php
namespace Octava\GeggsBundle\Command;

use Octava\GeggsBundle\Generator\VendorGenerator;
use Octava\GeggsBundle\Model\Vendor;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\Output;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Class VendorCreateCommand
 * @package Octava\GeggsBundle\Command
 */
class VendorCreateCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('vendor:create')
            ->addArgument('name', InputArgument::REQUIRED, 'Repository name for example: my_namespace/my_lib.git')
            ->addOption('dir', null, InputOption::VALUE_REQUIRED, 'The directory where to create the bundle', 'vendor/')
            ->addOption('url', null, InputOption::VALUE_REQUIRED, 'Git url for example: https://github.com', '')
            ->setDescription('Create vendor');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface|Output $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Generate vendor library');

        $name = $input->getArgument('name');
        $dir = $input->getOption('dir');
        $url = $input->getOption('url');

        $filesystem = $this->getContainer()->get('filesystem');
        if (!$filesystem->isAbsolutePath($dir)) {
            $dir = getcwd().'/'.$dir;
        }

        $vendor = new Vendor($dir, $name, $url);
        $generator = new VendorGenerator($filesystem);
        $generator->setSkeletonDirs(__DIR__.'/../Resources/skeleton');
        $generator->generate($vendor);

        $io->success('Everything is OK! Now get to work :).');

        return 1;
    }
}
