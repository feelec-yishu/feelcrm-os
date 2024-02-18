<?php

namespace Common\Model;

use Think\Csv;
use Think\Model;

class ExcelModel extends Model
{
    protected $autoCheckFields = false;



    public function getDataAnalysis($data,$tableHeader,$value,$name,$filename)
    {
        $str = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";

        $excelData = [];

        $status_num = count($data['status']);

        $letter = ['A','B'];

//        表头数组
        for($j=0;$j<$status_num;$j++)
        {
//            Excel表格式,根据状态数量决定列表格式
            $letter[$j+2] = substr($str,$j+2,1);

            $tableHeader[$j+2] = $data['status'][$j]['lang_name'];

        }

//        表格数组，数据内容
        foreach($data[$value] as $ck=>$cv)
        {
            $tempData[$ck] = [
                $cv[$name],
                $cv['ticket_num'] ? $cv['ticket_num'] : 0
            ];

            foreach($cv['status'] as &$cs)
            {
                $cs = $cs." (".getPercentage($cv['ticket_num'] ? $cv['ticket_num'] : 0,$cs).")";
            }

            foreach($tempData as $v)
            {
                $excelData[$ck] = array_merge($v,$cv['status']);
            }
        }

//        合计
        $a = 1;

        $total_num = $data['total']['ticket_num'];

        $total_status = $data['total']['status'];

        $totalData[0] = [L('TOTAL'),$total_num];

        foreach($total_status as &$v2)
        {
            $a++;

            $totalData[0][$a] = $v2." (".getPercentage($total_num ? $total_num : 0,$v2).")";
        }

        $excelData = array_merge($excelData,$totalData);

        if($value == 'channel')
        {
            foreach($excelData as $k=>&$v)
            {
                if($v[0] == 'PC')      $v[0] = 'PC';
                if($v[0] == 'Email')   $v[0] = L('MAIL');
                if($v[0] == 'API')     $v[0] = 'API';
                if($v[0] == 'Wechat')  $v[0] = L('WECHAT');
                if($v[0] == 'Mobile')  $v[0] = L('MOBILE_PHONE');
                if($v[0] == 'Visitor') $v[0] = L('VISITOR');
                if($v[0] == 'service') $v[0] = L('ONLINE_SERVICE');
                if($v[0] == 'crm')     $v[0] = 'CRM';
            }
        }

	    $filename = $filename.'-'.date('ymdhis').".csv";

	    $csv = new Csv();

	    $csv->exportCsv($excelData,$tableHeader,$filename);
/*
	    $filename = $filename.".xls";

	    $this->exportExcel($filename,$tableHeader,$letter,$excelData);
*/
    }



    /*
    * 输出Excel文件
    * @param   string  $filename       文件名称
    * @param   array   $tableHeader    表头数据
    * @param   array   $letter         列表标识 A B C D
    * @param   array   $excelData      表格数据
    */
    public function exportExcel($filename,$tableHeader,$letter,$excelData,$source='')
    {
        import('Org.Util.PHPExcel');//手动加载第三方插件

        $excel = new \PHPExcel();

        $objActSheet = $excel->getActiveSheet();

//        填充表头信息
        for($t = 0;$t < count($tableHeader);$t++)
        {
//            设置表头数据
            $objActSheet->setCellValue("$letter[$t]1","$tableHeader[$t]");

//            设置列宽
            $objActSheet->getColumnDimension("$letter[$t]")->setWidth('25');

//            文本居中
            $objActSheet->getStyle("$letter[$t]1")->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

//            加粗
            $objActSheet->getStyle("$letter[$t]1")->applyFromArray(['font'=> ['bold'=> true]]);

//            行高
            $objActSheet->getRowDimension(1)->setRowHeight('30');

//            垂直居中
            $objActSheet->getStyle("$letter[$t]1")->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
        }

//        填充表格信息
        for($i = 2;$i <= count($excelData)+1;$i++)
        {
            $l = 0;

            foreach($excelData[$i - 2] as $key=>$value)
            {
                //设置单元格数据
                $objActSheet->setCellValue("$letter[$l]$i","$value");

                //文本居中
                $objActSheet->getStyle("$letter[$l]$i")->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

                //设置行高
                $objActSheet->getRowDimension($i)->setRowHeight('20');

                //垂直居中
                $objActSheet->getStyle("$letter[$l]$i")->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);

                if($i == count($excelData)+1 && $source != 'ticket' && $source != 'feelcrm')
                {
                    //行高
                    $objActSheet->getRowDimension($i)->setRowHeight('30');

                    //加粗
                    $objActSheet->getStyle("$letter[$l]$i")->applyFromArray(['font'=> ['bold'=> true]]);
                }

                $l++;
            }
        }

//        创建Excel输入对象
        $write = new \PHPExcel_Writer_Excel5($excel);

