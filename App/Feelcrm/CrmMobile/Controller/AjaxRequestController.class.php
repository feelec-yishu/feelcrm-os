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

namespace CrmMobile\Controller;

use Think\Controller;

class AjaxRequestController extends Controller
{

	protected $member,$_company_id;

    public function _initialize()
    {
        $this->member = session('mobile');

        $this->_company_id = session('company_id');
    }

	public function getCustomerList() //动态获取客户
	{
		$field['company_id'] = $this->_company_id;

		$field['isvalid'] = 1;

		if($keyword = I('get.keyword'))
		{
			$keywordField = CrmgetDefineFormField($this->_company_id,'customer','name,phone,email',$keyword);

			$contacterField = CrmgetDefineFormField($this->_company_id,'contacter','name,phone,email',$keyword);

			$keywordCondition['customer_id'] = $keywordField ? ['in',$keywordField] : '0';

			$contacterField = checkContacterSqlField($this->_company_id,$contacterField);

			$keywordCondition['first_contact_id'] = $contacterField ? ['in',$contacterField] : '-1';

			$keywordCondition['_logic'] = "or";

			$field['_complex']=$keywordCondition;

			$this->assign('keyword', $keyword);
		}

		$getCustomerAuth = D('CrmSelectField')->getCustomerAuth($this->member,$this->_company_id,$this->member['member_id']);

		$memberRoleArr = $getCustomerAuth['memberRoleArr'];

		$field['member_id'] = $memberRoleArr;

		$selectCustomerCount =  getCrmDbModel('Customer')->where($field)->count();

		$selectCustomerPage = new \Think\Page($selectCustomerCount, 10);

		$selectCustomerlist =  getCrmDbModel('Customer')->where($field)->field('customer_id,customer_prefix,customer_no,first_contact_id,createtime')->limit($selectCustomerPage->firstRow, $selectCustomerPage->listRows)->order('createtime desc')->select();

		$html = '';

		foreach($selectCustomerlist as $key => &$val)
		{
			$val['detail'] = CrmgetCrmDetailList('customer',$val['customer_id'],$this->_company_id,'name');

			$val['contacter'] = CrmgetCrmDetailList('contacter',$val['first_contact_id'],$this->_company_id,'name,phone');
		}

		$this->ajaxReturn(['data'=>$selectCustomerlist,'pages'=>ceil($selectCustomerCount/10)]);

	}

	//获取客户所属联系人
	public function getContacter()
	{
		$customer_id = I('post.customer_id');

		$first_contact_id = getCrmDbModel('customer')->where(['company_id'=>$this>_company_id,'customer_id'=>$customer_id,'isvalid'=>1])->getField('first_contact_id');

		$contacter = getCrmDbModel('contacter')->where(['company_id'=>$this->_company_id,'customer_id'=>$customer_id,'isvalid'=>1])->select();

		$html = '<option value="">'.L("SELECT_CONTACT").'</option>';

		foreach($contacter as $key=>$val)
		{
			$contacter_detail = CrmgetCrmDetailList('contacter',$val['contacter_id'],$this->_company_id,'name');

			if($first_contact_id == $val['contacter_id'])
			{
				$html .= '<option value="'.$val['contacter_id'].'" selected>'.$contacter_detail['name'].'</option>';
			}
			else
			{
				$html .= '<option value="'.$val['contacter_id'].'">'.$contacter_detail['name'].'</option>';
			}

		}

		$this->ajaxReturn(['errcode'=>0,'msg'=>'ok','html'=>$html]);
	}

	//获取客户所属商机
	public function getOpportunity()
	{
		$customer_id = I('post.customer_id');

		$opportunity = getCrmDbModel('Opportunity')->where(['company_id'=>$this->_company_id,'customer_id'=>$customer_id,'isvalid'=>1])->select();

		$html = '<option value="">'.L("PLEASE_SELECT_OPPORTUNITY").'</option>';

		foreach($opportunity as $key=>$val)
		{
			$opportunity_detail = CrmgetCrmDetailList('opportunity',$val['opportunity_id'],$this->_company_id,'name');

			$html .= '<option value="'.$val['opportunity_id'].'">'.$opportunity_detail['name'].'</option>';
		}

		$this->ajaxReturn(['errcode'=>0,'msg'=>'ok','html'=>$html]);
	}

	//选择商机后的操作
	public function selectOpportunity()
	{
		$opportunity_id = I('post.opportunity_id');

		$opportunity_detail = CrmgetCrmDetailList('opportunity',$opportunity_id,$this->_company_id,'name,budget');

		$productfield = ['company_id'=>$this->_company_id,'opportunity_id'=>$opportunity_id];

		$product = getCrmDbModel('opportunity_product')->where($productfield)->select();

		$product_arr = array_column($product,'product_id');

		$product_html = '';

		$input_html = '';

		foreach($product as $key=>&$val)
		{
			$detail = CrmgetCrmDetailList('product',$val['product_id'],$this->_company_id);

			$product_html .= '<span data-id="'.$val['product_id'].'">'.$detail['name'].' </span>';

			$input_html .= '<input type="hidden" name="orderPro[]" value="'.$val['product_id'].'">';
		}

		$this->ajaxReturn(['errcode'=>0,'msg'=>'ok','opportunity'=>$opportunity_detail,'product'=>$product_arr,'product_html'=>$product_html,'input_html'=>$input_html]);
	}

	public function getProductList($id = '',$p = 1) //动态获取产品
	{
		$type_id = decrypt($id,'PRODUCT');

		$prolink = U('AjaxRequest/getProductList',['id'=>encrypt($type_id,'PRODUCT')]);

		$orderPro = I('post.orderPro');

		if($type_id)
		{
			$type_array = $this->getTypeid($type_id,1);

			$type_array = implode(',',$type_array);

			$field['company_id'] = $this->_company_id;

			$field['isvalid'] = '1';

			$field['type_id'] = array('in',$type_array);

			$field['closed'] = 0;
		}
		else
		{
			$field['company_id'] = $this->_company_id;

			$field['isvalid'] = '1';

			$field['closed'] = 0;
		}

		$count = getCrmDbModel('product')->where($field)->count();

		$Page = new \Think\Page($count, 10);

		if(strlen($Page->show()) > 16) $this->assign('page', $Page->show()); // 赋值分页输出

		$product = getCrmDbModel('product')->where($field)->limit($Page->firstRow, $Page->listRows)->select();

		//var_dump(count($product));die;
		if(count($product) < 10)
		{
			$p = -1;
		}
		else
		{
			$p ++;

			$prolink .= '/p/'.$p;

			$nextpage = '<div class="layui-flow-more" data-href="'.$prolink.'"><a href="javascript:;"><cite><i class="iconfont icon-xiangxiajiantou" ></i></cite></a></div>';
		}

		$html = '';

		if($product)
		{
			$product_type_field = getCrmLanguageData('type_name');
			
			foreach($product as $key=>&$val)
			{
				$product_detail = getCrmDbModel('product_detail')->where(['company_id'=>$this->_company_id,'product_id'=>$val['product_id']])->select();

				foreach($product_detail as $k1=>&$v1)
				{
					$form_name = getCrmDbModel('define_form')->where(['company_id'=>$this->_company_id,'form_id'=>$v1['form_id'],'type'=>'product'])->field('form_name')->find();

					$val[$form_name['form_name']] = $v1['form_content'];
				}

				$product_type = getCrmDbModel('product_type')->field(['*',$product_type_field])->where(['company_id'=>$this->_company_id,'type_id'=>$val['type_id']])->find();

				$val['type_name'] = $product_type['type_name'];

				$html .= '<li class="product-info" data-value="'.$val['product_id'].'" data-name="'.$val['name'].'">';

				if($val['product_img'])
				{
					$html .= '<img src="'.$val['product_img'].'" alt="">';
				}
				else
				{
					$html .= '<img src="/Attachs/face/face.png" alt="">';
				}

				$html .= '<div class="product-name">'.$val['name'].'</div>';

				if (in_array($val['product_id'], $orderPro))
				{
					$html .= '<span class="iconfont icon-check icon-checkbox-checked"></span>';
				}
				else
				{
					$html .= '<span class="iconfont icon-check"></span>';
				}

				$html .= '</li>';
			}

		}else
		{
			$html = '<div class="layui-flow-more">'.L("NO_MORE").'</div>';
		}

		$this->ajaxReturn(['errcode'=>0,'msg'=>'ok','html'=>$html,'page'=>$nextpage,'p'=>$p]);

	}

