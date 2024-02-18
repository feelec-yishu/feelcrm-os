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

namespace Index\Controller;

use Org\Util\ExcelAssistant;

use PHPExcel_Cell;

use PHPExcel_Exception;

use PHPExcel_Reader_Exception;

use PHPExcel_RichText;

use Think\Controller;

class UploadController extends Controller
{
//    微信LOGO和二维码
    public function UploadWeChat()
    {
        $result = D('Upload')->UploadWeChat($_FILES);

        $this->ajaxReturn($result);
    }


//    上传文件
    public function UploadTicketFile()
    {
        $result = D('Upload')->uploadAttachment();

        $this->ajaxReturn($result);
    }


//    上传图片
    public function uploadImageFile($type = '',$name = '')
    {
        $result = D('Upload')->UploadImageFile($type,$name);

        $this->ajaxReturn($result);
    }


//    上传头像
    public function uploadHeadFile($type = '')
    {
        $result = D('Upload')->UploadHeadFile($type);

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
        	'maxSize'  => 20 * 1024 * 1024, // 设置附件上传大小
            'rootPath' => './Attachs/',     // 文件上传的保存路径
            'savePath' => 'excel/',         // 文件上传的保存路径
            'saveName' => ['uniqid',''],
            'exts'     => ['xlsx','xls'],   // 设置附件上传类型
            'autoSub'  => true,
            'subName'  => ['date','Ymd'],
        ];

	    $source = I('post.source') ? I('post.source') : I('get.source');

        $Upload = new \Think\Upload($config);

        $info = $Upload->uploadOne($_FILES['excel']);

        if(!$info)
        {
            $data = ['status'=>0,'msg'=>$Upload->getError()];
        }
        else
        {
            $file = $config['rootPath'].$info['savepath'].$info['savename'];

            $data = $this->HandleImport($file,$source);
        }