        header("Pragma: public");

        header("Expires: 0");

        header("Cache-Control:must-revalidate, post-check=0, pre-check=0");

        header("Content-Type:application/force-download");

        header("Content-Type:application/vnd.ms-execl");

        header("Content-Type:application/octet-stream");

        header("Content-Type:application/download");;

        header('Content-Disposition:attachment;filename='.$filename);

        header("Content-Transfer-Encoding:binary");

        $write->save('php://output');
    }

	 /*
    * 输出Excel文件
    * @param   string  $filename       文件名称
    * @param   array   $tableHeader    表头数据
    * @param   array   $letter         列表标识 A B C D
    * @param   array   $excelData      表格数据
    * @param   array   $merage         要合并的单元格
    */
    public function exportExcelShipment($filename,$tableHeader1,$tableHeader2,$letter1,$letter2,$excelData,$merage,$source='')
    {
        import('Org.Util.PHPExcel');//手动加载第三方插件

        $excel = new \PHPExcel();

        $objActSheet = $excel->getActiveSheet();
		
		foreach($merage as $val)
		{
			$objActSheet->mergeCells("$val[0]1:$val[1]1"); //合并单元格
		}
		
//        填充表头信息
        for($t = 0;$t < count($tableHeader1);$t++)
        {
//            设置表头数据
            $objActSheet->setCellValue("$letter1[$t]1","$tableHeader1[$t]");

//            设置列宽
            $objActSheet->getColumnDimension("$letter1[$t]")->setWidth('25');

//            文本居中
            $objActSheet->getStyle("$letter1[$t]1")->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

//            加粗
            $objActSheet->getStyle("$letter1[$t]1")->applyFromArray(['font'=> ['bold'=> true]]);

//            行高
            $objActSheet->getRowDimension(1)->setRowHeight('30');

//            垂直居中
            $objActSheet->getStyle("$letter1[$t]1")->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);	
        }
		
		for($t = 0;$t < count($tableHeader2);$t++)
        {
//            设置表头数据
            $objActSheet->setCellValue("$letter2[$t]2","$tableHeader2[$t]");

//            设置列宽
            $objActSheet->getColumnDimension("$letter2[$t]")->setWidth('25');

//            文本居中
            $objActSheet->getStyle("$letter2[$t]2")->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

//            加粗
            $objActSheet->getStyle("$letter2[$t]2")->applyFromArray(['font'=> ['bold'=> true]]);

//            行高
            $objActSheet->getRowDimension(2)->setRowHeight('30');

//            垂直居中
            $objActSheet->getStyle("$letter2[$t]2")->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
        }

//        填充表格信息
        for($i = 3;$i <= count($excelData)+2;$i++)
        {
            $l = 0;

            foreach($excelData[$i - 3] as $key=>$value)
            {
                //设置单元格数据
                $objActSheet->setCellValue("$letter2[$l]$i","$value");

                //文本居中
                $objActSheet->getStyle("$letter2[$l]$i")->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

                //设置行高
                $objActSheet->getRowDimension($i)->setRowHeight('20');

                //垂直居中
                $objActSheet->getStyle("$letter2[$l]$i")->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);

                if($i == count($excelData)+1 && $source != 'ticket' && $source != 'feelcrm')
                {
                    //行高
                    $objActSheet->getRowDimension($i)->setRowHeight('30');

                    //加粗
                    $objActSheet->getStyle("$letter2[$l]$i")->applyFromArray(['font'=> ['bold'=> true]]);
                }

                $l++;
            }
        }

