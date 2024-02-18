<?php

namespace Common\Model;

Vendor('Qiniu.autoload');

use Qiniu\Auth;

use Qiniu\Storage\UploadManager;

use Think\Image;

use Think\Model;

use Think\Upload;

use Think\Upload\Driver\Qiniu\QiniuStorage;

class UploadModel extends Model
{
	protected $autoCheckFields = false;

	protected $_company_id;

	protected $_config;

	protected $max_upload_size;

	public function _initialize()
	{
		session('[start]');

		$this->_company_id = session('company_id');

		if($this->_company_id > 0)
		{
			$storage_space = M('storage')->where(['company_id'=>$this->_company_id])->getField('storage_space');

			if($storage_space == 20)
			{
				$this->_config = D('Qiniu')->getUploadConfig();

				if(!$this->_config)
				{
					echo json_encode(['status'=>1,'msg'=>'请先配置七牛云']);

					exit;
				}
			}
			else
			{
				$this->_config = [
					'maxSize'    => 500 * 1024 * 1024,
					'rootPath'   => './Attachs/',
					'savePath'   => 'Uploads/',
					'saveName'   => ['uniqid',''],
					'exts'       => ['jpeg', 'png', 'gif', 'jpg','zip', 'rar', 'txt', 'doc', 'docx', 'xlsx', 'xls','ppt', 'pptx', 'pdf', 'chf','bmp','7z','xmind','avi','mov','mp4'],
					'autoSub'    => true,
					'subName'    => ['date','Ymd'],
				];
			}

			$this->max_upload_size = 1024 * 1024 * 1024;
		}
		else
		{
			echo json_encode(L('ILLEGAL_ACCESS'));die;
		}
	}


