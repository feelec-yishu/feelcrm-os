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
use Crypto\CryptMessage;
use Think\Controller;

class AjaxRequestController extends Controller
{

	protected $member,$_company_id;

    public function _initialize()
    {
        $this->member = session('index');

        $this->_company_id = session('company_id');
    }

    //更新收款、发票的负责人（2021.10.27更新，之前的系统没有负责人字段，数据需更新一下负责人）
    public function updateMemberIdByReIn()
    {
    	//更新收款
    	$receipt = getCrmDbModel('receipt')->where(['member_id'=>0])->field('receipt_id,member_id,creater_id')->select();

    	foreach ($receipt as $key=>$val)
    	{
		    getCrmDbModel('receipt')->where(['receipt_id'=>$val['receipt_id']])->save(['member_id'=>$val['creater_id']]);
	    }

	    //更新发票
	    $invoice = getCrmDbModel('invoice')->where(['member_id'=>0])->field('invoice_id,member_id,creater_id')->select();

	    foreach ($invoice as $key=>$val)
	    {
		    getCrmDbModel('invoice')->where(['invoice_id'=>$val['invoice_id']])->save(['member_id'=>$val['creater_id']]);
	    }

    	echo '运行成功！';
    }

    //生成默认导入模板（2021-11-26更新，之前系统内所有商户没有，更新后需生成模板）
    public function createImportTemp()
    {
		$company = M('company_audit')->where(['activity'=>2,'due_time'=>['gt',time()]])->field('company_id')->select();

		foreach ($company as $key=>$val)
		{
			if(!$temp_id = getCrmDbModel('temp_file')->where(['company_id'=>$val['company_id']])->getField('temp_id'))
			{
				D('Excel')->createCrmImportTemp($val['company_id'],'clue');

				D('Excel')->createCrmImportTemp($val['company_id'],'customer');

				D('Excel')->createCrmImportTemp($val['company_id'],'opportunity');

				D('Excel')->createCrmImportTemp($val['company_id'],'product');

				D('Excel')->createCrmImportTemp($val['company_id'],'contract');
			}
		}
    }

	public function testWechat()
	{
		D('CrmCreateMessage')->createMessagetest(7,1,329,1,329,28,10,3);
	}

	public function checkDefineForm()
	{
		$company = M('company')->where(['crm_auth'=>10])->field('company_id')->select();

		foreach($company as $k => $v)
		{
			$fault_field = C('CRMFIELDS.CUSTOMER');

			$addFaultField = [];

			foreach($fault_field as $key=>&$val)
			{
				$val['company_id'] = str_replace($val['company_id'],$v['company_id'],$val['company_id']);

				if(!getCrmDbModel('define_form')->where(['company_id'=>$v['company_id'],'is_default'=>1,'type'=>'customer','form_name'=>$val['form_name']])->getField('form_id'))
				{
					$addFaultField[] = $val;
				}

			}
			getCrmDbModel('define_form')->addAll($addFaultField);


			$fault_field = C('CRMFIELDS.CONTACTER');

			$addFaultField = [];

			foreach($fault_field as $key=>&$val)
			{
				$val['company_id'] = str_replace($val['company_id'],$v['company_id'],$val['company_id']);

				if(!getCrmDbModel('define_form')->where(['company_id'=>$v['company_id'],'is_default'=>1,'type'=>'contacter','form_name'=>$val['form_name']])->getField('form_id'))
				{
					$addFaultField[] = $val;
				}

			}
			getCrmDbModel('define_form')->addAll($addFaultField);


			$fault_field = C('CRMFIELDS.PRODUCT');

			$addFaultField = [];

			foreach($fault_field as $key=>&$val)
			{
				$val['company_id'] = str_replace($val['company_id'],$v['company_id'],$val['company_id']);

				if(!getCrmDbModel('define_form')->where(['company_id'=>$v['company_id'],'is_default'=>1,'type'=>'product','form_name'=>$val['form_name']])->getField('form_id'))
				{
					$addFaultField[] = $val;
				}

			}
			getCrmDbModel('define_form')->addAll($addFaultField);


			$fault_field = C('CRMFIELDS.ORDER');

			$addFaultField = [];

			foreach($fault_field as $key=>&$val)
			{
				$val['company_id'] = str_replace($val['company_id'],$v['company_id'],$val['company_id']);

				if(!getCrmDbModel('define_form')->where(['company_id'=>$v['company_id'],'is_default'=>1,'type'=>'order','form_name'=>$val['form_name']])->getField('form_id'))
				{
					$addFaultField[] = $val;
				}

			}
			getCrmDbModel('define_form')->addAll($addFaultField);


			$fault_field = C('CRMFIELDS.ANALYSIS');

			$addFaultField = [];

			foreach($fault_field as $key=>&$val)
			{
				$val['company_id'] = str_replace($val['company_id'],$v['company_id'],$val['company_id']);

				if(!getCrmDbModel('define_form')->where(['company_id'=>$v['company_id'],'is_default'=>1,'type'=>'analysis','form_name'=>$val['form_name']])->getField('form_id'))
				{
					$addFaultField[] = $val;
				}

			}
			getCrmDbModel('define_form')->addAll($addFaultField);


			$fault_field = C('CRMFIELDS.COMPETITOR');

			$addFaultField = [];

			foreach($fault_field as $key=>&$val)
			{
				$val['company_id'] = str_replace($val['company_id'],$v['company_id'],$val['company_id']);

				if(!getCrmDbModel('define_form')->where(['company_id'=>$v['company_id'],'is_default'=>1,'type'=>'competitor','form_name'=>$val['form_name']])->getField('form_id'))
				{
					$addFaultField[] = $val;
				}

			}
			getCrmDbModel('define_form')->addAll($addFaultField);


			$fault_field = C('CRMFIELDS.CONTRACT');

			$addFaultField = [];

			foreach($fault_field as $key=>&$val)
			{
				$val['company_id'] = str_replace($val['company_id'],$v['company_id'],$val['company_id']);

				if(!getCrmDbModel('define_form')->where(['company_id'=>$v['company_id'],'is_default'=>1,'type'=>'contract','form_name'=>$val['form_name']])->getField('form_id'))
				{
					$addFaultField[] = $val;
				}

			}
			getCrmDbModel('define_form')->addAll($addFaultField);
		}

	}

//    拖拽更新表单排序
    public function updateDefinFormSort()
    {
        $ids    = I('post.ids');

        $i = 0;

        $j = I('post.selectSx');

        foreach($ids as $k => $v)
        {
            if($k == 0)
            {
                $data = ['orderby'=>I('post.selectSx')];
            }
            else
            {
                $data = ['orderby'=>++$j];
            }

            getCrmDbModel('define_form')->where(['form_id'=>$v])->save($data);

            $i++;
        }

        $this->ajaxReturn(['errcode'=>0,'msg'=>'ok']);
    }

