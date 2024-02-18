<?php
/**
 * Created by PhpStorm.
 * User: navyy
 * Date: 2017.07.21
 * Time: 9:56
 */
namespace Common\Model;

use Common\Model\BasicModel;

class CrmDefineFormModel extends BasicModel
{
    protected $autoCheckFields = false;

    protected $temp = [];

	public function CheckForm($company_id,$form,$type,$index='',$type_id = '',$require = [])
	{
		if(!$require)
		{
			$form_description = getCrmLanguageData('form_description');

			$require = getCrmDbModel('define_form')
				->field(['*',$form_description])
				->where(['company_id'=>$company_id,'closed' => 0,'type'=>$type])
				->order('orderby asc')->select();
		}

        foreach($require as $k=>$v)
        {
			if($v['form_type'] == 'region')
			{
				$form[$v['form_name']] = $form[$v['form_name'].'_defaultCountry'];

				if($form[$v['form_name'].'_defaultProv'])
				{
					$form[$v['form_name']] .= ','.$form[$v['form_name'].'_defaultProv'];
				}

				if($form[$v['form_name'].'_defaultCity'])
				{
					$form[$v['form_name']] .= ','.$form[$v['form_name'].'_defaultCity'];
				}

				if($form[$v['form_name'].'_defaultArea'])
				{
					$form[$v['form_name']] .= ','.$form[$v['form_name'].'_defaultArea'];
				}

			}

			if(!$form[$v['form_name']])
            {
	            //字段在查看范围内才判断
                if(!$index || !$v['role_id'] || in_array($index['role_id'],explode(',',$v['role_id'])))
                {
	                if($v['is_required'] == 0)
	                {
		                return ['status'=>0,'msg'=>$v['form_description'].' '.L('IS_REQUIRED')];
	                }
                }
			}
			else
			{
				if($v['is_unique'] == 1)
				{
					$uniqueData = CrmisUniqueData($type,$company_id,$v['form_id'],$form[$v['form_name']]);

					if($type_id)
					{
						if($uniqueData && $uniqueData[$type.'_id'] != $type_id)
						{
							return ['status'=>0,'msg'=>$v['form_description'].L('EXISTED')];
						}
					}
					else
					{
						if($uniqueData)
						{
							return ['status'=>0,'msg'=>$v['form_description'].L('EXISTED')];
						}
					}
				}

				$checkFormat = $this->checkFormat($v['form_type'],$form[$v['form_name']]);

				if($checkFormat['msg']){ return $checkFormat;}

				if($type == 'customer' && $v['form_name'] == 'website')
				{
					if(!isUrl($form[$v['form_name']]))
					{
						return ['status'=>0,'msg'=>L('ENTER_CORRECT_URL')];

						die;
					}
				}
			}

            $detail[$v['form_name']]['form_id'] = $v['form_id'];

            $detail[$v['form_name']]['form_content'] = $form[$v['form_name']];
        }

		if($type == 'contract')
		{
			if($detail['start_time']['form_content'] < $detail['sign_time']['form_content'])
			{
				return ['status'=>0,'msg'=>L('SHENGXIAOTIME_QIANDINGTIME')];
				die;
			}

			if($detail['end_time']['form_content'] <= $detail['sign_time']['form_content'])
			{
				return ['status'=>0,'msg'=>L('JIEZHITIME_QIANDINGTIME')];
				die;
			}

			if($detail['end_time']['form_content'] <= $detail['start_time']['form_content'])
			{
				return ['status'=>0,'msg'=>L('JIEZHITIME_SHENGXIAOTIME')];
				die;
			}
		}


		return ['detail'=>$detail];
	}

	public function checkFormat($form_type,$form_name)
	{
		if($form_type == 'phone')
		{
			if(!isMobile($form_name) && !isTel($form_name) && !isWorldMobile($form_name))
			{
				return ['status'=>0,'msg'=>L('PHONE_FORMAT_ERROR',['phone'=>$form_name])];

				die;
			}
		}

		if($form_type == 'email')
		{
			if(!isEmail($form_name))
			{
				return ['status'=>0,'msg'=>L('MAIL_FORMAT_ERROR',['email'=>$form_name])];

				die;
			}
		}

		return true;
	}
}
