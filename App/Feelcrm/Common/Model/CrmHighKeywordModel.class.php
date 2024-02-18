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
namespace Common\Model;

use Common\Model\BasicModel;

class CrmHighKeywordModel extends BasicModel
{
	protected $autoCheckFields = false;

	protected $_member;

	public function _initialize()
	{
		$this->_member = session('index');
	}

	public function memberAndGroup($company_id,$model,$type_id='',$isRecover=0)
	{
		//选择部门和用户
		if(I('get.highKeywordMemberId'))
		{
			$highKeywordMemberId = I('get.highKeywordMemberId');

			$highKeywordMemberId = explode(',',$highKeywordMemberId);
		}

		if(I('get.highKeywordCreateId'))
		{
			$highKeywordCreateId = I('get.highKeywordCreateId');

			$highKeywordCreateId = explode(',',$highKeywordCreateId);
		}

		if($model == 'Customer' || $model == 'Opportunity' || $model == 'Contacter' || $model == 'Follow')
		{
			if($model == 'Customer' && $type_id)
			{
				$getCustomerAuth = D('CrmSelectField')->getCustomerAuth($this->_member,$company_id,$this->_member['member_id'],'','pool');
			}
			else
			{
				$getCustomerAuth = D('CrmSelectField')->getCustomerAuth($this->_member,$company_id,$this->_member['member_id']);
			}
		}

		if($model == 'Clue')
		{
			if($type_id)
			{
				$getCustomerAuth = D('CrmSelectField')->getCustomerAuth($this->_member,$company_id,$this->_member['member_id'],'','cluePool');
			}
			else
			{
				$getCustomerAuth = D('CrmSelectField')->getCustomerAuth($this->_member,$company_id,$this->_member['member_id'],'','clue');
			}
		}

		if($model == 'Contract')
		{
			$getCustomerAuth = D('CrmSelectField')->getCustomerAuth($this->_member,$company_id,$this->_member['member_id'],'','contract');
		}

		if($model == 'Account')
		{
			$getCustomerAuth = D('CrmSelectField')->getCustomerAuth($this->_member,$company_id,$this->_member['member_id'],'','account');
		}

		if($model == 'Receipt')
		{
			$getCustomerAuth = D('CrmSelectField')->getCustomerAuth($this->_member,$company_id,$this->_member['member_id'],'','receipt');
		}

		if($model == 'Invoice')
		{
			$getCustomerAuth = D('CrmSelectField')->getCustomerAuth($this->_member,$company_id,$this->_member['member_id'],'','invoice');
		}

		if($getCustomerAuth['memberRoleArr'] && !is_array($getCustomerAuth['memberRoleArr']))
		{
			if($highKeywordMemberId) $highKeywordMemberId = $getCustomerAuth['memberRoleArr'];

			if($highKeywordCreateId) $highKeywordCreateId = $getCustomerAuth['memberRoleArr'];
		}

		$isGroup = 0;

		if((!$highKeywordMemberId && !$highKeywordCreateId) && I('get.highKeywordGroupId'))
		{
			$isGroup = 1;

			if($getCustomerAuth['memberRoleArr'] && !is_array($getCustomerAuth['memberRoleArr']))
			{
				$highKeywordCreateId = $highKeywordMemberId = $getCustomerAuth['memberRoleArr'];
			}
			else
			{
				$highKeywordGroupId = I('get.highKeywordGroupId');

				$memberByGroups = getGroupMemberByGroups($company_id,$highKeywordGroupId);

				$highKeywordMemberId = $memberByGroups['member_id'];

				//创建人维度查看权限
				$isCreaterView = D('RoleAuth')->checkRoleAuthByMenu($this->_member['company_id'],'CreaterView'.$model,$this->_member['role_id'],'crm');

				if($isCreaterView)
				{
					$highKeywordCreateId = $memberByGroups['member_id'];
				}
			}
		}

		if($highKeywordMemberId || $highKeywordCreateId)
		{
			if($type_id)
			{
				$field[$type_id] = ['in',implode(',',$highKeywordMemberId)];
			}
			else
			{
				$highKeyword = unserialize(urldecode(I('get.highKeyword')));

				if(!$highKeyword)
				{
					$highKeyword = I('get.highKeyword');
				}

				//全部满足且选择了负责人创建人时用and，否则用or查询
				if($highKeyword['condition'] == 1 && !$isGroup)
				{
					if($highKeywordMemberId)
					{
						if(count($highKeywordMemberId) > 1)
						{
							$member_id = implode(',',$highKeywordMemberId);

							$field['member_id'] = ['in',$member_id];
						}
						else
						{
							$field['member_id'] = $highKeywordMemberId[0];
						}
					}

					if($highKeywordCreateId)
					{
						if(count($highKeywordCreateId) > 1)
						{
							$creater_id = implode(',',$highKeywordCreateId);

							$field['creater_id'] = ['in',$creater_id];
						}
						else
						{
							$field['creater_id'] = $highKeywordCreateId[0];
						}
					}
				}
				else
				{
					$create_view_sql = "((";

					if($highKeywordMemberId)
					{
						if(count($highKeywordMemberId) > 1)
						{
							$member_id = implode(',',$highKeywordMemberId);

							$create_view_sql .= "member_id in (".$member_id.")";
						}
						else
						{
							$create_view_sql .= "member_id = ".$highKeywordMemberId[0];
						}
					}

					if($highKeywordCreateId)
					{
						if($highKeywordMemberId)
						{
							$create_view_sql .= " or ";
						}

						if(count($highKeywordCreateId) > 1)
						{
							$creater_id = implode(',',$highKeywordCreateId);

							$create_view_sql .= "creater_id in (".$creater_id.")";
						}
						else
						{
							$create_view_sql .= "creater_id = ".$highKeywordCreateId[0];
						}
					}

					$create_view_sql .= ") and member_id > 0";

					$create_view_sql .= ")";

					$field['_string'] = $create_view_sql;
				}
			}
		}

		return $field;
	}