	public function UploadWeChat($files)
	{
		$json = ['code'=>1,'msg'=>L('UPLOAD_FAIL')];

		if (!empty($files))
		{
			$upload = new Upload($this->_config);

			if(!$info = $upload->upload())
			{
				$json = array('code'=>1,'msg'=>$upload->getError());
			}
			else
			{
				foreach($info as $file)
				{
					$json = array('code'=>0,'msg'=>L('UPLOAD_SUCCESS'),'url'=>C('HTTP_PROTOCOL').'://'.C('HOST_DOMAIN').'/Attachs/'.$file['savepath'].$file['savename']);
				}
			}
		}

		return $json;
	}


//	PC端上传附件，弹窗方式
	public function uploadAttachment()
	{
		header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");

		header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");

		header("Cache-Control: no-store, no-cache, must-revalidate");

		header("Cache-Control: post-check=0, pre-check=0", false);

		header("Pragma: no-cache");

		if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS')
		{
			exit; // finish preflight CORS requests here
		}

		if ( !empty($_REQUEST[ 'debug' ]) )
		{
			$random = rand(0, intval($_REQUEST[ 'debug' ]) );
			if ( $random === 0 ) {
				header("HTTP/1.0 500 Internal Server Error");
				exit;
			}
		}

		// header("HTTP/1.0 500 Internal Server Error");
		// exit;

		// 5 minutes execution time
		set_time_limit(5 * 60);

		// 取消对此项的注释以伪造上载时间
//		    usleep(5000);

		$filePath = './Attachs/Uploads';

		// Settings
		$targetDir = $filePath.'/upload_tmp';    //存放分片临时目录

		$uploadDir = $filePath.'/'.date('Ymd');     //分片合并存放目录

		$cleanupTargetDir = true; // Remove old files

		$maxFileAge = 5 * 3600; // Temp file age in seconds

		// 创建分片文件目录
		if (!file_exists($targetDir))
		{
			mkdir($targetDir,0777,true);
		}

		// 创建合并文件目录
		if (!file_exists($uploadDir))
		{
			mkdir($uploadDir,0777,true);
		}

		// Get a file name
		if (isset($_REQUEST["name"]))
		{
			$fileName = $_REQUEST["name"];
		}
		elseif (!empty($_FILES))
		{
			$fileName = $_FILES["file"]["name"];
		}
		else
		{
			$fileName = uniqid("file_");
		}

		$info = new \SplFileInfo($fileName);

		$fileName = $info->getFilename();

		$oldName = $fileName;
		/*
				$fileName = iconv("UTF-8","gb2312", $fileName);

				$encode = mb_detect_encoding($fileName, ["ASCII", "UTF-8", "GB2312", "GBK", "BIG5","EUC-CN"]);

				if($encode != 'EUC-CN')
				{
					$fileName = iconv("gb2312","UTF-8", $fileName);
				}
		*/

		$filePath = $targetDir . '/' . $fileName;

		// $uploadPath = $uploadDir . '/' . $fileName;
		// Chunking might be enabled
		$chunk = isset($_REQUEST["chunk"]) ? intval($_REQUEST["chunk"]) : 0;

		$chunks = isset($_REQUEST["chunks"]) ? intval($_REQUEST["chunks"]) : 1;

		// Remove old temp files
		if ($cleanupTargetDir)
		{
			if (!is_dir($targetDir) || !$dir = opendir($targetDir))
			{
				die('{"jsonrpc" : "2.0", "error" : {"code": 100, "message": "Failed to open temp directory111."}, "id" : "id"}');
			}

			while (($file = readdir($dir)) !== false)
			{
				$tmpfilePath = $targetDir . '/' . $file;

				// If temp file is current file proceed to the next
				if ($tmpfilePath == "{$filePath}_{$chunk}.part" || $tmpfilePath == "{$filePath}_{$chunk}.parttmp")
				{
					continue;
				}
				// Remove temp file if it is older than the max age and is not the current file
				if (preg_match('/\.(part|parttmp)$/', $file) && (filemtime($tmpfilePath) < time() - $maxFileAge))
				{
					unlink($tmpfilePath);
				}
			}

			closedir($dir);
		}

		// Open temp file
		if (!$out = fopen("{$filePath}_{$chunk}.parttmp", "wb"))
		{
			die('{"jsonrpc" : "2.0", "error" : {"code": 102, "message": "Failed to open output stream222."}, "id" : "id"}');
		}

		if (!empty($_FILES))
		{
			if ($_FILES["file"]["error"] || !is_uploaded_file($_FILES["file"]["tmp_name"]))
			{
				die('{"jsonrpc" : "2.0", "error" : {"code": 103, "message": "Failed to move uploaded file333."}, "id" : "id"}');
			}

			// Read binary input stream and append it to temp file
			if (!$in = fopen($_FILES["file"]["tmp_name"], "rb"))
			{
				die('{"jsonrpc" : "2.0", "error" : {"code": 101, "message": "Failed to open input stream444."}, "id" : "id"}');
			}
		}
		else
		{
			if (!$in = fopen("php://input", "rb"))
			{
				die('{"jsonrpc" : "2.0", "error" : {"code": 101, "message": "Failed to open input stream555."}, "id" : "id"}');
			}
		}

		while ($buff = fread($in, 4096))
		{
			fwrite($out, $buff);
		}

		fclose($out);

		fclose($in);

		rename("{$filePath}_{$chunk}.parttmp", "{$filePath}_{$chunk}.part");

		$index = 0;

		$done = true;

		for($index = 0; $index < $chunks; $index++ )
		{
			if ( !file_exists("{$filePath}_{$index}.part") )
			{
				$done = false;

				break;
			}
		}

		if ($done)
		{
			$pathInfo = pathinfo($fileName);

			$hashStr = substr(md5($pathInfo['basename']),8,16);

			$hashName = time() . $hashStr . '.' .$pathInfo['extension'];

			$uploadPath = $uploadDir . '/' .$hashName;
			//$uploadPath = $uploadDir . '/' .$fileName;

			if (!$out = fopen($uploadPath, "wb"))
			{
				die('{"jsonrpc" : "2.0", "error" : {"code": 102, "message": "Failed to open output stream666."}, "id" : "id"}');
			}

			//flock($hander,LOCK_EX)文件锁
			if ( flock($out, LOCK_EX) )
			{
				for( $index = 0; $index < $chunks; $index++ )
				{
					if (!$in = fopen("{$filePath}_{$index}.part", "rb"))
					{
						break;
					}

					while ($buff = fread($in, 4096))
					{
						fwrite($out, $buff);
					}

					fclose($in);

					unlink("{$filePath}_{$index}.part");
				}

				flock($out, LOCK_UN);
			}

			fclose($out);

			$response = [
				'status'=>1,
				'msg'   => L('UPLOAD_SUCCESS'),
				'save_name'     => $uploadPath,
				'attach_name'   => $oldName,
				'attach_size'   => filesize($uploadPath),
				'attach_type'   => $pathInfo['extension'],
				'attach_link'   =>  C('HTTP_PROTOCOL').'://'.C('HOST_DOMAIN').ltrim($uploadPath,'.')
			];

			return $response;
		}

		// Return Success JSON-RPC response
		die('{"jsonrpc" : "2.0", "result" : null, "id" : "id"}');
	}


//  移动端上传附件，普通方式
	public function UploadTicketFile($files)
	{
		$Upload = new Upload($this->_config);

		$info = $Upload->upload($files);

		if($this->_config['driver'] == 'Qiniu')
		{
			$path = str_replace('/','_',$info['file']['savepath']);
		}
		else
		{
			$path = $this->_config['rootPath'].$info['file']['savepath'];

			$info['file']['save'] = './Attachs/'.$info['file']['savepath'].$info['file']['savename'];

			$info['file']['link'] = C('HTTP_PROTOCOL').'://'.C('HOST_DOMAIN').'/Attachs/'.$info['file']['savepath'].$info['file']['savename'];
		}

		$filename = $path.$info['file']['savename'];

		if(!$info)
		{
			$data = ['status'=>0,'msg'=>$Upload->getError()];
		}
		else
		{
			$data = [
				'status'=>1,
				'msg'   => L('UPLOAD_SUCCESS'),
				'name'  => $_FILES['file']['name'],
				'save'  => $info['file']['save'],
				'size'  => $_FILES['file']['size'],
				'cname' => $filename,
				'type'  => $info['file']['ext'],
				'link'  => $info['file']['link'],
			];
		}

		return $data;
	}


//    上传图片
	public function UploadImageFile($type = '',$name = '')
	{
//	    粘贴上传
		if($name == 'paste')
		{
//		    $http_type = ((isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) == 'on') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')) ? 'https://' : 'http://';

			$imageData = I('post.imageData');

			$image_base = trim($imageData['imageContent']);

			$img = str_replace('data:image/png;base64','',$image_base); //去掉 data:image/png;base64

			$img = str_replace('','+',$img);

			$data_img = base64_decode($img);

			$file = [
				'name'      => $imageData['imageName'],
				'ext'       => pathinfo($imageData['imageName'], PATHINFO_EXTENSION),
			];

			$saveName = $this->getSaveName($file);

            $uploadDir = './Attachs/Uploads/temp/';

            // 创建文件目录
            if (!file_exists($uploadDir))
            {
                mkdir($uploadDir,0777,true);
            }

			$url = '/Attachs/Uploads/temp/'.$saveName;

			$res = file_put_contents('.'.$url,$data_img);

			if($res)
			{
				$data = [
					"code" => 0,
					"msg" => '',
					"data" => [
						"src" => C('HTTP_PROTOCOL').'://'.C('HOST_DOMAIN').$url
					]
				];
			}
			else
			{
				$data = ['code'=>1,'msg'=>L('UPLOAD_FAIL2')];
			}
		}
		else//插件上传
		{
			switch($type)
			{
				case 'advert':$size = '1328x170';break;

				case 'banner':$size = '430x135';break;

				case 'editor':$size = '240x135';break;

				default :

					$size = '233x135';

					break;
			}

			$Upload = new Upload($this->_config);

			$info = $Upload->upload($_FILES);

			if(!$info)
			{
				$data = ['code'=>1,'msg'=>$Upload->getError()];
			}
			else
			{
				$src = '';$url=$thumb=$img_name=[];

				foreach($info as &$v)
				{
					if($this->_config['driver'] == 'Qiniu')
					{
						$thumb[] = $v['url']."?imageMogr2/auto-orient/thumbnail/".$size."!/format/png/blur/1x0/quality/100|imageslim";

						$img_name[] = str_replace('/','_',$v['name']);

						$src = $v['url']."?imageMogr2/auto-orient/thumbnail/".$size."!/format/png/blur/1x0/quality/100|imageslim";
					}
					else
					{
						$v['url'] = $thumb[] = $src = C('HTTP_PROTOCOL').'://'.C('HOST_DOMAIN').'/Attachs/'.$info['file']['savepath'].$info['file']['savename'];

						$img_name[] = $this->_config['rootPath'].$info['file']['savepath'].$info['file']['savename'];
					}

					$url[] = $v['url'];
				}

				$data = ['code' => 0,'msg' => L('UPLOAD_SUCCESS'),'url'=>$url,'thumb'=>$thumb,'img_name'=>$img_name];

				if($type == 'editor')
				{
					$data['data'] = ['src'=>$src,'title'=>''];
				}
			}
		}

		return $data;
	}

