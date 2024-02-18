<?php

namespace Index\Controller;

use Think\Controller;

class GoogleController extends Controller
{
	protected $url = "http://192.168.0.180:8082/hello/index?";

	function translate()
	{
		$name = 'City';

		$data = M($name)->where(['name_en'=>''])->field('id,name')->order('id asc')->select();

		foreach($data as $v)
		{
			$url = $this->url.http_build_query(['content'=>$v['name']]);

			$name_en = $this->getCurlData($url);

			if($name_en)
			{
				M($name)->where(['id'=>$v['id']])->setField(['name_en'=>$name_en]);
			}

			sleep(5);
		}
	}

	function translateRegion()
	{
		$name = 'Region';

		$data = M($name)->where(['name_en'=>''])->field('id,name')->order('id asc')->select();

		foreach($data as $v)
		{
			$url = $this->url.http_build_query(['content'=>$v['name']]);

			$name_en = $this->getCurlData($url);

			if($name_en)
			{
				M($name)->where(['id'=>$v['id']])->setField(['name_en'=>$name_en]);
			}

			sleep(5);
		}
	}

	function getCurlData($url)
	{
		$curl = curl_init();

		curl_setopt($curl, CURLOPT_URL, $url);

		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

		curl_setopt($curl, CURLOPT_HEADER, 0);

		$data = curl_exec($curl);

		curl_close($curl);

		return $data;
	}
}