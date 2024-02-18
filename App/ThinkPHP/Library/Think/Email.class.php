<?php
/**
 * @package     Application
 * @author      Yishu
 */
namespace Think;

use Think\Cache\Driver\Redis;

class Email
{
	public $account;

	public $password;

	public $address ;

	public $server;

	public $serverType;

	public $port = "110";

	public $redis;

	private $attachments = [];

	private $_connect;

	private $_mailInfo;

	private $_totalCount;

	private $_contentType;

	private $now;

	public function __construct($config)
	{
		$this->now = date("Y-m-d H:i:s",time());

		$this->account  = $config['account'];

		$this->password   = $config['password'];

		$this->address  = $config['account'];

		$this->server   = $config['pop'];

		$this->serverType   = 'imap';

		$this->port			= $config['receive_port'];

		$this->_contentType = [
			'ez' => 'application/andrew-inset', 'hqx' => 'application/mac-binhex40',
			'cpt' => 'application/mac-compactpro', 'doc' => 'application/msword',
			'bin' => 'application/octet-stream', 'dms' => 'application/octet-stream',
			'lha' => 'application/octet-stream', 'lzh' => 'application/octet-stream',
			'exe' => 'application/octet-stream', 'class' => 'application/octet-stream',
			'so' => 'application/octet-stream', 'dll' => 'application/octet-stream',
			'oda' => 'application/oda', 'pdf' => 'application/pdf',
			'ai' => 'application/postscript', 'eps' => 'application/postscript',
			'ps' => 'application/postscript', 'smi' => 'application/smil',
			'smil' => 'application/smil', 'mif' => 'application/vnd.mif',
			'xls' => 'application/vnd.ms-excel', 'ppt' => 'application/vnd.ms-powerpoint',
			'wbxml' => 'application/vnd.wap.wbxml', 'wmlc' => 'application/vnd.wap.wmlc',
			'wmlsc' => 'application/vnd.wap.wmlscriptc', 'bcpio' => 'application/x-bcpio',
			'vcd' => 'application/x-cdlink', 'pgn' => 'application/x-chess-pgn',
			'cpio' => 'application/x-cpio', 'csh' => 'application/x-csh',
			'dcr' => 'application/x-director', 'dir' => 'application/x-director',
			'dxr' => 'application/x-director', 'dvi' => 'application/x-dvi',
			'spl' => 'application/x-futuresplash', 'gtar' => 'application/x-gtar',
			'hdf' => 'application/x-hdf', 'js' => 'application/x-javascript',
			'skp' => 'application/x-koan', 'skd' => 'application/x-koan',
			'skt' => 'application/x-koan', 'skm' => 'application/x-koan',
			'latex' => 'application/x-latex', 'nc' => 'application/x-netcdf',
			'cdf' => 'application/x-netcdf', 'sh' => 'application/x-sh',
			'shar' => 'application/x-shar', 'swf' => 'application/x-shockwave-flash',
			'sit' => 'application/x-stuffit', 'sv4cpio' => 'application/x-sv4cpio',
			'sv4crc' => 'application/x-sv4crc', 'tar' => 'application/x-tar',
			'tcl' => 'application/x-tcl', 'tex' => 'application/x-tex',
			'texinfo' => 'application/x-texinfo', 'texi' => 'application/x-texinfo',
			't' => 'application/x-troff', 'tr' => 'application/x-troff',
			'roff' => 'application/x-troff', 'man' => 'application/x-troff-man',
			'me' => 'application/x-troff-me', 'ms' => 'application/x-troff-ms',
			'ustar' => 'application/x-ustar', 'src' => 'application/x-wais-source',
			'xhtml' => 'application/xhtml+xml', 'xht' => 'application/xhtml+xml',
			'au' => 'audio/basic', 'snd' => 'audio/basic',
			'mid' => 'audio/midi', 'midi' => 'audio/midi', 'kar' => 'audio/midi',
			'mpga' => 'audio/mpeg', 'mp2' => 'audio/mpeg', 'mp3' => 'audio/mpeg',
			'aif' => 'audio/x-aiff', 'aiff' => 'audio/x-aiff', 'aifc' => 'audio/x-aiff',
			'm3u' => 'audio/x-mpegurl', 'ram' => 'audio/x-pn-realaudio', 'rm' => 'audio/x-pn-realaudio',
			'rpm' => 'audio/x-pn-realaudio-plugin', 'ra' => 'audio/x-realaudio',
			'wav' => 'audio/x-wav', 'pdb' => 'chemical/x-pdb', 'xyz' => 'chemical/x-xyz',
			'bmp' => 'image/bmp', 'gif' => 'image/gif', 'ief' => 'image/ief',
			'jpeg' => 'image/jpeg', 'jpg' => 'image/jpeg', 'jpe' => 'image/jpeg',
			'png' => 'image/png', 'tiff' => 'image/tiff', 'tif' => 'image/tiff',
			'djvu' => 'image/vnd.djvu', 'djv' => 'image/vnd.djvu', 'wbmp' => 'image/vnd.wap.wbmp',
			'ras' => 'image/x-cmu-raster', 'pnm' => 'image/x-portable-anymap',
			'pbm' => 'image/x-portable-bitmap', 'pgm' => 'image/x-portable-graymap',
			'ppm' => 'image/x-portable-pixmap', 'rgb' => 'image/x-rgb', 'xbm' => 'image/x-xbitmap',
			'xpm' => 'image/x-xpixmap', 'xwd' => 'image/x-xwindowdump', 'igs' => 'model/iges',
			'iges' => 'model/iges', 'msh' => 'model/mesh', 'mesh' => 'model/mesh',
			'silo' => 'model/mesh', 'wrl' => 'model/vrml', 'vrml' => 'model/vrml',
			'css' => 'text/css', 'html' => 'text/html', 'htm' => 'text/html',
			'asc' => 'text/plain', 'txt' => 'text/plain', 'rtx' => 'text/richtext',
			'rtf' => 'text/rtf', 'sgml' => 'text/sgml', 'sgm' => 'text/sgml',
			'tsv' => 'text/tab-separated-values', 'wml' => 'text/vnd.wap.wml',
			'wmls' => 'text/vnd.wap.wmlscript', 'etx' => 'text/x-setext',
			'xsl' => 'text/xml', 'xml' => 'text/xml', 'mpeg' => 'video/mpeg',
			'mpg' => 'video/mpeg', 'mpe' => 'video/mpeg', 'qt' => 'video/quicktime',
			'mov' => 'video/quicktime', 'mxu' => 'video/vnd.mpegurl', 'avi' => 'video/x-msvideo',
			'movie' => 'video/x-sgi-movie', 'ice' => 'x-conference/x-cooltalk',
			'rar' => 'application/x-rar-compressed','zip' => 'application/x-zip-compressed',
			'*' => 'application/octet-stream', 'docx' => 'application/msword',
		];
	}