	public function clueHighKey($company_id,$highKeyword,$type='',$isRecover = 0)
	{
		$field = $map = [];

		if($highKeyword['condition'] == 1)
		{
			if($type == 'pool')
			{
				//选择部门和用户
				$memberAndGroupField = $this->memberAndGroup($company_id,'Clue','creater_id');

				if($memberAndGroupField) $field = array_merge($memberAndGroupField,$field);
				//if($highKeyword['creater_id']) $field['creater_id'] = $highKeyword['creater_id'];
			}
			else
			{
				if($isRecover)//回收站
				{
					$memberAndGroupField = $this->memberAndGroup($company_id,'Clue','',$isRecover);

					if($memberAndGroupField) $field = array_merge($memberAndGroupField,$field);
				}
				else
				{
					//选择部门和用户
					$memberAndGroupField = $this->memberAndGroup($company_id,'Clue');

					if($memberAndGroupField) $field = array_merge($memberAndGroupField,$field);
				}

				//if($highKeyword['member_id']) $field['member_id'] = $highKeyword['member_id'];
			}

			if($highKeyword['clue_no'])
			{
				$field['clue_no'] = ["like","%".$highKeyword['clue_no']."%"];
			}

			if($highKeyword['start_time'])
			{
				$starttime = strtotime($highKeyword['start_time']);

				$endtime = $highKeyword['end_time'] ? strtotime($highKeyword['end_time']) : time();

				if($highKeyword['time_range_type'])
				{
					if($highKeyword['time_range_type'] == 'next_contact_time')
					{
						$field['nextcontacttime'] = [['egt',$starttime],['elt',$endtime]];
					}
					elseif($highKeyword['time_range_type'] == 'last_follow_time')
					{
						$field['lastfollowtime'] = [['egt',$starttime],['elt',$endtime]];
					}
					else
					{
						$field['createtime'] = [['egt',$starttime],['elt',$endtime]];
					}
				}
				else
				{
					$field['createtime'] = [['egt',$starttime],['elt',$endtime]];
				}
			}

			if($highKeyword['status'])
			{
				$field['status'] = $highKeyword['status'];
			}

			$clue_arr = [];

			$keynum = 0;

			foreach($highKeyword['define_form'] as $key=>&$val)
			{
				if($val)
				{
					$clue_name = '';

					$this_form_type = getCrmDbModel('define_form')->where(['type'=>'clue','form_name'=>$key,'company_id'=>$company_id,'closed'=>0])->getField('form_type');

					if($this_form_type == 'region')
					{
						$thisval = '';

						if($highKeyword['define_form'][$key]['defaultCountry'])
						{
							$thisval = $highKeyword['define_form'][$key]['defaultCountry'];

							if($highKeyword['define_form'][$key]['defaultProv'])
							{
								$thisval .= ','.$highKeyword['define_form'][$key]['defaultProv'];
							}

							if($highKeyword['define_form'][$key]['defaultCity'])
							{
								$thisval .= ','.$highKeyword['define_form'][$key]['defaultCity'];
							}

							if($highKeyword['define_form'][$key]['defaultArea'])
							{
								$thisval .= ','.$highKeyword['define_form'][$key]['defaultArea'];
							}

							$clue_name = CrmgetDefineFormHighField($company_id,'clue',$key,$thisval);
						}
						$val = $thisval;
					}
					elseif($this_form_type == 'date')
					{
						if(is_array($val))
						{
							$clue_name = CrmgetDefineFormHighFieldTimeRange($company_id,'clue',$key,$val[0],$val[1]);
						}
						else
						{
							$clue_name = CrmgetDefineFormHighField($company_id,'clue',$key,$val);
						}
					}
					else
					{
						if($this_form_type == 'checkbox')
						{
							$val = implode(",", $val);
						}
						$clue_name = CrmgetDefineFormHighField($company_id,'clue',$key,$val);
					}

					if($keynum > 0 && !$clue_arr)
					{
						$clue_arr = [];
					}
					else
					{
						if ($clue_name && count($clue_name) > 0)
						{
							if($keynum > 0)
							{
								$clue_arr = array_intersect($clue_arr, $clue_name);
							}
							else
							{
								$clue_arr = array_merge($clue_arr, $clue_name);
							}
						}
						else
						{
							if($this_form_type == 'region' && $highKeyword['define_form'][$key]['defaultCountry'])
							{
								$clue_arr = [];
							}
							else
							{
								if($this_form_type == 'region')
								{
									$clue_arr = $clue_arr;
								}
								else
								{
									$clue_arr = [];
								}
							}
						}
					}
				}
				if($val)
				{
					$keynum ++;
				}
			}

			if($highKeyword['abandon_id'])
			{
				$abandon = getCrmDbModel('clue_abandon')->where(['abandon_id'=>$highKeyword['abandon_id'],'company_id'=>$company_id])->field('clue_id')->select();

				$abandon_clue = array_column($abandon,'clue_id');

				$abandon_clue = array_unique($abandon_clue);

				$clue_arr = array_merge($clue_arr,$abandon_clue);
			}

			$clue_arr1 = array_unique($clue_arr);//去除数组中重复数据，返回数组

			$clue_arr = array_diff_assoc($clue_arr, $clue_arr1);//取得数组中的重复数据，返回重复数组

			if(!$clue_arr && $clue_arr1)
			{
				$clue_arr = $clue_arr1;
			}

			if(count($clue_arr) > 0)
			{
				$field['clue_id'] = ['in',implode(',',$clue_arr)];
			}
			else
			{
				if($keynum > 0)
				{
					$field['clue_id'] = '-1';
				}
			}
		}
		else
		{
			if($type == 'pool')
			{
				//选择部门和用户
				$memberAndGroupField = $this->memberAndGroup($company_id,'Clue','creater_id');

				if($memberAndGroupField) $map = array_merge($memberAndGroupField,$map);
				//if($highKeyword['creater_id']) $map['creater_id'] = $highKeyword['creater_id'];
			}
			else
			{
				if($isRecover)//回收站
				{
					$memberAndGroupField = $this->memberAndGroup($company_id,'Clue','',$isRecover);

					if($memberAndGroupField) $map = array_merge($memberAndGroupField,$map);
				}
				else
				{
					//选择部门和用户
					$memberAndGroupField = $this->memberAndGroup($company_id,'Clue');

					if($memberAndGroupField) $map = array_merge($memberAndGroupField,$map);
				}
				//if($highKeyword['member_id']) $map['member_id'] = $highKeyword['member_id'];
			}

			if($highKeyword['clue_no'])
			{
				$map['clue_no'] = ["like","%".$highKeyword['clue_no']."%"];
			}

			if($highKeyword['start_time'])
			{
				$starttime = strtotime($highKeyword['start_time']);

				$endtime = $highKeyword['end_time'] ? strtotime($highKeyword['end_time']) : time();

				if($highKeyword['time_range_type'])
				{
					if($highKeyword['time_range_type'] == 'next_contact_time')
					{
						$map['nextcontacttime'] = [['egt',$starttime],['elt',$endtime]];
					}
					elseif($highKeyword['time_range_type'] == 'last_follow_time')
					{
						$map['lastfollowtime'] = [['egt',$starttime],['elt',$endtime]];
					}
					else
					{
						$map['createtime'] = [['egt',$starttime],['elt',$endtime]];
					}
				}
				else
				{
					$map['createtime'] = [['egt',$starttime],['elt',$endtime]];
				}
			}

			if($highKeyword['status'])
			{
				$map['status'] = $highKeyword['status'];
			}

			$clue_arr = [];

			$keynum = 0;

			foreach($highKeyword['define_form'] as $key=>&$val)
			{
				if($val)
				{
					$clue_name = '';

					$this_form_type = getCrmDbModel('define_form')->where(['type'=>'clue','form_name'=>$key,'company_id'=>$company_id,'closed'=>0])->getField('form_type');

					if($this_form_type == 'region')
					{
						$thisval = '';

						if($highKeyword['define_form'][$key]['defaultCountry'])
						{
							$thisval = $highKeyword['define_form'][$key]['defaultCountry'];

							if($highKeyword['define_form'][$key]['defaultProv'])
							{
								$thisval .= ','.$highKeyword['define_form'][$key]['defaultProv'];
							}

							if($highKeyword['define_form'][$key]['defaultCity'])
							{
								$thisval .= ','.$highKeyword['define_form'][$key]['defaultCity'];
							}

							if($highKeyword['define_form'][$key]['defaultArea'])
							{
								$thisval .= ','.$highKeyword['define_form'][$key]['defaultArea'];
							}

							$clue_name = CrmgetDefineFormHighField($company_id,'clue',$key,$thisval);
						}

						$val = $thisval;
					}
					elseif($this_form_type == 'date')
					{
						if(is_array($val))
						{
							$clue_name = CrmgetDefineFormHighFieldTimeRange($company_id,'clue',$key,$val[0],$val[1]);
						}
						else
						{
							$clue_name = CrmgetDefineFormHighField($company_id,'clue',$key,$val);
						}
					}
					else
					{
						if($this_form_type == 'checkbox')
						{
							$val = implode(",", $val);
						}

						$clue_name = CrmgetDefineFormHighField($company_id,'clue',$key,$val);
					}

					if($clue_name && count($clue_name) >0 )
					{
						$clue_arr = array_merge($clue_arr,$clue_name);
					}

				}
				if($val)
				{
					$keynum ++;
				}
			}

			if($highKeyword['abandon_id'])
			{
				$abandon = getCrmDbModel('clue_abandon')->where(['abandon_id'=>$highKeyword['abandon_id'],'company_id'=>$company_id])->field('clue_id')->select();

				$abandon_clue = array_column($abandon,'clue_id');

				$abandon_clue = array_unique($abandon_clue);

				$clue_arr = array_merge($clue_arr,$abandon_clue);
			}

			$clue_arr = array_unique($clue_arr);//去除数组中重复数据，返回数组

			if(count($clue_arr) > 0)
			{
				$map['clue_id'] = ['in',implode(',',$clue_arr)];
			}
			else
			{
				if($keynum > 0)
				{
					$map['clue_id'] = '-1';
				}
			}

			if($map)
			{
				$map['_logic'] = 'or';

				$field['_complex'] = $map;
			}
		}

		return ['field'=>$field];
	}

