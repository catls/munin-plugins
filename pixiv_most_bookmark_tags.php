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
    global $user,$password,$url;

    $Pixiv = new Pixiv($user,$password);
    $contents = $Pixiv->getPage($url);

    $html = str_get_html($contents);
    if($html != false && method_exists($html,'find')){
        $i = 1;
        foreach($html->find('.tag-list') as $element){
            foreach($element->find('dt') as $dt){
                $value = $dt->plaintext;
                $dt->next_sibling()->plaintext."<br />";
                foreach($dt->next_sibling()->find('li') as $dd){
                    $tag_name = $dd->plaintext;

                    if($tag_name != "未分類"){
                        echo "Rank{$i}.value {$value}\n";
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
    global $user,$password,$url;

    echo "graph_title pixiv most bookmark tags ";
    echo "graph_info  \n";
    echo "graph_category net:services\n";
    echo "graph yes\n";
    echo "graph_vlabel Counts\n";

    $Pixiv = new Pixiv($user,$password);
    $contents = $Pixiv->getPage($url);
    $html = str_get_html($contents);
    if($html != false && method_exists($html,'find')){
        $i = 1;
        foreach($html->find('.tag-list') as $element){
            foreach($element->find('dt') as $dt){
                $value = $dt->plaintext;
                $dt->next_sibling()->plaintext."<br />";
                foreach($dt->next_sibling()->find('li') as $dd){
                    $tag_name = $dd->plaintext;

                    if($tag_name != "未分類"){
                        echo "Rank{$i}.label Rank {$i}\n";
                        echo "Rank{$i}.info {$tag_name}\n";
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


