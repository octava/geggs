<?php
namespace Octava\GeggsBundle\Tests\Helper;

use Octava\GeggsBundle\Helper\ComposerHelper;

class ComposerLockHelperTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider dataProviderTestDiff
     * @param $lockOne
     * @param $lockTwo
     */
    public function testDiff($lockOne, $lockTwo, $expected, $exception = '')
    {
        if ($exception) {
            $this->setExpectedExceptionRegExp($exception);
        }

        $helper = new ComposerHelper();
        $actual = $helper->diff($lockOne, $lockTwo);
        $this->assertEquals($expected, $actual);
    }

    public function dataProviderTestDiff()
    {
        $data = [];

        $data[] = [
            file_get_contents(__DIR__.'/composer_locks/0-1.composer.lock'),
            file_get_contents(__DIR__.'/composer_locks/0-2.composer.lock'),
            [],
        ];
        $data[] = [
            file_get_contents(__DIR__.'/composer_locks/1-1.composer.lock'),
            file_get_contents(__DIR__.'/composer_locks/1-2.composer.lock'),
            [
                'zendframework/zend-servicemanager',
            ],
        ];
        $data[] = [
            file_get_contents(__DIR__.'/composer_locks/2-1.composer.lock'),
            file_get_contents(__DIR__.'/composer_locks/2-2.composer.lock'),
            [
                'composer/composer',
            ],
        ];
        $data[] = [
            file_get_contents(__DIR__.'/composer_locks/3-1.composer.lock'),
            file_get_contents(__DIR__.'/composer_locks/3-2.composer.lock'),
            [
                'symfony/symfony',
                'composer/ca-bundle',
            ],
        ];
        $data[] = [
            file_get_contents(__DIR__.'/composer_locks/4-1.composer.lock'),
            file_get_contents(__DIR__.'/composer_locks/4-2.composer.lock'),
            [
                'zendframework/zend-servicemanager',
                'composer/ca-bundle',
                'composer/composer',
            ],
        ];

        return $data;
    }
}