	public function customerHighKey($company_id,$highKeyword,$memberRoleArr='',$type = 'index',$isRecover = 0)
	{
		$field = $map = [];

		if($highKeyword['condition'] == 1)
		{
			if($type == 'pool')
			{
				//选择部门和用户
				$memberAndGroupField = $this->memberAndGroup($company_id,'Customer','creater_id');

				if($memberAndGroupField) $field = array_merge($memberAndGroupField,$field);
				//if($highKeyword['creater_id']) $field['creater_id'] = $highKeyword['creater_id'];
			}
			else
			{
				if($isRecover)//回收站
				{
					$memberAndGroupField = $this->memberAndGroup($company_id,'Customer','',$isRecover);

					if($memberAndGroupField) $field = array_merge($memberAndGroupField,$field);
				}
				else
				{
					//选择部门和用户
					$memberAndGroupField = $this->memberAndGroup($company_id,'Customer');

					if($memberAndGroupField) $field = array_merge($memberAndGroupField,$field);
				}
				//if($highKeyword['member_id']) $field['member_id'] = $highKeyword['member_id'];

				if($highKeyword['is_trade'] == 1)
				{
					$field['is_trade'] = 1;
				}
				elseif($highKeyword['is_trade'] == 0 && $highKeyword['is_trade'] != '' && $highKeyword['is_trade'] != null)
				{
					$field['is_trade'] = 0;
				}
			}

			if($highKeyword['customer_no'])
			{
				$field['customer_no'] = ["like","%".$highKeyword['customer_no']."%"];
			}

			if($highKeyword['start_time'])
			{
				$starttime = strtotime($highKeyword['start_time']);

				$endtime = $highKeyword['end_time'] ? strtotime($highKeyword['end_time']) : time();

				if($highKeyword['time_range_type'])
				{
					if($highKeyword['time_range_type'] == 'next_contact_time')
					{
						$field['nextcontacttime'] = [['egt',$starttime],['elt',$endtime]];
					}
					elseif($highKeyword['time_range_type'] == 'last_follow_time')
					{
						$field['lastfollowtime'] = [['egt',$starttime],['elt',$endtime]];
					}
					else
					{
						$field['createtime'] = [['egt',$starttime],['elt',$endtime]];
					}
				}
				else
				{
					$field['createtime'] = [['egt',$starttime],['elt',$endtime]];
				}
			}

			$customer_arr = [];

			$contacter_arr = [];

			$keynum = 0;

			foreach($highKeyword['define_form'] as $key=>&$val)
			{
				if($val)
				{
					$customer_name = '';

					$this_form_type = getCrmDbModel('define_form')->where(['type'=>'customer','form_name'=>$key,'company_id'=>$company_id,'closed'=>0])->getField('form_type');

					if($this_form_type == 'region')
					{
						$thisval = '';

						if($highKeyword['define_form'][$key]['defaultCountry'])
						{
							$thisval = $highKeyword['define_form'][$key]['defaultCountry'];

							if($highKeyword['define_form'][$key]['defaultProv'])
							{
								$thisval .= ','.$highKeyword['define_form'][$key]['defaultProv'];
							}

							if($highKeyword['define_form'][$key]['defaultCity'])
							{
								$thisval .= ','.$highKeyword['define_form'][$key]['defaultCity'];
							}

							if($highKeyword['define_form'][$key]['defaultArea'])
							{
								$thisval .= ','.$highKeyword['define_form'][$key]['defaultArea'];
							}

							$customer_name = CrmgetDefineFormHighField($company_id,'customer',$key,$thisval);
						}

						$val = $thisval;
					}
					elseif($this_form_type == 'date')
					{
						if(is_array($val))
						{
							$customer_name = CrmgetDefineFormHighFieldTimeRange($company_id,'customer',$key,$val[0],$val[1]);
						}
						else
						{
							$customer_name = CrmgetDefineFormHighField($company_id,'customer',$key,$val);
						}
					}
					else
					{
						if($this_form_type == 'checkbox')
						{
							$val = implode(",", $val);
						}
						$customer_name = CrmgetDefineFormHighField($company_id,'customer',$key,$val);
					}

					if($keynum > 0 && !$customer_arr)
					{
						$customer_arr = [];
					}
					else
					{
						if ($customer_name && count($customer_name) > 0)
						{
							if($keynum > 0)
							{
								$customer_arr = array_intersect($customer_arr, $customer_name);
							}
							else
							{
								$customer_arr = array_merge($customer_arr, $customer_name);
							}
						}
						else
						{
							if($this_form_type == 'region' && $highKeyword['define_form'][$key]['defaultCountry'])
							{
								$customer_arr = [];
							}
							else
							{
								if($this_form_type == 'region')
								{
									$customer_arr = $customer_arr;
								}
								else
								{
									$customer_arr = [];
								}
							}

						}
					}
				}

				if($val)
				{
					$keynum ++;
				}
			}

			if($highKeyword['abandon_id'])
			{
				$abandon = getCrmDbModel('customer_abandon')->where(['abandon_id'=>$highKeyword['abandon_id'],'company_id'=>$company_id])->field('customer_id')->select();

				$abandon_customer = array_column($abandon,'customer_id');

				$abandon_customer = array_unique($abandon_customer);

				$customer_arr = array_merge($customer_arr,$abandon_customer);
			}

			if($highKeyword['contacter_name'])
			{
				$contacter_name = CrmgetDefineFormHighField($company_id,'contacter','name',$highKeyword['contacter_name']);

				if($contacter_name && count($contacter_name) >0 )
				{
					$contacter_arr = array_merge($contacter_arr,$contacter_name);
				}
			}

			if($highKeyword['contacter_phone'])
			{
				$contacter_phone = CrmgetDefineFormHighField($company_id,'contacter','phone',$highKeyword['contacter_phone']);

				if($contacter_phone && count($contacter_phone) >0 )
				{
					$contacter_arr = array_merge($contacter_arr,$contacter_phone);
				}
			}

			if($highKeyword['contacter_email'])
			{
				$contacter_email = CrmgetDefineFormHighField($company_id,'contacter','email',$highKeyword['contacter_email']);

				if($contacter_email && count($contacter_email) >0 )
				{
					$contacter_arr = array_merge($contacter_arr,$contacter_email);
				}
			}

			$contacter_arr = array_unique($contacter_arr);//去除数组中重复数据，返回数组

			$customer_info_arr = [];

			foreach($contacter_arr as $key=>$val)
			{
				if($memberRoleArr)
				{
					$customer_info = getCrmDbModel('contacter')->where(['contacter_id'=>$val,'member_id'=>$memberRoleArr,'isvalid'=>1,'company_id'=>$company_id])->getField('customer_id');
				}
				else
				{
					$customer_info = getCrmDbModel('contacter')->where(['contacter_id'=>$val,'isvalid'=>1,'company_id'=>$company_id])->getField('customer_id');
				}

				if($customer_info)
				{
					$customer_info_arr[] .= $customer_info;
				}
			}

			$customer_arr = array_merge($customer_arr,$customer_info_arr);

			$customer_arr1 = array_unique($customer_arr);//去除数组中重复数据，返回数组

			$customer_arr = array_diff_assoc($customer_arr, $customer_arr1);//取得数组中的重复数据，返回重复数组

			if(!$customer_arr && $customer_arr1)
			{
				$customer_arr = $customer_arr1;
			}

			if(count($customer_arr) > 0)
			{
				$field['customer_id'] = ['in',implode(',',$customer_arr)];
			}
			else
			{
				if($keynum > 0)
				{
					$field['customer_id'] = '-1';
				}
			}
		}
		else
		{
			if($type == 'pool')
			{
				//选择部门和用户
				$memberAndGroupField = $this->memberAndGroup($company_id,'Customer','creater_id');

				if($memberAndGroupField) $map = array_merge($memberAndGroupField,$map);
				//if($highKeyword['creater_id']) $map['creater_id'] = $highKeyword['creater_id'];
			}
			else
			{
				if($isRecover)//回收站
				{
					$memberAndGroupField = $this->memberAndGroup($company_id,'Customer','',$isRecover);

					if($memberAndGroupField) $map = array_merge($memberAndGroupField,$map);
				}
				else
				{
					//选择部门和用户
					$memberAndGroupField = $this->memberAndGroup($company_id, 'Customer');

					if ($memberAndGroupField) $map = array_merge($memberAndGroupField, $map);
				}
				//if($highKeyword['member_id']) $map['member_id'] = $highKeyword['member_id'];

				if($highKeyword['is_trade'] == 1)
				{
					$map['is_trade'] = 1;
				}
				elseif($highKeyword['is_trade'] == 0 && $highKeyword['is_trade'] != '' && $highKeyword['is_trade'] != null)
				{
					$map['is_trade'] = 0;
				}
			}

			if($highKeyword['customer_no'])
			{
				$map['customer_no'] = ["like","%".$highKeyword['customer_no']."%"];
			}

			if($highKeyword['start_time'])
			{
				$starttime = strtotime($highKeyword['start_time']);

				$endtime = $highKeyword['end_time'] ? strtotime($highKeyword['end_time']) : time();

				if($highKeyword['time_range_type'])
				{
					if($highKeyword['time_range_type'] == 'next_contact_time')
					{
						$map['nextcontacttime'] = [['egt',$starttime],['elt',$endtime]];
					}
					elseif($highKeyword['time_range_type'] == 'last_follow_time')
					{
						$map['lastfollowtime'] = [['egt',$starttime],['elt',$endtime]];
					}
					else
					{
						$map['createtime'] = [['egt',$starttime],['elt',$endtime]];
					}
				}
				else
				{
					$map['createtime'] = [['egt',$starttime],['elt',$endtime]];
				}
			}

			$customer_arr = [];

			$contacter_arr = [];

			$keynum = 0;

			foreach($highKeyword['define_form'] as $key=>&$val)
			{
				if($val)
				{
					$customer_name = '';

					$this_form_type = getCrmDbModel('define_form')->where(['type'=>'customer','form_name'=>$key,'company_id'=>$company_id,'closed'=>0])->getField('form_type');

					if($this_form_type == 'region')
					{
						$thisval = '';

						if($highKeyword['define_form'][$key]['defaultCountry'])
						{
							$thisval = $highKeyword['define_form'][$key]['defaultCountry'];

							if($highKeyword['define_form'][$key]['defaultProv'])
							{
								$thisval .= ','.$highKeyword['define_form'][$key]['defaultProv'];
							}

							if($highKeyword['define_form'][$key]['defaultCity'])
							{
								$thisval .= ','.$highKeyword['define_form'][$key]['defaultCity'];
							}

							if($highKeyword['define_form'][$key]['defaultArea'])
							{
								$thisval .= ','.$highKeyword['define_form'][$key]['defaultArea'];
							}

							$customer_name = CrmgetDefineFormHighField($company_id,'customer',$key,$thisval);
						}

						$val = $thisval;
					}
					elseif($this_form_type == 'date')
					{
						if(is_array($val))
						{
							$customer_name = CrmgetDefineFormHighFieldTimeRange($company_id,'customer',$key,$val[0],$val[1]);
						}
						else
						{
							$customer_name = CrmgetDefineFormHighField($company_id,'customer',$key,$val);
						}
					}
					else
					{
						if($this_form_type == 'checkbox')
						{
							$val = implode(",", $val);
						}
						$customer_name = CrmgetDefineFormHighField($company_id,'customer',$key,$val);
					}

					if($customer_name && count($customer_name) >0 )
					{
						$customer_arr = array_merge($customer_arr,$customer_name);
					}

				}
				if($val)
				{
					$keynum ++;
				}
			}

			if($highKeyword['abandon_id'])
			{
				$abandon = getCrmDbModel('customer_abandon')->where(['abandon_id'=>$highKeyword['abandon_id'],'company_id'=>$company_id])->field('customer_id')->select();

				$abandon_customer = array_column($abandon,'customer_id');

				$abandon_customer = array_unique($abandon_customer);

				$customer_arr = array_merge($customer_arr,$abandon_customer);
			}

			if($highKeyword['contacter_name'])
			{
				$contacter_name = CrmgetDefineFormHighField($company_id,'contacter','name',$highKeyword['contacter_name']);

				if($contacter_name && count($contacter_name) >0 )
				{
					$contacter_arr = array_merge($contacter_arr,$contacter_name);
				}
			}

			if($highKeyword['contacter_phone'])
			{
				$contacter_phone = CrmgetDefineFormHighField($company_id,'contacter','phone',$highKeyword['contacter_phone']);

				if($contacter_phone && count($contacter_phone) >0 )
				{
					$contacter_arr = array_merge($contacter_arr,$contacter_phone);
				}
			}

			if($highKeyword['contacter_email'])
			{
				$contacter_email = CrmgetDefineFormHighField($company_id,'contacter','email',$highKeyword['contacter_email']);

				if($contacter_email && count($contacter_email) >0 )
				{
					$contacter_arr = array_merge($contacter_arr,$contacter_email);
				}
			}

			$contacter_arr = array_unique($contacter_arr);//去除数组中重复数据，返回数组

			$customer_info_arr = [];

			foreach($contacter_arr as $key=>$val)
			{
				if($memberRoleArr)
				{
					$customer_info = getCrmDbModel('contacter')->where(['contacter_id'=>$val,'member_id'=>$memberRoleArr,'isvalid'=>1,'company_id'=>$company_id])->getField('customer_id');
				}
				else
				{
					$customer_info = getCrmDbModel('contacter')->where(['contacter_id'=>$val,'isvalid'=>1,'company_id'=>$company_id])->getField('customer_id');
				}

				if($customer_info)
				{
					$customer_info_arr[] .= $customer_info;
				}
			}

			$customer_arr = array_merge($customer_arr,$customer_info_arr);

			$customer_arr = array_unique($customer_arr);//去除数组中重复数据，返回数组

			if(count($customer_arr) > 0)
			{
				$map['customer_id'] = ['in',implode(',',$customer_arr)];
			}
			else
			{
				if($keynum > 0)
				{
					$map['customer_id'] = '-1';
				}
			}

			if($map)
			{
				$map['_logic'] = 'or';

				$field['_complex'] = $map;
			}
		}

		return ['field'=>$field];
	}

