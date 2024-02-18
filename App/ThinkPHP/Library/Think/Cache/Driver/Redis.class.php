<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2014 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
namespace Think\Cache\Driver;

use Think\Cache;

defined('THINK_PATH') or exit();

/**
 * Redis缓存驱动
 * 要求安装phpredis扩展：https://github.com/nicolasff/phpredis
 */
class Redis extends Cache
{
	 /**
	 * 架构函数
     * @param array $options 缓存参数
     * @access public
     */
    public function __construct($options=array())
    {
        if ( !extension_loaded('redis') )
        {
            E(L('_NOT_SUPPORT_').':redis');
        }

        $options = array_merge(array (
            'host'          => C('REDIS_HOST') ? : '127.0.0.1',
            'port'          => C('REDIS_PORT') ? : 6379,
            'timeout'       => C('DATA_CACHE_TIMEOUT') ? : false,
            'auth'       => C('REDIS_AUTH') ? : NULL,
            'persistent'    => false,
            'dbindex'    => C('REDIS_DBINDEX') ? C('REDIS_DBINDEX') : 0
        ),$options);

        $this->options =  $options;
        $this->options['expire'] =  isset($options['expire'])?  $options['expire']  :   C('DATA_CACHE_TIME');
        $this->options['prefix'] =  isset($options['prefix'])?  $options['prefix']  :   C('DATA_CACHE_PREFIX');
        $this->options['length'] =  isset($options['length'])?  $options['length']  :   0;
        $func = $options['persistent'] ? 'pconnect' : 'connect';
        $this->handler  = new \Redis;
        $options['timeout'] === false ?
            $this->handler->$func($options['host'], $options['port']) :
            $this->handler->$func($options['host'], $options['port'], $options['timeout']);
        $this->handler->auth($options['auth']);
        $this->handler->select($this->options['dbindex']);
    }

    /**
     * 读取缓存
     * @access public
     * @param string $name 缓存变量名
     * @return mixed
     */
    public function get($name)
    {
        N('cache_read',1);
        $value = $this->handler->get($this->options['prefix'].$name);
        $jsonData  = json_decode( $value, true );
        return ($jsonData === NULL) ? $value : $jsonData;	//检测是否为JSON数据 true 返回JSON解析数组, false返回源数据
    }

    /**
     * 写入缓存
     * @access public
     * @param string $name 缓存变量名
     * @param mixed $value  存储数据
     * @param integer $expire  有效时间（秒）
     * @return boolean
     */
    public function set($name, $value, $expire = null)
    {
        N('cache_write',1);
        if(is_null($expire)) {
            $expire  =  $this->options['expire'];
        }
        $name   =   $this->options['prefix'].$name;
        //对数组/对象数据进行缓存处理，保证数据完整性
        $value  =  (is_object($value) || is_array($value)) ? json_encode($value) : $value;
        if(is_int($expire) && $expire) {
            $result = $this->handler->setex($name, $expire, $value);
        }else{
            $result = $this->handler->set($name, $value);
        }
        if($result && $this->options['length']>0) {
            // 记录缓存队列
            $this->queue($name);
        }
        return $result;
    }


    /**
     * 删除缓存
     * @access public
     * @param string $name 缓存变量名
     * @return boolean
     */
    public function rm($name) {
        return $this->handler->delete($this->options['prefix'].$name);
    }


	/**
	 * 删除缓存
	 * @access public
	 * @return boolean
	 */
	public function delete($name)
	{
		if(is_array($name))
		{
			foreach($name as &$v)
			{
				$v = $this->options['prefix'].$v;
			}

			return $this->handler->del($name);
		}
		else
		{
			return $this->handler->del($this->options['prefix'].$name);
		}
	}


    /**
     * 清除缓存
     * @access public
     * @return boolean
     */
    public function clear() {
        return $this->handler->flushDB();
    }


