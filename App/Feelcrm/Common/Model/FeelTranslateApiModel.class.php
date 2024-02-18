<?php

namespace Common\Model;

use Think\BaiduTranslateApi;

use Think\Cache\Driver\Redis;

use Think\Model;

class FeelTranslateApiModel extends Model
{
    protected $autoCheckFields = false;

    /* 翻译
    * @param string $query 需要翻译的内容
    * @param string $from 源语言
    * @param string $from 译文语言
    * return array $result
    */
    function getTranslateData($query,$from,$to)
    {
        $config = ['app_id'=>'20180907000204071','secret_key'=>'VWB_P8PrOK067osZSmhY'];

        $translate = new BaiduTranslateApi($config);

        $result = $translate->translate($query,$from,$to);

        return $result;
    }

    /* 翻译
    * @param int    $company_id 公司ID
    * @param int    $value 相关ID ，比如文章ID
    * @param string $content 源内容
    * @param string $source 内容类型，比如 标题，文章内容
    * return string $result
    */
    function getTransferContent($company_id = 0,$value=0,$content,$source)
    {
        $redis = new Redis();

        $lang = strtolower(cookie('think_language'));

        $from = 'zh';

        $to = '';

        if($lang == 'en-us') $to = 'en';

        if($lang == 'ja-jp') $to = 'jp';

        if(in_array($lang,['en-us','ja-jp']))
        {
            if($source == 'category_name')
            {
                $category_name = $redis->get('FAQ_CATEGORY_NAME_'.strtoupper($to).'_'.$company_id.'_'.$value);

                if($category_name)
                {
                    return $category_name;
                }
                else
                {
                    $result = $this->getTranslateData($content,$from,$to);

                    $redis->set('FAQ_CATEGORY_NAME_'.strtoupper($to).'_'.$company_id.'_'.$value,$result['trans_result'][0]['dst'],3600);

                    return $result['trans_result'][0]['dst'];
                }
            }
            else if($source == 'article_title')
            {
                $article_title = $redis->get('FAQ_ARTICLE_TITLE_'.strtoupper($to).'_'.$company_id.'_'.$value);

                if($article_title)
                {
                    return $article_title;
                }
                else
                {
                    $result = $this->getTranslateData($content,$from,$to);

                    $redis->set('FAQ_ARTICLE_TITLE_'.strtoupper($to).'_'.$company_id.'_'.$value,$result['trans_result'][0]['dst'],3600);

                    return $result['trans_result'][0]['dst'];
                }
            }
            else if($source == 'article_content')
            {
                $article_content = $redis->get('FAQ_ARTICLE_CONTENT_'.strtoupper($to).'_'.$company_id.'_'.$value);

                if($article_content)
                {
                    return $article_content;
                }
                else
                {
                    $result = $this->getTranslateData($content,$from,$to);

                    $article_content = '';

                    foreach($result['trans_result'] as $cv)
                    {
                        $article_content .= $cv['dst'].'<br/>';
                    }

                    $redis->set('FAQ_ARTICLE_CONTENT_'.strtoupper($to).'_'.$company_id.'_'.$value,$article_content,3600);

                    return $article_content;
                }
            }
            else
            {
                return $content;
            }
        }
        else
        {
            return $content;
        }
    }
}
