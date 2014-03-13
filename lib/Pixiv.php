<?php

class Pixiv {
    const LOGIN_URL = 'http://www.pixiv.net/login.php';

    protected $ch;
    protected $is_logged_in = false;
    protected $cookie;

    public $pixiv_id;
    public $password;

    public function __construct($pixiv_id='',$password=''){
        if($pixiv_id) $this->pixiv_id = $pixiv_id;
        if($password) $this->password = $password;

        setlocale(LC_ALL, 'ja_JP.UTF-8');
        $this->cookie = sys_get_temp_dir()."/pixiv.cookie.txt";

        // curl の初期化
        $headers = array(
            'User-Agent: Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.9.0.1) Gecko/2008070208 Firefox/3.0.1',
            'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
            'Accept-Language: en-us,en;q=0.8,de;q=0.6,ja;q=0.4,id;q=0.2',
            'Accept-Charset: ISO-8859-1,utf-8;q=0.7,*;q=0.7',
            'Keep-Alive: 300',
            'Connection: keep-alive',
        );
        $this->ch = curl_init();
        curl_setopt($this->ch, CURLOPT_HEADER, 0);
        curl_setopt($this->ch, CURLOPT_HTTPHEADER,$headers);
        curl_setopt($this->ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($this->ch, CURLOPT_COOKIESESSION,true);
        curl_setopt($this->ch, CURLOPT_COOKIEJAR,$this->cookie);
        curl_setopt($this->ch, CURLOPT_COOKIEFILE,$this->cookie);
    }

    /**
     * Proxyをセットする
     */
    public function setProxy($proxy){
        list($proxy_ip,$proxy_port) = explode(':',$proxy);
        curl_setopt($this->ch, CURLOPT_HTTPPROXYTUNNEL, TRUE);
        curl_setopt($this->ch, CURLOPT_PROXY,$proxy_ip);
        curl_setopt($this->ch, CURLOPT_PROXYPORT,$proxy_port);
    }

    /**
     * ログインする
     */
    public function login($pixiv_id='',$password=''){
        if($pixiv_id) $this->pixiv_id = $pixiv_id;
        if($password) $this->password = $password;

        curl_setopt($this->ch, CURLOPT_URL,self::LOGIN_URL);
        curl_setopt($this->ch, CURLOPT_POST, true);
        curl_setopt($this->ch, CURLOPT_POSTFIELDS,
            http_build_query(array(
                'pixiv_id' => $this->pixiv_id,
                'pass'     => $this->password,
                'mode'     => 'login'
            ))
        );

        $response = curl_exec($this->ch);

        if(preg_match('/プロフィールを見る/',$response)){
            $this->is_logged_in = true;
        }
        else{
            echo "ログインに失敗しました。\n";
        }
        return $response;
    }

    public function is_logged_in(){
        return $this->is_logged_in;
    }

    /**
     * URLを取得する
     */
    public function getPage($url,$data = array()){
        if(!$this->is_logged_in()){
            $this->login();
        }

        curl_setopt($this->ch, CURLOPT_URL,$url);
        curl_setopt($this->ch, CURLOPT_HTTPGET, true);
        if($data){
            curl_setopt($this->ch, CURLOPT_POSTFIELDS,$data);
        }
        $response = curl_exec($this->ch);

        return $response;
    }
}

?>