	//    上传ocr图片
	public function UploadOcrImageFile($type = '',$name = '')
	{
		switch($type)
		{
			case 'advert':$size = '1328x170';break;

			case 'banner':$size = '430x135';break;

			case 'editor':$size = '240x135';break;

			default :

				$size = '233x135';

				break;
		}

		$Upload = new Upload($this->_config);

		$info = $Upload->upload($_FILES);

		if(!$info)
		{
			$data = ['code'=>1,'msg'=>$Upload->getError()];
		}
		else
		{
			$src = '';$url=$thumb=$img_name=[];

			foreach($info as &$v)
			{
				if($this->_config['driver'] == 'Qiniu')
				{
					$thumb[] = $v['url']."?imageMogr2/auto-orient/thumbnail/".$size."!/format/png/blur/1x0/quality/100|imageslim";

					$img_name[] = str_replace('/','_',$v['name']);

					$src = $v['url']."?imageMogr2/auto-orient/thumbnail/".$size."!/format/png/blur/1x0/quality/100|imageslim";
				}
				else
				{
					$v['url'] = $thumb[] = $src = C('HTTP_PROTOCOL').'://'.C('HOST_DOMAIN').'/Attachs/'.$info['file']['savepath'].$info['file']['savename'];


					$image = new Image();

					$image->open($this->_config['rootPath'].$info['file']['savepath'].$info['file']['savename']);

					list ($src_w, $src_h) = getimagesize($this->_config['rootPath'].$info['file']['savepath'].$info['file']['savename']);

					if($src_w > $src_h){

						$image->thumb(500, 300, Image::IMAGE_THUMB_FILLED,$this->_config['rootPath'].$info['file']['savepath'].$info['file']['savename'])->save($this->_config['rootPath'].$info['file']['savepath'].'thumb_'.$info['file']['savename']);
					}
					else
					{
						$image->thumb(300, 500, Image::IMAGE_THUMB_FILLED,$this->_config['rootPath'].$info['file']['savepath'].$info['file']['savename'])->save($this->_config['rootPath'].$info['file']['savepath'].'thumb_'.$info['file']['savename']);
					}

					$thumbImg = $this->_config['rootPath'].$info['file']['savepath'].'thumb_'.$info['file']['savename'];

					$img_name[] = $this->_config['rootPath'].$info['file']['savepath'].$info['file']['savename'];
				}

				$url[] = $v['url'];
			}

			$data = ['code' => 0,'msg' => L('UPLOAD_SUCCESS'),'url'=>$url,'thumb'=>$thumb,'img_name'=>$img_name,'thumbImg'=>$thumbImg];

			if($type == 'editor')
			{
				$data['data'] = ['src'=>$src,'title'=>''];
			}
		}

		return $data;
	}


//    上传头像
	public function UploadHeadFile($type = '')
	{
		if($type == 'logo')
		{
			$size = '200x50';
		}
		else
		{
			$size = '80x80';
		}

		$Upload = new Upload($this->_config);

		$info = $Upload->upload($_FILES);

		if($this->_config['driver'] == 'Qiniu')
		{
			$url = $info['file']['url']."?imageMogr2/auto-orient/thumbnail/".$size."!/format/png/blur/1x0/quality/100|imageslim";

			$face_name = str_replace('/','_',$info['file']['name']);
		}
		else
		{
			$url = C('HTTP_PROTOCOL').'://'.C('HOST_DOMAIN').'/Attachs/'.$info['file']['savepath'].$info['file']['savename'];

			$face_name = $info['file']['name'];
		}

		if(!$info)
		{
			$data = ['code'=>1,'msg'=>$Upload->getError()];
		}
		else
		{
			$data = ['code'=>0,'msg'=>L('UPLOAD_SUCCESS'),'url'=>$url,'face_name'=>$face_name];
		}

		return $data;
	}