	public function opportunityHighKey($company_id,$highKeyword,$isRecover = 0)
	{
		$field = $map = [];

		if($highKeyword['condition'] == 1)
		{
			if($highKeyword['customer_id']) $field['customer_id'] = $highKeyword['customer_id'];

			if($isRecover)
			{
				$memberAndGroupField = $this->memberAndGroup($company_id,'Opportunity','',$isRecover);

				if ($memberAndGroupField) $field = array_merge($memberAndGroupField, $field);
			}
			else
			{
				//选择部门和用户
				$memberAndGroupField = $this->memberAndGroup($company_id, 'Opportunity');

				if ($memberAndGroupField) $field = array_merge($memberAndGroupField, $field);
			}
			//if($highKeyword['member_id']) $field['member_id'] = $highKeyword['member_id'];

			if($highKeyword['opportunity_no'])
			{
				$field['opportunity_no'] = ["like","%".$highKeyword['opportunity_no']."%"];
			}

			if($highKeyword['start_time'])
			{
				$starttime = strtotime($highKeyword['start_time']);

				$endtime = $highKeyword['end_time'] ? strtotime($highKeyword['end_time']) : time();

				if($highKeyword['time_range_type'])
				{
					if($highKeyword['time_range_type'] == 'next_contact_time')
					{
						$field['nextcontacttime'] = [['egt',$starttime],['elt',$endtime]];
					}
					elseif($highKeyword['time_range_type'] == 'last_follow_time')
					{
						$field['lastfollowtime'] = [['egt',$starttime],['elt',$endtime]];
					}
					elseif($highKeyword['time_range_type'] == 'predict_time')
					{
						$highKeyword['define_form']['predict_time'] = [$starttime,$endtime];
					}
					else
					{
						$field['createtime'] = [['egt',$starttime],['elt',$endtime]];
					}
				}
				else
				{
					$field['createtime'] = [['egt',$starttime],['elt',$endtime]];
				}
			}

			$opportunity_arr = [];

			$keynum = 0;

			foreach($highKeyword['define_form'] as $key=>&$val)
			{
				if($val)
				{
					$opportunity_name = '';

					$this_form_type = getCrmDbModel('define_form')->where(['type'=>'opportunity','form_name'=>$key,'company_id'=>$company_id,'closed'=>0])->getField('form_type');

					if($this_form_type == 'region')
					{
						$thisval = '';

						if($highKeyword['define_form'][$key]['defaultCountry'])
						{
							$thisval = $highKeyword['define_form'][$key]['defaultCountry'];

							if($highKeyword['define_form'][$key]['defaultProv'])
							{
								$thisval .= ','.$highKeyword['define_form'][$key]['defaultProv'];
							}

							if($highKeyword['define_form'][$key]['defaultCity'])
							{
								$thisval .= ','.$highKeyword['define_form'][$key]['defaultCity'];
							}

							if($highKeyword['define_form'][$key]['defaultArea'])
							{
								$thisval .= ','.$highKeyword['define_form'][$key]['defaultArea'];
							}

							$opportunity_name = CrmgetDefineFormHighField($company_id,'opportunity',$key,$thisval);
						}

						$val = $thisval;
					}
					elseif($this_form_type == 'date')
					{
						if(is_array($val))
						{
							$opportunity_name = CrmgetDefineFormHighFieldTimeRange($company_id,'opportunity',$key,$val[0],$val[1]);
						}
						else
						{
							$opportunity_name = CrmgetDefineFormHighField($company_id,'opportunity',$key,$val);
						}
					}
					else
					{
						if($this_form_type == 'checkbox')
						{
							$val = implode(",", $val);
						}

						$opportunity_name = CrmgetDefineFormHighField($company_id,'opportunity',$key,$val);
					}

					if($keynum > 0 && !$opportunity_arr)
					{
						$opportunity_arr = [];
					}
					else
					{
						if ($opportunity_name && count($opportunity_name) > 0)
						{
							if($keynum > 0)
							{
								$opportunity_arr = array_intersect($opportunity_arr, $opportunity_name);
							}
							else
							{
								$opportunity_arr = array_merge($opportunity_arr, $opportunity_name);
							}
						}
						else
						{
							if($this_form_type == 'region' && $highKeyword['define_form'][$key]['defaultCountry'])
							{
								$opportunity_arr = [];
							}
							else
							{
								if($this_form_type == 'region')
								{
									$opportunity_arr = $opportunity_arr;
								}
								else
								{
									$opportunity_arr = [];
								}
							}
						}
					}
				}

				if($val)
				{
					$keynum ++;
				}
			}

			$opportunity_arr1 = array_unique($opportunity_arr);//去除数组中重复数据，返回数组

			$opportunity_arr = array_diff_assoc($opportunity_arr, $opportunity_arr1);//取得数组中的重复数据，返回重复数组

			if(!$opportunity_arr && $opportunity_arr1)
			{
				$opportunity_arr = $opportunity_arr1;
			}

			if(count($opportunity_arr) > 0)
			{
				$field['opportunity_id'] = ['in',implode(',',$opportunity_arr)];
			}
			else
			{
				if($keynum > 0)
				{
					$field['opportunity_id'] = '-1';
				}
			}
		}
		else
		{
			if($highKeyword['customer_id']) $map['customer_id'] = $highKeyword['customer_id'];

			if($isRecover)
			{
				$memberAndGroupField = $this->memberAndGroup($company_id,'Opportunity','',$isRecover);

				if ($memberAndGroupField) $map = array_merge($memberAndGroupField, $map);
			}
			else
			{
				//选择部门和用户
				$memberAndGroupField = $this->memberAndGroup($company_id, 'Opportunity');

				if ($memberAndGroupField) $map = array_merge($memberAndGroupField, $map);
			}
			//if($highKeyword['member_id']) $map['member_id'] = $highKeyword['member_id'];

			if($highKeyword['opportunity_no'])
			{
				$map['opportunity_no'] = ["like","%".$highKeyword['opportunity_no']."%"];
			}

			if($highKeyword['start_time'])
			{
				$starttime = strtotime($highKeyword['start_time']);

				$endtime = $highKeyword['end_time'] ? strtotime($highKeyword['end_time']) : time();

				if($highKeyword['time_range_type'])
				{
					if($highKeyword['time_range_type'] == 'next_contact_time')
					{
						$map['nextcontacttime'] = [['egt',$starttime],['elt',$endtime]];
					}
					elseif($highKeyword['time_range_type'] == 'last_follow_time')
					{
						$map['lastfollowtime'] = [['egt',$starttime],['elt',$endtime]];
					}
					elseif($highKeyword['time_range_type'] == 'predict_time')
					{
						$highKeyword['define_form']['predict_time'] = [$starttime,$endtime];
					}
					else
					{
						$map['createtime'] = [['egt',$starttime],['elt',$endtime]];
					}
				}
				else
				{
					$map['createtime'] = [['egt',$starttime],['elt',$endtime]];
				}
			}

			$opportunity_arr = [];

			$keynum = 0;

			foreach($highKeyword['define_form'] as $key=>&$val)
			{
				if($val)
				{
					$opportunity_name = '';

					$this_form_type = getCrmDbModel('define_form')->where(['type'=>'opportunity','form_name'=>$key,'company_id'=>$company_id,'closed'=>0])->getField('form_type');

					if($this_form_type == 'region')
					{
						$thisval = '';

						if($highKeyword['define_form'][$key]['defaultCountry'])
						{
							$thisval = $highKeyword['define_form'][$key]['defaultCountry'];

							if($highKeyword['define_form'][$key]['defaultProv'])
							{
								$thisval .= ','.$highKeyword['define_form'][$key]['defaultProv'];
							}

							if($highKeyword['define_form'][$key]['defaultCity'])
							{
								$thisval .= ','.$highKeyword['define_form'][$key]['defaultCity'];
							}

							if($highKeyword['define_form'][$key]['defaultArea'])
							{
								$thisval .= ','.$highKeyword['define_form'][$key]['defaultArea'];
							}

							$opportunity_name = CrmgetDefineFormHighField($company_id,'opportunity',$key,$thisval);
						}

						$val = $thisval;
					}
					elseif($this_form_type == 'date')
					{
						if(is_array($val))
						{
							$opportunity_name = CrmgetDefineFormHighFieldTimeRange($company_id,'opportunity',$key,$val[0],$val[1]);
						}
						else
						{
							$opportunity_name = CrmgetDefineFormHighField($company_id,'opportunity',$key,$val);
						}
					}
					else
					{
						if($this_form_type == 'checkbox')
						{
							$val = implode(",", $val);
						}

						$opportunity_name = CrmgetDefineFormHighField($company_id,'opportunity',$key,$val);
					}

					if($opportunity_name && count($opportunity_name) >0 )
					{
						$opportunity_arr = array_merge($opportunity_arr,$opportunity_name);
					}
				}

				if($val)
				{
					$keynum ++;
				}
			}

			$opportunity_arr = array_unique($opportunity_arr);//去除数组中重复数据，返回数组

			if(count($opportunity_arr) > 0)
			{
				$map['opportunity_id'] = ['in',implode(',',$opportunity_arr)];
			}
			else
			{
				if($keynum > 0)
				{
					$map['opportunity_id'] = '-1';
				}
			}

			if($map)
			{
				$map['_logic'] = 'or';

				$field['_complex'] = $map;
			}
		}
//die;
		return ['field'=>$field];
	}