	public function getTypeid($type_id,$isfirst = 0)
	{
		//$type_array[] = $type_id;

		$isfirst != 1 || $type_array[] = $type_id;

		$product_type = getCrmDbModel('product_type')->where(['company_id'=>$this->_company_id,'closed'=>0,'parent_id'=>$type_id])->select();

		foreach($product_type as $key=>$val)
		{
			$type_array[] .= $val['type_id'];

			$count = getCrmDbModel('product_type')->where(['company_id'=>$this->_company_id,'closed'=>0,'parent_id'=>$val['type_id']])->count();

			if($count)
			{
				$type_array = array_merge($type_array,$this->getTypeid($val['type_id']));
			}
		}

		return $type_array;
	}

	//获取沟通类型的自定义回复列表
	public function getCmncateReply()
	{
		$cmncate_id = I('post.cmncate_id');

		$reply_field = getCrmLanguageData('reply_content');

		$cmncate_reply = getCrmDbModel('communicate_reply')->field(['*',$reply_field])->where(['company_id'=>$this->_company_id,'cmncate_id'=>$cmncate_id,'closed'=>0])->select();

		$html = '<option value="">'.L('CUSTOM_REPLY').'</option>';

		foreach($cmncate_reply as $key=>$val)
		{
			$html .= '<option value="'.$val['reply_id'].'">'.$val['reply_content'].'</option>';
		}

		$this->ajaxReturn(['errcode'=>0,'msg'=>'ok','html'=>$html]);
	}

	//获取地区
	public function getRegion()
	{
		if(IS_POST)
        {
            $data = I('post.');

			//var_dump($data);die;

            if($data['type'])
            {
                if($data['type'] == 'province')
                {
                    $where = ['country_code'=>$data['country_id']];

	                $province_name = getCrmLanguageData('name');

                    $province = getCrmDbModel('province')->field(['*',$province_name])->where($where)->select();

                    $this->ajaxReturn(['code'=>0,'data'=>$province]);
                }

                if($data['type'] == 'city')
                {
                    $where = ['country_code'=>$data['country_id'],'province_code'=>$data['province_id']];

	                $city_name = getCrmLanguageData('name');

                    $city = getCrmDbModel('city')->field(['*',$city_name])->where($where)->select();

                    $this->ajaxReturn(['code'=>0,'data'=>$city]);
                }

				if($data['type'] == 'area')
                {
	                $where = ['country_code'=>$data['country_id'],'province_code'=>$data['province_id'],'city_code'=>$data['city_id']];

	                $area_name = getCrmLanguageData('name');

                    $area = getCrmDbModel('area')->field(['*',$area_name])->where($where)->select();

                    $this->ajaxReturn(['code'=>0,'data'=>$area]);
                }
            }
		}
	}

	public function getMember()
	{
		if(IS_POST)
        {
			$count = M('member')->where(['company_id'=>$this->_company_id,'type'=>1,'closed'=>0,'feelec_opened'=>10])->count();

			$pages = $count/8;

			$Page = new \Think\Page($count, 8);

			if(strlen($Page->show()) > 16) $this->assign('page', $Page->show()); // 赋值分页输出

			$member = M('member')->where(['company_id'=>$this->_company_id,'type'=>1,'closed'=>0,'feelec_opened'=>10])->limit($Page->firstRow, $Page->listRows)->select();

            $this->ajaxReturn(['data'=>$member,'pages'=>$pages]);
        }
	}