	/*
	 * Open an IMAP stream to a mailbox
	 * @param string $host
	 * @param string $port
	 * @param string $user
	 * @param string $pass
	 * @return array
	 */
	public function mailConnect()
	{
		$this->_connect = imap_open('{'.$this->server.':'.$this->port.'/ssl}INBOX', $this->account, $this->password);

		if (!$this->_connect)
		{
			return ['code'=>1000,'message'=>'cannot connect: '.imap_last_error().' Error account：'.$this->account];
		}

//		$redis = new Redis();

//		$redis->delete('mailIds');

		return $this->mailReceived();
	}


	/**
	 * 读取收件箱邮件
	 * @return array result
	 */
	public function mailReceived()
	{
		$redis = new Redis();

		if($redis->lLen('mailId') == 0)
		{
			$this->getUnseenMails(); //当队列长度为0时，读取未读邮件，并入队
		}

		$mailId = $redis->lPop('mailId');

		if(!$mailId)
		{
			return ['code'=>1000,'message'=>"No Message for ".$this->account];
		}
		else
		{
			$head = $this->getHeader($mailId);  // 获取邮件头部信息，返回数组

			$body = $this->getBody($mailId);

//			从内到外去除Gmail的多余内容
			$body = preg_replace('#<blockquote[^>]*?class="gmail_quote"[^>]*>(.*?)</blockquote>#is', '', $body);

			$body = preg_replace('#<div[^>]*?class="gmail_attr"[^>]*>(.*?)</div>#is', '', $body);

			$body = preg_replace('#<div[^>]*?class="gmail_quote"[^>]*>(.*?)</div>#is', '', $body);

			$msgUid = imap_uid($this->_connect,trim($head['msgno']));

			$attachments = $this->getAttachments($msgUid,trim($head['msgno']));

			$attachmentList['name']	= NULL;

			$files = [];

			$image_path = dirname(dirname(dirname(dirname(__DIR__)))).'/Web/Attachs/mail/images';

			$attach_path = dirname(dirname(dirname(dirname(__DIR__)))).'/Web/Attachs/mail/attach';

			foreach ($attachments as $k => $attach)
			{
				$file_name = $this->decode_mime($attach['filename']);

//				转换存储文件名的编码防止文件保存失败
//				腾讯邮箱，附件原始编码为GB18030
				if(strpos(strtoupper($attach['filename']),'GB18030'))
				{
					$file_name = $this->mailDecode($attach['filename']);
				}
//				网易邮箱，附件原始编码为GBK
				elseif(strpos(strtoupper($attach['filename']),'GBK'))
				{
					$file_name = iconv("GBK","GB18030",$file_name);
				}
//				Outlook邮箱，附件原始编码为GB2312
				elseif(strpos(strtoupper($attach['filename']),'GB2312'))
				{
					$file_name = iconv("GB2312","GB18030",$file_name);
				}
//				阿里云邮箱，附件原始编码为UTF-8
				elseif(strpos(strtoupper($attach['filename']),'UTF-8'))
				{
					$file_name = iconv("UTF-8","GB18030",$file_name);
				}
				else
				{
					$file_name = iconv("UTF-8","GB18030",$file_name);
				}

				// 解决Linux服务器中文文件名乱码
				$encode = mb_detect_encoding($file_name, ['ASCII', 'UTF-8', 'GB2312', 'GBK', 'BIG5']);

				if ($encode == 'EUC-CN')
				{
					$file_name = iconv('GBK', 'UTF-8', $file_name);
				}

				$extend = explode("." ,$file_name);

				// inline: 内嵌图片
				if ($attach['type'] > 2 && $attach['inline'])
				{
//					重命名图片
					$save_name = sha1(date('YmdHis', time()).uniqid()).'.'.$extend[count($extend)-1];

//					储存图片
					file_put_contents($image_path.'/'.$save_name, base64_decode($attach['data']));

					$search = ['src="cid:'.$file_name.'"','src="cid:'.$attach['cid'].'"'];

					$replace = 'src='.C('HTTP_PROTOCOL').'://'.C('HOST_DOMAIN').'/Attachs/mail/images/'.$save_name;

					$body    = str_replace($search,$replace,$body);
				}
				// '附件' 保存到服务器 以供下载
				else if(in_array($attach['type'],[3,5,6]) && !$attach['inline'])
				{
					// 重命名附件，避免覆盖已存在的同名附件
					if(file_exists($attach_path.'/'.$file_name))
					{
						$file_name = 'FD-'.$mailId.'-'.$file_name;
					}

					$file['file_type'] = $extend[count($extend)-1];

					$file['file_name']  = $file_name;

					$file['save_name']  = './Attachs/mail/attach/'.$file_name;

					$file['file_link']  = C('HTTP_PROTOCOL').'://'.C('HOST_DOMAIN').'/Attachs/mail/attach/'.$file_name;

					$file['file_size']  = $attach['bytes'];

					$files[] = $file;

					$result['error'] = file_put_contents($attach_path.'/'.$file_name, base64_decode($attach['data']));

//                  此判断是针对内嵌图片的disposition参数为attachment的情况(正常的参数值应该是inline)，将body中的图片路径替换为附件路径，目前新浪邮箱会出现此情况
					if($attach['type'] == 5 && $attach['cid'] && in_array(strtoupper($attach['subtype']),['PNG','JPEG','JPG','GIF','BMP','TIF','SVG','ICO']))
					{
						$search = ['src="cid:'.$file_name.'"','src="cid:'.$attach['cid'].'"'];

						$replace = 'src='.C('HTTP_PROTOCOL').'://'.C('HOST_DOMAIN').'/Attachs/mail/attach/'.$file_name;

						$body    = str_replace($search,$replace,$body);
					}
				}
			}

			$mail = ['code'=>1001,'head'=>$head,'body'=>$body,"attachment"=>$files];

			// $redis->rPush('mailId',$mailId);

			// exit('Debuging');

			$result = $this->setMailSeen($mailId);//设置当前邮件为已读

			if($result)
			{
				$redis->sRem('mailIds',$mailId);
			}
			else
			{
				$redis->rPush('mailId',$mailId);
			}

			$this->closeMail();   //Close Mail Box
		}

		return $mail;
	}


//    获取邮箱中的未读邮件的序列号数组
	public function getUnseenMails()
	{
		if(!$this->_connect) return false;

		$redis = new Redis();

		$mailIds = imap_search($this->_connect,'UNSEEN');//UNSEEN 未读，从邮箱中搜索所有未读邮件

//        所有未读取的邮件ID进入Redis队列
		foreach($mailIds as $k=>$v)
		{
			$redis->rPush('mailId',$v);
		}

		return true;
	}


