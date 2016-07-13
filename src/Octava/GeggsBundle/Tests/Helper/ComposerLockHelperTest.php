<?php
namespace Octava\GeggsBundle\Tests\Helper;

use Octava\GeggsBundle\Helper\ComposerLockHelper;

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

        $helper = new ComposerLockHelper();
        $actual = $helper->diff($lockOne, $lockTwo);
        $this->assertEquals($expected, $actual);
    }

    public function dataProviderTestDiff()
    {
        $data = [];

        $data[] = [
            file_get_contents(__DIR__.'/composer_locks/0-1.composer.lock'),
            file_get_contents(__DIR__.'/composer_locks/0-2.composer.lock'),
            [
                'zendframework/zend-servicemanager',
            ],
        ];
        $data[] = [
            file_get_contents(__DIR__.'/composer_locks/1-1.composer.lock'),
            file_get_contents(__DIR__.'/composer_locks/1-2.composer.lock'),
            [
                'composer/composer',
            ],
        ];

        return $data;
    }
}
