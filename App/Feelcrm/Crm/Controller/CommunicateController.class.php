<?php

namespace Crm\Controller;

use Crm\Common\BasicController;

class CommunicateController extends BasicController
{
	protected $CommunicateFields = ['cmncate_name','name_en','name_jp','closed'];

	protected $CmncateReplyFields = ['reply_content','name_en','name_jp','closed'];

	public function index()
	{
		$field = getCrmLanguageData('cmncate_name');

		$communicate = getCrmDbModel('communicate')->field(['*',$field])->where(['company_id'=>$this->_company_id])->select();

		$this->assign('communicate',$communicate);

		$this->display();
	}



	//创建银行账户信息
	public function create()
	{
		if(IS_POST)
		{
			$communicate = $this->checkCreate();

			if($bank_id = getCrmDbModel('communicate')->add($communicate))
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
		$data = checkFields(I('post.cmncatesite'), $this->CommunicateFields);

		if(empty($data['cmncate_name'])) $this->ajaxReturn(['status'=>0,'msg'=>L('ENTER_TYPE_NAME')]);

        $data['create_time'] = NOW_TIME;

        $data['company_id'] = $this->_company_id;

        return $data;
	}

	public function edit($id='')
	{
		$cmncate_id = decrypt($id,'COMMUNICATE');

		$where = ['company_id'=>$this->_company_id,'cmncate_id'=>$cmncate_id];

	    if(!$detail = getCrmDbModel('communicate')->where($where)->find()) $this->error(L('TYPE_DOES_NOT_EXIST'),U('index'));

		if(IS_POST)
		{
			$communicate = $this->checkEdit();

			$save = getCrmDbModel('communicate')->where($where)->save($communicate);

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

			$this->assign('communicate',$detail);

			$this->display();
		}
	}

	 private function checkEdit()
	{
		$data = checkFields(I('post.cmncatesite'), $this->CommunicateFields);

		if(empty($data['cmncate_name'])) $this->ajaxReturn(['status'=>0,'msg'=>L('ENTER_TYPE_NAME')]);

		if(($data['closed'] != 0 || $data['closed'] != '0') && !$data['closed']) $data['closed'] = 1;

        return $data;
	}

	//删除
	public function delete($id='')
	{
	    if(IS_AJAX)
	    {
	        $cmncate_id = decrypt($id,'COMMUNICATE');

			$where = ['cmncate_id'=>$cmncate_id,'company_id'=>$this->_company_id];

			if(getCrmDbModel('communicate')->where($where)->getField('cmncate_id'))
			{
				if(getCrmDbModel('communicate')->where($where)->delete())
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


	//自定义回复
	public function reply($id = '')
	{
		if(!$id)
		{
			$this->error(L('TYPE_DOES_NOT_EXIST'),U('index'));

			die;
		}

		$cmncate_id = decrypt($id,'COMMUNICATE');

		$reply_field = getCrmLanguageData('reply_content');

		$cmncate_reply = getCrmDbModel('communicate_reply')->field(['*',$reply_field])->where(['company_id'=>$this->_company_id,'cmncate_id'=>$cmncate_id])->select();

		$cmncate_field = getCrmLanguageData('cmncate_name');

		foreach($cmncate_reply as $key=>&$val)
		{
			$cmncate_name = getCrmDbModel('communicate')->where(['company_id'=>$this->_company_id,'cmncate_id'=>$val['cmncate_id']])->getField($cmncate_field);

			$val['cmncate_name'] = $cmncate_name;
		}

		$this->assign('cmncate_id',$cmncate_id);

		$this->assign('cmncate_reply',$cmncate_reply);

		$this->display();
	}

	//添加自定义回复
	public function add_reply($id = '')
	{

		$cmncate_id = decrypt($id,'COMMUNICATE');

		if(IS_POST)
		{
			$cmncateReply = $this->checkReplyCreate();

			$cmncateReply['cmncate_id'] = $cmncate_id;

			if($cmncate_id){

				if($reply_id = getCrmDbModel('communicate_reply')->add($cmncateReply))
				{
					$result = ['status'=>2,'msg'=>L('SUBMIT_SUCCESS'),'reloadType'=>'parent','url'=>U('communicate/reply',['id'=>$id])];
				}
				else
				{
					$result = ['status'=>0,'msg'=>L('SUBMIT_FAILED')];
				}

			}
			else
			{
				$result = ['status'=>0,'msg'=>L('SUBMIT_FAILED')];
			}

            $this->ajaxReturn($result);
		}
		else
		{
			$cmncate_field = getCrmLanguageData('cmncate_name');

			$cmncate_name = getCrmDbModel('communicate')->where(['company_id'=>$this->_company_id,'cmncate_id'=>$cmncate_id])->getField($cmncate_field);

			$this->assign('cmncate_name',$cmncate_name);

			$this->assign('cmncate_id',$cmncate_id);

			$this->display();
		}
	}

	private function checkReplyCreate()
	{
		$data = checkFields(I('post.cmncatereply'), $this->CmncateReplyFields);

		if(empty($data['reply_content'])) $this->ajaxReturn(['status'=>0,'msg'=>L('ENTER_CONTENT')]);

        $data['create_time'] = NOW_TIME;

        $data['company_id'] = $this->_company_id;

        return $data;
	}

	//修改自定义回复
	public function edit_reply($id = '',$cmncate_id = '')
	{

		$reply_id = decrypt($id,'COMMUNICATE');

		$cmncate_id = decrypt($cmncate_id,'COMMUNICATE');

		$where = ['company_id'=>$this->_company_id,'cmncate_id'=>$cmncate_id,'reply_id'=>$reply_id];

	    if(!$detail = getCrmDbModel('communicate_reply')->where($where)->find()) $this->error(L('CONTENT_DOES_NOT_EXIST'),U('reply',['id'=>encrypt($cmncate_id,'COMMUNICATE')]));

		if(IS_POST)
		{
			$cmncateReply = $this->checkReplyEdit();

			$save = getCrmDbModel('communicate_reply')->where($where)->save($cmncateReply);

			if($save === false)
			{
			    $result = ['status'=>0,'msg'=>L('UPDATE_FAILED')];
			}
			else
			{
				$result = ['status'=>2,'msg'=>L('UPDATE_SUCCESS'),'reloadType'=>'parent','url'=>U('reply',['id'=>encrypt($cmncate_id,'COMMUNICATE')])];
			}

            $this->ajaxReturn($result);

		}
		else
		{
			$cmncate_field = getCrmLanguageData('cmncate_name');

			$cmncate_name = getCrmDbModel('communicate')->where(['company_id'=>$this->_company_id,'cmncate_id'=>$detail['cmncate_id']])->getField($cmncate_field);

			$this->assign('cmncate_name',$cmncate_name);

			$this->assign('cmncate_reply',$detail);

			$this->display();
		}
	}

	private function checkReplyEdit()
	{
		$data = checkFields(I('post.cmncatereply'), $this->CmncateReplyFields);

		if(empty($data['reply_content'])) $this->ajaxReturn(['status'=>0,'msg'=>L('ENTER_CONTENT')]);

        return $data;
	}

	//删除自定义回复
	public function del_reply($id='',$cmncate_id = '')
	{
	    if(IS_AJAX)
	    {
			$reply_id = decrypt($id,'COMMUNICATE');

			$cmncate_id = decrypt($cmncate_id,'COMMUNICATE');

			$where = ['cmncate_id'=>$cmncate_id,'company_id'=>$this->_company_id,'reply_id'=>$reply_id];

			if(getCrmDbModel('communicate_reply')->where($where)->getField('reply_id'))
			{
				if(getCrmDbModel('communicate_reply')->where($where)->delete())
				{
					$result = ['status'=>2,'msg'=>L('DELETE_SUCCESS'),'url'=>U('reply',['id'=>encrypt($cmncate_id,'COMMUNICATE')])];
				}
				else
				{
					$result = ['status'=>0,'msg'=>L('DELETE_FAILED')];
				}
			}
			else
			{
				$result = ['status'=>0,'msg'=>L('CONTENT_DOES_NOT_EXIST')];
			}

            $this->ajaxReturn($result);
        }
        else
        {
            $this->common->_empty();
        }
    }
}