	//获取线索自定义筛选内容
	public function clueScreen()
	{
		$Selectedscreen = I('post.Selectedscreen');

		$SelectedscreenFixed = I('post.SelectedscreenFixed');

		if($Selectedscreen && $SelectedscreenFixed)
		{
			$countSelected = count($Selectedscreen) + count($SelectedscreenFixed);
		}else
		{
			if($Selectedscreen)
			{
				$countSelected = count($Selectedscreen);
			}
			elseif($SelectedscreenFixed)
			{
				$countSelected = count($SelectedscreenFixed);
			}
			else
			{
				$countSelected = 0;
			}
		}

		if(session('CluehighKeyword'))
		{
			$highKeyword = session('CluehighKeyword');
		}

		$screen = I('post.screen');

		$screenFixed = I('post.screenFixed');

		$htmlL = '';

		$htmlR = '';
		//var_dump($countSelected);die;
		if($countSelected > 0)
		{
			$k = $countSelected + 4;
		}
		else
		{
			$k = 4;
		}

		foreach($screenFixed as $key=>$val)
		{
			switch ($val)
			{
				case '1':

					$titlename = L('CLUE_NO');

					if($highKeyword['clue_no'])
					{
						$htmlR .= '<div class="fl feelcrm-screen-list-R feelcrmScreen SelectedscreenFixed'.$val.' hidden" id="screen-layer'.$k.'"><div class="feelcrm-screen-info "><div class="feelcrm-screen-infoname" >'.L("UNLIMITED").'<i class="iconfont icon-gouxuan"></i></div></div><div class="feelcrm-screen-info feelcrm-screen-infoactive"><div class="feelcrm-screen-infoname" ><input type="text" name="highKeyword[clue_no]" value="'.$highKeyword['clue_no'].'" class="feelcrm-screen-input" placeholder="'.L('ENTER_CLUE_NUMBER').'" /><i class="iconfont icon-gouxuan"></i></div></div></div>';
					}
					else
					{
						$htmlR .= '<div class="fl feelcrm-screen-list-R feelcrmScreen SelectedscreenFixed'.$val.' hidden" id="screen-layer'.$k.'"><div class="feelcrm-screen-info feelcrm-screen-infoactive"><div class="feelcrm-screen-infoname" >'.L("UNLIMITED").'<i class="iconfont icon-gouxuan"></i></div></div><div class="feelcrm-screen-info "><div class="feelcrm-screen-infoname" ><input type="text" name="highKeyword[clue_no]" value="'.$highKeyword['clue_no'].'" class="feelcrm-screen-input" placeholder="'.L('ENTER_CLUE_NUMBER').'" /><i class="iconfont icon-gouxuan"></i></div></div></div>';
					}

					break;

				case '2':

					$titlename = L('STATUS');

					if($highKeyword['status'] == 1)
					{
						$htmlR .= '<div class="fl feelcrm-screen-list-R feelcrmScreen SelectedscreenFixed'.$val.' hidden" id="screen-layer'.$k.'"><div class="feelcrm-screen-info "><div class="feelcrm-screen-infoname" >'.L("UNLIMITED").'<i class="iconfont icon-gouxuan"></i></div></div><div class="feelcrm-screen-info" data-value="-1"><div class="feelcrm-screen-infoname" >'.L('NOT_FOLLOWED_UP').'<i class="iconfont icon-gouxuan"></i></div></div><div class="feelcrm-screen-info feelcrm-screen-infoactive" data-value="1"><div class="feelcrm-screen-infoname" >'.L('FOLLOWING_UP').'<i class="iconfont icon-gouxuan"></i></div></div><div class="feelcrm-screen-info " data-value="2"><div class="feelcrm-screen-infoname" >'.L("CONVERTED").'<i class="iconfont icon-gouxuan"></i></div></div><input type="hidden" name="highKeyword[status]" value="'.$highKeyword['status'].'" /></div>';
					}
					elseif($highKeyword['status'] == 2)
					{
						$htmlR .= '<div class="fl feelcrm-screen-list-R feelcrmScreen SelectedscreenFixed'.$val.' hidden" id="screen-layer'.$k.'"><div class="feelcrm-screen-info "><div class="feelcrm-screen-infoname" >'.L("UNLIMITED").'<i class="iconfont icon-gouxuan"></i></div></div><div class="feelcrm-screen-info" data-value="-1"><div class="feelcrm-screen-infoname" >'.L('NOT_FOLLOWED_UP').'<i class="iconfont icon-gouxuan"></i></div></div><div class="feelcrm-screen-info " data-value="1"><div class="feelcrm-screen-infoname" >'.L('FOLLOWING_UP').'<i class="iconfont icon-gouxuan"></i></div></div><div class="feelcrm-screen-info feelcrm-screen-infoactive" data-value="2"><div class="feelcrm-screen-infoname" >'.L("CONVERTED").'<i class="iconfont icon-gouxuan"></i></div></div><input type="hidden" name="highKeyword[status]" value="'.$highKeyword['status'].'" /></div>';
					}
					elseif($highKeyword['status'] == -1)
					{
						$htmlR .= '<div class="fl feelcrm-screen-list-R feelcrmScreen SelectedscreenFixed'.$val.' hidden" id="screen-layer'.$k.'"><div class="feelcrm-screen-info "><div class="feelcrm-screen-infoname" >'.L("UNLIMITED").'<i class="iconfont icon-gouxuan"></i></div></div><div class="feelcrm-screen-info feelcrm-screen-infoactive" data-value="-1"><div class="feelcrm-screen-infoname" >'.L('NOT_FOLLOWED_UP').'<i class="iconfont icon-gouxuan"></i></div></div><div class="feelcrm-screen-info " data-value="1"><div class="feelcrm-screen-infoname" >'.L('FOLLOWING_UP').'<i class="iconfont icon-gouxuan"></i></div></div><div class="feelcrm-screen-info" data-value="2"><div class="feelcrm-screen-infoname" >'.L("CONVERTED").'<i class="iconfont icon-gouxuan"></i></div></div><input type="hidden" name="highKeyword[status]" value="'.$highKeyword['status'].'" /></div>';
					}
					else
					{
						$htmlR .= '<div class="fl feelcrm-screen-list-R feelcrmScreen SelectedscreenFixed'.$val.' hidden" id="screen-layer'.$k.'"><div class="feelcrm-screen-info feelcrm-screen-infoactive"><div class="feelcrm-screen-infoname" >'.L("UNLIMITED").'<i class="iconfont icon-gouxuan"></i></div></div><div class="feelcrm-screen-info" data-value="-1"><div class="feelcrm-screen-infoname" >'.L('NOT_FOLLOWED_UP').'<i class="iconfont icon-gouxuan"></i></div></div><div class="feelcrm-screen-info " data-value="1"><div class="feelcrm-screen-infoname" >'.L('FOLLOWING_UP').'<i class="iconfont icon-gouxuan"></i></div></div><div class="feelcrm-screen-info " data-value="2"><div class="feelcrm-screen-infoname" >'.L("CONVERTED").'<i class="iconfont icon-gouxuan"></i></div></div><input type="hidden" name="highKeyword[status]" value="'.$highKeyword['status'].'" /></div>';
					}

					break;
			}
			$htmlL .= '<div onclick="switchScreen(this,'.$k.')" class="feelcrm-screen-title SelectedscreenFixed'.$val.'"><div class="feelcrm-screen-titlename">'.$titlename.'</div></div><input type="hidden" name="SelectedscreenFixed[]" value="'.$val.'" />';

			$k ++;
		}

		$form_description = getCrmLanguageData('form_description');

		foreach($screen as $key=>$val)
		{
			$defineform = getCrmDbModel('define_form')
				->field(['*',$form_description])
				->where(['company_id'=>$this->_company_id,'closed' => 0,'type'=>'clue','form_type'=>['not in',['region','textarea']],'form_id'=>$val])
				->order('orderby asc')
				->find();

			$htmlL .= '<div onclick="switchScreen(this,'.$k.')" class="feelcrm-screen-title Selectedscreen'.$val.'"><div class="feelcrm-screen-titlename">'.$defineform['form_description'].'</div></div><input type="hidden" name="Selectedscreen[]" value="'.$val.'" />';

			$htmlcontent = '';

			if(in_array($defineform['form_type'],['radio','select','checkbox','select_text']))
			{
				$screen_list = explode('|',$defineform['form_option']);

				if($defineform['form_type'] == 'checkbox')
				{
					foreach($screen_list as $k1=>$v1)
					{
						if(in_array($v1,$highKeyword['define_form'][$defineform['form_name']]))
						{
							$htmlcontent .= '<div class="feelcrm-screen-info feelcrm-screen-checkbox feelcrm-screen-infoactive"  data-value="'.$v1.'"><div class="feelcrm-screen-infoname" >'.$v1.'<i class="iconfont icon-gouxuan"></i></div></div>';
						}
						else
						{
							$htmlcontent .= '<div class="feelcrm-screen-info feelcrm-screen-checkbox" data-value="'.$v1.'"><div class="feelcrm-screen-infoname" >'.$v1.'<i class="iconfont icon-gouxuan"></i></div></div>';
						}
					}
					foreach($highKeyword['define_form'][$defineform['form_name']] as $k2 => $v2)
					{
						$htmlcontent .= '<input type="hidden" data-type="checkbox" name="highKeyword[define_form]['.$defineform['form_name'].'][]" value="'.$v2.'" />';
					}
				}
				else
				{
					foreach($screen_list as $k1=>$v1)
					{
						if($highKeyword['define_form'][$defineform['form_name']] == $v1)
						{
							$htmlcontent .= '<div class="feelcrm-screen-info feelcrm-screen-infoactive" data-value="'.$v1.'"><div class="feelcrm-screen-infoname" >'.$v1.'<i class="iconfont icon-gouxuan"></i></div></div>';
						}
						else
						{
							$htmlcontent .= '<div class="feelcrm-screen-info " data-value="'.$v1.'"><div class="feelcrm-screen-infoname" >'.$v1.'<i class="iconfont icon-gouxuan"></i></div></div>';
						}
					}

					$htmlcontent .= '<input type="hidden" name="highKeyword[define_form]['.$defineform['form_name'].']" value="'.$highKeyword['define_form'][$defineform['form_name']].'" />';
				}
			}
			elseif($defineform['form_type'] == 'date')
			{
				if($highKeyword['define_form'][$defineform['form_name']])
				{
					$htmlcontent .= '<div class="feelcrm-screen-info feelcrm-screen-infoactive"><div class="feelcrm-screen-infoname" ><input type="text" name="highKeyword[define_form]['.$defineform['form_name'].']" value="'.$highKeyword['define_form'][$defineform['form_name']].'" class="feelcrm-screen-input" placeholder="'.$defineform['form_description'].'" id="datetime'.$k.'" /><i class="iconfont icon-gouxuan"></i></div></div>';
				}
				else
				{
					$htmlcontent .= '<div class="feelcrm-screen-info "><div class="feelcrm-screen-infoname" ><input type="text" name="highKeyword[define_form]['.$defineform['form_name'].']" value="'.$highKeyword['define_form'][$defineform['form_name']].'" class="feelcrm-screen-input" placeholder="'.$defineform['form_description'].'" id="datetime'.$k.'" /><i class="iconfont icon-gouxuan"></i></div></div>';
				}

				$htmlcontent .= '<script type="text/javascript">$(function(){var dateInput = "#datetime'.$k.'";var theme="android-holo-light";$(dateInput).mobiscroll().datetime({theme: theme,lang: "zh",cancelText: "'.L('CANCEL').'",dateFormat: "yyyy-mm-dd",endYear: "'.date('Y',time()).'",dayText: "'.L('DAY').'",monthText: "'.L('MONTH').'",yearText: "'.L('YEAR').'",headerText: function(valueText){var array = valueText.split("-");return array[0] + "'.L('YEAR').'" + array[1] + "'.L('MONTH').'" + array[2].split(" ")[0] + "'.L('DAY').'" + array[2].split(" ")[1];},onBeforeShow:function(inst){inst.settings.readonly=false;},onSelect: function (valueText, inst){var selectedDate = valueText;}})})</script>';

			}
			elseif($defineform['form_type'] == 'region')
			{

			}
			else
			{
				if($highKeyword['define_form'][$defineform['form_name']])
				{
					$htmlcontent .= '<div class="feelcrm-screen-info feelcrm-screen-infoactive"><div class="feelcrm-screen-infoname" ><input type="text" name="highKeyword[define_form]['.$defineform['form_name'].']" value="'.$highKeyword['define_form'][$defineform['form_name']].'" class="feelcrm-screen-input" placeholder="'.L('ENTER').$defineform['form_description'].'" /><i class="iconfont icon-gouxuan"></i></div></div>';
				}
				else
				{
					$htmlcontent .= '<div class="feelcrm-screen-info "><div class="feelcrm-screen-infoname" ><input type="text" name="highKeyword[define_form]['.$defineform['form_name'].']" value="'.$highKeyword['define_form'][$defineform['form_name']].'" class="feelcrm-screen-input" placeholder="'.L('ENTER').$defineform['form_description'].'" /><i class="iconfont icon-gouxuan"></i></div></div>';
				}
			}

			$checkboxClass = "";

			if($defineform['form_type'] == 'checkbox')
			{
				$checkboxClass = "feelcrm-screen-checkbox-first";
			}

			if($highKeyword['define_form'][$defineform['form_name']])
			{
				$htmlR .= '<div class="fl feelcrm-screen-list-R feelcrmScreen Selectedscreen'.$val.' hidden" id="screen-layer'.$k.'" data-name="'.$defineform['form_name'].'"><div class="feelcrm-screen-info '.$checkboxClass.'"><div class="feelcrm-screen-infoname" >'.L("UNLIMITED").'<i class="iconfont icon-gouxuan"></i></div></div>'.$htmlcontent.'</div>';
			}
			else
			{
				$htmlR .= '<div class="fl feelcrm-screen-list-R feelcrmScreen Selectedscreen'.$val.' hidden" id="screen-layer'.$k.'" data-name="'.$defineform['form_name'].'"><div class="feelcrm-screen-info feelcrm-screen-infoactive '.$checkboxClass.'"><div class="feelcrm-screen-infoname" >'.L("UNLIMITED").'<i class="iconfont icon-gouxuan"></i></div></div>'.$htmlcontent.'</div>';
			}

			$k ++;
		}

		$this->ajaxReturn(['htmlL'=>$htmlL,'htmlR'=>$htmlR]);
	}