//        创建Excel输入对象
        $write = new \PHPExcel_Writer_Excel5($excel);

        header("Pragma: public");

        header("Expires: 0");

        header("Cache-Control:must-revalidate, post-check=0, pre-check=0");

        header("Content-Type:application/force-download");

        header("Content-Type:application/vnd.ms-execl");

        header("Content-Type:application/octet-stream");

        header("Content-Type:application/download");;

        header('Content-Disposition:attachment;filename='.$filename);

        header("Content-Transfer-Encoding:binary");

        $write->save('php://output');
    }

	/*
	* 生成模板文件
	* @param   string  $company_id     商户id
	*/
	public function createCrmImportTemp($company_id,$type)
	{
		$tableHeader = [];

		$form = getCrmDbModel('define_form')->where(['company_id'=>$company_id,'type'=>$type,'closed'=>0])->order('orderby')->select();

		foreach ($form as $key => $val)
		{
			$Header['title'] = $val['form_description'];

			$select = '';

			if(in_array($val['form_type'],['radio','select','select_text']))
			{
				$select = explode('|',$val['form_option']);
			}

			$Header = [
				[
					'title'=>$val['is_required'] == 0 ? '* '.$val['form_description'] : $val['form_description'],
					'select'=>$select,
				]
			];

			$tableHeader = array_merge($tableHeader,$Header);
		}

		if($type == 'clue')
		{
			$memberHeader = [['title'=>'* 负责人', 'select'=>'']];

			$createtimeHeader = [['title'=>'创建时间', 'select'=>'']];

			$tableHeader = array_merge($tableHeader,$createtimeHeader);

			$this->generateExcelModel($company_id,$tableHeader,'cluePool');

			$tableHeader = array_merge($memberHeader,$tableHeader);

			$this->generateExcelModel($company_id,$tableHeader,'clue');
		}

		if($type == 'opportunity')
		{
			$customerHeader = [['title'=>'* 所属客户', 'select'=>'']];

			$createtimeHeader = [['title'=>'创建时间', 'select'=>'']];

			$tableHeader = array_merge($customerHeader,$tableHeader,$createtimeHeader);

			$this->generateExcelModel($company_id,$tableHeader,'opportunity');
		}

		if($type == 'customer')
		{
			$contacterHeader = [];

			$form = getCrmDbModel('define_form')->where(['company_id'=>$company_id,'type'=>'contacter','closed'=>0])->order('orderby')->select();

			foreach ($form as $key => $val)
			{
				$Header['title'] = $val['form_description'];

				$select = '';

				if(in_array($val['form_type'],['radio','select','select_text']))
				{
					$select = explode('|',$val['form_option']);
				}

				$Header = [
					[
						'title'=>$val['is_required'] == 0 ? '* '.$val['form_description'] : $val['form_description'],
						'select'=>$select,
					]
				];

				$contacterHeader = array_merge($contacterHeader,$Header);
			}

			$memberHeader = [['title'=>'* 负责人', 'select'=>'',]];

			$this->generateExcelModel($company_id,$tableHeader,'customerPool',$contacterHeader);

			$tableHeader = array_merge($memberHeader,$tableHeader);

			$this->generateExcelModel($company_id,$tableHeader,'customer',$contacterHeader);
		}

		if($type == 'contract')
		{
			$customerHeader = [['title'=>'* 所属客户', 'select'=>'']];

			$memberHeader = [['title'=>'* 负责人', 'select'=>'']];

			$examineHeader = [['title'=>'* 审核人', 'select'=>'']];

			$createtimeHeader = [['title'=>'创建时间', 'select'=>'']];

			$tableHeader = array_merge($customerHeader,$memberHeader,$examineHeader,$tableHeader,$createtimeHeader);

			$this->generateExcelModel($company_id,$tableHeader,'contract');
		}

		if($type == 'product')
		{
			$product_type = getCrmDbModel('product_type')->where(['company_id'=>$company_id,'closed'=>0])->select();

			if($product_type)
			{
				$product_type  = getMenuTree($product_type,'type_id','parent_id','subClass');

				$type_arr = $this->resultCategory($product_type);
			}
			else
			{
				$type_arr = ['暂无分类'];
			}

			$productTypeHeader = [
				[
					'title'=>'* 产品分类',
					'select'=>$type_arr,
				]
			];

			$tableHeader = array_merge($productTypeHeader,$tableHeader);

			$status = [['title'=>'* 是否上架', 'select'=>['上架','下架']]];

			$tableHeader = array_merge($tableHeader,$status);

			$this->generateExcelModel($company_id,$tableHeader,'product');
		}
	}

	protected function resultCategory($data, $parent_name='')
	{
		$arr = [];

		foreach ($data as $key=>$val)
		{
			$pre = '';

			if($parent_name)
			{
				$pre .= $parent_name.'-';
			}

			$arr[] = $pre.$val['type_name'];

			if($val['subClass'])
			{
				$arr = array_merge($arr,$this->resultCategory($val['subClass'],$pre.$val['type_name']));
			}
		}

		return $arr;
	}

	/*
	* 生成Excel模板文件
	* @param   string  $company_id     商户id
	* @param   array   $tableHeader    表头数据
	* @param   array   $type           生成模板的类型
	*/
	public function generateExcelModel($company_id,$tableHeader,$type,$contacterHeader=[])
	{
		import('Org.Util.PHPExcel');//手动加载第三方插件

		$excel = new \PHPExcel();

		$objActSheet = $excel->getActiveSheet();

//		表头单元格内容 第一行
		$titleColumn = 'A';

		$objActSheet->mergeCells('A1:Z1');
		$objActSheet->getRowDimension(1)->setRowHeight('200');
		$objActSheet->getStyle('A1')->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);

		$objRichText =  new  \PHPExcel_RichText();
		//添加文字并设置文字颜色
		$objPayable = $objRichText->createTextRun(   "导入须知\n
1. 账号信息字段必须与用户登陆账号一致（例：负责人、审核人）\n
2. 关联字段（例：所属客户）请填写关联对象不包含编号前缀的编号\n
3. 标题中带“*”的为必填项，（客户导入模板中，联系人信息字段可不填），自定义字段设置为防重的，表格中不能重复\n
4. 自定义字段选项类型为多项选择时，表格内容必须与字段选项一致，格式：AA,BB；格式中的“,”必须是英文的\n
5. 地区类型中的表格内容必须与地区组件中的选项一致，格式：中国,四川,成都；格式中的“,”必须是英文的\n
6. 时间格式：2000-01-01 00:00:00\n
7. 导入失败时，失败行之前的数据都已导入成功，修改信息后，将失败行之前的数据全部删除再重新导入，以免重复");
		$objPayable->getFont()->setBold(true);
		$objPayable->getFont()->setSize(10);
		$objPayable->getFont()->setColor(new \PHPExcel_Style_Color(\PHPExcel_Style_Color::COLOR_RED));
		//将文字写到A1单元格中
		$objActSheet->getCell('A1')->setValue($objRichText);
		$objActSheet->getStyle('A1')->getAlignment()->setWrapText(true);

		//$lineBegin 表头行
		if(in_array($type,['customer','customerPool']))
		{
			$lineBegin = 3;

			$cols = ['A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z','AA','AB','AC','AD','AE','AF','AG','AH','AI','AJ','AK','AL','AM','AN','AO','AP','AQ','AR','AS','AT','AU','AV','AW','AX','AY','AZ'];

			for($i=0;$i<count($tableHeader);$i++)
			{
				if($i == 0)
				{
					$customerFirst = $cols[$i];
				}
				elseif($i == (count($tableHeader) - 1 ))
				{
					$customerLast = $cols[$i];
				}
				unset($cols[$i]);
			}

			//合并单元格
			$objActSheet->mergeCells($customerFirst.'2:'.$customerLast.'2');

			$objActSheet->setCellValue($customerFirst.'2','客户相关信息');

			//文本居中
			$objActSheet->getStyle($customerFirst.'2')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

			// 垂直居中
			$objActSheet->getStyle($customerFirst.'2')->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);

			//加粗
			$objActSheet->getStyle($customerFirst.'2')->applyFromArray(['font'=> ['bold'=> true]]);

			//设置颜色
			$objActSheet->getStyle($customerFirst.'2')->getFont()->setColor(new \PHPExcel_Style_Color(\PHPExcel_Style_Color::COLOR_RED));

			$cols = array_merge($cols);

			for($i=0;$i<count($contacterHeader);$i++)
			{
				if($i == 0)
				{
					$contacterFirst = $cols[$i];
				}
				elseif($i == (count($contacterHeader) - 1 ))
				{
					$contacterLast = $cols[$i];

					$createtimeFirst = $cols[$i + 1];
				}
			}

			$objActSheet->mergeCells($contacterFirst.'2:'.$contacterLast.'2');

			$objActSheet->setCellValue($contacterFirst.'2','联系人相关信息');
			$objActSheet->getStyle($contacterFirst.'2')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
			$objActSheet->getStyle($contacterFirst.'2')->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
			$objActSheet->getStyle($contacterFirst.'2')->applyFromArray(['font'=> ['bold'=> true]]);
			$objActSheet->getStyle($contacterFirst.'2')->getFont()->setColor(new \PHPExcel_Style_Color(\PHPExcel_Style_Color::COLOR_RED));

			$objActSheet->setCellValue($createtimeFirst.'2','创建时间');
			$objActSheet->getStyle($createtimeFirst.'2')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
			$objActSheet->getStyle($createtimeFirst.'2')->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
			$objActSheet->getStyle($createtimeFirst.'2')->applyFromArray(['font'=> ['bold'=> true]]);
			$objActSheet->getStyle($createtimeFirst.'2')->getFont()->setColor(new \PHPExcel_Style_Color(\PHPExcel_Style_Color::COLOR_RED));

			$objActSheet->getRowDimension(2)->setRowHeight('30');

			$tableHeader = array_merge($tableHeader,$contacterHeader);

			$createtimeHeader = [['title'=>'创建时间', 'select'=>'',]];

			$tableHeader = array_merge($tableHeader,$createtimeHeader);
		}
		else
		{
			$lineBegin = 2;
		}

