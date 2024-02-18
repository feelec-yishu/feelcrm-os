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

namespace Crm\Controller;

use Think\Controller;

class UploadController extends Controller
{
//    微信LOGO和二维码
    public function UploadWeChat()
    {
        $result = D('Upload')->UploadWeChat($_FILES);

        $this->ajaxReturn($result);
    }



//    上传文件到七牛
    public function UploadTicketFile()
    {
        $result = D('Upload')->uploadAttachment($_FILES);

        $this->ajaxReturn($result);
    }


//    上传图片到七牛
    public function uploadImageFile($type = '')
    {
        $name = I('get.name') ? I('get.name') : '';

        $result = D('Upload')->UploadImageFile($type,$name);

        $this->ajaxReturn($result);
    }


//    上传头像到七牛
    public function uploadHeadFile($type = '')
    {
        $result = D('Upload')->UploadHeadFile($type,$_FILES);

        $this->ajaxReturn($result);
    }


//    删除七牛文件
    public function delQiniuFile($from = '',$filename='')
    {
        $file_name = I('post.file_name') ? I('post.file_name') : $filename;

        $result = D('Upload')->DelQiniuFile($from,$file_name);

        $this->ajaxReturn($result);
    }


//    删除七牛头像
    public function delQiniuHead($face_name = '')
    {
        D('Upload')->DelQiniuHead($face_name);
    }


//    删除七牛图片
    public function delQiniuImage($from = '',$img_name = '')
    {
        $file_name = I('post.file_name') ? I('post.file_name') : $img_name;

        $result = D('Upload')->DelQiniuImage($from,$file_name);

        $this->ajaxReturn($result);
    }