	//获取客户自定义筛选内容
	public function customerScreen()
	{
		$Selectedscreen = I('post.Selectedscreen');

		$SelectedscreenFixed = I('post.SelectedscreenFixed');

		if($Selectedscreen && $SelectedscreenFixed)
		{
			$countSelected = count($Selectedscreen) + count($SelectedscreenFixed);
		}else
		{
			if($Selectedscreen)
			{
				$countSelected = count($Selectedscreen);
			}
			elseif($SelectedscreenFixed)
			{
				$countSelected = count($SelectedscreenFixed);
			}
			else
			{
				$countSelected = 0;
			}
		}

		if(session('CustomerhighKeyword'))
		{
			$highKeyword = session('CustomerhighKeyword');
		}

		$screen = I('post.screen');

		$screenFixed = I('post.screenFixed');

		$htmlL = '';

		$htmlR = '';
		//var_dump($countSelected);die;
		if($countSelected > 0)
		{
			$k = $countSelected + 4;
		}
		else
		{
			$k = 4;
		}

		foreach($screenFixed as $key=>$val)
		{
			switch ($val)
			{
				case '1':

					$titlename = L('CUSTOMER_NUMBER');

					if($highKeyword['customer_no'])
					{
						$htmlR .= '<div class="fl feelcrm-screen-list-R feelcrmScreen SelectedscreenFixed'.$val.' hidden" id="screen-layer'.$k.'"><div class="feelcrm-screen-info "><div class="feelcrm-screen-infoname" >'.L("UNLIMITED").'<i class="iconfont icon-gouxuan"></i></div></div><div class="feelcrm-screen-info feelcrm-screen-infoactive"><div class="feelcrm-screen-infoname" ><input type="text" name="highKeyword[customer_no]" value="'.$highKeyword['customer_no'].'" class="feelcrm-screen-input" placeholder="'.L('ENTER_CUSTOMER_NUMBER').'" /><i class="iconfont icon-gouxuan"></i></div></div></div>';
					}
					else
					{
						$htmlR .= '<div class="fl feelcrm-screen-list-R feelcrmScreen SelectedscreenFixed'.$val.' hidden" id="screen-layer'.$k.'"><div class="feelcrm-screen-info feelcrm-screen-infoactive"><div class="feelcrm-screen-infoname" >'.L("UNLIMITED").'<i class="iconfont icon-gouxuan"></i></div></div><div class="feelcrm-screen-info "><div class="feelcrm-screen-infoname" ><input type="text" name="highKeyword[customer_no]" value="'.$highKeyword['customer_no'].'" class="feelcrm-screen-input" placeholder="'.L('ENTER_CUSTOMER_NUMBER').'" /><i class="iconfont icon-gouxuan"></i></div></div></div>';
					}

                break;
				case '2':

					$titlename = L('TRANSACTION_STATUS');

					if($highKeyword['is_trade'] == 0 && $highKeyword['is_trade'] != '' && $highKeyword['is_trade'] != null)
					{
						$htmlR .= '<div class="fl feelcrm-screen-list-R feelcrmScreen SelectedscreenFixed'.$val.' hidden" id="screen-layer'.$k.'"><div class="feelcrm-screen-info "><div class="feelcrm-screen-infoname" >'.L("UNLIMITED").'<i class="iconfont icon-gouxuan"></i></div></div><div class="feelcrm-screen-info feelcrm-screen-infoactive" data-value="0"><div class="feelcrm-screen-infoname" >'.L('UNSOLD').'<i class="iconfont icon-gouxuan"></i></div></div><div class="feelcrm-screen-info " data-value="1"><div class="feelcrm-screen-infoname" >'.L("DEAL_DONE").'<i class="iconfont icon-gouxuan"></i></div></div><input type="hidden" name="highKeyword[is_trade]" value="'.$highKeyword['is_trade'].'" /></div>';
					}
					elseif($highKeyword['is_trade'] == 1)
					{
						$htmlR .= '<div class="fl feelcrm-screen-list-R feelcrmScreen SelectedscreenFixed'.$val.' hidden" id="screen-layer'.$k.'"><div class="feelcrm-screen-info "><div class="feelcrm-screen-infoname" >'.L("UNLIMITED").'<i class="iconfont icon-gouxuan"></i></div></div><div class="feelcrm-screen-info " data-value="0"><div class="feelcrm-screen-infoname" >'.L('UNSOLD').'<i class="iconfont icon-gouxuan"></i></div></div><div class="feelcrm-screen-info feelcrm-screen-infoactive" data-value="1"><div class="feelcrm-screen-infoname" >'.L("DEAL_DONE").'<i class="iconfont icon-gouxuan"></i></div></div><input type="hidden" name="highKeyword[is_trade]" value="'.$highKeyword['is_trade'].'" /></div>';
					}
					else
					{
						$htmlR .= '<div class="fl feelcrm-screen-list-R feelcrmScreen SelectedscreenFixed'.$val.' hidden" id="screen-layer'.$k.'"><div class="feelcrm-screen-info feelcrm-screen-infoactive"><div class="feelcrm-screen-infoname" >'.L("UNLIMITED").'<i class="iconfont icon-gouxuan"></i></div></div><div class="feelcrm-screen-info " data-value="0"><div class="feelcrm-screen-infoname" >'.L('UNSOLD').'<i class="iconfont icon-gouxuan"></i></div></div><div class="feelcrm-screen-info " data-value="1"><div class="feelcrm-screen-infoname" >'.L("DEAL_DONE").'<i class="iconfont icon-gouxuan"></i></div></div><input type="hidden" name="highKeyword[is_trade]" value="'.$highKeyword['is_trade'].'" /></div>';
					}

                break;
				case '3':

					$titlename = L('CONTACT_NAME');

					if($highKeyword['contacter_name'])
					{
						$htmlR .= '<div class="fl feelcrm-screen-list-R feelcrmScreen SelectedscreenFixed'.$val.' hidden" id="screen-layer'.$k.'"><div class="feelcrm-screen-info "><div class="feelcrm-screen-infoname" >'.L("UNLIMITED").'<i class="iconfont icon-gouxuan"></i></div></div><div class="feelcrm-screen-info feelcrm-screen-infoactive"><div class="feelcrm-screen-infoname" ><input type="text" name="highKeyword[contacter_name]" value="'.$highKeyword['contacter_name'].'" class="feelcrm-screen-input" placeholder="'.L('ENTER_CUSTOMER_NAME').'" /><i class="iconfont icon-gouxuan"></i></div></div></div>';
					}
					else
					{
						$htmlR .= '<div class="fl feelcrm-screen-list-R feelcrmScreen SelectedscreenFixed'.$val.' hidden" id="screen-layer'.$k.'"><div class="feelcrm-screen-info feelcrm-screen-infoactive"><div class="feelcrm-screen-infoname" >'.L("UNLIMITED").'<i class="iconfont icon-gouxuan"></i></div></div><div class="feelcrm-screen-info "><div class="feelcrm-screen-infoname" ><input type="text" name="highKeyword[contacter_name]" value="'.$highKeyword['contacter_name'].'" class="feelcrm-screen-input" placeholder="'.L('ENTER_CUSTOMER_NAME').'" /><i class="iconfont icon-gouxuan"></i></div></div></div>';
					}

                break;
				case '4':

					$titlename = L('CONTACT_NUMBER');

					if($highKeyword['contacter_phone'])
					{
						$htmlR .= '<div class="fl feelcrm-screen-list-R feelcrmScreen SelectedscreenFixed'.$val.' hidden" id="screen-layer'.$k.'"><div class="feelcrm-screen-info "><div class="feelcrm-screen-infoname" >'.L("UNLIMITED").'<i class="iconfont icon-gouxuan"></i></div></div><div class="feelcrm-screen-info feelcrm-screen-infoactive"><div class="feelcrm-screen-infoname" ><input type="text" name="highKeyword[contacter_phone]" value="'.$highKeyword['contacter_phone'].'" class="feelcrm-screen-input" placeholder="'.L('ENTER_CUSTOMER_PHONE').'" /><i class="iconfont icon-gouxuan"></i></div></div></div>';
					}
					else
					{
						$htmlR .= '<div class="fl feelcrm-screen-list-R feelcrmScreen SelectedscreenFixed'.$val.' hidden" id="screen-layer'.$k.'"><div class="feelcrm-screen-info feelcrm-screen-infoactive"><div class="feelcrm-screen-infoname" >'.L("UNLIMITED").'<i class="iconfont icon-gouxuan"></i></div></div><div class="feelcrm-screen-info "><div class="feelcrm-screen-infoname" ><input type="text" name="highKeyword[contacter_phone]" value="'.$highKeyword['contacter_phone'].'" class="feelcrm-screen-input" placeholder="'.L('ENTER_CUSTOMER_PHONE').'" /><i class="iconfont icon-gouxuan"></i></div></div></div>';
					}

                break;
				case '5':

					$titlename = L('CONTACT_EMAIL');

					if($highKeyword['contacter_email'])
					{
						$htmlR .= '<div class="fl feelcrm-screen-list-R feelcrmScreen SelectedscreenFixed'.$val.' hidden" id="screen-layer'.$k.'"><div class="feelcrm-screen-info "><div class="feelcrm-screen-infoname" >'.L("UNLIMITED").'<i class="iconfont icon-gouxuan"></i></div></div><div class="feelcrm-screen-info feelcrm-screen-infoactive"><div class="feelcrm-screen-infoname" ><input type="text" name="highKeyword[contacter_email]" value="'.$highKeyword['contacter_email'].'" class="feelcrm-screen-input" placeholder="'.L('ENTER_CUSTOMER_EMAIL').'" /><i class="iconfont icon-gouxuan"></i></div></div></div>';
					}
					else
					{
						$htmlR .= '<div class="fl feelcrm-screen-list-R feelcrmScreen SelectedscreenFixed'.$val.' hidden" id="screen-layer'.$k.'"><div class="feelcrm-screen-info feelcrm-screen-infoactive"><div class="feelcrm-screen-infoname" >'.L("UNLIMITED").'<i class="iconfont icon-gouxuan"></i></div></div><div class="feelcrm-screen-info "><div class="feelcrm-screen-infoname" ><input type="text" name="highKeyword[contacter_email]" value="'.$highKeyword['contacter_email'].'" class="feelcrm-screen-input" placeholder="'.L('ENTER_CUSTOMER_EMAIL').'" /><i class="iconfont icon-gouxuan"></i></div></div></div>';
					}

                break;
			}
			$htmlL .= '<div onclick="switchScreen(this,'.$k.')" class="feelcrm-screen-title SelectedscreenFixed'.$val.'"><div class="feelcrm-screen-titlename">'.$titlename.'</div></div><input type="hidden" name="SelectedscreenFixed[]" value="'.$val.'" />';

			$k ++;
		}

		$form_description = getCrmLanguageData('form_description');

		foreach($screen as $key=>$val)
		{
			$defineform = getCrmDbModel('define_form')
				->field(['*',$form_description])
				->where(['company_id'=>$this->_company_id,'closed' => 0,'type'=>'customer','form_type'=>['not in',['region','textarea']],'form_id'=>$val])
				->order('orderby asc')
				->find();

			$htmlL .= '<div onclick="switchScreen(this,'.$k.')" class="feelcrm-screen-title Selectedscreen'.$val.'"><div class="feelcrm-screen-titlename">'.$defineform['form_description'].'</div></div><input type="hidden" name="Selectedscreen[]" value="'.$val.'" />';

			$htmlcontent = '';

			if(in_array($defineform['form_type'],['radio','select','checkbox','select_text']))
			{
				$screen_list = explode('|',$defineform['form_option']);

				if($defineform['form_type'] == 'checkbox')
				{
					foreach($screen_list as $k1=>$v1)
					{
						if(in_array($v1,$highKeyword['define_form'][$defineform['form_name']]))
						{
							$htmlcontent .= '<div class="feelcrm-screen-info feelcrm-screen-checkbox feelcrm-screen-infoactive"  data-value="'.$v1.'"><div class="feelcrm-screen-infoname" >'.$v1.'<i class="iconfont icon-gouxuan"></i></div></div>';
						}
						else
						{
							$htmlcontent .= '<div class="feelcrm-screen-info feelcrm-screen-checkbox" data-value="'.$v1.'"><div class="feelcrm-screen-infoname" >'.$v1.'<i class="iconfont icon-gouxuan"></i></div></div>';
						}
					}
					foreach($highKeyword['define_form'][$defineform['form_name']] as $k2 => $v2)
					{
						$htmlcontent .= '<input type="hidden" data-type="checkbox" name="highKeyword[define_form]['.$defineform['form_name'].'][]" value="'.$v2.'" />';
					}
				}
				else
				{
					foreach($screen_list as $k1=>$v1)
					{
						if($highKeyword['define_form'][$defineform['form_name']] == $v1)
						{
							$htmlcontent .= '<div class="feelcrm-screen-info feelcrm-screen-infoactive" data-value="'.$v1.'"><div class="feelcrm-screen-infoname" >'.$v1.'<i class="iconfont icon-gouxuan"></i></div></div>';
						}
						else
						{
							$htmlcontent .= '<div class="feelcrm-screen-info " data-value="'.$v1.'"><div class="feelcrm-screen-infoname" >'.$v1.'<i class="iconfont icon-gouxuan"></i></div></div>';
						}
					}

					$htmlcontent .= '<input type="hidden" name="highKeyword[define_form]['.$defineform['form_name'].']" value="'.$highKeyword['define_form'][$defineform['form_name']].'" />';
				}
			}
			elseif($defineform['form_type'] == 'date')
			{
				if($highKeyword['define_form'][$defineform['form_name']])
				{
					$htmlcontent .= '<div class="feelcrm-screen-info feelcrm-screen-infoactive"><div class="feelcrm-screen-infoname" ><input type="text" name="highKeyword[define_form]['.$defineform['form_name'].']" value="'.$highKeyword['define_form'][$defineform['form_name']].'" class="feelcrm-screen-input" placeholder="'.$defineform['form_description'].'" id="datetime'.$k.'" /><i class="iconfont icon-gouxuan"></i></div></div>';
				}
				else
				{
					$htmlcontent .= '<div class="feelcrm-screen-info "><div class="feelcrm-screen-infoname" ><input type="text" name="highKeyword[define_form]['.$defineform['form_name'].']" value="'.$highKeyword['define_form'][$defineform['form_name']].'" class="feelcrm-screen-input" placeholder="'.$defineform['form_description'].'" id="datetime'.$k.'" /><i class="iconfont icon-gouxuan"></i></div></div>';
				}

				$htmlcontent .= '<script type="text/javascript">$(function(){var dateInput = "#datetime'.$k.'";var theme="android-holo-light";$(dateInput).mobiscroll().datetime({theme: theme,lang: "zh",cancelText: "'.L('CANCEL').'",dateFormat: "yyyy-mm-dd",endYear: "'.date('Y',time()).'",dayText: "'.L('DAY').'",monthText: "'.L('MONTH').'",yearText: "'.L('YEAR').'",headerText: function(valueText){var array = valueText.split("-");return array[0] + "'.L('YEAR').'" + array[1] + "'.L('MONTH').'" + array[2].split(" ")[0] + "'.L('DAY').'" + array[2].split(" ")[1];},onBeforeShow:function(inst){inst.settings.readonly=false;},onSelect: function (valueText, inst){var selectedDate = valueText;}})})</script>';

			}
			elseif($defineform['form_type'] == 'region')
			{

			}
			else
			{
				if($highKeyword['define_form'][$defineform['form_name']])
				{
					$htmlcontent .= '<div class="feelcrm-screen-info feelcrm-screen-infoactive"><div class="feelcrm-screen-infoname" ><input type="text" name="highKeyword[define_form]['.$defineform['form_name'].']" value="'.$highKeyword['define_form'][$defineform['form_name']].'" class="feelcrm-screen-input" placeholder="'.L('ENTER').$defineform['form_description'].'" /><i class="iconfont icon-gouxuan"></i></div></div>';
				}
				else
				{
					$htmlcontent .= '<div class="feelcrm-screen-info "><div class="feelcrm-screen-infoname" ><input type="text" name="highKeyword[define_form]['.$defineform['form_name'].']" value="'.$highKeyword['define_form'][$defineform['form_name']].'" class="feelcrm-screen-input" placeholder="'.L('ENTER').$defineform['form_description'].'" /><i class="iconfont icon-gouxuan"></i></div></div>';
				}
			}

			$checkboxClass = "";

			if($defineform['form_type'] == 'checkbox')
			{
				$checkboxClass = "feelcrm-screen-checkbox-first";
			}

			if($highKeyword['define_form'][$defineform['form_name']])
			{
				$htmlR .= '<div class="fl feelcrm-screen-list-R feelcrmScreen Selectedscreen'.$val.' hidden" id="screen-layer'.$k.'" data-name="'.$defineform['form_name'].'"><div class="feelcrm-screen-info '.$checkboxClass.'"><div class="feelcrm-screen-infoname" >'.L("UNLIMITED").'<i class="iconfont icon-gouxuan"></i></div></div>'.$htmlcontent.'</div>';
			}
			else
			{
				$htmlR .= '<div class="fl feelcrm-screen-list-R feelcrmScreen Selectedscreen'.$val.' hidden" id="screen-layer'.$k.'" data-name="'.$defineform['form_name'].'"><div class="feelcrm-screen-info feelcrm-screen-infoactive '.$checkboxClass.'"><div class="feelcrm-screen-infoname" >'.L("UNLIMITED").'<i class="iconfont icon-gouxuan"></i></div></div>'.$htmlcontent.'</div>';
			}

			$k ++;
		}

		$this->ajaxReturn(['htmlL'=>$htmlL,'htmlR'=>$htmlR]);
	}

