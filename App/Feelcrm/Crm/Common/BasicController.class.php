<?php
// +----------------------------------------------------------------------

// | FeelCRM开源客户管理系统

// +----------------------------------------------------------------------

// | 欢迎阅读学习系统程序代码，您的建议反馈是我们前进的动力

// | 开源版本仅供技术交流学习，请务必保留界面版权logo

// | 商业版本务必购买商业授权，以免引起法律纠纷

// | 禁止对系统程序代码以任何目的，任何形式的再发布

// | gitee下载：https://gitee.com/feelcrm_gitee

// | github下载：https://github.com/feelcrm-github

// | 开源官网：https://www.feelcrm.cn

// | 成都菲莱克斯科技有限公司 版权所有 拥有最终解释权

// +----------------------------------------------------------------------

namespace Crm\Common;

use Think\Controller;

use Common\Controller\CommonController;

class BasicController extends Controller
{
	protected $common,$_lang,$_langAuth,$_member,$member_id,$_company_id,$login_token,$_sms,$_company,$_crmsite,$callcenterAuth,$_source_type;

	public function _initialize()
	{
        $this->common = new CommonController();

        $this->common->feelDeskInit('index',$this->_member,$this->member_id,$this->_company_id,$this->login_token,$this->_lang,$this->_sms,$this->_company,'crm');

		$source_type = I('get.source_type') ? I('get.source_type') : '';

		if($source_type)
		{
			$this->_source_type = $source_type;

			$this->assign('source_type',$source_type);
		}

		if($this->_company_id)
		{
			if(!getCrmDbModel('define_form')->where(['company_id'=>$this->_company_id,'is_default'=>1,'type'=>'customer'])->getField('form_id'))
			{
				$fault_field = C('CRMFIELDS.CUSTOMER');

				foreach($fault_field as $key=>&$val)
				{
					$val['company_id'] = str_replace($val['company_id'],$this->_company_id,$val['company_id']);
				}

				getCrmDbModel('define_form')->addAll($fault_field);
			}
			if(!getCrmDbModel('define_form')->where(['company_id'=>$this->_company_id,'is_default'=>1,'type'=>'contacter'])->getField('form_id'))
			{
				$fault_field = C('CRMFIELDS.CONTACTER');

				foreach($fault_field as $key=>&$val)
				{
					$val['company_id'] = str_replace($val['company_id'],$this->_company_id,$val['company_id']);
				}

				getCrmDbModel('define_form')->addAll($fault_field);
			}
			if(!getCrmDbModel('define_form')->where(['company_id'=>$this->_company_id,'is_default'=>1,'type'=>'product'])->getField('form_id'))
			{
				$fault_field = C('CRMFIELDS.PRODUCT');

				foreach($fault_field as $key=>&$val)
				{
					$val['company_id'] = str_replace($val['company_id'],$this->_company_id,$val['company_id']);
				}

				getCrmDbModel('define_form')->addAll($fault_field);
			}
			if(!getCrmDbModel('define_form')->where(['company_id'=>$this->_company_id,'is_default'=>1,'type'=>'analysis'])->getField('form_id'))
			{
				$fault_field = C('CRMFIELDS.ANALYSIS');

				foreach($fault_field as $key=>&$val)
				{
					$val['company_id'] = str_replace($val['company_id'],$this->_company_id,$val['company_id']);
				}

				getCrmDbModel('define_form')->addAll($fault_field);
			}
			if(!getCrmDbModel('define_form')->where(['company_id'=>$this->_company_id,'is_default'=>1,'type'=>'competitor'])->getField('form_id'))
			{
				$fault_field = C('CRMFIELDS.COMPETITOR');

				foreach($fault_field as $key=>&$val)
				{
					$val['company_id'] = str_replace($val['company_id'],$this->_company_id,$val['company_id']);
				}

				getCrmDbModel('define_form')->addAll($fault_field);
			}
			if(!getCrmDbModel('define_form')->where(['company_id'=>$this->_company_id,'is_default'=>1,'type'=>'contract'])->getField('form_id'))
			{
				$fault_field = C('CRMFIELDS.CONTRACT');

				foreach($fault_field as $key=>&$val)
				{
					$val['company_id'] = str_replace($val['company_id'],$this->_company_id,$val['company_id']);
				}

				getCrmDbModel('define_form')->addAll($fault_field);
			}
			if(!getCrmDbModel('define_form')->where(['company_id'=>$this->_company_id,'is_default'=>1,'type'=>'shipment'])->getField('form_id'))
			{
				$fault_field = C('CRMFIELDS.SHIPMENT');

				foreach($fault_field as $key=>&$val)
				{
					$val['company_id'] = str_replace($val['company_id'],$this->_company_id,$val['company_id']);
				}

				getCrmDbModel('define_form')->addAll($fault_field);
			}
			if(!getCrmDbModel('define_form')->where(['company_id'=>$this->_company_id,'is_default'=>1,'type'=>'clue'])->getField('form_id'))
			{
				$fault_field = C('CRMFIELDS.CLUE');

				foreach($fault_field as $key=>&$val)
				{
					$val['company_id'] = str_replace($val['company_id'],$this->_company_id,$val['company_id']);
				}

				getCrmDbModel('define_form')->addAll($fault_field);
			}
			if(!getCrmDbModel('define_form')->where(['company_id'=>$this->_company_id,'is_default'=>1,'type'=>'opportunity'])->getField('form_id'))
			{
				$fault_field = C('CRMFIELDS.OPPORTUNITY');

				foreach($fault_field as $key=>&$val)
				{
					$val['company_id'] = str_replace($val['company_id'],$this->_company_id,$val['company_id']);
				}

				getCrmDbModel('define_form')->addAll($fault_field);
			}
		}

        $this->_langAuth = M('company')->where(['company_id'=>$this->_company_id])->field('en_auth,jp_auth')->find();

        $this->assign('langAuth',$this->_langAuth);

		$name = getCrmLanguageData('name');

		$country = getCrmDbModel('country')->field(['*',$name])->select();

		$this->assign('country',$country);

		$crmsite = getCrmDbModel('Setting')->where(['company_id'=>$this->_company_id])->find();

		$crmsite = unserialize($crmsite['value']);

		$this->_crmsite = $crmsite;

		if(!$crmsite['regionType'])
		{
			$crmsite['regionType'] = 'world';
		}

		$this->assign('crmsite',$crmsite);

		if($crmsite['regionType'] == 'world')
		{
			if($crmsite['defaultCountry'])
			{
				$province_name = getCrmLanguageData('name');

				$province = getCrmDbModel('province')
					->field(['*',$province_name])
					->where(['country_code'=>$crmsite['defaultCountry']])
					->select();

				$this->assign('province',$province);
			}

			if($crmsite['defaultCountry'] && $crmsite['defaultProv'])
			{
				$city_name = getCrmLanguageData('name');

				$city = getCrmDbModel('city')
					->field(['*',$city_name])
					->where(['country_code'=>$crmsite['defaultCountry'],'province_code'=>$crmsite['defaultProv']])
					->select();

				$this->assign('city',$city);
			}

			if($crmsite['defaultCountry'] && $crmsite['defaultProv'] && $crmsite['defaultCity'])
			{
				$area_name = getCrmLanguageData('name');

				$area = getCrmDbModel('area')
					->field(['*',$area_name])
					->where(['country_code'=>$crmsite['defaultCountry'],'province_code'=>$crmsite['defaultProv'],'city_code'=>$crmsite['defaultCity']])
					->select();

				$this->assign('area',$area);
			}
		}
		else
		{
			if($crmsite['defaultCountry'])
			{
				$province_name = getCrmLanguageData('name');

				$province = getCrmDbModel('province')
					->field(['*',$province_name])
					->where(['country_code'=>1])
					->select();

				$this->assign('province',$province);
			}

			if($crmsite['defaultCountry'] && $crmsite['defaultProv'])
			{
				$city_name = getCrmLanguageData('name');

				$city = getCrmDbModel('city')
					->field(['*',$city_name])
					->where(['country_code'=>1,'province_code'=>$crmsite['defaultProv']])
					->select();

				$this->assign('city',$city);
			}

			if($crmsite['defaultCountry'] && $crmsite['defaultProv'] && $crmsite['defaultCity'])
			{
				$area_name = getCrmLanguageData('name');

				$area = getCrmDbModel('area')
					->field(['*',$area_name])
					->where(['country_code'=>1,'province_code'=>$crmsite['defaultProv'],'city_code'=>$crmsite['defaultCity']])
					->select();

				$this->assign('area',$area);
			}
		}

		$callcenterAuth = D('RoleAuth')->checkRoleAuthByMenu($this->_company_id,'ToCallcenter',$this->_member['role_id']);

		if($callcenterAuth)
		{
			$callcenter = M('callcenter')->where(['company_id'=>$this->_company_id])->field('id')->find();

			if($callcenter)
			{
				$this->callcenterAuth = 1;

				$this->assign('callcenterAuth', 1);
			}
		}

		$customer_all_view_auth = D('RoleAuth')->checkRoleAuthByMenu($this->_member['company_id'],'all',$this->_member['role_id'],'crm');

		$customer_group_view_auth = D('RoleAuth')->checkRoleAuthByMenu($this->_member['company_id'],'group',$this->_member['role_id'],'crm');

		$customer_own_view_auth = D('RoleAuth')->checkRoleAuthByMenu($this->_member['company_id'],'own',$this->_member['role_id'],'crm');

		if($customer_all_view_auth)
		{
			$customer_view_auth = 'all';
		}
		elseif($customer_group_view_auth)
		{
			$customer_view_auth = 'group';
		}
		else
		{
			$customer_view_auth = 'own';
		}

		$this->assign('isCustomerAllViewAuth',$customer_all_view_auth);

		$this->assign('isCustomerGroupViewAuth',$customer_group_view_auth);

		$this->assign('isCustomerOwnViewAuth',$customer_own_view_auth);

		$this->assign('customer_view_auth',$customer_view_auth);
	}