	/**
	 * 读取邮件Header信息
	 * @param string $mailId
	 * @return bool|array
	 */
	public function getHeader($mailId)
	{
		$header = imap_headerinfo($this->_connect,$mailId);

		if(!$header)
		{
			return false;
		}

		$sender = $header->from[0];

		$replyTo= $header->reply_to[0];

//		$receiver = $header->to[0];

		if(strtolower($sender->mailbox)!='mailer-daemon' && strtolower($sender->mailbox)!='postmaster')
		{
			//$subject = $this->subjectDecode($header->subject); //获取转换编码后的邮件，解决乱码

			//获取转换编码后的邮件标题，解决乱码 -- START

			$str = imap_mime_header_decode($header->subject);

			$subject = '';

			for($i=0;$i<count($str);$i++)
			{
				if ($str[$i]->charset != "default")
				{
					$subject .= iconv($str[$i]->charset,'utf-8//IGNORE',$str[$i]->text);
				}
				else
				{
					$subject .= $str[$i]->text;
				}
			}

			//获取转换编码后的邮件标题，解决乱码 -- END

			$ccList = array();

			foreach ($header->cc as $k => $v)
			{
				$ccList[]=$v->mailbox.'@'.$v->host;
			}

			$toList=array();

			foreach ($header->to as $k => $v)
			{
				$toList[]=$v->mailbox.'@'.$v->host;
			}

			$ccList = implode(",", $ccList);

			$toList = implode(",", $toList);

			$mailHeader = [
				'id'            => $header->Msgno,
				'from'          => strtolower($sender->mailbox).'@'.$sender->host,//发件人邮箱地址
				'fromName'      => $this->subjectDecode($sender->personal),//发件人名称
				'ccList'        => $ccList,
				'toOtherName'   => $this->subjectDecode($replyTo->personal),
				'subject'       => $subject,//邮件主题
				'mailDate'      => date("Y-m-d H:i:s",$header->udate),
				'udate'         => $header->udate,
				'toList'        => $toList,
				'answered'      => $header->Answered,
				'flagged'       => $header->Flagged,
				'seen'          => $header->Unseen,
				'msgno'         => $header->Msgno,
			];
		}
		else
		{
			return false;
		}

//		getPrint(imap_fetchheader($this->_connect,$mailId));
//		getPrint($header);
//		getPrint($mailHeader);
//		die;

		return $mailHeader;
	}


