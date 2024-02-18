<?php

namespace Common\Model;

use Think\Model;

class BasicModel extends Model
{
	protected $pk   = '';

    protected $tableName =  '';


	/**
	* @return array
	*/
    public function fetchAll()
	{
		$result = $this->select();

		$data = [];

		foreach($result as $v)
		{
			$data[$v[$this->pk]] = $v;
		}

        return $data;
	}


	public function isExistedByField($where = [])
	{
		$pk = $this->where($where)->getField($this->pk);

		return $pk;
	}


    public function checkFields($data=[], $fields=[])
    {
        foreach ($data as $k => $val )
        {
            if (!in_array($k, $fields))
            {
                unset($data[$k]);
            }
        }

        return $data;
    }


    public function getFieldInfo($table,$where,$field,$isLangName = 0)
    {
        $lang = cookie('think_language');

        if($isLangName == 1)
        {
            if($lang == 'en-us') $field = 'name_en';

            if($lang == 'ja-jp') $field = 'name_en';
        }

        return M($table)->where($where)->getField($field);
    }


	/*
	* 通过当前语言变量返回要读取的语言字段
	* @param String $fieldName 字段名
	* @return String
	*/
    public function getNameByLang($fieldName)
    {
        $lang = cookie('think_language');

        $name = $fieldName.' as lang_name';

        if($lang == 'en-us') $name = 'name_en as lang_name';

        if($lang == 'ja-jp') $name = 'name_jp as lang_name';

        return $name;
    }
}