	/*
	* 返回列表的长度
	* 如果列表 key 不存在，则 key 被解释为一个空列表，返回 0
	* 如果 key 不是列表类型，返回FALSE,
	*
	* @param string $key    列表键名
	*
	* @return int|bool The size of the list identified by Key exists.
	* bool FALSE if the data type identified by Key is not list
	*
	* @link    https://redis.io/commands/llen
	* @example
	* <pre>
	* $redis->rPush('key1', 'A');
	* $redis->rPush('key1', 'B');
	* $redis->rPush('key1', 'C'); // key1 => [ 'A', 'B', 'C' ]
	* $redis->lLen('key1');       // 3
	* $redis->rPop('key1');
	* $redis->lLen('key1');       // 2
	* </pre>
	*/
	public function lLen(string $key)
	{
		return $this->handler->lLen($key);
	}


	/**
	* 根据参数 COUNT 的值，移除列表中与参数 VALUE 相等的元素.
	* count > 0 : 从表头开始向表尾搜索，移除与 VALUE 相等的元素，数量为 COUNT ,
	* count < 0 : 从表尾开始向表头搜索，移除与 VALUE 相等的元素，数量为 COUNT 的绝对值.
	* count = 0 : 移除表中所有与 VALUE 相等的值
	* @param string $key        列表键名
	* @param string $value      要删除的元素
	* @param int    $count      要删除的数量
	*
	* @return int|bool 返回被移除元素的数量，列表不存在时返回 0
	*
	* @link    https://redis.io/commands/lrem
	* @example
	* <pre>
	* $redis->lPush('key1', 'A');
	* $redis->lPush('key1', 'B');
	* $redis->lPush('key1', 'C');
	* $redis->lPush('key1', 'A');
	* $redis->lPush('key1', 'A');
	*
	* $redis->lRange('key1', 0, -1);   // array('A', 'A', 'C', 'B', 'A')
	* $redis->lRem('key1', 'A', 2);    // 2
	* $redis->lRange('key1', 0, -1);   // array('C', 'B', 'A')
	* </pre>
	*/
	public function lRem($key, $value, $count)
	{
		return $this->handler->lRem($key,$value,$count);
	}


	/**
	* 移除集合中的一个或多个成员元素，不存在的成员元素会被忽略.
	*
	* @param string $key
	* @param mixed $member   $member1,$member2,...需要移除的成员
	* @return int The number of elements removed from the set
	*
	* @link    https://redis.io/commands/srem
	* @example
	* <pre>
	* var_dump( $redis->sAdd('k', 'v1', 'v2', 'v3') );    // int(3)
	* var_dump( $redis->sRem('k', 'v2', 'v3') );          // int(2)
	* var_dump( $redis->sMembers('k') );
	* //// Output:
	* // array(1) {
	* //   [0]=> string(2) "v1"
	* // }
	* </pre>
	*/
	public function sRem($key,$member)
	{
		return $this->handler->sRem($key,$member);
	}


	/**
	* 将一个或多个值插入到列表的尾部(最右边).
	* 如果列表不存在，一个空列表会被创建并执行 RPUSH 操作.
	* 当列表存在但不是列表类型时，返回FALSE.
	*
	* @param string $key    列表名称
	* @param string|mixed $value,$value1... #加推入列表的值，Redis 2.4以后的版本支持多个值
	*
	* @return int|bool
    * int   返回列表最新长度
    * bool  当列表key存在，但key不是列表类型时，返回FALSE
	*
	* @link    https://redis.io/commands/rpush
	* @example
	* <pre>
	* $redis->rPush('l', 'v1', 'v2', 'v3', 'v4');    // int(4)
	* var_dump( $redis->lRange('l', 0, -1) );
	* // Output:
	* // array(4) {
	* //   [0]=> string(2) "v1"
	* //   [1]=> string(2) "v2"
	* //   [2]=> string(2) "v3"
	* //   [3]=> string(2) "v4"
	* // }
	* </pre>
	*/
	public function rPush($key,$value)
	{
		return $this->handler->rPush($key,$value);
	}