	//获取客户所属合同
	public function getContract()
	{
		$customer_id = I('post.customer_id');

		$type = I('post.type') ? I('post.type') : 'li';

		$contract = getCrmDbModel('contract')->where(['company_id'=>$this->_company_id,'customer_id'=>$customer_id,'isvalid'=>1,'status'=>2])->select();

		$html = '';

		if($type == 'option')
		{
			$html .= '<option value="">'.L("PLEASE_SELECT_CONTRACT").'</option>';

			if($contract)
			{
				foreach($contract as $key=>&$val)
				{
					$contract_detail = CrmgetCrmDetailList('contract',$val['contract_id'],$this->_company_id,'name,money');

					$html .= '<option value="'.$val['contract_id'].'" data-money="'.$contract_detail['money'].'">'.$contract_detail['name'].'</option>';
				}
			}
		}
		else
		{
			if($contract)
			{
				foreach($contract as $key=>&$val)
				{
					$val['id'] = $val['contract_id'];

					$contract_detail = CrmgetCrmDetailList('contract',$val['contract_id'],$this->_company_id,'name');

					$val['name'] = $contract_detail['name'];

					$html .= '<li data-value="'.$contract_detail['name'].'">';

					$html .=	'<input type="checkbox" name="order[contract_id][]" value="'.$val['contract_id'].'"/>';

					$html .=	'<div class="feeldesk-option">';

					$html .=	'<span class="feeldesk-option-title">'.$contract_detail['name'].'</span>';

					$html .=	'<span class="iconfont icon-check"></span>';

					$html .=	'</div>';

					$html .=	'</li>';
				}
			}
			else
			{
				$html =	'<li><span class="feeldesk-option-title">'.L('NO_DATA').'</span></li>';
			}
		}

		$this->ajaxReturn(['errcode'=>0,'msg'=>'ok','contract'=>$contract,'html'=>$html]);
	}
	//获取客户所属订单
	public function getOrder()
	{
		$customer_id = I('post.customer_id');

		$order = getCrmDbModel('order')->where(['company_id'=>$this->_company_id,'customer_id'=>$customer_id,'isvalid'=>1])->select();

		$html = '';

		if($order)
		{
			foreach($order as $key=>&$val)
			{
				$val['id'] = $val['order_id'];

				$contract_detail = CrmgetCrmDetailList('order',$val['order_id'],$this->_company_id,'name');

				$val['name'] = $contract_detail['name'];

				$html .= '<li data-value="'.$contract_detail['name'].'">';

				$html .= '<input type="checkbox" name="contract[order_id][]" value="'.$val['order_id'].'"/>';

				$html .= '<div class="feeldesk-option">';

				$html .= '<span class="feeldesk-option-title">'.$contract_detail['name'].'</span>';

				$html .= '<span class="iconfont icon-check"></span>';

				$html .= '</div>';

				$html .= '</li>';

			}
		}
		else
		{
			$html =	'<li><span class="feeldesk-option-title">'.L('NO_DATA').'</span></li>';
		}

		$this->ajaxReturn(['errcode'=>0,'msg'=>'ok','order'=>$order,'html'=>$html]);
	}