//        填充表头信息
		foreach($tableHeader as $key=>$val)
		{
//            设置表头数据
			$objActSheet->setCellValue($titleColumn."$lineBegin",$val['title']);

//            设置列宽
			$objActSheet->getColumnDimension($titleColumn)->setWidth('25');

//            文本居中
			$objActSheet->getStyle($titleColumn."$lineBegin")->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

//            加粗
			$objActSheet->getStyle($titleColumn."$lineBegin")->applyFromArray(['font'=> ['bold'=> true]]);

//            行高
			$objActSheet->getRowDimension($lineBegin)->setRowHeight('30');


//            垂直居中
			$objActSheet->getStyle($titleColumn."$lineBegin")->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);

			if($val['select'])
			{
				$row = $lineBegin + 1;

				$select = implode(',', $val['select']);

				while ($row <= $lineBegin+1)
				{
					$objValidation1 =$objActSheet->getCell($titleColumn.$row)->getDataValidation(); //从第二行开始有下拉样式
					$objValidation1->setType(\PHPExcel_Cell_DataValidation::TYPE_LIST )
						->setErrorStyle(\PHPExcel_Cell_DataValidation::STYLE_STOP )
						->setAllowBlank(true)
						->setShowInputMessage(true)
						->setShowErrorMessage(true)
						->setShowDropDown(true)
						->setErrorTitle('输入的值有误')
						->setError('您输入的值不在下拉框列表内.')
						->setPromptTitle('')
						->setPrompt('')
						->setFormula1('"' . $select . '"');

					$objActSheet->setDataValidation($titleColumn.$row.":".$titleColumn.($row + 100), $objValidation1);

					$row++;
				}
			}

			$titleColumn++;
		}

//        创建Excel输入对象
		$write = new \PHPExcel_Writer_Excel2007($excel);

		$save_path = './Attachs/excel/';

		$filename = date('ymdhis').time();

		$save_path .= $type.'Temp/';

		if(!file_exists($save_path))
		{
			mkdir ($save_path,'0777',true);

			chmod($save_path, 0777);

			$save_path .= $company_id.'/';

			if(!file_exists($save_path))
			{
				mkdir ($save_path,'0777',true);

				chmod($save_path, 0777);
			}
		}

		$file = $save_path.$filename.'.xlsx';

		chmod($file, 0777);

		$write->save($file);

		chmod($file, 0777);

		if($temp_id = getCrmDbModel('temp_file')->where(['company_id'=>$company_id,'type'=>$type])->getField('temp_id'))
		{
			getCrmDbModel('temp_file')->save(['temp_id'=>$temp_id,'file_link'=>$file]);
		}
		else
		{
			$data = ['company_id'=>$company_id,'type'=>$type,'file_link'=>$file];

			getCrmDbModel('temp_file')->add($data);
		}
	}
}