	/**
	 * get the body of the message
	 * @param string $mailId 邮件编号
	 * @return string
	 */
	public function getBody($mailId)
	{
		$body = $this->getPart($mailId, "TEXT/HTML");

		if ($body == '')
		{
			$body = $this->getPart($mailId, "TEXT/PLAIN");
		}

		if ($body == '')
		{
			return '';
		}

		return $body;
	}


	/*
	* 读取特定消息的结构并获取消息正文的特定部分
	* @param string $mailId    邮件编号
	* @param string $mimeType
	* @param object $structure
	* @param string $partNumber
	* @return string|bool
	*/
	private function getPart($mailId,$mimeType,$structure=false,$partNumber=false,$base64=true)
	{
		if (!$structure)
		{
			$structure = imap_fetchstructure($this->_connect,$mailId);
		}

		if ($structure)
		{
			if ($mimeType == $this->getMimeType($structure))
			{
				if (!$partNumber)
				{
					$partNumber = "1";
				}

				if($structure->ifparameters)
				{
					$fromEncoding = $structure->parameters[0]->value;
				}
				else
				{
					$fromEncoding = 'UTF-8';
				}

				$text = imap_fetchbody($this->_connect,$mailId,$partNumber);

				if ($structure->encoding == 3 && $base64)
				{
					$text = imap_base64($text);
				}
				else if ($structure->encoding == 4)
				{
					$text = imap_qprint($text);
				}

				// 获取内容编码
				/* 				if($fromEncoding)
								{
									$encode = mb_detect_encoding($text, $fromEncoding);
								}
								else
								{
									$encode = mb_detect_encoding($text, ["ASCII",'UTF-8','GB2312',"EUC-JP",'EUC-KR','EUC-CN','BIG5','KOI8-R','ISO-8859-1','ISO-8859-5']);
								}
				*/

				$encode = mb_detect_encoding($text, ["ASCII",'UTF-8','GB2312','GBK',"EUC-JP",'EUC-KR','EUC-CN','BIG5','KOI8-R','ISO-8859-1','ISO-8859-5']);

				// 非utf-8编码需要转码
				if($encode != 'UTF-8' && $base64)
				{
					if(strtoupper($fromEncoding)!=$encode)
					{
						$fromEncoding .= ','.$encode;
					}

					$text = mb_convert_encoding($text,'UTF-8',$encode);
				}

				return $text;
			}

			if ($structure->type == 1)
			{
				while (list($index, $subStructure) = each($structure->parts))
				{
					$prefix = '';

					if ($partNumber)
					{
						$prefix = $partNumber . '.';
					}

					$data = $this->getPart($mailId, $mimeType, $subStructure, $prefix . ($index + 1));

					if ($data)
					{
						return $data;
					}
				}
			}
		}

		return false;
	}


