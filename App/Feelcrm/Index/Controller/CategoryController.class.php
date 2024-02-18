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

use Index\Common\BasicController;

class CategoryController extends  BasicController
{
	protected $_filter = ['category_id','parent_id','name','name_en','name_jp'];

	/* 分类列表 */
	public function index()
	{
        $categoryName = D('Category')->getNameByLang('name');

        $category = D('Category')->where(['company_id'=>$this->_company_id])
            ->field("company_id,category_id,name,name_en,name_jp,parent_id,create_time,{$categoryName}")
            ->select();

        foreach($category as $k=>&$v)
        {
			$v['id'] = encrypt($v['category_id'],'categoryId');

            $v['name_cn'] = $v['name'];
        }

        $this->assign('categoryJson',json_encode($category));

		$this->display();
	}



    public function getCategoryTree($data,$parent_id)
    {
        $tree = '';

        foreach($data as $k => $v)
        {
            if($v['parent_id'] == $parent_id)
            {
                $v['parent_id'] = $this->getCategoryTree($data, $v['value']);

                $tree[] = $v;

                //unset($data[$k]);
            }
        }

        return $tree;
    }



	/* 添加分类 */
	public function create()
	{
        $data = $this->checkCreate();

        $category_id = M('category')->add($data);

        if($category_id)
        {
            $result = ['errcode'=>0,'msg'=>L('SUBMIT_SUCCESS'),'category_id'=>$category_id,'id'=>encrypt($category_id,'categoryId')];
        }
        else
        {
            $result = ['errcode'=>1,'msg'=>L('SUBMIT_FAILED')];
        }

        $this->ajaxReturn($result);
	}



	private function checkCreate()
	{
		$category = checkFields(I('post.category'), $this->_filter);

		if(empty($category['name']))
		{
			$this->ajaxReturn(['status'=>0,'msg'=>L('ENTER_CATEGORY_NAME')]);
		}

		if($this->_lang['en_auth'] == 10 && empty($category['name_en']))
		{
			$this->ajaxReturn(['status'=>0,'msg'=>L('ENTER_EN_NAME')]);
		}

		if($this->_lang['jp_auth'] == 10 && empty($category['name_jp']))
		{
			$this->ajaxReturn(['status'=>0,'msg'=>L('ENTER_JP_NAME')]);
		}

        $category['company_id'] = $this->_company_id;

        $category['create_time'] = NOW_TIME;

		return $category;
	}



	/* 编辑文章 */
	public function edit($id = '')
	{
        $id = decrypt($id,'categoryId');

		if(!$category_id = M('category')->where(['company_id'=>$this->_company_id,'category_id'=>$id])->getField('category_id'))
		{
			$this->ajaxReturn(['errcode'=>1,'msg'=>L('CATEGORY_NOT')]);
		}
        else
        {
            $data = checkFields(I('post.category'),$this->_filter);

            if(isset($data['name']) && empty($data['name']))
            {
                $this->ajaxReturn(['errcode'=>1,'msg'=>L('ENTER_CATEGORY_NAME')]);
            }
            else if(isset($data['name_en']) && empty($data['name_en']))
            {
                $this->ajaxReturn(['errcode'=>1,'msg'=>L('ENTER_EN_NAME')]);
            }
            else if(isset($data['name_jp']) && empty($data['name_jp']))
            {
                $this->ajaxReturn(['errcode'=>1,'msg'=>L('ENTER_JP_NAME')]);
            }
            else
            {
                $saveResult = M('category')->where(['category_id'=>$category_id,'company_id'=>$this->_company_id])->save($data);

                if($saveResult === false)
                {
                    $result = ['errcode'=>1,'msg'=>L('UPDATE_FAILED')];
                }
                else
                {
                    $result = ['errcode'=>0,'msg'=>'ok!'];
                }

                $this->ajaxReturn($result);
            }
        }
	}



    /* 删除文章分类 */
	public function delete() 
	{
		$category_id = decrypt(I("get.id"),'categoryId');

        $field = ['company_id'=>$this->_company_id,'category_id'=>$category_id];

        $category = M('category')->where($field)->field('category_id,parent_id')->find();

        if($category)
        {
            $article_id = M('article')->where(['category_id'=>$category_id])->getField('article_id');

            $childCategory = M('category')->where(['parent_id'=>$category_id])->field('category_id')->count();

            if($childCategory > 0)
            {
                $result = ['errcode'=>1,'msg'=>L('DEL_CATEGORY_FAILED_CHILD')];
            }
            else if($article_id > 0)
            {
                $result = ['errcode'=>1,'msg'=>L('DEL_CATEGORY_FAILED')];
            }
            else if(M('category')->where($field)->delete())
            {
                $result = ['errcode'=>0,'msg'=>L('DELETE_SUCCESS')];
            }
            else
            {
                $result = ['errcode'=>1,'msg'=>L('DELETE_FAILED')];
            }
        }
        else
        {
            $result = ['status'=>0,'msg'=>L('CATEGORY_NOT')];
        }

        $this->ajaxReturn($result);
    }
}