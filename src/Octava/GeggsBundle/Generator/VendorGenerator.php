<?php
namespace Octava\GeggsBundle\Generator;

use Octava\GeggsBundle\Model\Vendor;
use Sensio\Bundle\GeneratorBundle\Generator\Generator;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\ProcessBuilder;

/**
 * Class VendorGenerator
 * @package Octava\GeggsBundle\Generator
 */
class VendorGenerator extends Generator
{
    private $filesystem;

    /**
     * VendorGenerator constructor.
     * @param Filesystem $filesystem
     */
    public function __construct(Filesystem $filesystem)
    {
        $this->filesystem = $filesystem;
    }

    /**
     * @return Filesystem
     */
    public function getFilesystem()
    {
        return $this->filesystem;
    }

    /**
     * @param Vendor $vendor
     */
    public function generate(Vendor $vendor)
    {
        $this->_checkRepositoryExists($vendor->getFullRepositoryUrl());
        $this->_checkTargetDirectory($vendor->getTargetDirectory());

        $dir = $vendor->getTargetDirectory();
        $parameters = [
            'name' => $vendor->getName(),
            'short_name' => $vendor->getShortName(),
            'description' => 'description',
        ];

        $this->renderFile('vendor/README.md.twig', $dir.'/README.md', $parameters);
        $this->renderFile('vendor/composer.json.twig', $dir.'/composer.json', $parameters);
        $this->renderFile('vendor/phpunit.xml.twig', $dir.'/phpunit.xml', $parameters);
        $this->renderFile('vendor/.htaccess.twig', $dir.'/src/.htaccess', $parameters);

        $this->_gitInit($vendor);
    }

    /**
     * @param $dir
     */
    protected function _checkTargetDirectory($dir)
    {
        if (file_exists($dir)) {
            if (!is_dir($dir)) {
                throw new \RuntimeException(
                    sprintf(
                        'Unable to generate the vendor as the target directory "%s" exists but is a file.',
                        realpath($dir)
                    )
                );
            }
            $files = scandir($dir);
            if ($files != ['.', '..']) {
                throw new \RuntimeException(
                    sprintf('Unable to generate the vendor as the target directory "%s" is not empty.', realpath($dir))
                );
            }
            if (!is_writable($dir)) {
                throw new \RuntimeException(
                    sprintf(
                        'Unable to generate the vendor as the target directory "%s" is not writable.',
                        realpath($dir)
                    )
                );
            }
        }
    }

    /**
     * @param Vendor $vendor
     */
    protected function _gitInit(Vendor $vendor)
    {
        $baseBuilder = new ProcessBuilder();
        $baseBuilder->setWorkingDirectory($vendor->getTargetDirectory());
        $baseBuilder->setPrefix('git'); //TODO: bin from config

        $builder = clone $baseBuilder;
        $builder->add('init');
        $builder->getProcess()->mustRun();

        $builder = clone $baseBuilder;
        $builder->add('add');
        $builder->add('.');
        $builder->getProcess()->mustRun();

        $builder = clone $baseBuilder;
        $builder->add('remote');
        $builder->add('add');
        $builder->add('origin');
        $builder->add($vendor->getFullRepositoryUrl());
        $builder->getProcess()->mustRun();
    }

    private function _checkRepositoryExists($repositoryUrl)
    {
        $builder = new ProcessBuilder();
        $builder->setPrefix('git'); //TODO: bin from config
        $builder
            ->add('ls-remote')
            ->add($repositoryUrl);
        $process = $builder->getProcess();
        $process->run();

        if (0 === $process->getExitCode()) {
            throw  new \RuntimeException(sprintf('Repository "%s" already exists', $repositoryUrl));
        }
    }
}
