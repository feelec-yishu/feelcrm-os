<?php
/*
* Date:2019/05/28
* Author:Jesus
*/

namespace Think\Session\Driver;

class Redis
{
	private $handler;//Redis句柄

	private $get_result;

	public function __construct()
	{
		if (!extension_loaded('redis') )
		{
			E(L('_NOT_SUPPERT_').':redis');
		}

		if(empty($options))
		{
			$options = [
				'host'          => C('REDIS_HOST') ? C('REDIS_HOST') : '127.0.0.1',
				'port'          => C('REDIS_PORT') ? C('REDIS_PORT') : 6379,
				'auth'          => C('REDIS_AUTH') ? C('REDIS_AUTH') : false,
				'db'            => C('SESSION_REDIS_DB') ? C('SESSION_REDIS_DB') : 0,
				'timeout'       => C('SESSION_CACHE_TIME') ? C('SESSION_CACHE_TIME') : false,
				'persistent'    => C('SESSION_PERSISTENT') ? C('SESSION_PERSISTENT') : false,
			];
		}

		$options['host'] = explode(',', $options['host']);

		$options['port'] = explode(',', $options['port']);

		$options['auth'] = explode(',', $options['auth']);

		foreach ($options['host'] as $key=>$value)
		{
			if (!isset($options['port'][$key]))
			{
				$options['port'][$key] = $options['port'][0];
			}

			if (!isset($options['auth'][$key]))
			{
				$options['auth'][$key] = $options['auth'][0];
			}
		}

		$this->options =  $options;

		$expire = C('SESSION_EXPIRE');

		$this->options['expire'] = isset($expire) ? (int)$expire : (int)ini_get('session.gc_maxlifetime');;

		$this->options['prefix'] = isset($options['prefix']) ? $options['prefix'] : C('SESSION_PREFIX');

		$this->handler  = new \Redis;
	}


	/**
	* 连接Redis服务端
	* @access public
	* @param bool $is_master : 是否连接主服务器
	*/
	public function connect($is_master = true)
	{
		if($is_master)
		{
			$i = 0;
		}
		else
		{
			$count = count($this->options['host']);

			if ($count == 1)
			{
				$i = 0;
			}
			else
			{
				$i = rand(1, $count - 1);	//多个从服务器随机选择
			}
		}

		$func = $this->options['persistent'] ? 'pconnect' : 'connect';

		try
		{
			if ($this->options['timeout'] === false)
			{
				$result = $this->handler->$func($this->options['host'][$i], $this->options['port'][$i]);

				if (!$result)
					throw new \Think\Exception('Redis Error', 100);

			}
			else
			{
				$result = $this->handler->$func($this->options['host'][$i], $this->options['port'][$i], $this->options['timeout']);

				if (!$result)
					throw new \Think\Exception('Redis Error', 101);
			}

			if ($this->options['auth'][$i])
			{
				$result = $this->handler->auth($this->options['auth'][$i]);

				if (!$result)
				{
					throw new \Think\Exception('Redis Password Error', 102);
				}
			}

			$this->handler->select($this->options['db']);
		}
		catch ( \Exception $e )
		{
			exit('Error Message:'.$e->getMessage().'<br>Error Code:'.$e->getCode().'');
		}
	}


	/*
	* 打开Session
	* @access public
	* @param string $savePath
	* @param mixed $sessName
	*/
	public function open($savePath, $sessName)
	{
		return true;
	}


	/*
	* 关闭Session
	* @access public
	*/
	public function close()
	{
		if ($this->options['persistent'] == 'pconnect')
		{
			$this->handler->close();
		}

		return true;
	}


	/*
	* 读取Session
	* @access public
	* @param string $sessID
	*/
	public function read($sessID)
	{
		$this->connect(0);

		$this->get_result = $this->handler->get($this->options['prefix'].$sessID);

		if($this->get_result)
		{
			return $this->get_result;
		}
		else
		{
			//如果Redis中没有数据，这里只能返回空字符串，因为如果返回false，session将跳过write，直接close，造成不能储存到redis中，
			//具体原因不明，推测是因为read的返回值只能是字符串
			return '';
		}
	}


	/*
	* 写入Session
	* @access public
	* @param string $sessID
	* @param String $sessData
	*/
	public function write($sessID, $sessData,$expire = null)
	{
		if (!$sessData || $sessData == $this->get_result)
		{
			return true;
		}

		$this->connect(1);

		if(is_null($expire))
		{
			$expire = $this->options['expire'];
		}

		$sessID = $this->options['prefix'].$sessID;

		if(is_int($expire) && $expire > 0)
		{
			$result = $this->handler->setex($sessID, $expire, $sessData);

			$re = $result ? 'true' : 'false';
		}
		else
		{
			$result = $this->handler->set($sessID, $sessData);

			$re = $result ? 'true' : 'false';
		}

		return $result;
	}


	/*
	* 删除Session
	* @access public
	* @param string $sessID
	*/
	public function destroy($sessID)
	{
		$this->connect(1);

		return $this->handler->delete($this->options['prefix'].$sessID);
	}


	/*
	* Session 垃圾回收
	* @access public
	* @param string $sessMaxLifeTime
	*/
	public function gc($sessMaxLifeTime)
	{
		return true;
	}


	/**
	* 执行方法
	* @access public
	*/
	public function execute()
	{
		session_set_save_handler(
			array(&$this, "open"),
			array(&$this, "close"),
			array(&$this, "read"),
			array(&$this, "write"),
			array(&$this, "destroy"),
			array(&$this, "gc")
		);
	}


	public function __destruct()
	{
		if ($this->options['persistent'] == 'pconnect')
		{
			$this->handler->close();
		}

		session_write_close();
	}
}