	public function getUploadConfig()
	{
		$config = $this->_config['driverConfig'];

		$auth = new Auth($config['accessKey'],$config['secretKey']);

		$token = $auth->uploadToken($config['bucket']);

		return ['token'=>$token,'url'=>$config['up_host'],'domain'=>$config['domain']];
	}


	/*
	* 删除上传的文件和图片
	* @param string $file  文件
	* @param string $space 储存空间 local 本地储存，qiniu 七牛云储存
	* @param string $from
	* @return array $data  返回信息
	*/
	public function deleteUploadFile($file,$from = '')
	{
		$space = $this->getUploadSpaceByFile($file);

		if($space == 'local')
		{
			$isHttps = strpos($file,'https');

			if($isHttps)
			{
				$replace = 'https://'.C('HOST_DOMAIN');
			}
			else
			{
				$replace = 'http://'.C('HOST_DOMAIN');
			}

			$file = str_replace($replace,'',$file);

			if(!file_exists($file)) $file = '.'.$file;

			if(unlink($file))
			{
				$data = ['status'=>1,'msg'=>L('DELETE_SUCCESS')];

				$this->deleteDir(dirname($file));
			}
			else
			{
				$data = ['status'=>0,'msg'=>L('DELETE_FAILED')];
			}

			return $data;
		}
		else
		{
			if(strpos($file,'http') !== false)
			{
				$domain = M('qiniu')->where(['company_id'=>$this->_company_id])->getField('domain');

				$file = getQiniuFileName('http://'.$domain.'/',$file);
			}

			$config = D('Qiniu')->getUploadConfig();

			$Qiniu = new QiniuStorage($config['driverConfig']);

			$result = $Qiniu->del($file);

			$error = $Qiniu->errorStr;

			if(!$from)
			{
				if(is_array($result) && !($error))
				{
					$data = ['status'=>1,'msg'=>L('DELETE_SUCCESS')];
				}
				else
				{
					$data = ['status'=>0,'msg'=>L('DELETE_FAILED').','.$error];
				}

				return $data;
			}
		}
	}