	//获取沟通类型的自定义回复列表
	public function getCmncateReply()
	{
		$cmncate_id = I('post.cmncate_id');

		$reply_field = getCrmLanguageData('reply_content');

		$cmncate_reply = getCrmDbModel('communicate_reply')->field(['*',$reply_field])->where(['company_id'=>$this->_company_id,'cmncate_id'=>$cmncate_id,'closed'=>0])->select();

		$html = '<option value="">'.L("CUSTOM_REPLY").'</option>';

		foreach($cmncate_reply as $key=>$val)
		{
			$html .= '<option value="'.$val['reply_id'].'">'.$val['reply_content'].'</option>';
		}

		$this->ajaxReturn(['errcode'=>0,'msg'=>'ok','html'=>$html]);
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

		$product_type_field = getCrmLanguageData('type_name');

		$html = '';

		$proKey = 1;

		foreach($product as $key=>&$val)
		{
			$thisProduct = getCrmDbModel('product')->where(['company_id'=>$this->_company_id,'isvalid'=>1,'product_id'=>$val['product_id']])->field('product_img,type_id')->find();

			$detail = CrmgetCrmDetailList('product',$val['product_id'],$this->_company_id);

			$type_name = getCrmDbModel('product_type')->where(['company_id'=>$this->_company_id,'type_id'=>$thisProduct['type_id']])->getField($product_type_field);

			$html .= '<tr data-id="'.$val['product_id'].'" class="checkedPro"><input type="hidden" name="contractPro['.$proKey.'][product_id]" value="'.encrypt($val['product_id'],'PRODUCT').'" />';

			$html .= '<td>'.$detail['name'].'</td>';

			$html .= '<td>'.$type_name.'</td>';

			$html .= '<td>'.number_format($detail['list_price'],2).'</td>';

			$html .= '<td><input type="number" name="contractPro['.$proKey.'][unit_price]" class="w50 proInput proUnitPrice" onkeyup="value=value.replace(/[^\d.]/g,\'\')" /></td>';

			$html .= '<td><input type="number" name="contractPro['.$proKey.'][num]" class="w50 proInput proContractNum" onkeyup="this.value=this.value.replace(/\D/g,\'\')" onafterpaste="this.value=this.value.replace(/\D/g,\'\')" /></td>';

			$html .= '<td><span class="proTotalPrice">0.00</span><input type="hidden" class="proTotalPrice" name="contractPro['.$proKey.'][total_price]"/></td>';

			$html .= '</tr>';

			$proKey ++;
		}

		$this->ajaxReturn(['errcode'=>0,'msg'=>'ok','opportunity'=>$opportunity_detail,'product'=>$product_arr,'html'=>$html,'proKey'=>$proKey]);
	}