	public function contacterHighKey($company_id,$highKeyword,$isRecover = 0)
	{
		$field = $map = [];

		if($highKeyword['condition'] == 1)
		{
			if($highKeyword['customer_id']) $field['customer_id'] = $highKeyword['customer_id'];

			if($isRecover)
			{
				$memberAndGroupField = $this->memberAndGroup($company_id,'Contacter','',$isRecover);

				if ($memberAndGroupField) $field = array_merge($memberAndGroupField, $field);
			}
			else
			{
				//选择部门和用户
				$memberAndGroupField = $this->memberAndGroup($company_id, 'Contacter');

				if ($memberAndGroupField) $field = array_merge($memberAndGroupField, $field);
			}

			if($highKeyword['start_time'])
			{
				$starttime = strtotime($highKeyword['start_time']);

				$endtime = $highKeyword['end_time'] ? strtotime($highKeyword['end_time']) : time();

				$field['createtime'] = [['egt',$starttime],['elt',$endtime]];
			}

			$contacter_arr = [];

			$keynum = 0;

			foreach($highKeyword['define_form'] as $key=>&$val)
			{
				if($val)
				{
					$contacter_name = '';

					$this_form_type = getCrmDbModel('define_form')->where(['type'=>'contacter','form_name'=>$key,'company_id'=>$company_id,'closed'=>0])->getField('form_type');

					if($this_form_type == 'region')
					{
						$thisval = '';

						if($highKeyword['define_form'][$key]['defaultCountry'])
						{
							$thisval = $highKeyword['define_form'][$key]['defaultCountry'];

							if($highKeyword['define_form'][$key]['defaultProv'])
							{
								$thisval .= ','.$highKeyword['define_form'][$key]['defaultProv'];
							}

							if($highKeyword['define_form'][$key]['defaultCity'])
							{
								$thisval .= ','.$highKeyword['define_form'][$key]['defaultCity'];
							}

							if($highKeyword['define_form'][$key]['defaultArea'])
							{
								$thisval .= ','.$highKeyword['define_form'][$key]['defaultArea'];
							}

							$contacter_name = CrmgetDefineFormHighField($company_id,'contacter',$key,$thisval);
						}
						$val = $thisval;
					}
					elseif($this_form_type == 'date')
					{
						if(is_array($val))
						{
							$contacter_name = CrmgetDefineFormHighFieldTimeRange($company_id,'contacter',$key,$val[0],$val[1]);
						}
						else
						{
							$contacter_name = CrmgetDefineFormHighField($company_id,'contacter',$key,$val);
						}
					}
					else
					{
						if($this_form_type == 'checkbox')
						{
							$val = implode(",", $val);
						}
						$contacter_name = CrmgetDefineFormHighField($company_id,'contacter',$key,$val);
					}

					if($keynum > 0 && !$contacter_arr)
					{
						$contacter_arr = [];
					}
					else
					{
						if ($contacter_name && count($contacter_name) > 0)
						{
							if($keynum > 0)
							{
								$contacter_arr = array_intersect($contacter_arr, $contacter_name);
							}
							else
							{
								$contacter_arr = array_merge($contacter_arr, $contacter_name);
							}
						}
						else
						{
							if($this_form_type == 'region' && $highKeyword['define_form'][$key]['defaultCountry'])
							{
								$contacter_arr = [];
							}
							else
							{
								if($this_form_type == 'region')
								{
									$contacter_arr = $contacter_arr;
								}
								else
								{
									$contacter_arr = [];
								}
							}
						}
					}
				}
				if($val)
				{
					$keynum ++;
				}
			}

			$contacter_arr1 = array_unique($contacter_arr);//去除数组中重复数据，返回数组

			$contacter_arr = array_diff_assoc($contacter_arr, $contacter_arr1);//取得数组中的重复数据，返回重复数组

			if(!$contacter_arr && $contacter_arr1)
			{
				$contacter_arr = $contacter_arr1;
			}

			if(count($contacter_arr) > 0)
			{
				$field['contacter_id'] = ['in',implode(',',$contacter_arr)];
			}
			else
			{
				if($keynum > 0)
				{
					$field['contacter_id'] = '-1';
				}
			}
		}
		else
		{
			if($highKeyword['customer_id']) $map['customer_id'] = $highKeyword['customer_id'];

			if($isRecover)
			{
				$memberAndGroupField = $this->memberAndGroup($company_id,'Contacter','',$isRecover);

				if ($memberAndGroupField) $map = array_merge($memberAndGroupField, $map);
			}
			else
			{
				//选择部门和用户
				$memberAndGroupField = $this->memberAndGroup($company_id, 'Contacter');

				if ($memberAndGroupField) $map = array_merge($memberAndGroupField, $map);
			}

			if($highKeyword['start_time'])
			{
				$starttime = strtotime($highKeyword['start_time']);

				$endtime = $highKeyword['end_time'] ? strtotime($highKeyword['end_time']) : time();

				$map['createtime'] = [['egt',$starttime],['elt',$endtime]];
			}

			$contacter_arr = [];

			$keynum = 0;

			foreach($highKeyword['define_form'] as $key=>&$val)
			{
				if($val)
				{
					$contacter_name = '';

					$this_form_type = getCrmDbModel('define_form')->where(['type'=>'contacter','form_name'=>$key,'company_id'=>$company_id,'closed'=>0])->getField('form_type');

					if($this_form_type == 'region')
					{
						$thisval = '';

						if($highKeyword['define_form'][$key]['defaultCountry'])
						{
							$thisval = $highKeyword['define_form'][$key]['defaultCountry'];

							if($highKeyword['define_form'][$key]['defaultProv'])
							{
								$thisval .= ','.$highKeyword['define_form'][$key]['defaultProv'];
							}

							if($highKeyword['define_form'][$key]['defaultCity'])
							{
								$thisval .= ','.$highKeyword['define_form'][$key]['defaultCity'];
							}

							if($highKeyword['define_form'][$key]['defaultArea'])
							{
								$thisval .= ','.$highKeyword['define_form'][$key]['defaultArea'];
							}

							$contacter_name = CrmgetDefineFormHighField($company_id,'contacter',$key,$thisval);
						}

						$val = $thisval;
					}
					elseif($this_form_type == 'date')
					{
						if(is_array($val))
						{
							$contacter_name = CrmgetDefineFormHighFieldTimeRange($company_id,'contacter',$key,$val[0],$val[1]);
						}
						else
						{
							$contacter_name = CrmgetDefineFormHighField($company_id,'contacter',$key,$val);
						}
					}
					else
					{
						if($this_form_type == 'checkbox')
						{
							$val = implode(",", $val);
						}

						$contacter_name = CrmgetDefineFormHighField($company_id,'contacter',$key,$val);
					}

					if($contacter_name && count($contacter_name) >0 )
					{
						$contacter_arr = array_merge($contacter_arr,$contacter_name);
					}

				}
				if($val)
				{
					$keynum ++;
				}
			}

			$contacter_arr = array_unique($contacter_arr);//去除数组中重复数据，返回数组

			if(count($contacter_arr) > 0)
			{
				$map['contacter_id'] = ['in',implode(',',$contacter_arr)];
			}
			else
			{
				if($keynum > 0)
				{
					$map['contacter_id'] = '-1';
				}
			}

			if($map)
			{
				$map['_logic'] = 'or';

				$field['_complex'] = $map;
			}
		}

		return ['field'=>$field];
	}

	public function folowHighKey($company_id,$highKeyword)
	{
		$field = $map = [];

		if($highKeyword['condition'] == 1)
		{
			if($highKeyword['content']) $field['content'] = ["like","%".$highKeyword['content']."%"];

			//选择部门和用户
			$memberAndGroupField = $this->memberAndGroup($company_id,'Follow','member_id');

			if($memberAndGroupField) $field = array_merge($memberAndGroupField,$field);

			//if($highKeyword['member_id']) $field['member_id'] = $highKeyword['member_id'];

			if($highKeyword['start_time'])
			{
				$starttime = strtotime($highKeyword['start_time']);

				$endtime = $highKeyword['end_time'] ? strtotime($highKeyword['end_time']) : time();

				$field['createtime'] = [['egt',$starttime],['elt',$endtime]];
			}

			if($highKeyword['customer_id']) $field['customer_id'] = $highKeyword['customer_id'];

			if($highKeyword['clue_id']) $field['clue_id'] = $highKeyword['clue_id'];

			if($highKeyword['cmncate_id']) $field['cmncate_id'] = $highKeyword['cmncate_id'];
		}
		else
		{
			if($highKeyword['content']) $map['content'] = ["like","%".$highKeyword['content']."%"];

			//选择部门和用户
			$memberAndGroupField = $this->memberAndGroup($company_id,'Follow','member_id');

			if($memberAndGroupField) $map = array_merge($memberAndGroupField,$map);

			//if($highKeyword['member_id']) $map['member_id'] = $highKeyword['member_id'];

			if($highKeyword['start_time'])
			{
				$starttime = strtotime($highKeyword['start_time']);

				$endtime = $highKeyword['end_time'] ? strtotime($highKeyword['end_time']) : time();

				$map['createtime'] = [['egt',$starttime],['elt',$endtime]];
			}

			if($highKeyword['customer_id']) $map['customer_id'] = $highKeyword['customer_id'];

			if($highKeyword['clue_id']) $map['clue_id'] = $highKeyword['clue_id'];

			if($highKeyword['cmncate_id']) $map['cmncate_id'] = $highKeyword['cmncate_id'];

			if($map)
			{
				$map['_logic'] = 'or';

				$field['_complex'] = $map;
			}
		}

		return ['field'=>$field];
	}

