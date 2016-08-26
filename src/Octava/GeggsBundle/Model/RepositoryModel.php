<?php
namespace Octava\GeggsBundle\Model;

use Octava\GeggsBundle\Provider\AbstractProvider;
use ReflectionMethod;

/**
 * Class RepositoryModel
 * @package Octava\GeggsBundle\Model
 */
class RepositoryModel
{
    const TYPE_ROOT = 'root';

    const TYPE_VENDOR = 'vendor';

    /**
     * @var string
     */
    protected $type;

    /**
     * @var string
     */
    protected $absolutePath;

    /**
     * @var string
     */
    protected $rootPath;

    /**
     * @var AbstractProvider
     */
    protected $provider;

    /**
     * RepositoryModel constructor.
     * @param string $type
     * @param string $rootPath
     * @param string $path
     * @param AbstractProvider $provider
     */
    public function __construct($type, $rootPath, $path, AbstractProvider $provider)
    {
        $this->type = $type;
        $this->rootPath = $rootPath;
        $this->absolutePath = $path;
        $this->provider = $provider;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        $result = [];
        $result[] = (string)$this->getPath();
        $branch = $this->getBranch();
        if ($branch) {
            $result[] = ' (' . $branch . ')';
        }

        return implode('', $result);
    }

    /**
     * Relative path
     * @return string
     */
    public function getPath()
    {
        return substr($this->absolutePath, strlen($this->rootPath) + 1);
    }

    /**
     * @return string
     */
    public function getBranch()
    {
        $result = $this->getProvider()->run('rev-parse', ['--abbrev-ref', 'HEAD']);

        return $result;
    }

    /**
     * @return AbstractProvider
     */
    public function getProvider()
    {
        return $this->provider;
    }

    /**
     * @example symfony/symfony
     * @return string
     */
    public function getPackageName()
    {
        $result = null;
        if ($this->getType() === self::TYPE_VENDOR) {
            $parts = explode(DIRECTORY_SEPARATOR, $this->getAbsolutePath());

            $parts = array_slice($parts, count($parts) - 2);
            $result = implode(DIRECTORY_SEPARATOR, $parts);
        }

        return $result;
    }

    /**
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return string
     */
    public function getAbsolutePath()
    {
        return $this->absolutePath;
    }

    /**
     * @example dev-master
     * @return string
     */
    public function getVersion()
    {

    }

    /**
     * @return string
     */
    public function dump()
    {
        $result = [];

        $reflection = new \ReflectionClass($this);
        $methods = $reflection->getMethods(ReflectionMethod::IS_PUBLIC);

        foreach ($methods as $method) {
            if (0 === strpos($method->getName(), 'get') && !in_array($method->getName(), ['getProvider'])) {
                $propertyName = lcfirst(substr($method->getName(), 3));
                $result[$propertyName] = $method->invoke($this);
            }
        }

        return $result;
    }

    /**
     * @return bool
     */
    public function hasChanges()
    {
        $raw = $this->getRawStatus();

        return !empty($raw);
    }

    /**
     * Result of `git status` command
     * @return string
     */
    public function getRawStatus()
    {
        return $this->getProvider()->run('status', ['--porcelain']);
    }

    /**
     * @return bool
     */
    public function hasCommits()
    {
        $output = $this->getProvider()->run(
            'log',
            [
                '--branches',
                '--not',
                '--remotes',
                '--simplify-by-decoration',
                '--decorate',
                '--oneline',
            ]
        );
        $result = trim($output);

        return !empty($result);
    }

    /**
     * @return array
     */
    public function getUnpushedCommits()
    {
        $branch = $this->getBranch();
        $output = $this->getProvider()->run('log', ['origin/' . $branch . '..' . $branch]);

        $result = [];
        $i = -1;
        foreach (explode("\n", $output) as $line) {
            $line = trim($line);
            if (empty($line)) {
                continue;
            }
            if (preg_match('/^commit\s+.*$/i', $line)) {
                $i++;
                $result[$i] = 'no text for commit';
                continue;
            }
            if (preg_match('/^Date:\s*/', $line)) {
                continue;
            }
            if ($i >= 0) {
                $result[$i] = $line;
            }
        }

        return $result;
    }

    public function hasConflicts()
    {
        $output = $this->getConflicts();

        return !empty($output);
    }

    /**
     * @return string
     */
    public function getConflicts()
    {
        $output = $this->getProvider()->run(
            'diff',
            [
                '--name-status',
                '--diff-filter=U',
            ]
        );

        $result = str_replace('U', 'C', trim($output));

        return $result;
    }

    /**
     * @return bool
     */
    public function hasRemote()
    {
        return $this->getProvider()->hasRemoteBranch($this->getBranch());
    }
}
