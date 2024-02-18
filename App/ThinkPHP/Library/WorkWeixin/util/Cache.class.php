<?php
namespace WorkWeixin\util;

class Cache
{
    public static function setJsTicket($ticket,$company_id)
    {
        $memcache = self::getMemcache();
        $memcache->set("js_ticket",$ticket,$company_id); // js ticket有效期为7200秒，这里设置为7000秒
    }

    public static function getJsTicket($company_id)
    {
        $memcache = self::getMemcache();
        return $memcache->get("js_ticket",$company_id);
    }

    private static function getMemcache()
    {
        /*if (class_exists("Memcache"))
        {
            $memcache = new Memcache;
            if ($memcache->connect('localhost', 11211))
            {
                return $memcache;
            }
        }*/

        return new FileCache;
    }

    public static function get($key,$company_id)
    {
        return self::getMemcache()->get($key,$company_id);
    }

    public static function set($key, $value,$company_id)
    {
        self::getMemcache()->set($key, $value,$company_id);
    }
}

/**
 * fallbacks
 */
class FileCache
{
	function set($key, $value,$company_id)
	{
        if($key&&$value)
        {
            $data = json_decode($this->get_file(CACHE_PATH ."work_weixin_cache_{$company_id}.php"),true);

            $item = array();

            $item["$key"] = $value;

            $keyList = array('isv_corp_access_token','suite_access_token','js_ticket','corp_access_token');

            if(in_array($key,$keyList))
            {
                $item['expire_time'] = time() + 7000;
            }
            else
            {
                $item['expire_time'] = 0;
            }

            $item['create_time'] = time();

            $data["$key"] = $item;

            $this->set_file(CACHE_PATH."work_weixin_cache_{$company_id}.php",json_encode($data));
        }
	}

	function get($key,$company_id)
	{
        if($key)
        {
            $data = json_decode($this->get_file(CACHE_PATH ."work_weixin_cache_{$company_id}.php"),true);

            if($data&&array_key_exists($key,$data))
            {
                $item = $data["$key"];

                if(!$item) return false;

                if($item['expire_time']>0&&$item['expire_time'] < time()) return false;

                return $item["$key"];
            }
            else
            {
                return false;
            }
        }
	}

    function get_file($filename)
    {
        if (!file_exists($filename))
        {
            $fp = fopen($filename, "w");

            fwrite($fp, "<?php exit();?>" . '');

            fclose($fp);

            return false;
        }
        else
        {
            $content = trim(substr(file_get_contents($filename), 15));
        }

        return $content;
    }


    function set_file($filename, $content)
    {
        $fp = fopen($filename, "w");

        fwrite($fp, "<?php exit();?>" . $content);

        fclose($fp);
    }
}