	public function contractHighKey($company_id,$highKeyword,$isRecover = 0)
	{
		$field = $map = [];

		if($highKeyword['condition'] == 1)
		{
			if($highKeyword['customer_id']) $field['customer_id'] = $highKeyword['customer_id'];

			if($isRecover)
			{
				$memberAndGroupField = $this->memberAndGroup($company_id,'Contract','',$isRecover);

				if ($memberAndGroupField) $field = array_merge($memberAndGroupField, $field);
			}
			else
			{
				//选择部门和用户
				$memberAndGroupField = $this->memberAndGroup($company_id, 'Contract');

				if ($memberAndGroupField) $field = array_merge($memberAndGroupField, $field);
			}
			//if($highKeyword['member_id']) $field['member_id'] = $highKeyword['member_id'];

			if($highKeyword['contract_no'])
			{
				$field['contract_no'] = ["like","%".$highKeyword['contract_no']."%"];
			}

			if($highKeyword['start_time'])
			{
				$starttime = strtotime($highKeyword['start_time']);

				$endtime = $highKeyword['end_time'] ? strtotime($highKeyword['end_time']) : time();

				if($highKeyword['time_range_type'])
				{
					if($highKeyword['time_range_type'] == 'sign_time')
					{
						$highKeyword['define_form']['sign_time'] = [$starttime,$endtime];
					}
					elseif($highKeyword['time_range_type'] == 'start_time')
					{
						$highKeyword['define_form']['start_time'] = [$starttime,$endtime];
					}
					elseif($highKeyword['time_range_type'] == 'end_time')
					{
						$highKeyword['define_form']['end_time'] = [$starttime,$endtime];
					}
					else
					{
						$field['createtime'] = [['egt',$starttime],['elt',$endtime]];
					}
				}
				else
				{
					$field['createtime'] = [['egt',$starttime],['elt',$endtime]];
				}
			}

			$contract_arr = [];

			$keynum = 0;

			foreach($highKeyword['define_form'] as $key=>&$val)
			{
				if($val)
				{
					$contract_name = '';

					$this_form_type = getCrmDbModel('define_form')->where(['type'=>'contract','form_name'=>$key,'company_id'=>$company_id,'closed'=>0])->getField('form_type');

					if($this_form_type == 'region')
					{
						$thisval = '';

						if($highKeyword['define_form'][$key]['defaultCountry'])
						{
							$thisval = $highKeyword['define_form'][$key]['defaultCountry'];

							if($highKeyword['define_form'][$key]['defaultProv'])
							{
								$thisval .= ','.$highKeyword['define_form'][$key]['defaultProv'];
							}

							if($highKeyword['define_form'][$key]['defaultCity'])
							{
								$thisval .= ','.$highKeyword['define_form'][$key]['defaultCity'];
							}

							if($highKeyword['define_form'][$key]['defaultArea'])
							{
								$thisval .= ','.$highKeyword['define_form'][$key]['defaultArea'];
							}

							$contract_name = CrmgetDefineFormHighField($company_id,'contract',$key,$thisval);
						}

						$val = $thisval;
					}
					elseif($this_form_type == 'date')
					{
						if(is_array($val))
						{
							$contract_name = CrmgetDefineFormHighFieldTimeRange($company_id,'contract',$key,$val[0],$val[1]);
						}
						else
						{
							$contract_name = CrmgetDefineFormHighField($company_id,'contract',$key,$val);
						}
					}
					else
					{
						if($this_form_type == 'checkbox')
						{
							$val = implode(",", $val);
						}

						$contract_name = CrmgetDefineFormHighField($company_id,'contract',$key,$val);
					}

					if($keynum > 0 && !$contract_arr)
					{
						$contract_arr = [];
					}
					else
					{
						if ($contract_name && count($contract_name) > 0)
						{
							if($keynum > 0)
							{
								$contract_arr = array_intersect($contract_arr, $contract_name);
							}
							else
							{
								$contract_arr = array_merge($contract_arr, $contract_name);
							}
						}
						else
						{
							if($this_form_type == 'region' && $highKeyword['define_form'][$key]['defaultCountry'])
							{
								$contract_arr = [];
							}
							else
							{
								if($this_form_type == 'region')
								{
									$contract_arr = $contract_arr;
								}
								else
								{
									$contract_arr = [];
								}
							}
						}
					}
				}

				if($val)
				{
					$keynum ++;
				}
			}

			$contract_arr1 = array_unique($contract_arr);//去除数组中重复数据，返回数组

			$contract_arr = array_diff_assoc($contract_arr, $contract_arr1);//取得数组中的重复数据，返回重复数组

			if(!$contract_arr && $contract_arr1)
			{
				$contract_arr = $contract_arr1;
			}

			if(count($contract_arr) > 0)
			{
				$field['contract_id'] = ['in',implode(',',$contract_arr)];
			}
			else
			{
				if($keynum > 0)
				{
					$field['contract_id'] = '-1';
				}
			}
		}
		else
		{
			if($highKeyword['customer_id']) $map['customer_id'] = $highKeyword['customer_id'];

			if($isRecover)
			{
				$memberAndGroupField = $this->memberAndGroup($company_id,'Contract','',$isRecover);

				if ($memberAndGroupField) $map = array_merge($memberAndGroupField, $map);
			}
			else
			{
				//选择部门和用户
				$memberAndGroupField = $this->memberAndGroup($company_id, 'Contract');

				if ($memberAndGroupField) $map = array_merge($memberAndGroupField, $map);
			}
			//if($highKeyword['member_id']) $map['member_id'] = $highKeyword['member_id'];

			if($highKeyword['contract_no'])
			{
				$map['contract_no'] = ["like","%".$highKeyword['contract_no']."%"];
			}

			if($highKeyword['start_time'])
			{
				$starttime = strtotime($highKeyword['start_time']);

				$endtime = $highKeyword['end_time'] ? strtotime($highKeyword['end_time']) : time();

				if($highKeyword['time_range_type'])
				{
					if($highKeyword['time_range_type'] == 'sign_time')
					{
						$highKeyword['define_form']['sign_time'] = [$starttime,$endtime];
					}
					elseif($highKeyword['time_range_type'] == 'start_time')
					{
						$highKeyword['define_form']['start_time'] = [$starttime,$endtime];
					}
					elseif($highKeyword['time_range_type'] == 'end_time')
					{
						$highKeyword['define_form']['end_time'] = [$starttime,$endtime];
					}
					else
					{
						$map['createtime'] = [['egt',$starttime],['elt',$endtime]];
					}
				}
				else
				{
					$map['createtime'] = [['egt',$starttime],['elt',$endtime]];
				}
			}

			$contract_arr = [];

			$keynum = 0;

			foreach($highKeyword['define_form'] as $key=>&$val)
			{
				if($val)
				{
					$contract_name = '';

					$this_form_type = getCrmDbModel('define_form')->where(['type'=>'contract','form_name'=>$key,'company_id'=>$company_id,'closed'=>0])->getField('form_type');

					if($this_form_type == 'region')
					{
						$thisval = '';

						if($highKeyword['define_form'][$key]['defaultCountry'])
						{
							$thisval = $highKeyword['define_form'][$key]['defaultCountry'];

							if($highKeyword['define_form'][$key]['defaultProv'])
							{
								$thisval .= ','.$highKeyword['define_form'][$key]['defaultProv'];
							}

							if($highKeyword['define_form'][$key]['defaultCity'])
							{
								$thisval .= ','.$highKeyword['define_form'][$key]['defaultCity'];
							}

							if($highKeyword['define_form'][$key]['defaultArea'])
							{
								$thisval .= ','.$highKeyword['define_form'][$key]['defaultArea'];
							}

							$contract_name = CrmgetDefineFormHighField($company_id,'contract',$key,$thisval);
						}

						$val = $thisval;
					}
					elseif($this_form_type == 'date')
					{
						if(is_array($val))
						{
							$contract_name = CrmgetDefineFormHighFieldTimeRange($company_id,'contract',$key,$val[0],$val[1]);
						}
						else
						{
							$contract_name = CrmgetDefineFormHighField($company_id,'contract',$key,$val);
						}
					}
					else
					{
						if($this_form_type == 'checkbox')
						{
							$val = implode(",", $val);
						}

						$contract_name = CrmgetDefineFormHighField($company_id,'contract',$key,$val);
					}

					if($contract_name && count($contract_name) >0 )
					{
						$contract_arr = array_merge($contract_arr,$contract_name);
					}

				}

				if($val)
				{
					$keynum ++;
				}
			}

			$contract_arr = array_unique($contract_arr);//去除数组中重复数据，返回数组

			if(count($contract_arr) > 0)
			{
				$map['contract_id'] = ['in',implode(',',$contract_arr)];
			}
			else
			{
				if($keynum > 0)
				{
					$map['contract_id'] = '-1';
				}
			}

			if($map)
			{
				$map['_logic'] = 'or';

				$field['_complex'] = $map;
			}
		}
//die;
		return ['field'=>$field];
	}

