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
                0 => 'HEAD',
                1 => 'master',
                2 => 'WWW-12992',
                3 => 'svn',
            ],
        ];

        return $data;
    }
}