	/*
	* 获取消息结构的子类型和类型
	* @param object $structure
	*/
	private function getMimeType($structure)
	{
		$mimeType = array("TEXT", "MULTIPART", "MESSAGE", "APPLICATION", "AUDIO", "IMAGE", "VIDEO", "OTHER");

		if($structure->subtype && $structure->subtype != "PNG")
		{
			return $mimeType[(int) $structure->type] . '/' . $structure->subtype;
		}

		return "TEXT/PLAIN";
	}


	/*
	* 获取附件  (内嵌 | 附件)
	*/
	public function getAttachments($msgUid,$msgno,$prefix='',$index = 1,$fullPrefix = true)
	{
		$structure = imap_fetchstructure($this->_connect,$msgUid,FT_UID);

		$this->attachments = array();

		foreach($structure->parts as $key=>$part)
		{
			$partNumber = $prefix . $index;

			/* 在第一个parts中取附件及内嵌图片 */
			if($part->type > 2)
			{
				if(isset($part->id) && $part->disposition != 'attachment')
				{
					$id = str_replace(array('<', '>'), '', $part->id);

					$this->attachments[$id] = [
						'cid'       => $id,
						'type'      => $part->type,
						'subtype'   => $part->subtype,
						'filename'  => $this->getFilenameFromPart($part),
						'bytes'     => $part->bytes,
						'data'      => $this->getPart($msgno, $this->getMimeType($part), $part, $partNumber, false),
						'inline'    => true,
					];
				}
				else
				{
					$this->attachments[] = [
						'cid'       => isset($part->id) ? str_replace(array('<', '>'), '', $part->id) : '',
						'type'      => $part->type,
						'subtype'   => $part->subtype,
						'filename'  => $this->getFilenameFromPart($part),
						'bytes'     => $part->bytes,
						'data'      => $this->getPart($msgno, $this->getMimeType($part), $part, $partNumber, false),
						'inline'    => false,
					];
				}
			}

			/* 在第二个parts中取附件及内嵌图片 */
			foreach($structure->parts[$key]->parts as $key1 => $value1)
			{
				$part2 = $structure->parts[$key]->parts[$key1];

				$partNumber1 = ($key+1).".".($key1+1);

				if($part2->ifdparameters)
				{
					if($part2->type > 2)
					{
//						腾讯邮箱内嵌图片没有disposition参数，附件没有id参数
//                      阿里邮箱内嵌图片disposition参数为inline，附件没有id参数
//						网易邮箱内嵌图片disposition参数为inline，附件没有id参数
//						新浪邮箱内嵌图片disposition参数为attachment，附件没有id参数
//						Gmail邮箱内嵌图disposition参数为inline，附件带有id参数
//						Outlook邮箱内嵌图disposition参数为inline，附件带有id参数
//						Yahoo邮箱内嵌图disposition参数为inline，附件带有id参数
						if(isset($part2->id) && $part2->disposition != 'attachment')
						{
							$id = str_replace(array('<', '>'), '', $part2->id);

							$this->attachments[$id] = [
								'cid'       => $id,
								'type'      => $part2->type,
								'subtype'   => $part2->subtype,
								'filename'  => $this->getFilenameFromPart($part2),
								'bytes'     => $part2->bytes,
								'data'      => $this->getPart($msgno, $this->getMimeType($part2), $part2, $partNumber1, false),
								'inline'    => true,
							];
						}
						else
						{
							$this->attachments[] = [
								'cid'       => isset($part2->id) ? str_replace(array('<', '>'), '', $part2->id) : '',
								'type'      => $part2->type,
								'subtype'   => $part2->subtype,
								'filename'  => $this->getFilenameFromPart($part2),
								'bytes'     => $part2->bytes,
								'data'      => $this->getPart($msgno,$this->getMimeType($part2), $part2, $partNumber1, false),
								'inline'    => false,
							];
						}
					}
				}
			}

			$index++;
		}

		return $this->attachments;
	}


