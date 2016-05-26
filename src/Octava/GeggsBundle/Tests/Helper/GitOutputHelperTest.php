<?php
namespace Octava\GeggsBundle\Tests\Helper;

use Octava\GeggsBundle\Helper\GitOutputHelper;

class GitOutputHelperTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @param $pattern
     * @param $expected
     *
     * @dataProvider dataProviderTestExtractLocalBranches
     */
    public function testExtractLocalBranches($pattern, $expected)
    {
        $actual = GitOutputHelper::extractLocalBranches($pattern);

        $this->assertEquals($actual, $expected);
    }

    public function dataProviderTestExtractLocalBranches()
    {
        $data = [];

        $data[] = [
            '  [TESTAWWW-13119] Merge branch \'master\' of git.srv.robofx.com:robofx/site
  [WWW-12986] WWW-12992: modify curl
* [master] change standart',
            [
                0 => 'TESTAWWW-13119',
                1 => 'WWW-12986',
                2 => 'master',
            ],
        ];

        return $data;
    }

    /**
     * @param $pattern
     * @param $expected
     *
     * @dataProvider dataProviderTestExtractRemoteBranches
     */
    public function testExtractRemoteBranches($pattern, $expected)
    {
        $actual = GitOutputHelper::extractRemoteBranches($pattern);

        $this->assertEquals($actual, $expected);
    }

    public function dataProviderTestExtractRemoteBranches()
    {
        $data = [];

        $data[] = [
            '  [origin/HEAD] WWW-12992: modify curl
  [origin/master] WWW-12992: modify curl
  [origin/WWW-12992] WWW-12992: modify curl
  [origin/svn] WWW-12947 Исправил отображение название страницы в структуре при редактирование',
            [
                'HEAD',
                'master',
                'WWW-12992',
                'svn',
            ],
        ];
        $data[] = [
            '  [origin/master] WWW-13119: fix geggs local config
  [origin/svn] WWW-12947 Исправил отображение название страницы в структуре при редактирование',
            [
                'master',
                'svn',
            ],
        ];

        return $data;
    }

    /**
     * @param $pattern
     * @param $expected
     *
     * @dataProvider dataProviderTestExtractRemoteBranches2
     */
    public function testExtractRemoteBranches2($pattern, $expected)
    {
        $actual = GitOutputHelper::extractRemoteBranches2($pattern);

        $this->assertEquals($actual, $expected);
    }

    public function dataProviderTestExtractRemoteBranches2()
    {
        $data = [];

        $data[] = [
            'From git@git.srv.robofx.com:avp-lib/Debug.git
8694f2ec35c6649fef01faeea93527f746b52c12	HEAD
1934f7869870fc2b51705a55ef82356d5880e816	refs/heads/TESTAWWW-13119
47713b1a9c3b9e722a4c139558291dac44459bc1	refs/heads/TESTBWWW-13119
8694f2ec35c6649fef01faeea93527f746b52c12	refs/heads/master
8e0d8b467ea0bccfbc97b7813341cd53c0d96429	refs/heads/svn',
            [
                'TESTAWWW-13119',
                'TESTBWWW-13119',
                'master',
                'svn',
            ],
        ];

        return $data;
    }
}
