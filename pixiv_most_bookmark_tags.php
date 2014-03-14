#!/usr/bin/php
<?php
/**
* README
*
* [pixiv]
* env.pixiv_user munin
* env.pixiv_pass munin
*/

#%# family=auto
#%# capabilities=autoconf


require_once dirname(__FILE__).'/lib/Pixiv.php';
require_once dirname(__FILE__).'/lib/simple_html_dom.php';
require_once dirname(__FILE__).'/locale/PixivTag.php';

$user     = @getenv('pixiv_user');
$password = @getenv('pixiv_pass');

$url = 'http://www.pixiv.net/bookmark_tag_all.php';


if (!isset($argv[1])) {
    report();
}
else {
    if (function_exists($argv[1])) {
        eval($argv[1].'();');
    }
    else {
        echo 'Unknown argument '.$argv[1]."\n";
    }
}


function report() {
    global $user,$password,$url,$locale;

    $Pixiv = new Pixiv($user,$password);
    $contents = $Pixiv->getPage($url);

    $html = str_get_html($contents);
    if($html != false && method_exists($html,'find')){
        $i = 1;
        foreach($html->find('.tag-list') as $element){
            foreach($element->find('dt') as $dt){
                $value = $dt->plaintext;
                foreach($dt->next_sibling()->find('li') as $dd){
                    $tag_name = $dd->plaintext;
                    $tag_name_en = (array_key_exists($tag_name,$locale) && $locale[$tag_name])
                        ? $locale[$tag_name]
                        : $tag_name;

                    if($tag_name != "未分類"){
                        echo "{$tag_name_en}.value {$value}\n";
                        if($i++ >= 15){
                            break 3;
                        }
                    }
                }
            }
        }
        $html->clear();
    }
    unset($html);
}

function autoconf() {
    global $user,$password;

    $Pixiv = new Pixiv();
    $Pixiv->login($user,$password);
    if ($Pixiv->is_logged_in() == true) {
        echo "yes\n";
    }
    else{
        echo "ログイン出来ませんでした。\n";
        die();
    }
}

function config() {
    global $user,$password,$url,$locale;

    echo "graph_title pixiv most bookmark tags ";
    echo "graph_info  \n";
    echo "graph_category web:services\n";
    echo "graph yes\n";
    echo "graph_vlabel Counts\n";
    echo "graph_scale no\n";

    $Pixiv = new Pixiv($user,$password);
    $contents = $Pixiv->getPage($url);
    $html = str_get_html($contents);
    if($html != false && method_exists($html,'find')){
        $i = 1;
        foreach($html->find('.tag-list') as $element){
            foreach($element->find('dt') as $dt){
                $value = $dt->plaintext;
                foreach($dt->next_sibling()->find('li') as $dd){
                    $tag_name = $dd->plaintext;
                    $tag_name_en = (array_key_exists($tag_name,$locale) && $locale[$tag_name])
                        ? $locale[$tag_name]
                        : $tag_name;

                    if($tag_name != "未分類"){
                        echo "{$tag_name_en}.label {$tag_name_en}\n";
                        echo "{$tag_name_en}.info {$tag_name}\n";
                        if($i++ >= 15){
                            break 3;
                        }
                    }
                }
            }
        }
        $html->clear();
    }
    unset($html);
}



function getTags() {
    global $user,$password,$url,$locale;

    $Pixiv = new Pixiv($user,$password);

    $contents = $Pixiv->getPage($url);
    $html = str_get_html($contents);
    if($html != false && method_exists($html,'find')){
        $i = 1;
        foreach($html->find('.tag-list') as $element){
            foreach($element->find('dt') as $dt){
                $value = $dt->plaintext;
                foreach($dt->next_sibling()->find('li') as $dd){
                    $tag_name = $dd->plaintext;
                    $locale[$tag_name] = (array_key_exists($tag_name,$locale))
                        ? $locale[$tag_name]
                        : '';
                }
            }
        }
        $html->clear();
    }
    unset($html);

    $url_list = array(
        'http://www.pixiv.net/tags.php',
        'http://www.pixiv.net/tags.php?p=2',
        'http://www.pixiv.net/tags.php?p=3',
    );
    foreach($url_list as $url){
        $contents = $Pixiv->getPage($url);
        $html = str_get_html($contents);
        if($html != false && method_exists($html,'find')){
            foreach($html->find('.tag-list') as $element){
                foreach($element->find('.tag-name') as $li){
                    $tag_name = $li->plaintext;
                    $locale[$tag_name] = (array_key_exists($tag_name,$locale))
                        ? $locale[$tag_name]
                        : '';
                }
            }
            $html->clear();
        }
        unset($html);
    }

    echo "<?php\n";
    echo "\$locale = array(\n";
    foreach($locale as $key => $val){
        if(!preg_match('/^[a-zA-Z0-9]+$/',$key)){
            echo "\t'{$key}' => '{$val}',\n";
        }
    }
    echo ");";
}
?>
