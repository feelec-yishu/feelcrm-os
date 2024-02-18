<?php
namespace Think;

class Csv
{
//	导出csv文件
	public function exportCsv($data,$tableHeader,$filename)
	{
		set_time_limit(0);//防止超时

		ini_set("memory_limit", "512M");//防止内存溢出

		header ( 'Content-Type: application/vnd.ms-excel' );

		header ( 'Content-Disposition: attachment;filename='.$filename );

		header ( 'Cache-Control: max-age=0' );

//		打开PHP文件句柄,php://output 表示直接输出到浏览器,$exportUrl表示输出到指定路径文件下
		$file = fopen('php://output',"a");

//		输出Excel列名信息
		$headerList = [];

		foreach ($tableHeader as $k=>$v)
		{
//			CSV的Excel支持GBK编码，一定要转换，否则乱码
			$headerList[$k] = iconv('UTF-8', 'GB2312//IGNORE',$v);
		}

//		将数据通过fputcsv写到文件句柄
		fputcsv($file,$headerList);

//		每隔$limit行，刷新输出缓冲区，不要太大，也不要太小
		$limit = 1000;

		$calc = 0;

		$row = [];

		foreach ($data as $v)
		{
			$calc++;

			//刷新输出缓冲区，防止由于数据过多造成问题
			if($limit == $calc)
			{
				ob_flush();

				flush();

				$calc = 0;
			}

//			每一行的数据
			foreach ($v as $vo)
			{
				$vo = is_numeric($vo) ? $vo."\t" : $vo;

				$row[] = iconv('UTF-8', 'GB2312//IGNORE',$vo);
			}

			fputcsv($file,$row);

			unset($row);
		}

		unset($list);

		fclose($file);

		exit;
	}
}