<?php

namespace Crm\Controller;

use Crm\Common\BasicController;

class LoseController extends BasicController
{
	protected $LoseFields = ['lose_name','name_en','name_jp','closed'];

	public function index()
	{
		$field = getCrmLanguageData('lose_name');

		$lose = getCrmDbModel('lose')->field(['*',$field])->where(['company_id'=>$this->_company_id])->select();

		$this->assign('lose',$lose);

		$this->display();
	}


	//创建失单原因
	public function create()
	{
		if(IS_POST)
		{
			$lose = $this->checkCreate();

			if($lose_id = getCrmDbModel('lose')->add($lose))
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
		$data = checkFields(I('post.lose'), $this->LoseFields);

		if(empty($data['lose_name'])) $this->ajaxReturn(['status'=>0,'msg'=>L('ENTER_REASON_FOR_GIVING_UP')]);

        $data['createtime'] = NOW_TIME;

        $data['company_id'] = $this->_company_id;

        return $data;
	}


	public function edit($id='')
	{
		$lose_id = decrypt($id,'LOSE');

		$where = ['company_id'=>$this->_company_id,'lose_id'=>$lose_id];

	    if(!$detail = getCrmDbModel('lose')->where($where)->find()) $this->error(L('ENTER_REASON_NOT_EXIST'),U('index'));

		if(IS_POST)
		{
			$lose = $this->checkEdit();

			$save = getCrmDbModel('lose')->where($where)->save($lose);

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
			$this->assign('lose',$detail);

			$this->display();
		}
	}


	private function checkEdit()
	{
		$data = checkFields(I('post.lose'), $this->LoseFields);

		if(empty($data['lose_name'])) $this->ajaxReturn(['status'=>0,'msg'=>L('ENTER_REASON_FOR_GIVING_UP')]);

		if($data['closed'] != 0 || !$data['closed']) $data['closed'] = 1;

        return $data;
	}


	//删除
	public function delete($id='')
	{
	    if(IS_AJAX)
	    {
	        $lose_id = decrypt($id,'LOSE');

			$where = ['lose_id'=>$lose_id,'company_id'=>$this->_company_id];

			if(getCrmDbModel('lose')->where($where)->getField('lose_id'))
			{
				if(getCrmDbModel('lose')->where($where)->delete())
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