	public function ocrCardDiscern()
	{
		$crmsite = getCrmDbModel('setting')->where(['key'=>'crmsite','company_id'=>$this->_company_id])->getField('value');

		$crmsite = unserialize($crmsite);

		if(!$crmsite || $crmsite['ocrEnable'] != 1)
		{
			$this->ajaxReturn(['errcode'=>2,'msg'=>L('OCR_RECOGNITION_NOT_TURNED_ON')]);
		}

		if(!$crmsite['ocrAppcode'])
		{
			$this->ajaxReturn(['errcode'=>2,'msg'=>L('APPCODE_CANNOT_BE_EMPTY')]);
		}

		$thumbImg = I('post.thumbImg');

		$img_base64 = imgToBase64($thumbImg);

		$header = array('Content-Type: application/x-www-form-urlencoded','APPCODE: '.$crmsite['ocrAppcode']);

		$url = 'http://cloudapi.chaterman.com/api/ocr/card';

		$data['img'] = $img_base64;

		$result = tocurl($url,$header,http_build_query($data));

		if($result['success'])
		{
			$customerData = [];

			$contacterData = [];

			$contacterData['position'] = '';

			$customerData['address'] = '';

			$ocrData = $result['data'][0]['items'];

			foreach($ocrData as $key=>$val)
			{
				if($val['desc'] == '姓名')
				{
					$contacterData['name'] = $val['content'];
				}

				if($val['desc'] == '职务/部门')
				{
					$contacterData['position'] = $contacterData['position'] ? $contacterData['position'].' '.$val['content'] : $val['content'];
				}

				if($val['desc'] == '手机')
				{
					$customerData['phone'] = $val['content'];

					$contacterData['phone'] = $val['content'];
				}

				if($val['desc'] == '公司')
				{
					$customerData['name'] = $val['content'] ? $val['content'] : $contacterData['name'];
				}

				if($val['desc'] == '地址')
				{
					$customerData['address'] = $customerData['address'] ? $customerData['address'].' '.$val['content'] : $val['content'];
				}

				if($val['desc'] == '电子邮箱')
				{
					$customerData['email'] = $val['content'];

					$contacterData['email'] = $val['content'];
				}

				if($val['desc'] == '网址')
				{
					$customerData['website'] = $val['content'];
				}
			}

			$url = C('HTTP_PROTOCOL').'://'.C('HOST_DOMAIN').'/CrmMobile/customer/create/customerData/'.base64_encode(urlencode(serialize($customerData))).'/contacterData/'.base64_encode(urlencode(serialize($contacterData))).'/fromType/OCR';

			$this->ajaxReturn(['errcode'=>0,'msg'=>'ok','url'=>$url]);
		}
		else
		{
			//$this->ajaxReturn(['errcode'=>2,'msg'=>$result['msg']]);
			$this->ajaxReturn(['errcode'=>2,'msg'=>'识别失败']);
		}

	}