	public function accountHighKey($company_id,$highKeyword,$isRecover = 0)
	{
		$field = $map = [];

		if($highKeyword['condition'] == 1)
		{
			if($isRecover)
			{
				$memberAndGroupField = $this->memberAndGroup($company_id,'Account','',$isRecover);

				if ($memberAndGroupField) $field = array_merge($memberAndGroupField, $field);
			}
			else
			{
				//选择部门和用户
				$memberAndGroupField = $this->memberAndGroup($company_id,'Account');

				if($memberAndGroupField) $field = array_merge($memberAndGroupField,$field);
			}
			//if($highKeyword['member_id']) $field['member_id'] = $highKeyword['member_id'];

			if($highKeyword['account_no'])
			{
				$field['account_no'] = ["like","%".$highKeyword['account_no']."%"];
			}

			if($highKeyword['account_time'])
			{
				$field['account_time'] = strtotime($highKeyword['account_time']);
			}

			if($highKeyword['start_time'])
			{
				$starttime = strtotime($highKeyword['start_time']);

				$endtime = $highKeyword['end_time'] ? strtotime($highKeyword['end_time']) : time();

				$field['createtime'] = [['egt',$starttime],['elt',$endtime]];
			}
		}
		else
		{
			if($isRecover)
			{
				$memberAndGroupField = $this->memberAndGroup($company_id,'Account','',$isRecover);

				if ($memberAndGroupField) $map = array_merge($memberAndGroupField, $map);
			}
			else
			{
				//选择部门和用户
				$memberAndGroupField = $this->memberAndGroup($company_id,'Account');

				if($memberAndGroupField) $map = array_merge($memberAndGroupField,$map);
			}
			//if($highKeyword['member_id']) $map['member_id'] = $highKeyword['member_id'];

			if($highKeyword['account_no'])
			{
				$map['account_no'] = ["like","%".$highKeyword['account_no']."%"];
			}

			if($highKeyword['account_time'])
			{
				$map['account_time'] = strtotime($highKeyword['account_time']);
			}

			if($highKeyword['start_time'])
			{
				$starttime = strtotime($highKeyword['start_time']);

				$endtime = $highKeyword['end_time'] ? strtotime($highKeyword['end_time']) : time();

				$map['createtime'] = [['egt',$starttime],['elt',$endtime]];
			}

			if($map)
			{
				$map['_logic'] = 'or';

				$field['_complex'] = $map;
			}
		}

		return ['field'=>$field];
	}

	public function receiptHighKey($company_id,$highKeyword,$isRecover = 0)
	{
		$field = $map = [];

		if($highKeyword['condition'] == 1)
		{
			if($isRecover)
			{
				$memberAndGroupField = $this->memberAndGroup($company_id,'Receipt','',$isRecover);

				if ($memberAndGroupField) $field = array_merge($memberAndGroupField, $field);
			}
			else
			{
				//选择部门和用户
				$memberAndGroupField = $this->memberAndGroup($company_id, 'Receipt');

				if ($memberAndGroupField) $field = array_merge($memberAndGroupField, $field);
			}

			if($highKeyword['customer_id']) $field['customer_id'] = $highKeyword['customer_id'];

			if($highKeyword['contract_id']) $field['contract_id'] = $highKeyword['contract_id'];

			if($highKeyword['receipt_no'])
			{
				$field['receipt_no'] = ["like","%".$highKeyword['receipt_no']."%"];
			}

			if($highKeyword['receipt_type']) $field['receipt_type'] = $highKeyword['receipt_type'];

			if($highKeyword['receipt_time'])
			{
				$field['receipt_time'] = strtotime($highKeyword['receipt_time']);
			}

			if($highKeyword['status']) $field['status'] = $highKeyword['status'];

			if($highKeyword['examine_id']) $field['_string'] = 'FIND_IN_SET('.$highKeyword['examine_id'].',examine_id)';

			if($highKeyword['creater_id']) $field['creater_id'] = $highKeyword['creater_id'];

			if($highKeyword['start_time'])
			{
				$starttime = strtotime($highKeyword['start_time']);

				$endtime = $highKeyword['end_time'] ? strtotime($highKeyword['end_time']) : time();

				$field['createtime'] = [['egt',$starttime],['elt',$endtime]];
			}
		}
		else
		{
			if($isRecover)
			{
				$memberAndGroupField = $this->memberAndGroup($company_id,'Receipt','',$isRecover);

				if ($memberAndGroupField) $map = array_merge($memberAndGroupField, $map);
			}
			else
			{
				//选择部门和用户
				$memberAndGroupField = $this->memberAndGroup($company_id, 'Receipt');

				if ($memberAndGroupField) $map = array_merge($memberAndGroupField, $map);
			}

			if($highKeyword['customer_id']) $map['customer_id'] = $highKeyword['customer_id'];

			if($highKeyword['contract_id']) $map['contract_id'] = $highKeyword['contract_id'];

			if($highKeyword['receipt_no'])
			{
				$map['receipt_no'] = ["like","%".$highKeyword['receipt_no']."%"];
			}

			if($highKeyword['receipt_type']) $map['receipt_type'] = $highKeyword['receipt_type'];

			if($highKeyword['receipt_time'])
			{
				$map['receipt_time'] = strtotime($highKeyword['receipt_time']);
			}

			if($highKeyword['status']) $map['status'] = $highKeyword['status'];

			if($highKeyword['examine_id']) $map['_string'] = 'FIND_IN_SET('.$highKeyword['examine_id'].',examine_id)';

			if($highKeyword['creater_id']) $map['creater_id'] = $highKeyword['creater_id'];

			if($highKeyword['start_time'])
			{
				$starttime = strtotime($highKeyword['start_time']);

				$endtime = $highKeyword['end_time'] ? strtotime($highKeyword['end_time']) : time();

				$map['createtime'] = [['egt',$starttime],['elt',$endtime]];
			}

			if($map)
			{
				$map['_logic'] = 'or';

				$field['_complex'] = $map;
			}
		}

		return ['field'=>$field];
	}

	public function invoiceHighKey($company_id,$highKeyword,$isRecover = 0)
	{
		$field = $map = [];

		if($highKeyword['condition'] == 1)
		{
			if($isRecover)
			{
				$memberAndGroupField = $this->memberAndGroup($company_id,'Invoice','',$isRecover);

				if ($memberAndGroupField) $field = array_merge($memberAndGroupField, $field);
			}
			else
			{
				//选择部门和用户
				$memberAndGroupField = $this->memberAndGroup($company_id, 'Invoice');

				if ($memberAndGroupField) $field = array_merge($memberAndGroupField, $field);
			}

			if($highKeyword['customer_id']) $field['customer_id'] = $highKeyword['customer_id'];

			if($highKeyword['contract_id']) $field['contract_id'] = $highKeyword['contract_id'];

			if($highKeyword['invoice_no'])
			{
				$field['invoice_no'] = ["like","%".$highKeyword['invoice_no']."%"];
			}

			if($highKeyword['invoice_type']) $field['invoice_type'] = $highKeyword['invoice_type'];

			if($highKeyword['invoice_time'])
			{
				$field['invoice_time'] = strtotime($highKeyword['invoice_time']);
			}

			if($highKeyword['status']) $field['status'] = $highKeyword['status'];

			if($highKeyword['examine_id']) $field['_string'] = 'FIND_IN_SET('.$highKeyword['examine_id'].',examine_id)';

			if($highKeyword['creater_id']) $field['creater_id'] = $highKeyword['creater_id'];

			if($highKeyword['start_time'])
			{
				$starttime = strtotime($highKeyword['start_time']);

				$endtime = $highKeyword['end_time'] ? strtotime($highKeyword['end_time']) : time();

				$field['createtime'] = [['egt',$starttime],['elt',$endtime]];
			}
		}
		else
		{
			if($isRecover)
			{
				$memberAndGroupField = $this->memberAndGroup($company_id,'Invoice','',$isRecover);

				if ($memberAndGroupField) $map = array_merge($memberAndGroupField, $map);
			}
			else
			{
				//选择部门和用户
				$memberAndGroupField = $this->memberAndGroup($company_id, 'Invoice');

				if ($memberAndGroupField) $map = array_merge($memberAndGroupField, $map);
			}

			if($highKeyword['customer_id']) $map['customer_id'] = $highKeyword['customer_id'];

			if($highKeyword['contract_id']) $map['contract_id'] = $highKeyword['contract_id'];

			if($highKeyword['invoice_no'])
			{
				$map['invoice_no'] = ["like","%".$highKeyword['invoice_no']."%"];
			}

			if($highKeyword['invoice_type']) $map['invoice_type'] = $highKeyword['invoice_type'];

			if($highKeyword['invoice_time'])
			{
				$map['invoice_time'] = strtotime($highKeyword['invoice_time']);
			}

			if($highKeyword['status']) $map['status'] = $highKeyword['status'];

			if($highKeyword['examine_id']) $map['_string'] = 'FIND_IN_SET('.$highKeyword['examine_id'].',examine_id)';

			if($highKeyword['creater_id']) $map['creater_id'] = $highKeyword['creater_id'];

			if($highKeyword['start_time'])
			{
				$starttime = strtotime($highKeyword['start_time']);

				$endtime = $highKeyword['end_time'] ? strtotime($highKeyword['end_time']) : time();

				$map['createtime'] = [['egt',$starttime],['elt',$endtime]];
			}

			if($map)
			{
				$map['_logic'] = 'or';

				$field['_complex'] = $map;
			}
		}

		return ['field'=>$field];
	}