	/*
	* 获取附件名称 (内嵌 | 附件)
	*/
	private function getFilenameFromPart($part)
	{
		$filename = '';

		if($part->ifdparameters)
		{
			foreach($part->dparameters as $object)
			{
				if(strtolower($object->attribute) == 'filename')
				{
					$filename = $object->value;
				}
			}
		}

		if(!$filename && $part->ifparameters)
		{
			foreach($part->parameters as $object)
			{
				if(strtolower($object->attribute) == 'name')
				{
					$filename = $object->value;
				}
			}
		}

		return $filename;
	}


	public function mailDecode($str)
	{
		$resp = imap_utf8(trim($str));

		if(preg_match("/=\?/", $resp))
		{
			return iconv_mime_decode($resp, ICONV_MIME_DECODE_CONTINUE_ON_ERROR, "UTF-8");
		}

		return $resp;
	}


	/*
	* decode the subject of chinese
	* @param string $subject
	* @return string
	*/
	public function subjectDecode($subject)
	{
		$beginStr = substr($subject, 0, 5);

		if ($beginStr == '=?ISO')
		{
			$separator = '=?ISO-2022-JP';
			$toEncoding = 'ISO-2022-JP';
		}
		else if ($beginStr == '=?UTF')
		{
			$separator = '=?UTF-8';
			$toEncoding = 'UTF8';
		}
		else
		{
			$separator = '=?GBK';
			$toEncoding = 'GBK';
		}

		$encode = strstr($subject, $separator);

		if ($encode)
		{
			$explodeArr = explode($separator, $subject);

			$length = count($explodeArr);

			$subjectArr = array();

			for ($i = 0; $i < $length / 2; $i++)
			{
				$subjectArr[$i][] = $explodeArr[$i * 2];

				if (@$explodeArr[$i * 2 + 1])
				{
					$subjectArr[$i][] = $explodeArr[$i * 2 + 1];
				}
			}

			foreach ($subjectArr as $arr)
			{
				$subSubject = implode($separator, $arr);

				if (count($arr) == 1)
				{
					$subSubject = $separator . $subSubject;
				}

				$begin = strpos($subSubject, "=?");

				$end = strpos($subSubject, "?=");

				$beginStr = '';

				$endStr = '';
				if ($end > 0)
				{
					if ($begin > 0)
					{
						$beginStr = substr($subSubject, 0, $begin);
					}

					if ((strlen($subSubject) - $end) > 2)
					{
						$endStr = substr($subSubject, $end + 2, strlen($subSubject) - $end - 2);
					}

					$str = substr($subSubject, 0, $end - strlen($subSubject));

					$pos = strrpos($str, "?");

					$str = substr($str, $pos + 1, strlen($str) - $pos);

					$subSubject = $beginStr . imap_base64($str) . $endStr;

					$subSubjectArr[] = iconv($toEncoding, 'utf-8', $subSubject);

					mb_convert_encoding($subSubject, 'utf-8', 'gb2312,ISO-2022-JP');
				}
			}

			$subject = implode('', $subSubjectArr);
		}

		return $subject;
	}