	/**
	* 从左边移除并返回列表的第一个元素
	*
	* @param   string $key
	*
	* @return  mixed|bool if command executed successfully BOOL FALSE in case of failure (empty list)
	*
	* @link    https://redis.io/commands/lpop
	* @example
	* <pre>
	* $redis->rPush('key1', 'A');
	* $redis->rPush('key1', 'B');
	* $redis->rPush('key1', 'C');  // key1 => [ 'A', 'B', 'C' ]
	* $redis->lPop('key1');        // key1 => [ 'B', 'C' ]
	* </pre>
	*/
	public function lPop($key)
	{
		return $this->handler->lPop($key);
	}


	/**
	* 将一个或多个成员元素加入到集合中，已经存在于集合的成员元素将被忽略
    * 假如集合 key 不存在，则创建一个只包含添加的元素作成员的集合
	*
	* @param string       $key       # 集合Key
	* @param string|mixed $value     # 需要添加到集合的元素，可多个，$value1,$value2,...
	*
	* @return int|bool
    * int   被添加到集合中的新元素的数量，不包括被忽略的元素.
	* bool  当集合 key 不是集合类型时，返回FALSE
	*
	* @link    https://redis.io/commands/sadd
	* @example
	* <pre>
	* $redis->sAdd('k', 'v1');                // int(1)
	* $redis->sAdd('k', 'v1', 'v2', 'v3');    // int(2)
	* </pre>
	*/
	public function sAdd($key,$value)
	{
		return $this->handler->sAdd($key,$value);
	}


	/**
	* 判断成员元素是否是集合的成员.
	*
	* @param string       $key      # 集合Key
	* @param string|mixed $value    # 要检测的成员
	*
	* @return bool
    * 如果成员元素是集合的成员，返回 TRUE
	* 如果成员元素不是集合的成员，或 key 不存在，返回 FALSE
    *
	* @link    https://redis.io/commands/sismember
	* @example
	* <pre>
	* $redis->sAdd('key1' , 'set1');
	* $redis->sAdd('key1' , 'set2');
	* $redis->sAdd('key1' , 'set3'); // 'key1' => {'set1', 'set2', 'set3'}
	*
	* $redis->sIsMember('key1', 'set1'); // TRUE
	* $redis->sIsMember('key1', 'setX'); // FALSE
	* </pre>
	*/
	public function sIsMember($key, $value)
	{
		return $this->handler->sIsMember($key,$value);
	}


	/**
	* 设置 key 的过期时间
	*
	* @param string $key    # key名
	* @param int    $ttl    # 有效期，以秒为单位
	*
	* @return bool
	* 设置成功返回 TRUE 。 当 key 不存在或者不能为 key 设置过期时间时(比如在低于 2.1.3 版本的 Redis 中你尝试更新 key 的过期时间)返回 FALSE
	* @link    https://redis.io/commands/expire
	* @example
	* <pre>
	* $redis->set('x', '42');
	* $redis->expire('x', 3);  // x will disappear in 3 seconds.
	* sleep(5);                    // wait 5 seconds
	* $redis->get('x');            // will return `FALSE`, as 'x' has expired.
	* </pre>
	*/
	public function expire($key,$ttl)
	{
		return $this->handler->expire($key,$ttl);
	}


	/**
	* 以秒为单位返回 key 的剩余过期时间.
	*
	* @param string $key
	*
	* @return int|bool the time left to live in seconds
	* 当 key 不存在时，返回 -2 。 当 key 存在但没有设置剩余生存时间时，返回 -1 。 否则，以秒为单位，返回 key 的剩余生存时间
	* @link    https://redis.io/commands/ttl
	* @example
	* <pre>
	* $redis->setex('key', 123, 'test');
	* $redis->ttl('key'); // int(123)
	* </pre>
	*/
	public function ttl($key)
	{
		return $this->handler->ttl($key);
	}
}
