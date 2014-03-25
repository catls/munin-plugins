<?php

require_once dirname(__FILE__).'/../lib/Pixiv.php';

class PixivTest extends PHPUnit_Framework_TestCase {
    protected $Pixiv;

    public function setUp()
    {
        $cookie = sys_get_temp_dir()."/pixiv.cookie.txt";
        if(file_exists($cookie)){
            /** rename出来る権限が必要 */
            rename($cookie,$cookie.'.bak');
        }
        $this->Pixiv = new Pixiv();
        $this->assertEquals(false,$this->Pixiv->isLoggedIn());
    }
    public function tearDown()
    {
        $cookie = sys_get_temp_dir()."/pixiv.cookie.txt.bak";
        if(file_exists($cookie)){
            /** rename出来る権限が必要 */
            rename($cookie,preg_replace('/\.bak$/','',$cookie));
        }
    }

    public function testLogin()
    {
        $this->assertEquals(false,$this->Pixiv->isLoggedIn());

        $this->assertEquals(false,$this->Pixiv->logIn('dummy','dummy'));
        $this->assertEquals(false,$this->Pixiv->isLoggedIn());

        $this->assertEquals(true,$this->Pixiv->logIn($GLOBALS["PIXIV_ID"],$GLOBALS["PASSWORD"]));
        $this->assertEquals(true,$this->Pixiv->isLoggedIn());
    }

    public function testGetPage()
    {
        $url = 'http://www.pixiv.net/bookmark_tag_all.php';

        $this->Pixiv->logIn($GLOBALS["PIXIV_ID"],$GLOBALS["PASSWORD"]);
        $contents = $this->Pixiv->getPage($url);

        $this->assertEquals(true,preg_match('/あなたのブックマークタグ一覧/',$contents));
    }
}