	public function productHighKey($company_id,$highKeyword)
	{
		if($highKeyword['condition'] == 1)
		{
			$product_arr = [];

			$keynum = 0;

			if($highKeyword['start_time'])
			{
				$starttime = strtotime($highKeyword['start_time']);

				$endtime = $highKeyword['end_time'] ? strtotime($highKeyword['end_time']) : time();

				$field['createtime'] = [['egt',$starttime],['elt',$endtime]];
			}

			foreach($highKeyword['define_form'] as $key=>&$val)
			{
				if($val)
				{
					$product_name = '';

					$this_form_type = getCrmDbModel('define_form')->where(['type'=>'product','form_name'=>$key,'company_id'=>$company_id,'closed'=>0])->getField('form_type');

					if($this_form_type == 'region')
					{
						$thisval = '';

						if($highKeyword['define_form'][$key]['defaultCountry'])
						{
							$thisval = $highKeyword['define_form'][$key]['defaultCountry'];

							if($highKeyword['define_form'][$key]['defaultProv'])
							{
								$thisval .= ','.$highKeyword['define_form'][$key]['defaultProv'];
							}

							if($highKeyword['define_form'][$key]['defaultCity'])
							{
								$thisval .= ','.$highKeyword['define_form'][$key]['defaultCity'];
							}

							if($highKeyword['define_form'][$key]['defaultArea'])
							{
								$thisval .= ','.$highKeyword['define_form'][$key]['defaultArea'];
							}

							$product_name = CrmgetDefineFormHighField($company_id,'product',$key,$thisval);
						}
						$val = $thisval;
					}
					elseif($this_form_type == 'date')
					{
						if(is_array($val))
						{
							$product_name = CrmgetDefineFormHighFieldTimeRange($company_id,'product',$key,$val[0],$val[1]);
						}
						else
						{
							$product_name = CrmgetDefineFormHighField($company_id,'product',$key,$val);
						}
					}
					else
					{
						if($this_form_type == 'checkbox')
						{
							$val = implode(",", $val);
						}

						$product_name = CrmgetDefineFormHighField($company_id,'product',$key,$val);
					}

					if($keynum > 0 && !$product_arr)
					{
						$product_arr = [];
					}
					else
					{
						if ($product_name && count($product_name) > 0)
						{
							if($keynum > 0)
							{
								$product_arr = array_intersect($product_arr, $product_name);
							}
							else
							{
								$product_arr = array_merge($product_arr, $product_name);
							}
						}
						else
						{
							if($this_form_type == 'region' && $highKeyword['define_form'][$key]['defaultCountry'])
							{
								$product_arr = [];
							}
							else
							{
								if($this_form_type == 'region')
								{
									$product_arr = $product_arr;
								}
								else
								{
									$product_arr = [];
								}
							}
						}
					}
				}
				if($val)
				{
					$keynum ++;
				}
			}

			$product_arr1 = array_unique($product_arr);//去除数组中重复数据，返回数组

			$product_arr = array_diff_assoc($product_arr, $product_arr1);//取得数组中的重复数据，返回重复数组

			if(!$product_arr && $product_arr1)
			{
				$product_arr = $product_arr1;
			}

			if(count($product_arr) > 0)
			{
				$field['product_id'] = ['in',implode(',',$product_arr)];
			}
			else
			{
				if($keynum > 0)
				{
					$field['product_id'] = '-1';
				}
			}
		}
		else
		{
			$product_arr = [];

			$keynum = 0;

			if($highKeyword['start_time'])
			{
				$starttime = strtotime($highKeyword['start_time']);

				$endtime = $highKeyword['end_time'] ? strtotime($highKeyword['end_time']) : time();

				$map['createtime'] = [['egt',$starttime],['elt',$endtime]];
			}

			foreach($highKeyword['define_form'] as $key=>&$val)
			{
				if($val)
				{
					$product_name = '';

					$this_form_type = getCrmDbModel('define_form')->where(['type'=>'product','form_name'=>$key,'company_id'=>$company_id,'closed'=>0])->getField('form_type');

					if($this_form_type == 'region')
					{
						$thisval = '';

						if($highKeyword['define_form'][$key]['defaultCountry'])
						{
							$thisval = $highKeyword['define_form'][$key]['defaultCountry'];

							if($highKeyword['define_form'][$key]['defaultProv'])
							{
								$thisval .= ','.$highKeyword['define_form'][$key]['defaultProv'];
							}

							if($highKeyword['define_form'][$key]['defaultCity'])
							{
								$thisval .= ','.$highKeyword['define_form'][$key]['defaultCity'];
							}

							if($highKeyword['define_form'][$key]['defaultArea'])
							{
								$thisval .= ','.$highKeyword['define_form'][$key]['defaultArea'];
							}

							$product_name = CrmgetDefineFormHighField($company_id,'product',$key,$thisval);
						}
						$val = $thisval;
					}
					elseif($this_form_type == 'date')
					{
						if(is_array($val))
						{
							$product_name = CrmgetDefineFormHighFieldTimeRange($company_id,'product',$key,$val[0],$val[1]);
						}
						else
						{
							$product_name = CrmgetDefineFormHighField($company_id,'product',$key,$val);
						}
					}
					else
					{
						if($this_form_type == 'checkbox')
						{
							$val = implode(",", $val);
						}

						$product_name = CrmgetDefineFormHighField($company_id,'product',$key,$val);
					}

					if($product_name && count($product_name) >0 )
					{
						$product_arr = array_merge($product_arr,$product_name);
					}
				}

				if($val)
				{
					$keynum ++;
				}
			}

			$product_arr = array_unique($product_arr);//去除数组中重复数据，返回数组

			if(count($product_arr) > 0)
			{
				$map['product_id'] = ['in',implode(',',$product_arr)];
			}
			else
			{
				if($keynum > 0)
				{
					$map['product_id'] = '-1';
				}
			}

			if($map)
			{
				$map['_logic'] = 'or';

				$field['_complex'] = $map;
			}
		}

		return ['field'=>$field];
	}

	public function getSortBy()
	{
		if($sort_by = I('get.sort_by'))
		{
			switch ($sort_by)
			{
				case 'contacttime-asc':
					$order = 'nextcontacttime asc';
					break;

				case 'contacttime-desc':
					$order = 'nextcontacttime desc';
					break;

				case 'followtime-asc':
					$order = 'lastfollowtime asc';
					break;

				case 'followtime-desc':
					$order = 'lastfollowtime desc';
					break;

				case 'createtime-asc':
					$order = 'createtime asc';
					break;

				case 'createtime-desc':
					$order = 'createtime desc';
					break;
			}
		}
		else
		{
			$sort_by = 'followtime-desc';

			$order = 'lastfollowtime desc';
		}

		return ['sort_by'=>$sort_by,'order'=>$order];
	}

	public function getFieldSqlByTime($type,$field,$value,$company_id)
	{
		switch ($value)
		{
			//昨天
			case 'yesterday':
				$startTime = date('Y-m-d',strtotime("-1 day"));
				$endTime = date('Y-m-d',strtotime("-1 day")).' 23:59:59';
				break;

			//今天
			case 'today':
				$startTime = date('Y-m-d',time());
				$endTime = date('Y-m-d',time()).' 23:59:59';
				break;

			//上周
			case 'last-week':
				$startTime = date('Y-m-d', strtotime("last week Monday", time()));
				$endTime = date('Y-m-d H:i:s', (strtotime(date('Y-m-d', strtotime("last week Sunday", time()))) + 24 * 3600 - 1));
				break;

			//本周
			case 'this-week':
				$startTime = date('Y-m-d H:i:s', strtotime("this week Monday", time()));
				$endTime = date('Y-m-d H:i:s', (strtotime(date('Y-m-d H:i:s', strtotime("this week Sunday", time()))) + 24 * 3600 - 1));
				break;

			//上月
			case 'prev-month':
				$startTime = date('Y-m-d H:i:s', mktime(0, 0, 0, date('m') - 1, 1, date('Y')));
				$endTime = date('Y-m-d H:i:s', mktime(23, 59, 59, date('m') - 1, date('t', $startTime), date('Y')));
				break;

			//本月
			case 'this-month':
				$startTime = date('Y-m-01 0:0:0', strtotime(date("Y-m-d")));
				$endTime = date('Y-m-d 23:59:59', strtotime($startTime."+1 month -1 day"));
				break;

			//本季
			case 'this-season':
				$season = ceil((date('n'))/3);
				$startTime = date('Y-m-d H:i:s', mktime(0,0,0,$season*3-3+1,1,date('Y')));
				$endTime = date('Y-m-d H:i:s', mktime(23,59,59,$season*3,date('t',mktime(0,0,0,$season*3,1,date("Y"))),date('Y')));
				break;

			//本年
			case 'this-year':
				$startTime = date('Y-m-d H:i:s', mktime(0, 0, 0, 1, 1, date('Y')));
				$endTime = date('Y-m-d H:i:s', mktime(23, 59, 59, 12, 31, date('Y')));
				break;

			//自定义时间段
			default:
				$time = explode(' - ',$value);
				$startTime = $time[0].' 00:00:00';
				$endTime = $time[1].' 23:59:59';
		}

		//筛选所选时间段的所有数据
		$time_form = getCrmDbModel('define_form')
			->where(['company_id'=>$company_id,'type'=>$type,'form_name'=>$field])
			->field('form_id,form_type')->find();

		$form_content = getCrmDbModel($type.'_detail')->where(['company_id'=>$company_id,'form_id'=>$time_form['form_id'],'form_content'=>['between',[$startTime,$endTime]]])->field($type.'_id,form_content')->select();

		if($form_content)
		{
			$data_arr = array_column($form_content,$type.'_id');

			$time_sql = $type.'_id in ('.implode(',',$data_arr).')';
		}else
		{
			$time_sql = $type.'_id = 0';
		}

		return $time_sql;
	}
}