	function decode_mime($str)
	{
		$str = imap_mime_header_decode($str);

		if ($str[0]->charset != "default")
		{
			return iconv($str[0]->charset,'utf-8//IGNORE',$str[0]->text);
		}
		else
		{
			return $str[0]->text;
		}
	}


//    根据序列号将邮件设为已读
	function setMailSeen($mid)
	{
		if(!$this->_connect) return false;

		return imap_setflag_full($this->_connect, $mid, "\\Seen \\Flagged");//根据序列号

//      return imap_setflag_full($this->marubox, $mid, "\\Seen \\Flagged",ST_UID);//ST_UID根据UID
	}


	/*
	* 关闭连接
	*/
	public function closeMail()
	{
		if(!$this->_connect) exit('not connect');

		imap_close($this->_connect, CL_EXPUNGE);
	}


	/* 以下方法未使用	*/

	/**
	 * Get information about the current mailbox
	 *
	 * @return object|bool
	 */
	public function mailInfo()
	{
		$this->_mailInfo = imap_mailboxmsginfo($this->_connect);

		if (!$this->_mailInfo)
		{
			echo "get mailInfo failed: " . imap_last_error();

			return false;
		}

		return $this->_mailInfo;
	}


	/**
	 * Read an overview of the information in the headers of the given message
	 *
	 * @param string $msgRange
	 * @return array
	 */
	public function mailList($msgRange = '')
	{
		if ($msgRange)
		{
			$range = $msgRange;
		}
		else
		{
			$this->mailTotalCount();

			$range = "1:" . $this->_totalCount;
		}

		$overview = imap_fetch_overview($this->_connect, $range);

		foreach ($overview as $val)
		{
			$mailList[$val->msgno] = (array) $val;
		}

		return $mailList;
	}


	/**
	 * get the total count of the current mailbox
	 *
	 * @return int
	 */
	public function mailTotalCount()
	{
		$check = imap_check($this->_connect);

		$this->_totalCount = $check->Nmsgs;

		return $this->_totalCount;
	}


	/**
	 * Mark a message for deletion from current mailbox
	 *
	 * @param string $msgCount
	 */
	public function mailDelete($msgCount)
	{
		imap_delete($this->_connect, $msgCount);
	}


	/**
	 * put the message from unread to read
	 *
	 * @param string $msgCount
	 * @return bool
	 */
	public function mailRead($msgCount)
	{
		return imap_setflag_full($this->_connect, $msgCount, "\\Seen");
	}
}
