<?php

namespace Crm\Controller;

use Crm\Common\BasicController;

class AbandonController extends BasicController
{
	protected $AbandonFields = ['abandon_name','name_en','name_jp','closed'];

	public function index()
	{
		$field = getCrmLanguageData('abandon_name');

		$abandon = getCrmDbModel('abandon')->field(['*',$field])->where(['company_id'=>$this->_company_id])->select();

		$this->assign('abandon',$abandon);

		$this->display();
	}

	//创建放弃原因
	public function create()
	{
		if(IS_POST)
		{
			$abandon = $this->checkCreate();

			if($abandon_id = getCrmDbModel('abandon')->add($abandon))
			{
			    $result = ['status'=>2,'msg'=>L('SUBMIT_SUCCESS'),'reloadType'=>'parent','url'=>U('index')];
			}
			else
			{
			    $result = ['status'=>0,'msg'=>L('SUBMIT_FAILED')];
			}

            $this->ajaxReturn($result);
		}
		else
		{
			$this->display();
		}
	}


	private function checkCreate()
	{
		$data = checkFields(I('post.abandon'), $this->AbandonFields);

		if(empty($data['abandon_name'])) $this->ajaxReturn(['status'=>0,'msg'=>L('ENTER_REASON_FOR_GIVING_UP')]);

        $data['createtime'] = NOW_TIME;

        $data['company_id'] = $this->_company_id;

        return $data;
	}


	public function edit($id='')
	{
		$abandon_id = decrypt($id,'ABANDON');

		$where = ['company_id'=>$this->_company_id,'abandon_id'=>$abandon_id];

	    if(!$detail = getCrmDbModel('abandon')->where($where)->find()) $this->error(L('ENTER_REASON_NOT_EXIST'),U('index'));

		if(IS_POST)
		{
			$abandon = $this->checkEdit();

			$save = getCrmDbModel('abandon')->where($where)->save($abandon);

			if($save === false)
			{
			    $result = ['status'=>0,'msg'=>L('UPDATE_FAILED')];
			}
			else
			{
				$result = ['status'=>2,'msg'=>L('UPDATE_SUCCESS'),'reloadType'=>'parent','url'=>U('index')];
			}

            $this->ajaxReturn($result);
		}
		else
		{

			$this->assign('abandon',$detail);

			$this->display();
		}
	}


	private function checkEdit()
	{
		$data = checkFields(I('post.abandon'), $this->AbandonFields);

		if(empty($data['abandon_name'])) $this->ajaxReturn(['status'=>0,'msg'=>L('ENTER_REASON_FOR_GIVING_UP')]);

		if($data['closed'] != 0 || !$data['closed']) $data['closed'] = 1;

        return $data;
	}


	//删除
	public function delete($id='')
	{
	    if(IS_AJAX)
	    {
	        $abandon_id = decrypt($id,'ABANDON');

			$where = ['abandon_id'=>$abandon_id,'company_id'=>$this->_company_id];

			if(getCrmDbModel('abandon')->where($where)->getField('abandon_id'))
			{
				if(getCrmDbModel('abandon')->where($where)->delete())
				{
					$result = ['status'=>2,'msg'=>L('DELETE_SUCCESS'),'url'=>U('index')];
				}
				else
				{
					$result = ['status'=>0,'msg'=>L('DELETE_FAILED')];
				}
			}
			else
			{
				$result = ['status'=>0,'msg'=>L('TYPE_DOES_NOT_EXIST')];
			}

            $this->ajaxReturn($result);
        }
        else
        {
            $this->common->_empty();
        }
    }
}