	//获取合同应收款
	public function getAccount()
	{
		$customer_id = I('post.customer_id');

		$contract_id = I('post.contract_id');

		$account = getCrmDbModel('account')->where(['company_id'=>$this->_company_id,'customer_id'=>$customer_id,'contract_id'=>$contract_id,'isvalid'=>1])->select();

		$html = '';

		$html .= '<option value="">'.L('PLEASE_SELECT_RECEIVABLE').'</option>';

		if($account)
		{
			foreach($account as $key=>$val)
			{
				$html .= '<option value="'.$val['account_id'].'" data-money="'.$val['account_money'].'">'.$val['account_prefix'].$val['account_no'].' --- '.L('RECEIVABLE').$val['account_money'].'</option>';
			}
		}

		$this->ajaxReturn(['errcode'=>0,'msg'=>'ok','html'=>$html]);
	}

	//获取合同收款
	public function getReceipt()
	{
		$customer_id = I('post.customer_id');

		$contract_id = I('post.contract_id');

		$map = ['company_id'=>$this->_company_id,'customer_id'=>$customer_id,'contract_id'=>$contract_id,'isvalid'=>1];

		$status = I('post.status');

		if($status)
		{
			$map['status'] = $status;
		}

		$receipt = getCrmDbModel('receipt')->where($map)->select();

		$html = '';

		$receiptJson = [];

		if($receipt)
		{
			foreach($receipt as $key=>$val)
			{
				$html .= '<li data-value="'.$val['receipt_prefix'].$val['receipt_no'].'">';

				$html .= '<input type="checkbox" name="receipt_id[]" value="'.$val['receipt_id'].'"/>';

				$html .= '<div class="feeldesk-option">';

				$html .= '<span class="feeldesk-option-title">'.$val['receipt_prefix'].$val['receipt_no'].' --- '.L('RECEIVE_PAYMENT').$val['receipt_money'].'</span>';

				$html .= '<span class="iconfont icon-check"></span>';

				$html .= '</div>';

				$html .= '</li>';
			}
		}

		$this->ajaxReturn(['errcode'=>0,'msg'=>'ok','html'=>$html]);
	}

