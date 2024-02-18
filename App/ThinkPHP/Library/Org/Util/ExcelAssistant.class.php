<?php
namespace Org\Util;
/**
 * Created by PhpStorm.
 * Author: Yishu
 * Date: 2019.01.09
 * Time: 18:29
 */
class ExcelAssistant
{
	private $carr = array();//结果数组，初始值为A

	private $curlet = 'A';//当前末尾字母，初始化为A

	private $curletpi = 0;//当前字符串从右向左的位数，用来上位递归加1处理（从0开始）

	private $tmpar = array('A');//临时数组，存储结果字符串上每位的字符。初始值为'A'

	private static $a = array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z');

	private static $b = array('A'=>0,'B'=>1,'C'=>2,'D'=>3,'E'=>4,'F'=>5,'G'=>6,'H'=>7,'I'=>8,'J'=>9,'K'=>10,'L'=>11,'M'=>12,'N'=>13,'O'=>14,'P'=>15,'Q'=>16,'R'=>17,'S'=>18,'T'=>19,'U'=>20,'V'=>21,'W'=>22,'X'=>23,'Y'=>24,'Z'=>25);

	/*
	* 获取excel列头
	* @param $num 列数总计
	*/
	public function GetExcelTit($num)
	{
		$i = 0;

		while($i<$num)
		{
			$this->curletpi = 0;//没有处理上位的时候，只处理当前位

			$this->tmpar[count($this->tmpar)-1] = $this->curlet;

			$this->carr[] = implode('',$this->tmpar);

			$this->curlet = $this->GetNextLetter($this->curlet);

			if($this->curlet == 'A')
			{
				//说明过了一圈，该向上位递归加1了
				$this->curletpi++;//从当前位左边的那位开始处理

				$this->RecursiveAddUp();
			}

			$i++;
		}

		return $this->carr;
	}

	/**
	 * 根据字母获取下位字母
	 * A-Z循环
	 */
	public function GetNextLetter($l)
	{
		$k = self::$b[$l];//当前字母索引

		$k++;//下位字母索引

		if($k == 26)
		{
			$l = 'A';//反转
		}
		else
		{
			$l = self::$a[$k];
		}

		return $l;
	}

	/**
	 * 递归向上位加一
	 * 在这里，只有一次计算结果为A后，才会向上加1，
	 * 但不是每个位加了之后都要往上位冒泡，所以不能遍历每个位
	 * @author RexCao
	 */
	public function RecursiveAddUp(){
		//先更新最右位的字母
		$this->tmpar[count($this->tmpar)-1] = 'A';

		if($this->curletpi+1>count($this->tmpar))
		{
			$this->tmpar = array_merge(array('A'),$this->tmpar);
		}
		else
		{
			$cl = $this->tmpar[count($this->tmpar)-$this->curletpi-1];//当前位的字母

			$cl = $this->GetNextLetter($cl);

			if($cl == 'A')
			{
				$this->tmpar[count($this->tmpar)-$this->curletpi-1] = 'A';//要去处理更上位了，先更新本位

				$this->curletpi++;//再上一位

				$this->RecursiveAddUp();
			}
			else
			{
				//更新当前位的字母为新字母即可
				$this->tmpar[count($this->tmpar)-$this->curletpi-1] = $cl;
			}
		}
	}
}