	/*
	* 导出Excel
	* @param $letter		列 数组
	* @param $tableheader	表头数据 数组
	* @param $exceldata		表数据 数组
	* @param return			输出Excel文件至浏览器
	*/
	protected function exportExcel($letter,$tableHeader,$excelData)
	{
		import("Org.Util.PHPExcel");

		$excel = new \PHPExcel();

		$objActSheet = $excel->getActiveSheet();

		//填充表头数据
		for($i = 0;$i < count($tableHeader);$i++)
		{
			$objActSheet->setCellValue("$letter[$i]1",$tableHeader[$i]);

			$width = $i == 1 ? 50 : 25;

			//设置列宽
			$objActSheet->getColumnDimension("$letter[$i]")->setWidth($width);

			//文本居中
			$objActSheet->getStyle("$letter[$i]1")->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

			//加粗
			$objActSheet->getStyle("$letter[$i]1")->applyFromArray(['font'=> ['bold'=> true]]);

			//行高
			$objActSheet->getRowDimension(1)->setRowHeight('30');

			//垂直居中
			$objActSheet->getStyle("$letter[$i]1")->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
		}


		//填充表格信息
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

				$l++;
			}
		}

		$write = new \PHPExcel_Writer_Excel5($excel);//非2007格式

		header("Pragma: public");

		header("Expires: 0");

		header("Cache-Control:must-revalidate, post-check=0, pre-check=0");

		header("Content-Type:application/force-download");

		header("Content-Type:application/vnd.ms-execl");

		header("Content-Type:application/octet-stream");

		header("Content-Type:application/download");;

		header('Content-Disposition:attachment;filename=客户数据-'.date('YmdHis').'.xls');

		header("Content-Transfer-Encoding:binary");

		$write->save('php://output');
	}
}