        $this->ajaxReturn($data);
    }


    /*
	* 处理导入的Excel文件
	* @param string $file   Excel文件路径
	* @param string $source 导入类型，library：知识库；faq：FAQ；customer：会员；member：用户
	*/
    private function HandleImport($file,$source='')
    {
//        检查Excel文件是否存在
        if(!file_exists($file))
        {
            return ['error'=>0,'msg'=>L('FILE_DOES_NOT_EXIST')];
        }
        else
        {
	        $company_id = session('company_id');

	        import('Org.Util.PHPExcel');//手动加载第三方插件

	        try
	        {
//                加载excel文件
		        $objPHPExcel = \PHPExcel_IOFactory::load($file);

//		          第一个工作薄
		        $sheet = $objPHPExcel->getSheet(0);

//		          获取Excel有效行数
		        $rows = $sheet->getHighestRow();

//                Execl表结构
		        if($source == 'library')
		        {
			        $columnNumber = PHPExcel_Cell::columnIndexFromString($sheet->getHighestDataColumn());

//	                  根据数字获取已使用的列头数组
			        $ExcelAssistant = new ExcelAssistant();

			        $columns = $ExcelAssistant->GetExcelTit($columnNumber);

			        $cols = ['A'=>'name','B'=>'title','C'=>'content','D'=>'title_en','E'=>'content_en','F'=>'title_jp','G'=>'content_jp',
			                 'H'=>'is_recommend','I'=>'sort'];

			        foreach($columns as $ck=>$cv)
			        {
				        if(!$cols[$cv]) $cols[$cv] = 'ask_title';
			        }

			        $url = U('Library/manager');
		        }
		        else if($source == 'faq')
		        {
			        $columnNumber = PHPExcel_Cell::columnIndexFromString($sheet->getHighestDataColumn());

//	                  根据数字获取已使用的列头数组
			        $ExcelAssistant = new ExcelAssistant();

			        $columns = $ExcelAssistant->GetExcelTit($columnNumber);

			        $cols = ['A'=>'name','B'=>'title','C'=>'content','D'=>'title_en','E'=>'content_en','F'=>'title_jp','G'=>'content_jp',
			                 'H'=>'is_recommend','I'=>'sort'];

			        foreach($columns as $ck=>$cv)
			        {
				        if(!$cols[$cv]) $cols[$cv] = 'ask_title';
			        }

			        $url = U('Faq/manager');
		        }
		        else if($source == 'customer')
		        {
			        $cols = ['A'=>'name','B'=>'account','C'=>'mobile','D'=>'email'];

			        $url = U('Customer/index');
		        }
		        else if($source == 'member')
		        {
			        $cols = ['A'=>'name','B'=>'account','C'=>'mobile','D'=>'email','E'=>'group_id','F'=>'role_id'];

			        $url = U('Member/index');
		        }
		        else
		        {
			        $cols = ['A'=>'type_name','B'=>'fast_title','C'=>'fast_content'];

			        $url = U('FastReply/index');
		        }

//                读取每行内容
		        $count = [];

		        $j = 0;

//		        第一行是导入须知，第二行是表头，这里从第三行开始取读取数据，循环行数，获取数据
		        for ($i=3; $i<=$rows; ++$i)
		        {
			        $add = $member = $firm = [];

			        $add['create_time'] = NOW_TIME;

			        $a = 0;

			        $ask = [];

			        foreach ($cols as $k => $v)
			        {
				        $value = $sheet->getCell($k.$i)->getValue();

				        if ($value instanceof PHPExcel_RichText)
				        {
					        $value = $value->__toString();
				        }

				        $add[$v] = $value;

				        $count[$i][$v] = $value ? $value : '';

//				        知识库导入问题
				        if($source == 'library')
				        {
					        $add['member_id'] = session('index')['member_id'];

					        if($v == 'name')
					        {
						        $directory = explode('>',$value);

						        $level1_pid = $level2_pid = $level3_pid = 0;

						        foreach($directory as $dk=>$dv)
						        {
							        if($dk == 0)
							        {
								        $dWhere = ['company_id'=>$company_id,'name'=>$dv,'level'=>1];

								        $directory_id = M('library_directory')->where($dWhere)->getField('directory_id');

								        if(!$directory_id)
								        {
									        $dWhere['create_time'] = NOW_TIME;

									        $directory_id = M('library_directory')->add($dWhere);
								        }

								        $level1_pid = $directory_id;
							        }

							        if($dk == 1)
							        {
								        $dWhere = ['company_id'=>$company_id,'name'=>$dv,'level'=>2,'parent_id'=>$level1_pid];

								        $directory_id = M('library_directory')->where($dWhere)->getField('directory_id');

								        if(!$directory_id)
								        {
									        $dWhere['create_time'] = NOW_TIME;

									        $directory_id = M('library_directory')->add($dWhere);
								        }

								        $level2_pid = $directory_id;
							        }

							        if($dk == 2)
							        {
								        $dWhere = ['company_id'=>$company_id,'name'=>$dv,'level'=>3,'parent_id'=>$level2_pid];

								        $directory_id = M('library_directory')->where($dWhere)->getField('directory_id');

								        if(!$directory_id)
								        {
									        $dWhere['create_time'] = NOW_TIME;

									        $directory_id = M('library_directory')->add($dWhere);
								        }

								        $level3_pid = $directory_id;
							        }

							        if($dk == 3)
							        {
								        $dWhere = ['company_id'=>$company_id,'name'=>$dv,'level'=>4,'parent_id'=>$level3_pid];

								        $directory_id = M('library_directory')->where($dWhere)->getField('directory_id');

								        if(!$directory_id)
								        {
									        $dWhere['create_time'] = NOW_TIME;

									        $directory_id = M('library_directory')->add($dWhere);
								        }
							        }

							        $add['directory_id'] = $directory_id;
						        }

						        unset($add['name']);
					        }

					        if(in_array($v,['title_en','title_jp']))
					        {
						        $add[$v] = $value ? $value : '';
					        }

					        if($v == 'is_recommend')
					        {
						        $add['is_recommend'] = $value == '是' ? 10 : 20;
					        }

					        if($v == 'sort')
					        {
						        $add['sort'] = $value ? $value : 100;
					        }

					        if($v == 'ask_title' && !empty($value))
					        {
						        $ask[$a]['ask_title'] = $value;

						        $a++;
					        }
				        }
				        else if($source == 'faq')
				        {
					        $add['member_id'] = session('index')['member_id'];

					        if($v == 'name')
					        {
						        $directory = explode('>',$value);

						        $level1_pid = $level2_pid = $level3_pid = 0;

						        foreach($directory as $dk=>$dv)
						        {
							        if($dk == 0)
							        {
								        $dWhere = ['company_id'=>$company_id,'name'=>$dv,'level'=>1];

								        $directory_id = M('faq_directory')->where($dWhere)->getField('directory_id');

								        if(!$directory_id)
								        {
									        $dWhere['create_time'] = NOW_TIME;

									        $directory_id = M('faq_directory')->add($dWhere);
								        }

								        $level1_pid = $directory_id;
							        }

							        if($dk == 1)
							        {
								        $dWhere = ['company_id'=>$company_id,'name'=>$dv,'level'=>2,'parent_id'=>$level1_pid];

								        $directory_id = M('faq_directory')->where($dWhere)->getField('directory_id');

								        if(!$directory_id)
								        {
									        $dWhere['create_time'] = NOW_TIME;

									        $directory_id = M('faq_directory')->add($dWhere);
								        }

								        $level2_pid = $directory_id;
							        }

							        if($dk == 2)
							        {
								        $dWhere = ['company_id'=>$company_id,'name'=>$dv,'level'=>3,'parent_id'=>$level2_pid];

								        $directory_id = M('faq_directory')->where($dWhere)->getField('directory_id');

								        if(!$directory_id)
								        {
									        $dWhere['create_time'] = NOW_TIME;

									        $directory_id = M('faq_directory')->add($dWhere);
								        }

								        $level3_pid = $directory_id;
							        }

							        if($dk == 3)
							        {
								        $dWhere = ['company_id'=>$company_id,'name'=>$dv,'level'=>4,'parent_id'=>$level3_pid];

								        $directory_id = M('faq_directory')->where($dWhere)->getField('directory_id');

								        if(!$directory_id)
								        {
									        $dWhere['create_time'] = NOW_TIME;

									        $directory_id = M('faq_directory')->add($dWhere);
								        }
							        }

							        $add['directory_id'] = $directory_id;
						        }

						        unset($add['name']);
					        }

					        if(in_array($v,['title_en','title_jp']))
					        {
						        $add[$v] = $value ? $value : '';
					        }

					        if($v == 'is_recommend')
					        {
						        $add['is_recommend'] = $value == '是' ? 10 : 20;
					        }

					        if($v == 'sort')
					        {
						        $add['sort'] = $value ? $value : 100;
					        }

					        if($v == 'ask_title' && !empty($value))
					        {
						        $ask[$a]['ask_title'] = $value;

						        $a++;
					        }
				        }
				        else if($source == 'customer')//导入会员
				        {
					        $add['type'] = 2;

					        $add['password'] = md5('123456');

					        $add['create_ip'] = get_client_ip();

					        $firm = ['company_id'=>$company_id,'firm_name'=>$add['name'],'firm_status'=>0,'access_token'=>getAccessToken($company_id,'FeelDesk'),'create_time'=>NOW_TIME,'create_ip'=>get_client_ip()];

					        if($v == 'account' && $value)
					        {
						        if(!isMobile($value) && !isEmail($value))
						        {
							        $this->ajaxReturn(['error'=>0,'msg'=>L('ACCOUNT_FORMAT_ERROR',['account'=>$value]),'url'=>$url]);
						        }
						        else
						        {
							        $member = M('member')->where(['mobile|email|account'=>$value])->field('firm_id,member_id,mobile,email')->find();
						        }
					        }
					        else if($v == 'mobile' && $value)
					        {
						        if(!isMobile($value))
						        {
							        $this->ajaxReturn(['error'=>0,'msg'=>L('MOBILE_FORMAT_ERROR',['mobile'=>$value]),'url'=>$url]);
						        }
						        else
						        {
							        $member = M('member')->where(['mobile|account'=>$value])->field('firm_id,member_id,mobile,email')->find();
						        }
					        }
					        else if($v == 'email' && $value)
					        {
						        if(!isEmail($value))
						        {
							        $this->ajaxReturn(['error'=>0,'msg'=>L('MAIL_FORMAT_ERROR',['email'=>$value]),'url'=>$url]);
						        }
						        else
						        {
							        $member = M('member')->where(['email|account'=>$value])->field('firm_id,member_id,mobile,email')->find();
						        }
					        }
				        }
				        else if($source == 'member')
				        {
					        $add['type'] = 1;

					        $add['password'] = md5('123456');

					        $add['create_ip'] = get_client_ip();

					        if($v == 'account' && $value)
					        {
						        if(!isMobile($value) && !isEmail($value))
						        {
							        $this->ajaxReturn(['error'=>0,'msg'=>L('ACCOUNT_FORMAT_ERROR',['account'=>$value]),'url'=>$url]);
						        }
						        else
						        {
							        $member = M('member')->where(['mobile|email|account'=>$value])->field('member_id,mobile,email')->find();
						        }
					        }
					        else if($v == 'mobile' && $value)
					        {
						        if(!isMobile($value))
						        {
							        $this->ajaxReturn(['error'=>0,'msg'=>L('MOBILE_FORMAT_ERROR',['mobile'=>$value]),'url'=>$url]);
						        }
						        else
						        {
							        $member = M('member')->where(['mobile|account'=>$value])->field('member_id,mobile,email')->find();
						        }
					        }
					        else if($v == 'email' && $value)
					        {
						        if(!isEmail($value))
						        {
							        $this->ajaxReturn(['error'=>0,'msg'=>L('MAIL_FORMAT_ERROR',['email'=>$value]),'url'=>$url]);
						        }
						        else
						        {
							        $member = M('member')->where(['email|account'=>$value])->field('member_id,mobile,email')->find();
						        }
					        }
					        else if($v == 'group_id' && $value)
					        {
						        $group_id = M('group')->where(['company_id'=>$company_id,'group_name'=>$value])->getField('group_id');

						        if(!$group_id)
						        {
							        $addGroup = ['company_id'=>$company_id,'group_name'=>$value,'ticket_auth'=>10,'client_ip'=>get_client_ip(),'create_time'=>NOW_TIME];

							        if($group_id = M('group')->add($addGroup))
							        {
								        saveFeelDeskEncodeId($company_id,$group_id,'Group');

								        $add['group_id'] = $group_id;
							        }
							        else
							        {
								        $this->ajaxReturn(['error'=>0,'msg'=>'部门'.$value.L('IMPORT_FAILED'),'url'=>$url]);
							        }
						        }
						        else
						        {
							        $add['group_id'] = $group_id;
						        }
					        }
					        else if($v == 'role_id' && $value)
					        {
						        $role_id = M('role')->where(['company_id'=>$company_id,'role_name'=>$value])->getField('role_id');

						        if(!$role_id)
						        {
							        $addRole = ['company_id'=>$company_id,'role_name'=>$value,'client_ip'=>get_client_ip(),'create_time'=>NOW_TIME];

							        if($role_id = M('role')->add($addRole))
							        {
								        saveFeelDeskEncodeId($company_id,$role_id,'Role');

								        $add['role_id'] = $role_id;
							        }
							        else
							        {
								        $this->ajaxReturn(['error'=>0,'msg'=>'角色'.$value.'导入失败','url'=>$url]);
							        }
						        }
						        else
						        {
							        $add['role_id'] = $role_id;
						        }
					        }
				        }
				        else//导入快捷回复
				        {
					        if($v == 'type_name' && $value)
					        {
						        $fast_type_id = M('fastreply_type')->where(["type_name"=>$value,'company_id'=>$company_id])->getField('fast_type_id');

						        if(!$fast_type_id)
						        {
							        $fast_type_id = M('fastreply_type')->add(["type_name"=>$value,'company_id'=>$company_id,'create_time'=>NOW_TIME]);
						        }

						        $add['fast_type_id'] = $fast_type_id;
					        }

					        unset($add['type_name']);
				        }

				        $add['company_id'] = $company_id;
			        }

//                    开始导入
			        if($source == 'library')
			        {
				        if(!$problem_id = M('library_problem')->add($add))
				        {
					        $error = M('library_problem')->getDbError();

					        return ['error'=>0,'msg'=>L('LINE_NUMBER').":".$i.",".L('IMPORT_FAILED')."，问题：".$add['title'].",原因：".$error];
				        }
				        else
				        {
					        foreach($ask as &$as)
					        {
						        $as['company_id'] = $company_id;

						        $as['problem_id'] = $problem_id;

						        $as['create_time'] = NOW_TIME;
					        }

					        M('library_ask')->addAll($ask);

					        $j++;
				        }
			        }
			        else if($source == 'faq')
			        {
				        if(!$problem_id = M('faq_problem')->add($add))
				        {
					        $error = M('faq_problem')->getDbError();

					        return ['error'=>0,'msg'=>L('LINE_NUMBER').":".$i.",".L('IMPORT_FAILED')."，问题：".$add['title'].",原因：".$error];
				        }
				        else
				        {
					        foreach($ask as &$as)
					        {
						        $as['company_id'] = $company_id;

						        $as['problem_id'] = $problem_id;

						        $as['create_time'] = NOW_TIME;
					        }

					        M('faq_ask')->addAll($ask);

					        $j++;
				        }
			        }
			        else if($source == 'customer')
			        {
				        if($member['member_id'] > 0)
				        {
					        $save = $add;

					        $save['member_id'] = $member['member_id'];

					        $save['mobile'] = $save['mobile'] ? $save['mobile'] : $member['mobile'];

					        $save['email'] = $save['email'] ? $save['email'] : $member['email'];

					        if(!$member['firm_id'])
					        {
						        $firm_id = M('firm')->add($firm);

						        if($firm_id > 0)
						        {
							        $save['firm_id'] = $firm_id;

							        saveFeelDeskEncodeId($company_id,$firm_id,'Firm');

//					                  创建默认角色、权限
							        D('MemberReg')->createDefaultRole($company_id,$member['member_id'],false,$firm_id);
						        }
					        }

					        M('member')->save($save);
				        }
				        else
				        {
					        if($add['account'])
					        {
						        $add['mobile'] = $add['mobile'] ? $add['mobile'] : '';

						        $add['email'] = $add['email'] ? $add['email'] : '';

						        $add['create_time'] = NOW_TIME;

						        $add['is_first_customer'] = 2;

						        $firm_id = M('firm')->add($firm);

						        if($firm_id > 0)
						        {
							        $add['firm_id'] = $firm_id;

							        saveFeelDeskEncodeId($company_id,$firm_id,'Firm');
						        }

						        if(!$member_id = M('member')->add($add))
						        {
							        $error = M('member')->getDbError();

							        return ['error'=>0,'msg'=>L('LINE_NUMBER').":".$i.",".L('IMPORT_FAILED')."，会员姓名：".$add['name'].",原因：".$error];
						        }
						        else
						        {
//					                  创建默认角色、权限
							        D('MemberReg')->createDefaultRole($company_id,$member_id,false,$firm_id);

							        saveFeelDeskEncodeId($company_id,$member_id,'Member');
						        }
					        }
				        }

				        $j++;
			        }
			        else if($source == 'member')
			        {
				        if($member['member_id'] > 0)
				        {
					        $save = $add;

					        $save['member_id'] = $member['member_id'];

					        $save['mobile'] = $save['mobile'] ? $save['mobile'] : $member['mobile'];

					        $save['email'] = $save['email'] ? $save['email'] : $member['email'];

					        M('member')->save($save);
				        }
				        else
				        {
					        $add['create_time'] = NOW_TIME;

					        if($add['account'])
					        {
						        $add['mobile'] = $add['mobile'] ? $add['mobile'] : '';

						        $add['email'] = $add['email'] ? $add['email'] : '';

						        if(!$member_id = M('member')->add($add))
						        {
							        $error = M('member')->getDbError();

							        return ['error'=>0,'msg'=>L('LINE_NUMBER').":".$i.",".L('IMPORT_FAILED')."，用户姓名：".$add['name'].",原因：".$error];
						        }
						        else
						        {
							        saveFeelDeskEncodeId($company_id,$member_id,'Member');
						        }
					        }
				        }

				        $j++;
			        }
			        else
			        {
				        if(!M('fastreply')->add($add))
				        {
					        $error = M('fastreply')->getDbError();

					        return ['error'=>0,'msg'=>L('LINE_NUMBER').":".$i.",".L('IMPORT_FAILED')."，回复语标题：".$add['fast_title'].",原因：".$error];
				        }
				        else
				        {
					        $j++;
				        }
			        }
		        }

		        $data = ['error'=>1,'msg'=>L('IMPORT_SUCCESS'),'url'=>$url];

		        @unlink($file);

		        D('Upload')->deleteDir(dirname($file));

		        return $data;
	        }
	        catch (PHPExcel_Reader_Exception $e)
	        {
		        return ['error'=>0,'msg'=>$e->getMessage()];
	        }
	        catch (PHPExcel_Exception $e)
	        {
		        return ['error'=>0,'msg'=>$e->getMessage()];
	        }
        }
    }
}