	public function deleteTicketFile($files = '')
	{
		$images = unserialize($files);

		$data = [];

		foreach($images as $v)
		{
			$space = $this->getUploadSpaceByFile($v);

			if($space == 'local')
			{
				$isHttps = strpos($v,'https');

				if($isHttps)
				{
					$replace = 'https://'.C('HOST_DOMAIN');
				}
				else
				{
					$replace = 'http://'.C('HOST_DOMAIN');
				}

				$v = str_replace($replace,'',$v);

				if(!file_exists($v)) $v = '.'.$v;

				if(unlink($v))
				{
					$this->deleteDir(dirname($v));

					$data = ['status'=>1,'msg'=>L('DELETE_SUCCESS')];
				}
				else
				{
					$data = ['status'=>0,'msg'=>L('DELETE_FAILED')];
				}
			}
			else
			{
				$data = D('Qiniu')->delUploadImage($v);
			}
		}

		return $data;
	}


//    编辑时，删除富文本中修改的图片
	public function DelEditorImage($old_content='',$new_content='')
	{
		$imageMogr2 = "?imageMogr2/auto-orient/thumbnail/240x135!/format/png/blur/1x0/quality/100|imageslim";

		$old_content = str_replace($imageMogr2,'',html_entity_decode($old_content));

		$new_content = str_replace($imageMogr2,'',html_entity_decode($new_content));

		$pattern = "/<[img|IMG].*?src=[\'|\"](.*?(?:[\.gif|\.jpg|\.png]))[\'|\"].*?[\/]?>/";

		preg_match_all($pattern,$old_content,$old_images);

		preg_match_all($pattern,$new_content,$new_images);

		$old_img_name = $new_img_name =[];

//        旧图名称集合
		if($old_images[1])
		{
			foreach($old_images[1] as $v1)
			{
				$old_img_name[] = getQiniuImageName("http://".$this->_config['driverConfig']['domain']."/",$v1);
			}
		}

//        新图名称集合
		if($new_images[1])
		{
			foreach($new_images[1] as $v1)
			{
				$new_img_name[] = getQiniuImageName("http://".$this->_config['driverConfig']['domain']."/",$v1);
			}
		}

//        删除内容中不一致的图片
		foreach($old_img_name as $o)
		{
			if(!in_array($o,$new_img_name))
			{
				$this->delQiniuImage(1,$o);
			}
		}
	}


//    编辑时，删除上传的图片
	public function delEditImage($img=[],$old_img='')
	{
		if($old_img)
		{
			$old_img = unserialize($old_img);

			foreach($old_img as $v)
			{
				if(!in_array($v,$img))
				{
					$img_name = getQiniuImageName("http://".$this->_config['driverConfig']['domain']."/",$v);

					$this->deleteUploadFile($img_name,1);
				}
			}
		}

		if(!empty($img))
		{
			$image = serialize($img);
		}
		else
		{
			$image = null;
		}

		return $image;
	}


//    删除时，删除富文本中的图片
	public function delImage($content='')
	{
		$content = str_replace('?imageMogr2/auto-orient/thumbnail/240x135!/format/png/blur/1x0/quality/100|imageslim','',html_entity_decode($content));

		$pattern = "/<[img|IMG].*?src=[\'|\"](.*?(?:[\.gif|\.jpg|\.png]))[\'|\"].*?[\/]?>/";

		preg_match_all($pattern,$content,$img);

		if($img[1])
		{
			foreach($img[1] as $v1)
			{
				$img_name = getQiniuImageName("http://".$this->_config['driverConfig']['domain']."/",$v1);

				$this->deleteUploadFile($img_name,1);
			}
		}
	}


