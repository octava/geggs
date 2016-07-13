<?php
namespace Octava\GeggsBundle\Helper;

use Octava\GeggsBundle\Exception\InvalidArgumentException;
use Octava\GeggsBundle\Exception\RuntimeException;

class ComposerHelper
{
    const COMPOSER_JSON = 'composer.json';
    const COMPOSER_LOCK = 'composer.lock';
    const COMPOSER_LOCK_CONFLICT = 'composer.lock_cc';

    public function extractAllVendors($composerJson)
    {
        $composer = $this->jsonDecode($composerJson);
        $vendors = !empty($composer['require']) ? array_keys($composer['require']) : [];
        $vendors = array_merge($vendors, !empty($composer['require-dev']) ? array_keys($composer['require-dev']) : []);

        return $vendors;
    }

    public function diff($lockOne, $lockTwo)
    {
        if (empty($lockOne)) {
            throw  new InvalidArgumentException('Invalid argument lockOne, must be not empty.');
        }
        if (empty($lockTwo)) {
            throw  new InvalidArgumentException('Invalid argument lockOne, must be not empty.');
        }

        $one = $this->jsonDecode($lockOne);
        $two = $this->jsonDecode($lockTwo);

        $onePackages = !empty($one['packages']) ? $one['packages'] : [];
        $twoPackages = !empty($two['packages']) ? $two['packages'] : [];
        $result = $this->findDiff($onePackages, $twoPackages);

        $onePackages = !empty($one['packages-dev']) ? $one['packages-dev'] : [];
        $twoPackages = !empty($two['packages-dev']) ? $two['packages-dev'] : [];
        $result = array_merge($result, $this->findDiff($onePackages, $twoPackages));

        return $result;
    }

    public function jsonDecode($json)
    {
        $result = json_decode($json, true);

        if (!$result) {
            throw new RuntimeException('Json decode error: '.json_last_error_msg());
        }

        return $result;
    }

    protected function findDiff(array $onePackages, array $twoPackages)
    {
        $one = array_column($onePackages, 'time', 'name');
        $two = array_column($twoPackages, 'time', 'name');

        $result = [];
        foreach ($one as $name => $date) {
            $oneDate = new \DateTime($date);
            if (array_key_exists($name, $two)) {
                $twoDate = new \DateTime($two[$name]);
                if ($oneDate != $twoDate) {
                    $result[] = $name;
                }
                unset($two[$name]);
            } else {
                $result[] = $name;
            }
        }

        $result = array_merge($result, array_diff(array_keys($two), array_keys($one)));

        return $result;
    }
}