	public function getProductList($id = '') //动态获取产品
	{
		$type_id = decrypt($id,'PRODUCT');

		$checkedPro = I('post.checkedPro');

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

		$keyword = I('post.keyword') ? I('post.keyword') : '';

		if($keyword)
		{
			$keywordField = CrmgetDefineFormField($this->_company_id,'product','name,product_num',$keyword);

			$field['product_id'] = $keywordField ? ['in',$keywordField] : '0';
		}

		$count = getCrmDbModel('product')->where($field)->count();

		$Page = new \Think\Page($count, 15);

		if(strlen($Page->show()) > 16) $this->assign('page', $Page->show()); // 赋值分页输出

		$product = getCrmDbModel('product')->where($field)->limit($Page->firstRow, $Page->listRows)->select();

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

				$html .= '<tr>';

				if(in_array($val['product_id'],$checkedPro))
				{
					$html .= '<td><input type="checkbox" value="'.$val['product_id'].'" checked name="product[]" /></td>';
				}
				else
				{
					$html .= '<td><input type="checkbox" value="'.$val['product_id'].'" name="product[]" /></td>';
				}

				$html .= '<td>'.$val['type_name'].'</td>';

				if($val['product_img'])
				{
					$html .= '<td><img src="'.$val['product_img'].'" width="40" height="40" /></td>';
				}
				else
				{
					$html .= '<td><img src="/public/crm/img/default_pro.png" width="40" height="40" /></td>';
				}

				$html .= '<td>'.$val['name'].'</td>';

				$html .= '<td>'.$val['product_num'].'</td>';

				$html .= '<td>'.$val['list_price'].'</td> ';

				$html .= '</tr>';
			}

		}else
		{
			$html = '<tr class="nodata center"><td colspan="11"><p><i class="iconfont icon-nothing fts20"></i></p><p>'.L('NO_DATA').'</p></td></tr>';
		}
		//var_dump($html);die;
		$this->ajaxReturn(['errcode'=>0,'msg'=>'ok','html'=>$html,'page'=>$Page->show()]);

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

	public function addOrderProduct()
	{
		$product_id = I('post.product');

		$order_id = decrypt(I('post.order_id'),'ORDER');

		if($order_id)
		{
			$order = getCrmDbModel('order')->where(['company_id'=>$this->_company_id,'order_id'=>$order_id,'isvalid'=>'1'])->find();
		}

		$html = '';

		$product_type_field = getCrmLanguageData('type_name');

		foreach($product_id as $key=>$val)
		{
			$product = getCrmDbModel('product')->where(['company_id'=>$this->_company_id,'product_id'=>$val,'isvalid'=>'1','closed'=>0])->find();

			$productform = getCrmDbModel('define_form')->where(['company_id'=>$this->_company_id,'closed' => 0,'type'=>'product'])->order('orderby asc')->select();

			foreach($productform as $k=>&$v)
			{
				if(in_array($v['form_type'],['radio','select','checkbox','select_text']))
				{
					$v['option'] = explode('|',$v['form_option']);
				}

				$product_detail = getCrmDbModel('product_detail')->where(['company_id'=>$this->_company_id,'form_id'=>$v['form_id'],'product_id'=>$val])->find();

				$product[$v['form_name']] = $product_detail['form_content'];

				$product_type = getCrmDbModel('product_type')->field(['*',$product_type_field])->where(['company_id'=>$this->_company_id,'type_id'=>$product['type_id']])->find();

				$product['type_name'] = $product_type['type_name'];

			}

			if($order_id)
			{
				$order_pro = getCrmDbModel('order_product')->where(['company_id'=>$this->_company_id,'order_id'=>$order_id,'product_id'=>$val])->find();

				$html .= '<tr><input type="hidden" name="orderPro['.$key.'][product_id]" value="'.encrypt($val,'PRODUCT').'" /><input type="hidden" name="orderPro['.$key.'][customer_id]" value="'.encrypt($order['customer_id'],'CUSTOMER').'" />';

				$html .= '<td>'.$product['name'].'</td>';

				$html .= '<td>'.$product['type_name'].'</td>';

				$html .= '<td>'.number_format($product['list_price'],2).'</td>';

				$html .= '<td><input type="number" name="orderPro['.$key.'][unit_price]" value="'.$order_pro['unit_price'].'" class="w50 proInput proUnitPrice" onkeyup="value=value.replace(/[^\d.]/g,\'\')" /></td>';

				$html .= '<td><input type="number" name="orderPro['.$key.'][num]" value="'.$order_pro['num'].'" class="w50 proInput proOrderNum" onkeyup="this.value=this.value.replace(/\D/g,\'\')" onafterpaste="this.value=this.value.replace(/\D/g,\'\')" /></td>';

				$html .= '<td><span class="proTotalPrice">'.$order_pro['total_price'].'</span><input type="hidden" value="'.$order_pro['total_price'].'" class="proTotalPrice" name="orderPro['.$key.'][total_price]"/></td>';

				$html .= '</tr>';

			}else{

				$html .= '<tr><input type="hidden" name="orderPro['.$key.'][product_id]" value="'.encrypt($val,'PRODUCT').'" />';

				$html .= '<td>'.$product['name'].'</td>';

				$html .= '<td>'.$product['type_name'].'</td>';

				$html .= '<td>'.number_format($product['list_price'],2).'</td>';

				$html .= '<td><input type="number" name="orderPro['.$key.'][unit_price]" class="w50 proInput proUnitPrice" onkeyup="value=value.replace(/[^\d.]/g,\'\')" /></td>';

				$html .= '<td><input type="number" name="orderPro['.$key.'][num]" class="w50 proInput proOrderNum" onkeyup="this.value=this.value.replace(/\D/g,\'\')" onafterpaste="this.value=this.value.replace(/\D/g,\'\')" /></td>';

				$html .= '<td><span class="proTotalPrice">0.00</span><input type="hidden" class="proTotalPrice" name="orderPro['.$key.'][total_price]"/></td>';

				$html .= '</tr>';

			}

		}

		$html .= '<tr>';

		if($order_id)
		{


			$orderform = getCrmDbModel('define_form')->where(['company_id'=>$this->_company_id,'closed' => 0,'type'=>'order','form_name'=>'price'])->order('orderby asc')->select();

			foreach($orderform as $k=>&$v)
			{
				if(in_array($v['form_type'],['radio','select','checkbox','select_text']))
				{
					$v['option'] = explode('|',$v['form_option']);
				}

				$order_detail = getCrmDbModel('order_detail')->where(['company_id'=>$this->_company_id,'form_id'=>$v['form_id'],'order_id'=>$order_id])->find();

				$order[$v['form_name']] = $order_detail['form_content'];
			}

			$html .= '<td colspan="8">'.L("TOTAL").'：<span class="orderTotalPrice">'.$order['price'].'</span></td>';
		}
		else
		{
			$html .= '<td colspan="8">'.L("TOTAL").'：<span class="orderTotalPrice">0.00</span></td>';
		}

		$html .= '</tr>';

		$this->ajaxReturn(['errcode'=>0,'msg'=>'ok','html'=>$html]);
	}

	public function addOpportunityProduct()
	{
		$product_id = I('post.product');

		$checkedPro = I('post.checkedPro');

		$proKey = I('post.proKey');

		$html = '';

		foreach($product_id as $key=>$val)
		{
			if(!in_array($val,$checkedPro))
			{
				$product = getCrmDbModel('product')->where(['company_id'=>$this->_company_id,'product_id'=>$val,'isvalid'=>'1','closed'=>0])->find();

				$productform = getCrmDbModel('define_form')->where(['company_id'=>$this->_company_id,'closed' => 0,'type'=>'product'])->order('orderby asc')->select();

				$product_type_field = getCrmLanguageData('type_name');

				foreach($productform as $k=>&$v)
				{
					if(in_array($v['form_type'],['radio','select','checkbox','select_text']))
					{
						$v['option'] = explode('|',$v['form_option']);
					}

					$product_detail = getCrmDbModel('product_detail')->where(['company_id'=>$this->_company_id,'form_id'=>$v['form_id'],'product_id'=>$val])->find();

					$product[$v['form_name']] = $product_detail['form_content'];

					$product_type = getCrmDbModel('product_type')->field(['*',$product_type_field])->where(['company_id'=>$this->_company_id,'type_id'=>$product['type_id']])->find();

					$product['type_name'] = $product_type['type_name'];

				}

				$html .= '<tr data-id="'.$val.'" class="checkedPro"><input type="hidden" name="opportunityPro['.$proKey.'][product_id]" value="'.encrypt($val,'PRODUCT').'" />';

				$html .= '<td>'.$product['product_num'].'</td>';

				$html .= '<td>'.$product['name'].'</td>';

				$html .= '<td>'.$product['type_name'].'</td>';

				$html .= '<td>'.number_format($product['list_price'],2).'</td>';

				$html .= '</tr>';

				$proKey ++;
			}
		}

		$this->ajaxReturn(['errcode'=>0,'msg'=>'ok','html'=>$html,'proKey'=>$proKey]);
	}

	public function addContractProduct()
	{
		$product_id = I('post.product');

		$contract_id = decrypt(I('post.contract_id'),'CONTRACT');

		$checkedPro = I('post.checkedPro');

		$proKey = I('post.proKey');

		if($contract_id)
		{
			$contract = getCrmDbModel('contract')->where(['company_id'=>$this->_company_id,'contract_id'=>$contract_id,'isvalid'=>'1'])->find();
		}

		$html = '';

		foreach($product_id as $key=>$val)
		{
			if(!in_array($val,$checkedPro))
			{
				$product = getCrmDbModel('product')->where(['company_id'=>$this->_company_id,'product_id'=>$val,'isvalid'=>'1','closed'=>0])->find();

				$productform = getCrmDbModel('define_form')->where(['company_id'=>$this->_company_id,'closed' => 0,'type'=>'product'])->order('orderby asc')->select();

				$product_type_field = getCrmLanguageData('type_name');

				foreach($productform as $k=>&$v)
				{
					if(in_array($v['form_type'],['radio','select','checkbox','select_text']))
					{
						$v['option'] = explode('|',$v['form_option']);
					}

					$product_detail = getCrmDbModel('product_detail')->where(['company_id'=>$this->_company_id,'form_id'=>$v['form_id'],'product_id'=>$val])->find();

					$product[$v['form_name']] = $product_detail['form_content'];

					$product_type = getCrmDbModel('product_type')->field(['*',$product_type_field])->where(['company_id'=>$this->_company_id,'type_id'=>$product['type_id']])->find();

					$product['type_name'] = $product_type['type_name'];
				}

				if($contract_id)
				{
					$contract_pro = getCrmDbModel('contract_product')->where(['company_id'=>$this->_company_id,'contract_id'=>$contract_id,'product_id'=>$val])->find();

					$html .= '<tr data-id="'.$val.'" class="checkedPro"><input type="hidden" name="contractPro['.$proKey.'][product_id]" value="'.encrypt($val,'PRODUCT').'" /><input type="hidden" name="contractPro['.$proKey.'][customer_id]" value="'.encrypt($contract['customer_id'],'CUSTOMER').'" />';

					$html .= '<td>'.$product['name'].'</td>';

					$html .= '<td>'.$product['type_name'].'</td>';

					$html .= '<td>'.number_format($product['list_price'],2).'</td>';

					$html .= '<td><input type="number" name="contractPro['.$proKey.'][unit_price]" value="'.$contract_pro['unit_price'].'" class="w50 proInput proUnitPrice" onkeyup="value=value.replace(/[^\d.]/g,\'\')" /></td>';

					$html .= '<td><input type="number" name="contractPro['.$proKey.'][num]" value="'.$contract_pro['num'].'" class="w50 proInput proContractNum" onkeyup="this.value=this.value.replace(/\D/g,\'\')" onafterpaste="this.value=this.value.replace(/\D/g,\'\')" /></td>';

					$html .= '<td><span class="proTotalPrice">'.$contract_pro['total_price'].'</span><input type="hidden" value="'.$contract_pro['total_price'].'" class="proTotalPrice" name="contractPro['.$proKey.'][total_price]"/></td>';

					$html .= '</tr>';
				}
				else
				{
					$html .= '<tr data-id="'.$val.'" class="checkedPro"><input type="hidden" name="contractPro['.$proKey.'][product_id]" value="'.encrypt($val,'PRODUCT').'" />';

					$html .= '<td>'.$product['name'].'</td>';

					$html .= '<td>'.$product['type_name'].'</td>';

					$html .= '<td>'.number_format($product['list_price'],2).'</td>';

					$html .= '<td><input type="number" name="contractPro['.$proKey.'][unit_price]" class="w50 proInput proUnitPrice" onkeyup="value=value.replace(/[^\d.]/g,\'\')" /></td>';

					$html .= '<td><input type="number" name="contractPro['.$proKey.'][num]" class="w50 proInput proContractNum" onkeyup="this.value=this.value.replace(/\D/g,\'\')" onafterpaste="this.value=this.value.replace(/\D/g,\'\')" /></td>';

					$html .= '<td><span class="proTotalPrice">0.00</span><input type="hidden" class="proTotalPrice" name="contractPro['.$proKey.'][total_price]"/></td>';

					$html .= '</tr>';
				}

				$proKey ++;
			}
		}

		// $html .= '<tr>';

		// if($contract_id)
		// {
			// $contractform = getCrmDbModel('define_form')->where(['company_id'=>$this->_company_id,'closed' => 0,'type'=>'contract','form_name'=>'money'])->order('orderby asc')->select();

			// foreach($contractform as $k=>&$v)
			// {
				// if(in_array($v['form_type'],['radio','select','checkbox','select_text']))
				// {
					// $v['option'] = explode('|',$v['form_option']);
				// }

				// $contract_detail = getCrmDbModel('contract_detail')->where(['company_id'=>$this->_company_id,'form_id'=>$v['form_id'],'contract_id'=>$contract_id])->find();

				// $contract[$v['form_name']] = $contract_detail['form_content'];
			// }

			// $html .= '<td colspan="8">总计：<span class="contractTotalPrice">'.$contract['money'].'</span></td>';
		// }
		// else
		// {
			// $html .= '<td colspan="8">总计：<span class="contractTotalPrice">0.00</span></td>';
		// }

		// $html .= '</tr>';

		$this->ajaxReturn(['errcode'=>0,'msg'=>'ok','html'=>$html,'proKey'=>$proKey]);
	}

	public function getMemberList() //动态获取用户
	{

		$field['company_id'] = $this->_company_id;

		$field['isvalid'] = '1';

		$field['closed'] = 0;

		$field['type'] = 1;

		$field['feelec_opened'] = 10;

		$role = D('Role')->where(['company_id'=>$this->_company_id])->field('role_id,role_name')->fetchAll();

        $group = D('Group')->where(['company_id'=>$this->_company_id])->field('group_id,group_name')->fetchAll();

		if($keyword = I('get.sMemberKeyword'))
		{
			$field['name|mobile']= ['like','%'.$keyword.'%'];

			$this->assign('keyword', $keyword);
		}

		$memberCount =  M('Member')->where($field)->count();

		$memberPage = new \Think\Page($memberCount, 10);

		$list =  M('Member')->where($field)->limit($memberPage->firstRow, $memberPage->listRows)->select();

		$html = '';

		if($list)
		{
			foreach($list as $key=>&$val)
			{
				$html .= '<tr>';

				$html .= '<td><input type="radio" value="'.encrypt($val['member_id'],'MEMBER').'" name="member" lay-skin="primary" /></td>';

				$html .= '<td>'.$val['name'].'</td>';

				$html .= '<td>'.$val['email'].'</td>';

				$html .= '<td>'.$val['mobile'].'</td>';

				$html .= '<td>'.CrmgetMemberGroupName($group,$val['group_id']).'</td>';

				$html .= '<td>'.$role[$val['role_id']]['role_name'].'</td> ';

				$html .= '</tr>';
			}

		}else
		{
			$html = '<tr class="nodata center"><td colspan="11"><p><i class="iconfont icon-nothing fts20"></i></p><p>'.L('NO_DATA').'</p></td></tr>';
		}

		$this->ajaxReturn(['errcode'=>0,'msg'=>'ok','html'=>$html,'page'=>$memberPage->show()]);

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

	public function getCustomerList() //动态获取客户
	{
		$field['company_id'] = $this->_company_id;

		$field['isvalid'] = 1;

		$getCustomerAuth = D('CrmSelectField')->getCustomerAuth($this->member,$this->_company_id,$this->member['member_id']);

		$field['member_id'] = $getCustomerAuth['memberRoleArr'];

		if($highKeyword = I('get.highKeyword'))
		{
			$highKeyword['condition'] = 1;

			$getCustomerAuth = D('CrmSelectField')->getCustomerAuth($this->member,$this->_company_id,$this->member['member_id']);

			$memberRoleArr = $getCustomerAuth['memberRoleArr'];

			$customerHighKey = D('CrmHighKeyword')->customerHighKey($this->_company_id,$highKeyword,$memberRoleArr);

			if($customerHighKey['field'])
			{
				$field = array_merge($field,$customerHighKey['field']);
			}

			$this->assign('highKeyword',$highKeyword);
		}

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

		if($selectCustomerlist)
		{
			foreach($selectCustomerlist as $key => &$val)
			{
				$val['detail'] = CrmgetCrmDetailList('customer',$val['customer_id'],$this->_company_id,'name,phone');

				//$val['contacter'] = CrmgetCrmDetailList('contacter',$val['first_contact_id'],$this->_company_id,'name,phone');

				$html .= '<tr>';

				$html .= '<td><input type="radio" value="'.$val['customer_id'].'" data-name="'.$val['detail']['name'].'" name="selectCustomer" lay-skin="primary" /></td>';

				$html .= '<td>'.$val['customer_prefix'].$val['customer_no'].'</td>';

				$html .= '<td>'.$val['detail']['name'].'</td>';

				//$html .= '<td>'.$val['contacter']['name'].'</td>';

				$html .= '<td>'.$val['detail']['phone'].'</td>';

				$html .= '<td>'.getDates($val['createtime']).'</td> ';

				$html .= '</tr>';
			}
		}
		else
		{
			$html = '<tr class="nodata center"><td colspan="11"><p><i class="iconfont icon-nothing fts20"></i></p><p>'.L('NO_DATA').'</p></td></tr>';
		}

		$this->ajaxReturn(['errcode'=>0,'msg'=>'ok','html'=>$html,'page'=>$selectCustomerPage->show()]);

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

					$html .=	'<input type="checkbox" name="contract_relation[]" value="'.$val['contract_id'].'"/>';

					$html .=	'<div class="feeldesk-option">';

					$html .=	'<span class="feeldesk-option-title">'.$contract_detail['name'].'</span>';

					$html .=	'<span class="iconfont icon-xuanze"></span>';

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

				$html .=	'<input type="checkbox" name="order_relation[]" value="'.$val['order_id'].'"/>';

				$html .=	'<div class="feeldesk-option">';

				$html .=	'<span class="feeldesk-option-title">'.$contract_detail['name'].'</span>';

				$html .=	'<span class="iconfont icon-xuanze"></span>';

				$html .=	'</div>';

				$html .=	'</li>';
			}
		}
		else
		{
			$html =	'<li><span class="feeldesk-option-title">'.L('NO_DATA').'</span></li>';
		}

		$this->ajaxReturn(['errcode'=>0,'msg'=>'ok','order'=>$order,'html'=>$html]);
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

		$html .= '<option value="">'.L('SELECT_PAYMENT').'</option>';

		$receiptJson = [];

		if($receipt)
		{
			foreach($receipt as $key=>$val)
			{
				$html .= '<option value="'.$val['receipt_id'].'" data-money="'.$val['receipt_money'].'">'.$val['receipt_prefix'].$val['receipt_no'].' --- '.L('RECEIVE_PAYMENT').$val['receipt_money'].'</option>';

				$receiptJson[$key]['id'] = $val['receipt_id'];

				$receiptJson[$key]['name'] = $val['receipt_prefix'].$val['receipt_no'].' --- '.L('RECEIVE_PAYMENT').$val['receipt_money'];
			}
		}

		$this->ajaxReturn(['errcode'=>0,'msg'=>'ok','html'=>$html,'receiptJson'=>$receiptJson]);
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

	//获取发票信息详情
	public function getInvoiceInfoDetail()
	{
		$invoiceinfo_id = I('post.invoiceinfo_id');

		$invoiceinfo = getCrmDbModel('customer_invoiceinfo')->where(['company_id'=>$this->_company_id,'invoiceinfo_id'=>$invoiceinfo_id,'isvalid'=>1])->find();

		$this->ajaxReturn(['errcode'=>0,'msg'=>'ok','data'=>$invoiceinfo]);
	}

	//单独修改主表内容
	public function updateFormInfo()
	{
		if(IS_POST)
        {
			if($this->member)
			{
				$id = I('post.id');

				$type = I('post.type');

				$content = I('post.content');

				$form_name = I('post.form_name');

				if(is_array($content))
				{
					$content = implode(',',$content);
				}

				$info = getCrmDbModel($type)->where(['company_id'=>$this->_company_id,$type.'_id'=>$id])->getField($type.'_id');

				if($info)
				{
					$data[$type.'_id'] = $id;

					$data[$form_name] = $content;

					$save = getCrmDbModel($type)->save($data);

					if($save === false)
					{
						$this->ajaxReturn(['status'=>0,'msg'=>L('FAIL_TO_EDIT')]);
					}
					else
					{
						if($form_name == 'customer_type')
						{
							$content = $content == 'agent' ? L('DEALER') : L('ORDINARY_CUSTOMER');
						}
						elseif($form_name == 'is_trade')
						{
							$content = $content == 1 ? '<span class="blue8">'.L("DEAL_DONE").'</span>' : '<span class="red1">'.L('UNSOLD').'</span>';
						}

						$this->ajaxReturn(['status'=>2,'msg'=>L('SUCCESSFULLY_MODIFIED'),'data'=>$content]);
					}
				}
				else
				{
					$this->ajaxReturn(['status'=>0,'msg'=>L('INCORRECT_PARAMETERS')]);
				}
			}
			else
			{
				exit('Not Fount 404');
			}
		}
		else
        {
            exit('Not Fount 404');
        }
	}

	//单独修改自定义字段内容
	public function updateFormContent()
	{
		if(IS_POST)
        {
			if($this->member)
			{
				$id = I('post.id');

				$type = I('post.type');

				$content = I('post.content');

				$form_name = I('post.form_name');

				if(is_array($content))
				{
					$content = implode(',',$content);
				}

				$define_form = getCrmDbModel('define_form')->where(['company_id'=>$this->_company_id,'closed' => 0,'type'=>$type,'form_name'=>$form_name])->find();

				if($define_form)
				{
					$define_form_arr[] = $define_form;

					if($define_form['form_type'] == 'region')
					{
						$region = explode(',',$content);

						$form = [$form_name.'_defaultCountry'=>$region[0],$form_name.'_defaultProv'=>$region[1],$form_name.'_defaultCity'=>$region[2],$form_name.'_defaultArea'=>$region[3]];
					}
					else
					{
						$form = [$form_name=>$content];
					}

					$CheckForm = D('CrmDefineForm')->CheckForm($this->_company_id,$form,$type,$this->member,$id,$define_form_arr);

					if($CheckForm['detail'])
					{
						if(!getCrmDbModel($type.'_detail')->where(['company_id'=>$this->_company_id,$type.'_id'=>$id,'form_id'=>$define_form['form_id']])->getField($type.'_id'))
						{
							$data = ['company_id'=>$this->_company_id,$type.'_id'=>$id,'form_id'=>$define_form['form_id']];

							$data['form_content'] = $content;

							if(getCrmDbModel($type.'_detail')->add($data))
							{
								if($define_form['form_type'] == 'region')
								{
									$detail = CrmgetCrmDetailList($type,$id,$this->_company_id,$form_name);

									$content = $detail[$form_name];
								}

								$this->ajaxReturn(['status'=>2,'msg'=>L('SUCCESSFULLY_MODIFIED'),'data'=>$content]);
							}
							else
							{
								$this->ajaxReturn(['status'=>0,'msg'=>L('FAIL_TO_EDIT')]);
							}
						}
						else
						{
							$save = getCrmDbModel($type.'_detail')->where(['company_id'=>$this->_company_id,$type.'_id'=>$id,'form_id'=>$define_form['form_id']])->save(['form_content'=>$content]);

							if($save === false)
							{
								$this->ajaxReturn(['status'=>0,'msg'=>L('FAIL_TO_EDIT')]);
							}
							else
							{
								if($define_form['form_type'] == 'region')
								{
									$detail = CrmgetCrmDetailList($type,$id,$this->_company_id,$form_name);

									$content = $detail[$form_name];
								}

								$this->ajaxReturn(['status'=>2,'msg'=>L('SUCCESSFULLY_MODIFIED'),'data'=>$content]);
							}
						}

					}
					else
					{
						$this->ajaxReturn($CheckForm);
					}
				}
				else
				{
					$this->ajaxReturn(['status'=>0,'msg'=>L('INCORRECT_PARAMETERS')]);
				}
			}
			else
			{
				exit('Not Fount 404');
			}
		}
		else
        {
            exit('Not Fount 404');
        }
	}

	//根据部门id获取用户
	public function getMemberByGroup()
	{
		$group_id = I('post.group_id');

		if(!$group_id)
		{
			$this->ajaxReturn(['errcode'=>0,'msg'=>'ok','data'=>'']);
		}
		else
		{
			$members = M('member')->where(['company_id'=>$this->_company_id,'group_id'=>$group_id,'type'=>1,'closed'=>0,'feelec_opened'=>10])->field('member_id,name,group_id')->select();

			$this->ajaxReturn(['errcode'=>0,'msg'=>'ok','data'=>$members]);
		}
	}

	//根据部门id获取用户
	public function getMemberByGroups()
	{
		$groups = I('post.groups');

		if(!$groups)
		{
			$this->ajaxReturn(['errcode'=>0,'msg'=>'ok','data'=>[]]);
		}
		else
		{
			$field = ['company_id'=>$this->_company_id,'type'=>1,'closed'=>0,'feelec_opened'=>10];

			if(is_array($groups))
			{
				//            查询部门下的用户
				foreach($groups as $k=>$v)
				{
					$members[$k] = M('member')
						->where(["find_in_set('{$v}',group_id)",$field])
						->field('member_id,name,group_id')->select();
				}

				$membersId = [];

				$j = 0;

				foreach($members as $k1=>$v1)
				{
					foreach($v1 as $k2=>$v2)
					{
						if(!in_array($v2['member_id'],$membersId))
						{
							$membersId[$j] = $v2['member_id'];

							$users[$j] = ['member_id'=>$v2['member_id'],'name'=>$v2['name'],'id'=>$v2['member_id']];

							$j ++;
						}
					}
				}

				$this->ajaxReturn(['errcode'=>0,'msg'=>'ok','data'=>$users]);
			}
			else
			{
				$members = M('member')->where(["find_in_set('{$groups}',group_id)",$field])->field('member_id,name,group_id')->select();

				$this->ajaxReturn(['errcode'=>0,'msg'=>'ok','data'=>$members]);
			}
		}
	}

	//根据角色id获取用户
	public function getMemberByRole()
	{
		$role_id = I('post.role_id');

		if(!$role_id)
		{
			$this->ajaxReturn(['errcode'=>0,'msg'=>'ok','data'=>[]]);
		}
		else
		{
			$field = ['company_id'=>$this->_company_id,'type'=>1,'closed'=>0,'feelec_opened'=>10];

			if(is_array($role_id))
			{
				$field['role_id'] = ['in',implode(',',$role_id)];

				$members = M('member')->where($field)->field('member_id,name,role_id')->select();

				foreach ($members as $key => &$val)
				{
					$val['id'] = $val['member_id'];
				}

				$this->ajaxReturn(['errcode'=>0,'msg'=>'ok','data'=>$members]);
			}
			else
			{
				$field['role_id'] = $role_id;

				$members = M('member')->where($field)->field('member_id,name,role_id')->select();

				$this->ajaxReturn(['errcode'=>0,'msg'=>'ok','data'=>$members]);
			}
		}
	}

	public function OnclickCall()
	{
		$phone = I('post.phone');

		if(!isMobile($phone))
		{
			$this->ajaxReturn(['code'=>1,'msg'=>L('MOBILE_FORMAT_ERROR',['mobile'=>$phone])]);
		}

		$callcenter = M('callcenter')->where(['company_id'=>$this->_company_id])->find();

		if($callcenter)
		{
			[$msec, $sec] = explode(' ', microtime());

			$timestamp = (float)sprintf('%.0f', (floatval($msec) + floatval($sec)) * 1000); //毫秒时间戳

			$callcenter_link = M('callcenter_link')->where(['company_id' => $this->_company_id, 'member_id' => $this->member['member_id']])->find();

			if($callcenter_link)
			{
				$userid = $callcenter_link['employee_id'];

				$str = 'appId=' . $callcenter['appid'] . '&timestamp=' . $timestamp . "&userId=" . $userid;

				$sign = base64_encode(hash_hmac("sha1", $str, $callcenter['app_secret'], true));

				$data['appId'] = $callcenter['appid'];

				$data['timestamp'] = $timestamp;

				$data['signature'] = $sign;

				$data['userId'] = $userid;


				$data['call_num'] = $phone;

				$url = $callcenter['api_url'] . 'OnclickCall';

				$return = FeelDeskCurl($url, 'POST', json_encode($data));

				if($return['code'] == 0)
				{
					$result = ['code'=>0,'msg'=>'Call Success'];
				}
				else
				{
					$result = ['code'=>1,'msg'=>$return['message']];
				}
			}
			else
			{
				$result = ['code'=>1,'msg'=>L('UNBOUND_CALL_CENTER_ACCOUNT')];
			}
		}
		else
		{
			$result = ['code'=>1,'msg'=>L('CALLCENTER_TOP')];
		}

		$this->ajaxReturn($result);

	}

	public function getClueList() //动态获取线索
	{
		$field['company_id'] = $this->_company_id;

		$field['isvalid'] = 1;

		if($keyword = I('get.keyword'))
		{
			$keywordField = CrmgetDefineFormField($this->_company_id,'clue','name,phone,company',$keyword);

			$field['clue_id'] = $keywordField ? ['in',$keywordField] : '0';

			$this->assign('keyword', $keyword);
		}

		$getCustomerAuth = D('CrmSelectField')->getCustomerAuth($this->member,$this->_company_id,$this->member['member_id'],'','clue');

		$memberRoleArr = $getCustomerAuth['memberRoleArr'];

		$field['member_id'] = $memberRoleArr;

		$selectClueCount =  getCrmDbModel('Clue')->where($field)->count();

		$selectCluePage = new \Think\Page($selectClueCount, 10);

		$selectCluelist =  getCrmDbModel('Clue')->where($field)->field('clue_id,clue_prefix,clue_no,createtime')->limit($selectCluePage->firstRow, $selectCluePage->listRows)->order('createtime desc')->select();

		$html = '';

		if($selectCluelist)
		{
			foreach($selectCluelist as $key => &$val)
			{
				$val['detail'] = CrmgetCrmDetailList('clue',$val['clue_id'],$this->_company_id,'name,phone,company');

				$html .= '<tr>';

				$html .= '<td><input type="radio" value="'.$val['clue_id'].'" data-name="'.$val['detail']['name'].'" name="selectClue" lay-skin="primary" /></td>';

				$html .= '<td>'.$val['clue_prefix'].$val['clue_no'].'</td>';

				$html .= '<td>'.$val['detail']['name'].'</td>';

				$html .= '<td>'.$val['detail']['phone'].'</td>';

				$html .= '<td>'.$val['detail']['company'].'</td>';

				$html .= '<td>'.getDates($val['createtime']).'</td> ';

				$html .= '</tr>';
			}
		}
		else
		{
			$html = '<tr class="nodata center"><td colspan="11"><p><i class="iconfont icon-nothing fts20"></i></p><p>'.L('NO_DATA').'</p></td></tr>';
		}

		$this->ajaxReturn(['errcode'=>0,'msg'=>'ok','html'=>$html,'page'=>$selectCluePage->show()]);

	}

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