	/*
	* 通过文件名称获取文件储存空间
	* @param string $file      文件
	* @return string $space    储存空间
	*/
	public function getUploadSpaceByFile($file)
	{
		if(strpos($file,'Attachs'))
		{
			$space = 'local';
		}
		else
		{
			$space = 'qiniu';
		}

		return $space;
	}


//	  检查目录下是否存在文件，不存在则删除目录
	public function deleteDir($path)
	{
		if(is_dir($path))
		{
			$dir = scandir($path);

			unset($dir[0],$dir[1]);

			if(empty($dir)) rmdir($path);
		}
	}


	/*
	 * 根据上传文件命名规则取得保存文件名
	 * @param string $file 文件信息
	 * @return string $savename
	 */
	private function getSaveName($file)
	{
		$rule = $this->_config['saveName'];

		//保持文件名不变
		if (empty($rule))
		{
			/* 解决pathinfo中文文件名BUG */
			$filename = substr(pathinfo("_{$file['name']}", PATHINFO_FILENAME), 1);

			$savename = $filename;
		}
		else
		{
			$savename = $this->getName($rule, $file['name']);

			if(empty($savename))
			{
				$this->error = L('UPLOAD_FAIL6');

				return false;
			}
		}

		/* 文件保存后缀，支持强制更改文件后缀 */
		$ext = empty($this->config['saveExt']) ? $file['ext'] : $this->saveExt;

		$savename = md5($savename) . '.' . $ext;

		return $savename;
	}