	//获取收款金额
	public function getReceiptMoney()
	{
		$receipt_id = I('post.receipt_id');

		$receipt_money = getCrmDbModel('receipt')->where(['company_id'=>$this->_company_id,'isvalid'=>1,'receipt_id'=>['in',$receipt_id]])->sum('receipt_money');

		$receipt_money = $receipt_money ? $receipt_money : 0;

		$this->ajaxReturn(['errcode'=>0,'msg'=>'ok','receipt_money'=>$receipt_money]);
	}

	//获取发票信息列表
	public function getCustomerInvoiceInfo()
	{
		$customer_id = I('post.customer_id');

		$invoiceinfo = getCrmDbModel('customer_invoiceinfo')->where(['company_id'=>$this->_company_id,'customer_id'=>$customer_id,'closed'=>0,'isvalid'=>1])->select();

		$html = '';

		$html .= '<option value="">'.L('SELECT_INVOICE_INFORMATION').'</option>';

		if($invoiceinfo)
		{
			foreach($invoiceinfo as $key=>$val)
			{
				$html .= '<option value="'.$val['invoiceinfo_id'].'">'.$val['invoice_rise'].'</option>';
			}
		}

		$this->ajaxReturn(['errcode'=>0,'msg'=>'ok','html'=>$html]);
	}

	public function getClueList() //动态获取线索
	{
		$field['company_id'] = $this->_company_id;

		$field['isvalid'] = 1;

		if($keyword = I('get.keyword'))
		{
			$keywordField = CrmgetDefineFormField($this->_company_id,'clue','name,phone,company',$keyword);

			$field['clue_id'] = $keywordField ? ['in',$keywordField] : '0';
		}

		$getCustomerAuth = D('CrmSelectField')->getCustomerAuth($this->member,$this->_company_id,$this->member['member_id'],'','clue');

		$memberRoleArr = $getCustomerAuth['memberRoleArr'];

		$field['member_id'] = $memberRoleArr;

		$selectClueCount =  getCrmDbModel('Clue')->where($field)->count();

		$selectCluePage = new \Think\Page($selectClueCount, 10);

		$selectCluelist =  getCrmDbModel('Clue')->where($field)->field('clue_id,clue_prefix,clue_no,createtime')->limit($selectCluePage->firstRow, $selectCluePage->listRows)->order('createtime desc')->select();

		foreach($selectCluelist as $key => &$val)
		{
			$val['detail'] = CrmgetCrmDetailList('clue',$val['clue_id'],$this->_company_id,'name');
		}

		$this->ajaxReturn(['data'=>$selectCluelist,'pages'=>ceil($selectClueCount/10)]);
	}

	//修改商机阶段
	public function updateOpportunityStage()
	{
		$opportunity_id = I('get.opportunity_id');

		$opportunity_id = decrypt($opportunity_id,'OPPORTUNITY');

		if(!$opportunity_id)
		{
			$this->ajaxReturn(['status'=>0,'msg'=>L('OPPORTUNITY_DOES_NOT_EXIST')]);
		}

		$stage = I('get.stage');

		$opportunity = getCrmDbModel('opportunity')->where(['company_id'=>$this->_company_id,'opportunity_id'=>$opportunity_id,'isvalid'=>1])->field('member_id,createtime')->find();

		if($opportunity)
		{
			$opportunityStageFormId = getCrmDbModel('define_form')
				->where(['company_id'=>$this->_company_id,'closed' => 0,'type'=>'opportunity','form_name'=>'stage'])
				->getField('form_id');

			if(getCrmDbModel('opportunity_detail')->where(['company_id'=>$this->_company_id,'opportunity_id'=>$opportunity_id,'form_id'=>$opportunityStageFormId])->save(['form_content'=>$stage]))
			{
				$this->ajaxReturn(['status'=>2,'msg'=>L('SUCCESSFULLY_MODIFIED')]);
			}
			else
			{
				$this->ajaxReturn(['status'=>0,'msg'=>L('FAILED_TO_MODIFY_OPPORTUNITY_STAGE')]);
			}
		}
		else
		{
			$this->ajaxReturn(['status'=>0,'msg'=>L('OPPORTUNITY_DOES_NOT_EXIST')]);
		}
	}
}