	//    删除上传文件和图片
    public function deleteUploadFile($filename='',$from = '')
    {
        $file_name = I('post.file_name') ? I('post.file_name') : $filename;

        $result = D('Upload')->deleteUploadFile($file_name,$from);

        $this->ajaxReturn($result);
    }



//    导入Excel
    public function importExcel()
    {
        //文件上传设置
        $config = [
            'maxSize'    =>    3145728,  // 设置附件上传大小
            'rootPath'   =>    './Attachs/',// 文件上传的保存路径
            'savePath'   =>    'excel/',// 文件上传的保存路径
            'saveName'   =>    array('uniqid',''),
            'exts'       =>    array('xlsx', 'xls'),  // 设置附件上传类型
            'autoSub'    =>    true,
            'subName'    =>    array('date','Ymd'),
        ];

        $source = I('get.source');

        $Upload = new \Think\Upload($config);

        $info = $Upload->uploadOne($_FILES['excel']);

        if(!$info)
        {
            $data = ['status'=>0,'msg'=>$Upload->getError()];
        }
        else
        {
            $file = $info['savepath'].$info['savename'];

            $data = $this->HandleImport($file,$source);
        }

        $this->ajaxReturn($data);
    }



//    处理导入的模板
    private function HandleImport($file,$source='')
    {
        session('[start]');

        $cid = session('company_id');

		$mid = session('index.member_id');

        session('[pause]');

        $file = './Attachs/'.$file;//创建上面表格的文件夹

        //检查目或文件是否存在
        if(!file_exists($file))
        {
            $data = ['error'=>0,'msg'=>L('FILE_DOES_NOT_EXIST')];

            return $data;
        }

        import('Org.Util.PHPExcel');//手动加载第三方插件

		import("Org.Util.PHPExcel.Shared.Date");

        //$objPHPExcel = \PHPExcel_IOFactory::load($file);//加载excel文件

		$objReader = \PHPExcel_IOFactory::createReader('Excel2007');

		$objReader->setReadDataOnly(true); //使用文件流读取文件
		$objPHPExcel = $objReader->load($file);
		$sheet = $objPHPExcel->getSheet(0);

        $rows = $sheet->getHighestRow();//获取Excel行数

		$shared = new \PHPExcel_Shared_Date();  //转换ecxel时间戳

	    $cols = ['A'=>'','B'=>'','C'=>'','D'=>'','E'=>'','F'=>'','G'=>'','H'=>'','I'=>'','J'=>'','K'=>'','L'=>'','M'=>'','N'=>'','O'=>'','P'=>'','Q'=>'','R'=>'','S'=>'','T'=>'','U'=>'','V'=>'','W'=>'','X'=>'','Y'=>'','Z'=>'','AA'=>'','AB'=>'','AC'=>'','AD'=>'','AE'=>'','AF'=>'','AG'=>'','AH'=>'','AI'=>'','AJ'=>'','AK'=>'','AL'=>'','AM'=>'','AN'=>'','AO'=>'','AP'=>'','AQ'=>'','AR'=>'','AS'=>'','AT'=>'','AU'=>'','AV'=>'','AW'=>'','AX'=>'','AY'=>'','AZ'=>''];

//        Execl表结构
        if(in_array($source,['customer','customer_pool']))
        {
	        $customer_cols = $contacter_cols = $createtime_cols = [];

	        foreach ($cols as $key=>$val)
	        {
		        $value = $sheet->getCell($key.'2')->getValue();//获取表格的内容,2为字段描述行

		        if ($value instanceof PHPExcel_RichText)
		        {
			        $value = $value->__toString();
		        }

		        if($value == '客户相关信息')
		        {
		        	$customer_cols[$key] = '';
		        }
		        elseif($value == '联系人相关信息')
		        {
			        $contacter_cols[$key] = '';
		        }
		        elseif($value == '创建时间')
		        {
			        $createtime_cols[$key] = '';
		        }
		        elseif(!$contacter_cols && $customer_cols)
		        {
			        $customer_cols[$key] = '';
		        }
		        elseif(!$createtime_cols && $contacter_cols)
		        {
			        $contacter_cols[$key] = '';
		        }
	        }

	        //客户字段
	        foreach ($customer_cols as $key=>$val)
	        {
		        $value = $sheet->getCell($key.'3')->getValue();//获取表格的内容,3为title行

		        if ($value instanceof PHPExcel_RichText)
		        {
			        $value = $value->__toString();
		        }

		        if($value)
		        {
			        $value = explode('* ',$value);

			        $value = count($value) > 1 ? $value[1] : $value[0];

			        if($value == '负责人')
			        {
				        $customer_cols[$key] = 'member';
			        }
			        else
			        {
				        $customer_form_name = getCrmDbModel('define_form')
					        ->where(['type'=>'customer','company_id'=>$cid,'closed'=>0,'form_description'=>$value])
					        ->getField('form_name');

				        $customer_cols[$key] = 'defineform_'.$customer_form_name;
			        }
		        }
	        }

	        //联系人字段
	        foreach ($contacter_cols as $key=>$val)
	        {
		        $value = $sheet->getCell($key.'3')->getValue();//获取表格的内容,3为title行

		        if ($value instanceof PHPExcel_RichText)
		        {
			        $value = $value->__toString();
		        }

		        if($value)
		        {
			        $value = explode('* ',$value);

			        $value = count($value) > 1 ? $value[1] : $value[0];

			        $contacter_form_name = getCrmDbModel('define_form')
					        ->where(['type'=>'contacter','company_id'=>$cid,'closed'=>0,'form_description'=>$value])
					        ->getField('form_name');

			        $contacter_cols[$key] = 'defineform_'.$contacter_form_name;
		        }
	        }

	        foreach ($createtime_cols as $key=>$val)
	        {
		        $createtime_cols[$key] = 'createtime';
	        }

	        $url = $source == 'customer_pool' ? U('Customer/pool') : U('Customer/index');
        }
        else if($source == 'opportunity')
        {
	        foreach ($cols as $key=>$val)
	        {
		        $value = $sheet->getCell($key.'2')->getValue();//获取表格的内容,2为title行

		        if ($value instanceof PHPExcel_RichText)
		        {
			        $value = $value->__toString();
		        }

		        if($value)
		        {
			        $value = explode('* ',$value);

			        $value = count($value) > 1 ? $value[1] : $value[0];

			        if($value == '所属客户')
			        {
			            $cols[$key] = 'customer';
			        }
			        elseif($value == '创建时间')
			        {
				        $cols[$key] = 'createtime';
			        }
			        else
			        {
				        $opportunity_form_name = getCrmDbModel('define_form')
					        ->where(['type'=>'opportunity','company_id'=>$cid,'closed'=>0,'form_description'=>$value])
					        ->getField('form_name');

				        $cols[$key] = 'defineform_'.$opportunity_form_name;
			        }
		        }
		        else
		        {
			        unset($cols[$key]);
		        }
	        }

	        $url = U('Opportunity/index');
        }
		else if($source == 'contract')
        {
	        foreach ($cols as $key=>$val)
	        {
		        $value = $sheet->getCell($key.'2')->getValue();//获取表格的内容,2为title行

		        if ($value instanceof PHPExcel_RichText)
		        {
			        $value = $value->__toString();
		        }

		        if($value)
		        {
			        $value = explode('* ',$value);

			        $value = count($value) > 1 ? $value[1] : $value[0];

			        if($value == '所属客户')
			        {
				        $cols[$key] = 'customer';
			        }
			        elseif($value == '负责人')
			        {
				        $cols[$key] = 'member';
			        }
			        elseif($value == '审核人')
			        {
				        $cols[$key] = 'examine';
			        }
			        elseif($value == '创建时间')
			        {
				        $cols[$key] = 'createtime';
			        }
			        else
			        {
				        $contract_form_name = getCrmDbModel('define_form')
					        ->where(['type'=>'contract','company_id'=>$cid,'closed'=>0,'form_description'=>$value])
					        ->getField('form_name');

				        $cols[$key] = 'defineform_'.$contract_form_name;
			        }
		        }
		        else
		        {
			        unset($cols[$key]);
		        }
	        }

            $url = U('Contract/index');
        }
		else if(in_array($source,['clue','clue_pool']))
		{
			foreach ($cols as $key=>$val)
			{
				$value = $sheet->getCell($key.'2')->getValue();//获取表格的内容,2为title行

				if ($value instanceof PHPExcel_RichText)
				{
					$value = $value->__toString();
				}

				if($value)
				{
					$value = explode('* ',$value);

					$value = count($value) > 1 ? $value[1] : $value[0];

					if($value == '负责人')
					{
						$cols[$key] = 'member';
					}
					elseif($value == '创建时间')
					{
						$cols[$key] = 'createtime';
					}
					else
					{
						$clue_form_name = getCrmDbModel('define_form')
							->where(['type'=>'clue','company_id'=>$cid,'closed'=>0,'form_description'=>$value])
							->getField('form_name');

						$cols[$key] = 'defineform_'.$clue_form_name;
					}
				}
				else
				{
					unset($cols[$key]);
				}
			}

			$url = $source == 'clue_pool' ? U('Clue/pool') :U('Clue/index');
		}
		else if($source == 'product')
		{
			foreach ($cols as $key=>$val)
			{
				$value = $sheet->getCell($key.'2')->getValue();//获取表格的内容,2为title行

				if ($value instanceof PHPExcel_RichText)
				{
					$value = $value->__toString();
				}

				if($value)
				{
					$value = explode('* ',$value);

					$value = count($value) > 1 ? $value[1] : $value[0];

					if($value == '产品分类')
					{
						$cols[$key] = 'type_name';
					}
					elseif($value == '是否上架')
					{
						$cols[$key] = 'closed';
					}
					else
					{
						$product_form_name = getCrmDbModel('define_form')
							->where(['type'=>'product','company_id'=>$cid,'closed'=>0,'form_description'=>$value])
							->getField('form_name');

						$cols[$key] = 'defineform_'.$product_form_name;
					}
				}
				else
				{
					unset($cols[$key]);
				}
			}

			$url = U('Product/index');
		}

        $fp = fopen('../App/Feelcrm/Runtime/Logs/excle_log.txt','w');//写入日志,提前创建Mylogs文件夹

		$crmsite = getCrmDbModel('Setting')->where(['company_id'=>$cid])->find();

		$crmsite = unserialize($crmsite['value']);

//        读取每行内容
        $count = [];

        $j = 0;

	    $form_description = getCrmLanguageData('form_description');

		if(in_array($source,['customer','customer_pool']))
		{
			$customer_form = getCrmDbModel('define_form')
				->where(['type'=>'customer','company_id'=>$cid,'closed'=>0])
				->field('form_id,form_name,form_type,is_required,is_unique,'.$form_description.',form_option')
				->select();//客户自定义字段

			$contacter_form = getCrmDbModel('define_form')->where(['type'=>'contacter','company_id'=>$cid,'closed'=>0])
				->field('form_id,form_name,form_type,is_required,is_unique,'.$form_description.',form_option')
				->select();//联系人自定义字段

			for ($i=4; $i<=$rows; ++$i)
			{
				//$add['createtime'] = NOW_TIME;

				$add['company_id'] = $cid;

				if($crmsite['customerCode'])
				{
					$add['customer_prefix'] = $crmsite['customerCode'];
				}
				else
				{
					$add['customer_prefix'] = 'C-';
				}

				$add['creater_id'] = $mid;

				$add['entry_method'] = 'IMPORT';

				foreach ($customer_cols as $k => $v)
				{
					$value = $sheet->getCell($k.$i)->getValue();//获取表格的内容,$i=4是为了排除第一行title

					if ($value instanceof PHPExcel_RichText)
					{
						$value = $value->__toString();
					}

					$count[$i][$v] = $value;

					$add['customer_no'] = date('YmdHi',time()).'-'.strtoupper(substr(md5(microtime(true).$cid), rand(0,9), 4));

					if($source == 'customer')
					{
						if($v == 'member')
						{
							$member = M('member')->where(['account'=>$value,'company_id'=>$cid,'type'=>1])->field('member_id')->find();

							if(!$member)
							{
								fwrite($fp,L('LINE_NUMBER').":".$i.",".L('PROCESSING_FAILED')."，".L('REASON')."：".L('ACCOUNT_PERSON_CHARGE_WRONG')."\r\n");

								$this->ajaxReturn(['error'=>0,'msg'=>L('LINE_NUMBER').":".$i."，".L('IMPORT_FAILED')."，".L('ACCOUNT_PERSON_CHARGE_WRONG')."",'url'=>$url]);
							}else
							{
								$add['member_id'] = $member['member_id'];
							}
						}
					}
					else
					{
						$add['member_id'] = 0;
					}
				}

				foreach ($createtime_cols as $k => $v)
				{
					$value = $sheet->getCell($k.$i)->getValue();//获取表格的内容,$i=4是为了排除第一行title

					if ($value instanceof PHPExcel_RichText)
					{
						$value = $value->__toString();
					}

					if($v == 'createtime' && $value)
					{
						$add['createtime'] = strtotime(gmdate('Y-m-d H:i:s',$shared ->ExcelToPHP($value)));

						$contacter['createtime'] = strtotime(gmdate('Y-m-d H:i:s',$shared ->ExcelToPHP($value)));
					}
					else
					{
						$add['createtime'] = NOW_TIME;

						$contacter['createtime'] = NOW_TIME;
					}
				}

				if(!$customer_id = getCrmDbModel('customer')->add($add))
				{
					$error = getCrmDbModel('customer')->getDbError();

					fwrite($fp,L('LINE_NUMBER').":".$i.",".L('PROCESSING_FAILED')."，".L('REASON')."：".$error."\r\n");
				}
				else
				{
					saveFeelCRMEncodeId($customer_id,$cid);

					$contacter['company_id'] = $cid;

					$contacter['customer_id'] = $customer_id;

					$contacter['member_id'] = $add['member_id'];

					$contacter['creater_id'] = $mid;

					$contacter_id = getCrmDbModel('contacter')->add($contacter);

					getCrmDbModel('customer')->where(['customer_id'=>$customer_id,'company_id'=>$cid])->save(['first_contact_id'=>$contacter_id]); //添加客户首要联系人id

					foreach($customer_cols as $k1 => $v1)
					{
						$customer_form_name = explode('defineform_',$v1);

						if($customer_form_name[1])
						{
							$value = $sheet->getCell($k1 . $i)->getValue();//获取表格的内容,$i=4是为了排除第一行title

							if ($value instanceof PHPExcel_RichText)
							{
								$value = $value->__toString();
							}

							//客户自定义字段添加
							foreach($customer_form as $k2=>$v2)
							{
								if($customer_form_name[1] == $v2['form_name'])
								{
									$customer_detail['customer_id'] = $customer_id;

									$customer_detail['company_id'] = $cid;

									$customer_detail['form_id'] = $v2['form_id'];

									$customer_detail['form_content'] = $this->checkDefineForm($i,$v2,$value,$cid,'customer',$customer_id,$contacter_id);

									getCrmDbModel('customer_detail')->add($customer_detail);

									if($customer_form_name[1] == 'name')
									{
										$content_name = $customer_detail['form_content'];
									}
								}
							}
						}
					}

					foreach($contacter_cols as $k1 => $v1)
					{
						$contacter_form_name = explode('defineform_',$v1);

						if($contacter_form_name[1])
						{
							$value = $sheet->getCell($k1 . $i)->getValue();//获取表格的内容,$i=4是为了排除第一行title

							if ($value instanceof PHPExcel_RichText)
							{
								$value = $value->__toString();
							}

							//联系人自定义字段添加
							foreach($contacter_form as $k2=>$v2)
							{
								if($contacter_form_name[1] == $v2['form_name'])
								{
									$contacter_detail['contacter_id'] = $contacter_id;

									$contacter_detail['company_id'] = $cid;

									$contacter_detail['form_id'] = $v2['form_id'];

									if($value)
									{
										$contacter_detail['form_content'] = $this->checkDefineForm($i,$v2,$value,$cid,'contacter',$contacter_id);

										getCrmDbModel('contacter_detail')->add($contacter_detail);

										if($contacter_form_name[1] == 'name')
										{
											$contacter_name = $contacter_detail['form_content'];
										}
									}
								}
							}
						}
					}

					D('CrmLog')->addCrmLog('customer',1,$cid,$mid,$customer_id,0,0,0,$content_name);

					$contractContent = getCrmDbModel('contacter_detail')->where(['company_id'=>$cid,'contacter_id'=>$contacter_id])->select();

					if(!$contractContent)
					{
						getCrmDbModel('contacter')->where($contacter)->delete();

						getCrmDbModel('customer')->where(['customer_id'=>$customer_id,'company_id'=>$cid])->save(['first_contact_id'=>0]);
					}
					else
					{
						D('CrmLog')->addCrmLog('contacter',1,$cid,$mid,$customer_id,$contacter_id,0,0,$contacter_name);
					}

					$j++;
				}
			}
		}
		elseif($source == 'opportunity')
		{
			$opportunity_form = getCrmDbModel('define_form')
				->where(['type'=>'opportunity','company_id'=>$cid,'closed'=>0])
				->field('form_id,form_name,form_type,is_required,is_unique,'.$form_description.',form_option')
				->select();

			for ($i=3; $i<=$rows; ++$i)
			{
				$add['company_id'] = $cid;

				if($crmsite['opportunityCode'])
				{
					$add['opportunity_prefix'] = $crmsite['opportunityCode'];
				}
				else
				{
					$add['opportunity_prefix'] = 'S-';
				}

				$add['creater_id'] = $mid;

				$add['entry_method'] = 'IMPORT';

				foreach ($cols as $k => $v)
				{
					$value = $sheet->getCell($k.$i)->getValue();//获取表格的内容,$i=4是为了排除第一行title

					if ($value instanceof PHPExcel_RichText)
					{
						$value = $value->__toString();
					}

					$count[$i][$v] = $value;

					$add['opportunity_no'] = date('YmdHi',time()).'-'.strtoupper(substr(md5(microtime(true).$cid), rand(0,9), 4));

					if($v == 'createtime' && $value)
					{
						$add['createtime'] = strtotime(gmdate('Y-m-d H:i:s',$shared ->ExcelToPHP($value)));
					}
					else
					{
						$add['createtime'] = NOW_TIME;
					}

					if($v == 'customer')
					{
						$customer = getCrmDbModel('customer')->where(['customer_no'=>$value,'company_id'=>$cid,'isvalid'=>1])->field('customer_id,member_id')->find();

						if(!$customer)
						{
							fwrite($fp,L('LINE_NUMBER').":".$i.",".L('PROCESSING_FAILED')."，".L('REASON')."：".L('INCORRECT_CUSTOMER_NUMBER')."\r\n");

							$this->ajaxReturn(['error'=>0,'msg'=>L('LINE_NUMBER').":".$i."，".L('IMPORT_FAILED')."，".L('INCORRECT_CUSTOMER_NUMBER')."",'url'=>$url]);
						}else
						{
							$add['customer_id'] = $customer['customer_id'];

							$add['member_id'] = $customer['member_id'] ? $customer['member_id'] : 0;
						}
					}
				}

				if(!$opportunity_id = getCrmDbModel('opportunity')->add($add))
				{
					$error = getCrmDbModel('opportunity')->getDbError();

					fwrite($fp,L('LINE_NUMBER').":".$i.",".L('PROCESSING_FAILED')."，".L('REASON')."：".$error."\r\n");
				}
				else
				{
					foreach($cols as $k1 => $v1)
					{
						$opportunity_form_name = explode('defineform_',$v1);

						if($opportunity_form_name[1])
						{
							$value = $sheet->getCell($k1.$i)->getValue();//获取表格的内容,$i=3是为了排除第一行title

							if ($value instanceof PHPExcel_RichText)
							{
								$value = $value->__toString();
							}

							foreach($opportunity_form as $k2=>$v2)
							{
								if($opportunity_form_name[1] == $v2['form_name'])
								{
									$opportunity_detail['opportunity_id'] = $opportunity_id;

									$opportunity_detail['company_id'] = $cid;

									$opportunity_detail['form_id'] = $v2['form_id'];

									$opportunity_detail['form_content'] = $this->checkDefineForm($i,$v2,$value,$cid,'opportunity',$opportunity_id);

									getCrmDbModel('opportunity_detail')->add($opportunity_detail);

									if($opportunity_form_name[1] == 'name')
									{
										$content_name = $opportunity_detail['form_content'];
									}
								}
							}
						}
					}

					D('CrmLog')->addCrmLog('opportunity',1,$cid,$mid,$add['customer_id'],0,0,0,$content_name,0,0,0,0,0,0,0,0,0,$opportunity_id);

					$j++;
				}
			}
		}
		elseif($source == 'contract')
		{
			$contract_form = getCrmDbModel('define_form')
				->where(['type'=>'contract','company_id'=>$cid,'is_default'=>1])
				->field('form_id,form_name,form_type,is_required,is_unique,'.$form_description.',form_option')
				->select();

			for ($i=3; $i<=$rows; ++$i)
			{
				//$add['createtime'] = NOW_TIME;

				$add['company_id'] = $cid;

				if($crmsite['contractCode'])
				{
					$add['contract_prefix'] = $crmsite['contractCode'];
				}
				else
				{
					$add['contract_prefix'] = 'H-';
				}

				$add['creater_id'] = $mid;

				$add['entry_method'] = 'IMPORT';

				foreach ($cols as $k => $v)
				{
					$value = $sheet->getCell($k.$i)->getValue();//获取表格的内容,$i=4是为了排除第一行title

					if ($value instanceof PHPExcel_RichText)
					{
						$value = $value->__toString();
					}

					$count[$i][$v] = $value;

					$add['contract_no'] = date('YmdHi',time()).'-'.strtoupper(substr(md5(microtime(true).$cid), rand(0,9), 4));

					if($v == 'createtime' && $value)
					{
						$add['createtime'] = strtotime(gmdate('Y-m-d H:i:s',$shared ->ExcelToPHP($value)));
					}
					else
					{
						$add['createtime'] = NOW_TIME;
					}

					if($v == 'customer')
					{
						$customer = getCrmDbModel('customer')->where(['customer_no'=>$value,'company_id'=>$cid,'isvalid'=>1])->field('customer_id')->find();

						if(!$customer)
						{
							fwrite($fp,L('LINE_NUMBER').":".$i.",".L('PROCESSING_FAILED')."，".L('REASON')."：".L('INCORRECT_CUSTOMER_NUMBER')."\r\n");

							$this->ajaxReturn(['error'=>0,'msg'=>L('LINE_NUMBER').":".$i."，".L('IMPORT_FAILED')."，".L('INCORRECT_CUSTOMER_NUMBER')."",'url'=>$url]);
						}else
						{
							$add['customer_id'] = $customer['customer_id'];
						}
					}

					if($v == 'member')
					{
						$member = M('member')->where(['account'=>$value,'company_id'=>$cid,'type'=>1])->field('member_id')->find();

						if(!$member)
						{
							fwrite($fp,L('LINE_NUMBER').":".$i.",".L('PROCESSING_FAILED')."，".L('REASON')."：".L('ACCOUNT_PERSON_CHARGE_WRONG')."\r\n");

							$this->ajaxReturn(['error'=>0,'msg'=>L('LINE_NUMBER').":".$i."，".L('IMPORT_FAILED')."，".L('ACCOUNT_PERSON_CHARGE_WRONG')."",'url'=>$url]);
						}
						else
						{
							$add['member_id'] = $member['member_id'];
						}
					}

					if($v == 'examine')
					{
						$examine_arr = explode('-',$value);

						$add['examine_id'] = [];

						foreach ($examine_arr as $key=>$val)
						{
							$member = M('member')->where(['account'=>$val,'company_id'=>$cid,'type'=>1])->field('member_id')->find();

							if(!$member)
							{
								fwrite($fp,L('LINE_NUMBER').":".$i.",".L('PROCESSING_FAILED')."，".L('REASON')."：".L('EXAMINE_ACCOUNT_ERROR')."\r\n");

								$this->ajaxReturn(['error'=>0,'msg'=>L('LINE_NUMBER').":".$i."，".L('IMPORT_FAILED')."，".L('EXAMINE_ACCOUNT_ERROR')."",'url'=>$url]);
							}
							else
							{
								$add['examine_id'][$key] = $member['member_id'];
							}
						}

						$add['examine_id'] = implode(',',$add['examine_id']);
					}
				}

				if(!$contract_id = getCrmDbModel('contract')->add($add))
				{
					$error = getCrmDbModel('contract')->getDbError();

					fwrite($fp,L('LINE_NUMBER').":".$i.",".L('PROCESSING_FAILED')."，".L('REASON')."：".$error."\r\n");
				}
				else
				{
					foreach($cols as $k1 => $v1)
					{
						$contract_form_name = explode('defineform_',$v1);

						if($contract_form_name[1])
						{
							$value = $sheet->getCell($k1.$i)->getValue();//获取表格的内容,$i=4是为了排除第一行title

							if ($value instanceof PHPExcel_RichText)
							{
								$value = $value->__toString();
							}

							foreach($contract_form as $k2=>$v2)
							{
								if($contract_form_name[1] == $v2['form_name'])
								{
									$contract_detail['contract_id'] = $contract_id;

									$contract_detail['company_id'] = $cid;

									$contract_detail['form_id'] = $v2['form_id'];

									$contract_detail['form_content'] = $this->checkDefineForm($i,$v2,$value,$cid,'contract',$contract_id);
									getCrmDbModel('contract_detail')->add($contract_detail);

									if($contract_form_name[1] == 'name')
									{
										$content_name = $contract_detail['form_content'];
									}
								}
							}
						}
					}

					getCrmDbModel('customer')->where(['company_id'=>$cid,'customer_id'=>$add['customer_id']])->save(['is_trade'=>1]);

					D('CrmLog')->addCrmLog('contract',1,$cid,$mid,$add['customer_id'],0,0,0,$content_name,0,0,0,0,$contract_id);

					$j++;
				}
			}
		}
		else if(in_array($source,['clue','clue_pool']))
		{
			$clue_form = getCrmDbModel('define_form')
				->where(['type'=>'clue','company_id'=>$cid,'closed'=>0])
				->field('form_id,form_name,form_type,is_required,is_unique,'.$form_description.',form_option')
				->select();//线索自定义字段

			for ($i=3; $i<=$rows; ++$i)
			{
				$add['company_id'] = $cid;

				if($crmsite['clueCode'])
				{
					$add['clue_prefix'] = $crmsite['clueCode'];
				}
				else
				{
					$add['clue_prefix'] = 'X-';
				}

				$add['creater_id'] = $mid;

				$add['entry_method'] = 'IMPORT';

				$add['status'] = -1;

				foreach ($cols as $k => $v)
				{
					$value = $sheet->getCell($k.$i)->getValue();//获取表格的内容,$i=3是为了排除第一行title

					if ($value instanceof PHPExcel_RichText)
					{
						$value = $value->__toString();
					}

					$count[$i][$v] = $value;

					$add['clue_no'] = date('YmdHi',time()).'-'.strtoupper(substr(md5(microtime(true).$cid), rand(0,9), 4));

					if($v == 'createtime' && $value)
					{
						$add['createtime'] = strtotime(gmdate('Y-m-d H:i:s',$shared ->ExcelToPHP($value)));
					}
					else
					{
						$add['createtime'] = NOW_TIME;
					}

					if($source == 'clue')
					{
						if($v == 'member')
						{
							$member = M('member')->where(['account'=>$value,'company_id'=>$cid,'type'=>1])->field('member_id')->find();

							if(!$member)
							{
								fwrite($fp,L('LINE_NUMBER').":".$i.",".L('PROCESSING_FAILED')."，".L('REASON')."：".L('ACCOUNT_PERSON_CHARGE_WRONG')."\r\n");

								$this->ajaxReturn(['error'=>0,'msg'=>L('LINE_NUMBER').":".$i."，".L('IMPORT_FAILED')."，".L('ACCOUNT_PERSON_CHARGE_WRONG')."",'url'=>$url]);
							}else
							{
								$add['member_id'] = $member['member_id'];
							}
						}
					}
					else
					{
						$add['member_id'] = 0;
					}
				}

				if(!$clue_id = getCrmDbModel('clue')->add($add))
				{
					$error = getCrmDbModel('clue')->getDbError();

					fwrite($fp,L('LINE_NUMBER').":".$i.",".L('PROCESSING_FAILED')."，".L('REASON')."：".$error."\r\n");
				}
				else
				{
					saveFeelCRMEncodeId($clue_id,$cid,'Clue');

					foreach($cols as $k1 => $v1)
					{
						$clue_form_name = explode('defineform_',$v1);

						if($clue_form_name[1])
						{
							$value = $sheet->getCell($k1.$i)->getValue();//获取表格的内容,$i=4是为了排除第一行title

							if ($value instanceof PHPExcel_RichText)
							{
								$value = $value->__toString();
							}

							foreach($clue_form as $k2=>$v2)
							{
								if($clue_form_name[1] == $v2['form_name'])
								{
									$clue_detail['clue_id'] = $clue_id;

									$clue_detail['company_id'] = $cid;

									$clue_detail['form_id'] = $v2['form_id'];

									$clue_detail['form_content'] = $this->checkDefineForm($i,$v2,$value,$cid,'clue',$clue_id);

									getCrmDbModel('clue_detail')->add($clue_detail);

									if($clue_form_name[1] == 'name')
									{
										$content_name = $clue_detail['form_content'];
									}
								}
							}
						}
					}

					//记录操作日志
					D('CrmLog')->addCrmLog('clue',1,$cid,$mid,0,0,0,0,$content_name,0,0,0,0,0,0,0,0,$clue_id);

					$j++;
				}
			}
		}
		else if($source == 'product')
		{
			$product_form = getCrmDbModel('define_form')
				->where(['type'=>'product','company_id'=>$cid,'closed'=>0])
				->field('form_id,form_name,form_type,is_required,is_unique,'.$form_description.',form_option')
				->select();//产品自定义字段

			//查询所有产品分类并按 type_name 进行树状排列
			$product_type = getCrmDbModel('product_type')->where(['company_id'=>$cid,'closed'=>0])->select();

			$product_type  = getMenuTree($product_type,'type_id','parent_id','subClass');

			$product_type = CrmfetchAllChild($product_type,'type_name');

			for ($i=3; $i<=$rows; ++$i)
			{
				$add['company_id'] = $cid;

				$add['isvalid'] = 1;

				$add['createtime'] = NOW_TIME;

				foreach ($cols as $k => $v)
				{
					if($v)
					{
						$value = $sheet->getCell($k.$i)->getValue();//获取表格的内容,$i=3是为了排除第一行title

						if ($value instanceof PHPExcel_RichText)
						{
							$value = $value->__toString();
						}

						$count[$i][$v] = $value;

						if($v == 'type_name')
						{
							$type_arr = explode('-',$value);

							//从树状数组中取出对应type_id
							$type_id = getTypeIdByImportValue($cid,$type_arr,$product_type);

							if($type_id)
							{
								$add['type_id'] = $type_id;
							}
							else
							{
								fwrite($fp,L('LINE_NUMBER').":".$i.",".L('PROCESSING_FAILED')."，".L('REASON')."：".L('CONTENT_WRONG')."\r\n");
								$this->ajaxReturn(['error'=>0,'msg'=>L('LINE_NUMBER').":".$i."，".L('IMPORT_FAILED')."，".L('CONTENT_WRONG')."",'url'=>$url]);
							}
						}
						else if($v == 'closed')
						{
							if($value == '上架')
							{
								$add['closed'] = 0;
							}
							elseif($value == '下架')
							{
								$add['closed'] = 1;
							}
							else
							{
								fwrite($fp,L('LINE_NUMBER').":".$i.",".L('PROCESSING_FAILED')."，".L('REASON')."：".L('CONTENT_WRONG')."\r\n");
								$this->ajaxReturn(['error'=>0,'msg'=>L('LINE_NUMBER').":".$i."，".L('IMPORT_FAILED')."，".L('CONTENT_WRONG')."",'url'=>$url]);
							}
						}
					}
				}

				if(!$product_id = getCrmDbModel('product')->add($add))
				{
					$error = getCrmDbModel('product')->getDbError();

					fwrite($fp,L('LINE_NUMBER').":".$i.",".L('PROCESSING_FAILED')."，".L('REASON')."：".$error."\r\n");
				}
				else
				{
					foreach($cols as $k1 => $v1)
					{
						$product_form_name = explode('defineform_',$v1);

						if($product_form_name[1])
						{
							$value = $sheet->getCell($k1.$i)->getValue();//获取表格的内容,$i=3是为了排除第一行title

							if ($value instanceof PHPExcel_RichText)
							{
								$value = $value->__toString();
							}

							foreach($product_form as $k2=>$v2)
							{
								if($product_form_name[1] == $v2['form_name'])
								{
									$product_detail['product_id'] = $product_id;

									$product_detail['company_id'] = $cid;

									$product_detail['form_id'] = $v2['form_id'];

									$product_detail['form_content'] = $this->checkDefineForm($i,$v2,$value,$cid,'product',$product_id);

									getCrmDbModel('product_detail')->add($product_detail);
								}
							}
						}
					}

					$j++;
				}
			}
		}

        if($j == count($count))
        {
            $data = ['error'=>1,'msg'=>L('IMPORT_SUCCESS'),'url'=>$url];
        }
        else
        {
            $data = ['error'=>0,'msg'=>L('LINE_EXPORT_FAILED',['line'=>$count.'-'.$j]),'url'=>$url];
        }

        @unlink($file);

        return $data;
    }