	/**
	 * 根据指定的规则获取文件或目录名称
	 * @param  array  $rule     规则
	 * @param  string $filename 原文件名
	 * @return string           文件或目录名称
	 */
	private function getName($rule, $filename)
	{
		$name = '';

		if(is_array($rule))
		{ //数组规则
			$func     = $rule[0];

			$param    = (array)$rule[1];

			foreach ($param as &$value)
			{
				$value = str_replace('__FILE__', $filename, $value);
			}

			$name = call_user_func_array($func, $param);
		}
		else if (is_string($rule))
		{ //字符串规则
			if(function_exists($rule))
			{
				$name = call_user_func($rule);
			}
			else
			{
				$name = $rule;
			}
		}

		return $name;
	}

	public function saveUploadFile($files = [],$company_id=0,$source = 'contract',$contract_id=0,$clue_id=0,$customer_id=0,$opportunity_id=0,$follow_id=0)
	{
		if(!empty($files))
		{
			$file = [];

			foreach($files['links'] as $k=>$v)
			{
				$file[$k]['company_id'] = $company_id;

				$file[$k]['contract_id']  = $contract_id ? $contract_id : 0;

				$file[$k]['clue_id']  = $clue_id ? $clue_id : 0;

				$file[$k]['customer_id']  = $customer_id ? $customer_id : 0;

				$file[$k]['opportunity_id']  = $opportunity_id ? $opportunity_id : 0;

				$file[$k]['follow_id']  = $follow_id ? $follow_id : 0;

				$file[$k]['file_link']  = $v;

				$file[$k]['save_name']  = $files['saves'][$k];

				$file[$k]['file_name']  = $files['names'][$k];

				$file[$k]['file_size']  = $files['sizes'][$k];

				$file[$k]['file_type']  = $files['types'][$k];

				$file[$k]['file_form']  = $source;

				$file[$k]['create_time'] = NOW_TIME;
			}

			getCrmDbModel('upload_file')->addAll($file);
		}
	}

	public function updateUploadFile($files = [],$delFiles = [],$company_id=0,$source = 'contract',$contract_id=0,$clue_id=0,$customer_id=0,$opportunity_id=0,$follow_id=0)
	{
		if($contract_id)
		{
			getCrmDbModel('upload_file')->where(['contract_id'=>$contract_id])->delete();
		}
		elseif($follow_id)
		{
			getCrmDbModel('upload_file')->where(['follow_id'=>$follow_id])->delete();
		}
		elseif($opportunity_id)
		{
			getCrmDbModel('upload_file')->where(['opportunity_id'=>$opportunity_id])->delete();
		}
		elseif($clue_id)
		{
			getCrmDbModel('upload_file')->where(['clue_id'=>$clue_id])->delete();
		}
		elseif($customer_id)
		{
			getCrmDbModel('upload_file')->where(['customer_id'=>$customer_id])->delete();
		}

		if(!empty($files))
		{
			$file = [];

			foreach($files['links'] as $k=>$v)
			{
				$file[$k]['company_id'] = $company_id;

				$file[$k]['contract_id']  = $contract_id ? $contract_id : 0;

				$file[$k]['clue_id']  = $clue_id ? $clue_id : 0;

				$file[$k]['customer_id']  = $customer_id ? $customer_id : 0;

				$file[$k]['opportunity_id']  = $opportunity_id ? $opportunity_id : 0;

				$file[$k]['follow_id']  = $follow_id ? $follow_id : 0;

				$file[$k]['file_link']  = $v;

				$file[$k]['save_name']  = $files['saves'][$k];

				$file[$k]['file_name']  = $files['names'][$k];

				$file[$k]['file_size']  = $files['sizes'][$k];

				$file[$k]['file_type']  = $files['types'][$k];

				$file[$k]['file_form']  = $source;

				$file[$k]['create_time'] = NOW_TIME;
			}

			getCrmDbModel('upload_file')->addAll($file);
		}

		if(!empty($delFiles))
		{
			foreach($delFiles as $v)
			{
				D('Upload')->deleteUploadFile($v);
			}
		}
	}
}