	public function checkDefineForm($i,$form,$value,$cid,$type,$this_id,$contacter_id = 0)
	{
		$shared = new \PHPExcel_Shared_Date();

		if(!$value && $form['is_required'] != 1)
		{
			getCrmDbModel($type)->where(['company_id'=>$cid,$type.'_id'=>$this_id])->delete();
			getCrmDbModel($type.'_detail')->where(['company_id'=>$cid,$type.'_id'=>$this_id])->delete();
			if($contacter_id > 0)
			{
				getCrmDbModel('contacter')->where(['company_id'=>$cid,'contacter_id'=>$contacter_id])->delete();
				getCrmDbModel('contacter_detail')->where(['company_id'=>$cid,'contacter_id'=>$contacter_id])->delete();
			}

			$this->ajaxReturn(['status'=>0,'msg'=>L('LINE_NUMBER').":".$i."，".L('IMPORT_FAILED')."，".$form['form_description'].L('IS_REQUIRED'),'url'=>$url]);
		}

		if($value && in_array($form['form_type'],['radio','select','checkbox','select_text']))
		{
			$customer_option = explode('|',$form['form_option']);

			if($form['form_type'] == 'checkbox')
			{
				$value_option = explode(',',$value);

				foreach ($value_option as $va) {

					if (in_array($va, $customer_option))
					{
						continue;
					}
					else
					{
						getCrmDbModel($type)->where(['company_id'=>$cid,$type.'_id'=>$this_id])->delete();
						getCrmDbModel($type.'_detail')->where(['company_id'=>$cid,$type.'_id'=>$this_id])->delete();

						if($contacter_id > 0)
						{
							getCrmDbModel('contacter')->where(['company_id'=>$cid,'contacter_id'=>$contacter_id])->delete();
							getCrmDbModel('contacter_detail')->where(['company_id'=>$cid,'contacter_id'=>$contacter_id])->delete();
						}

						//fwrite($fp,"行号:".$i.",处理失败，原因：内容有误\r\n");

						$this->ajaxReturn(['status'=>0,'msg'=>L('LINE_NUMBER').":".$i."，".L('IMPORT_FAILED')."，".L('REASON')."：".$form['form_description'].L('CONTENT_WRONG')."",'url'=>$url]);
					}
				}

				$customer_detail['form_content'] = $value;
			}
			else
			{
				if(in_array($value,$customer_option))
				{
					$customer_detail['form_content'] = $value;
				}
				else
				{
					getCrmDbModel($type)->where(['company_id'=>$cid,$type.'_id'=>$this_id])->delete();
					getCrmDbModel($type.'_detail')->where(['company_id'=>$cid,$type.'_id'=>$this_id])->delete();

					if($contacter_id > 0)
					{
						getCrmDbModel('contacter')->where(['company_id'=>$cid,'contacter_id'=>$contacter_id])->delete();
						getCrmDbModel('contacter_detail')->where(['company_id'=>$cid,'contacter_id'=>$contacter_id])->delete();
					}

					//fwrite($fp,"行号:".$i.",处理失败，原因：内容有误\r\n");

					$this->ajaxReturn(['status'=>0,'msg'=>L('LINE_NUMBER').":".$i."，".L('IMPORT_FAILED')."，".L('REASON')."：".$form['form_description'].L('CONTENT_WRONG')."",'url'=>$url]);
				}
			}
		}
		else if($value && $form['form_type'] == 'date')
		{
			$customer_detail['form_content'] = gmdate('Y-m-d H:i:s',$shared->ExcelToPHP($value));
		}
		else if($value && $form['form_type'] == 'region')
		{
			$customer_detail['form_content'] = '';

			$region = explode(',',$value);

			$crmsite = getCrmDbModel('Setting')->where(['company_id'=>$cid])->find();

			$crmsite = unserialize($crmsite['value']);

			if($crmsite['regionType'] == 'world')
			{
				$country_code = getCrmDbModel('country')->where(['name'=>$region[0]])->getField('code');

				$customer_detail['form_content'] .= $country_code;

				if($country_code)
				{
					$province_code = getCrmDbModel('province')->where(['name'=>$region[1],'country_code'=>$country_code])->getField('code');

					if($province_code)
					{
						$customer_detail['form_content'] .= ','.$province_code;

						$city_code = getCrmDbModel('city')->where(['name'=>$region[2],'country_code'=>$country_code,'province_code'=>$province_code])->getField('code');

						if($city_code)
						{
							$customer_detail['form_content'] .= ','.$city_code;

							$area_code = getCrmDbModel('area')->where(['name'=>$region[3],'country_code'=>$country_code,'province_code'=>$province_code,'city_code'=>$city_code])->getField('code');

							if($area_code)
							{
								$customer_detail['form_content'] .= ','.$area_code;
							}
						}
					}
				}
			}
			else
			{
				$country_code = 1;

				$customer_detail['form_content'] .= $country_code;

				$province_code = getCrmDbModel('province')->where(['name'=>$region[0],'country_code'=>$country_code])->getField('code');

				if($province_code)
				{
					$customer_detail['form_content'] .= ','.$province_code;

					$city_code = getCrmDbModel('city')->where(['name'=>$region[1],'country_code'=>$country_code,'province_code'=>$province_code])->getField('code');

					if($city_code)
					{
						$customer_detail['form_content'] .= ','.$city_code;

						$area_code = getCrmDbModel('area')->where(['name'=>$region[2],'country_code'=>$country_code,'province_code'=>$province_code,'city_code'=>$city_code])->getField('code');

						if($area_code)
						{
							$customer_detail['form_content'] .= ','.$area_code;
						}
					}
				}
			}

		}
		else if($value && $form['form_type'] == 'number')
		{
			if(!is_numeric($value))
			{
				getCrmDbModel($type)->where(['company_id'=>$cid,$type.'_id'=>$this_id])->delete();
				getCrmDbModel($type.'_detail')->where(['company_id'=>$cid,$type.'_id'=>$this_id])->delete();

				if($contacter_id > 0)
				{
					getCrmDbModel('contacter')->where(['company_id'=>$cid,'contacter_id'=>$contacter_id])->delete();
					getCrmDbModel('contacter_detail')->where(['company_id'=>$cid,'contacter_id'=>$contacter_id])->delete();
				}

				$this->ajaxReturn(['status'=>0,'msg'=>L('LINE_NUMBER').":".$i."，".L('IMPORT_FAILED')."，".L('REASON')."：".$form['form_description'].L('CONTENT_WRONG')."",'url'=>$url]);
			}
			else
			{
				$customer_detail['form_content'] = $value;
			}
		}
		else
		{
			$customer_detail['form_content'] = $value ? $value : '';
		}

		if($value && $form['is_unique'] == 1)
		{
			$uniqueData = CrmisUniqueData($type,$cid,$form['form_id'],$customer_detail['form_content']);

			if($uniqueData)
			{
				getCrmDbModel($type)->where(['company_id'=>$cid,$type.'_id'=>$this_id])->delete();
				getCrmDbModel($type.'_detail')->where(['company_id'=>$cid,$type.'_id'=>$this_id])->delete();

				if($contacter_id > 0)
				{
					getCrmDbModel('contacter')->where(['company_id'=>$cid,'contacter_id'=>$contacter_id])->delete();
					getCrmDbModel('contacter_detail')->where(['company_id'=>$cid,'contacter_id'=>$contacter_id])->delete();
				}

				$this->ajaxReturn(['status'=>0,'msg'=>L('LINE_NUMBER').":".$i."，".L('IMPORT_FAILED')."，".$form['form_description'].L('EXISTED'),'url'=>$url]);
			}
		}

		return $customer_detail['form_content'];
	}
}
