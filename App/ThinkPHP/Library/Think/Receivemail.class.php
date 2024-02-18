<?php  
// Main ReciveMail Class File - Version 1.0 (03-06-2015)  
/* 
 * File: recivemail.class.php 
 * Description: Reciving mail With Attechment 
 * Version: 1.1 
 * Created: 03-06-2015 
 * Author: Sara Zhou 
 */
namespace Think;

use Think\Cache\Driver\Redis;

class receiveMail
{
    var $server='';

	var $username='';  

	var $password='';  
      
    var $marubox;

    var $email='';

    function __construct($username,$password,$EmailAddress,$mailserver='localhost',$servertype='imap',$port='993',$ssl = true) //Constructure
    {
		if($servertype=='imap')
		{
			if($port=='') $port='143';

			$strConnect='{'.$mailserver.':'.$port. '/ssl}INBOX';
		}
		else
		{
			$strConnect='{'.$mailserver.':'.$port. '/pop3'.($ssl ? "/ssl" : "").'}INBOX';   
		}

		$this->server		=   $strConnect;  

		$this->username	=   $username;  

		$this->password	=   $password;

		$this->email		=   $EmailAddress;  
    }



	//链接邮箱服务器
	function connect()  
    {
		$this->marubox = @imap_open($this->server,$this->username,$this->password);  

		if(!$this->marubox)  
		{
			return false;
//          echo "Error: Connecting to mail server";  
//          exit;
        }

        return true;  
    }


	//获取邮件头部信息(标题，发送人等)
    function getHeaders($mid)
    {  
        if(!$this->marubox)
		{
			return false;  
		}

        $mail_header = imap_header($this->marubox,$mid);  

        $sender = $mail_header->from[0];  

        $sender_replyto = $mail_header->reply_to[0];  

        if(strtolower($sender->mailbox)!='mailer-daemon' && strtolower($sender->mailbox)!='postmaster')  
        {
            //$subject = $this->decode_mime($mail_header->subject); //获取转换编码后的邮件，解决乱码

			//获取转换编码后的邮件，解决乱码 -- START

			$str = imap_mime_header_decode($mail_header->subject);

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

			//获取转换编码后的邮件，解决乱码 -- END

            $ccList = array();

            foreach ($mail_header->cc as $k => $v)  
            {  
                $ccList[]=$v->mailbox.'@'.$v->host;  
            }

            $toList=array();

            foreach ($mail_header->to as $k => $v)  
            {
                $toList[]=$v->mailbox.'@'.$v->host;  
            }

            $ccList=implode(",", $ccList);

            $toList=implode(",", $toList);

            $mail_details=array(
                    'mid'   =>$mid,
                    'fromBy'=>strtolower($sender->mailbox).'@'.$sender->host,  
                    'fromName'=>$this->decode_mime($sender->personal),  
                    'ccList'=>$ccList,//strtolower($sender_replyto->mailbox).'@'.$sender_replyto->host,  
                    'toNameOth'=>$this->decode_mime($sender_replyto->personal),  
                    'subject'=>$subject,  
                    'mailDate'=>date("Y-m-d H:i:s",$mail_header->udate),  
                    'udate'=>$mail_header->udate,  
                    'toList'=>$toList//strtolower($mail_header->to[0]->mailbox).'@'.$mail_header->to[0]->host  
//                  'to'=>strtolower($mail_header->toaddress)  
                );
        }

        return $mail_details;  
    }  



    function get_mime_type(&$structure) //Get Mime type Internal Private Use  
    {
        $primary_mime_type = array("TEXT", "MULTIPART", "MESSAGE", "APPLICATION", "AUDIO", "IMAGE", "VIDEO", "OTHER");   

        if($structure->subtype && $structure->subtype != "PNG")
        {
            return $primary_mime_type[(int) $structure->type] . '/' . $structure->subtype;   
        }

        return "TEXT/PLAIN";   
    }



    function get_part($stream, $msg_number, $mime_type, $structure = false, $part_number = false) //Get Part Of Message Internal Private Use  
    {
        if(!$structure)
		{   
            $structure = imap_fetchstructure($stream, $msg_number);   
        }

        if($structure)
		{
            if($mime_type == $this->get_mime_type($structure))
            {
                if(!$part_number)   
                {
                    $part_number = "1";   
                }

                $text = imap_fetchbody($stream, $msg_number, $part_number);

                if($structure->encoding == 3)  
                {
                    $encode = mb_detect_encoding(imap_base64($text), array("ASCII",'UTF-8',"GB2312","GBK",'BIG5'));

                    return mb_convert_encoding(imap_base64($text),'utf-8',$encode);
                }
                else if($structure->encoding == 4)  
                {
                    $encode = mb_detect_encoding(imap_qprint($text), array("ASCII",'UTF-8',"GB2312","GBK",'BIG5'));

                    return mb_convert_encoding(imap_qprint($text),'utf-8',$encode);
                }  
                else  
                {
                    return iconv('gb2312','utf-8',$text);
                }  
            }

            if($structure->type == 1) /* multipart */   
            {
                while(list($index, $sub_structure) = each($structure->parts))  
                {
                    if($part_number)  
                    {   
                        $prefix = $part_number . '.';   
                    }

                    $data = $this->get_part($stream, $msg_number, $mime_type, $sub_structure, $prefix . ($index + 1));

                    if($data)  
                    {
                        return $data;   
                    }   
                }   
            }   
        }

        return false;   
    }



//    获取邮箱中的邮件总数量
    function getTotalMails() //Get Total Number off Email In Mailbox
    {  
        if(!$this->marubox)
            return false;

//      return imap_headers($this->marubox);  
        return imap_num_msg($this->marubox);
    }



//    获取邮箱中的未读邮件的序列号数组
    function getUnseenMails()
    {
        if(!$this->marubox) return false;

        $redis = new Redis();

        $mids = imap_search($this->marubox,'UNSEEN');//UNSEEN 未读，从邮箱中搜索所有未读邮件

//        所有未读取的邮件ID进入Redis队列
        foreach($mids as $k=>$v)
        {
            if(!$redis->sIsMember('mailIds',$v))//检查集合中是否存在指定的值
            {
                $redis->sAdd('mailIds',$v);

                $redis->rPush('mailId',$v);
            }
        }
    }



//    根据序列号
    function setMailSeen($mid)
    {
        if(!$this->marubox)
            return false;

        return imap_setflag_full($this->marubox, $mid, "\\Seen \\Flagged");//根据序列号
//        return imap_setflag_full($this->marubox, $mid, "\\Seen \\Flagged",ST_UID);//ST_UID根据UID
    }


	function GetAttach($mid,$path) // Get Atteced File from Mail
    {  
        if(!$this->marubox)  
            return false;  
  
        $struckture = imap_fetchstructure($this->marubox,$mid);  

//        getPrint($struckture);die;
        $files=array();

        if($struckture->parts)  
        {  
            foreach($struckture->parts as $key => $value)  
            {
                $file = [];

                $enc = $struckture->parts[$key]->encoding;

//                取邮件附件
                if($struckture->parts[$key]->ifdparameters)  
                {
                    $file['imgcid'] = ltrim($struckture->parts[$key]->id,'<');

                    $file['imgcid'] = rtrim($file['imgcid'],'>');

                    foreach($struckture->parts[$key]->dparameters as $dp)
                    {
                        if($dp->attribute == 'filename')
                        {
                            $name =  $this->decode_mime($dp->value);

                            $extend =explode("." , $name);

                            $file['extension'] = $extend[count($extend)-1];

                            $file['pathname']  = $this->setPathName($key, $file['extension']);

	                        $file['filename']  = $name;

                            $file['title']     = !empty($name) ? htmlspecialchars($name) : str_replace('.' . $file['extension'], '', $name);
                        }

                        if($dp->attribute == 'size')
                        {
                            $file['size']  = $dp->value;//真实大小
                        }
                    }

                    $file['size'] = $file['size'] ? $file['size'] : $struckture->parts[$key]->bytes;
/*
//                    命名附件,转码
                    $name = $this->decode_mime($struckture->parts[$key]->dparameters[0]->value);
                    $file['extension'] = $extend[count($extend)-1];
                    $file['pathname']  = $this->setPathName($key, $file['extension']);
                    $file['title']     = !empty($name) ? htmlspecialchars($name) : str_replace('.' . $file['extension'], '', $name);
                    $file['size']  = $struckture->parts[$key]->dparameters[1]->value;
*/
                    if(in_array(strtolower(@$struckture->parts[$key]->disposition),['"ATTACHMENT"','attachment']))
                    {  
                        $file['type'] = 1;
                    }  
                    else  
                    {  
                        $file['type'] = 0;
                    }

                    $files[] = $file;                     

                    $message = imap_fetchbody($this->marubox,$mid,$key+1);

                    if ($enc == 0) $message = imap_8bit($message);

                    if ($enc == 1) $message = imap_8bit ($message);

                    if ($enc == 2) $message = imap_binary ($message);

                    if ($enc == 3) $message = imap_base64 ($message);   //图片

                    if ($enc == 4) $message = quoted_printable_decode($message);

                    if ($enc == 5) $message = $message;

//                    储存到本地
                    $fp = fopen($path.$file['pathname'],"w");

                    fwrite($fp,$message);

                    fclose($fp);
                }

//                处理内容中包含图片的部分
                if($struckture->parts[$key]->parts)  
                {
                    foreach($struckture->parts[$key]->parts as $keyb => $valueb)  
                    {
                        $enc = $struckture->parts[$key]->parts[$keyb]->encoding;

                        if($struckture->parts[$key]->parts[$keyb]->ifdparameters)
                        {
//                            图片真实大小
                            $size = $struckture->parts[$key]->parts[$keyb]->dparameters[1]->value;

//                            命名图片
                            $name=$this->decode_mime($struckture->parts[$key]->parts[$keyb]->dparameters[0]->value);  

                            $extend =explode("." , $name);

                            $file['extension'] = $extend[count($extend)-1];

                            $file['pathname']  = $this->setPathName($key, $file['extension']);

	                        $file['filename']  = $name;

                            $file['title']     = !empty($name) ? htmlspecialchars($name) : str_replace('.' . $file['extension'], '', $name);

                            $file['size']      = $size ? $size : $struckture->parts[$key]->parts[$keyb]->bytes;

                            $file['type']      = 0;

                            $files[] = $file;

                            $partnro = ($key+1).".".($keyb+1);  
                              
                            $message = imap_fetchbody($this->marubox,$mid,$partnro);  

                            if ($enc == 0) $message = imap_8bit($message);

                            if ($enc == 1) $message = imap_8bit ($message);

                            if ($enc == 2) $message = imap_binary ($message);

                            if ($enc == 3) $message = imap_base64 ($message);

                            if ($enc == 4) $message = quoted_printable_decode($message);

                            if ($enc == 5) $message = $message;

//                            储存到本地
                            $fp=fopen($path.$file['pathname'],"w");

                            fwrite($fp,$message);

                            fclose($fp);
                        }  
                    }
                }
            }
        }

        //move mail to taskMailBox  
        $this->move_mails($mid, $this->marubox);        
  
        return $files;  
    }  



	function getBody($mid,&$path,$imageList) // 获取邮件内容
    {
        if(!$this->marubox)
		{
			return false;  
		}

        $body = $this->get_part($this->marubox, $mid, "TEXT/HTML");

        if ($body == "")
		{
            $body = $this->get_part($this->marubox, $mid, "TEXT/PLAIN");
		}

        if ($body == "")
		{
            return '';
        }

//        处理图片
        $body = $this->embed_images($body,$path,$imageList);

        return $body;  
    }  


//    替换img中的src
	function embed_images(&$body,&$path,$imageList)  
    {
        // get all img tags  
        preg_match_all('/<img.*?>/', $body, $matches);

        if (!isset($matches[0])) return;

        foreach ($matches[0] as $img)  
        {  
            // replace image web path with local path  
            preg_match('/src="(.*?)"/', $img, $m);

            if (!isset($m[1])) continue;

            $arr = parse_url($m[1]);

            if (!isset($arr['scheme']) || !isset($arr['path'])) continue;

//          if (!isset($arr['host']) || !isset($arr['path']))continue;

            if ($arr['scheme'] != "http")
            {
/*
                $filename = explode("@", $arr['path']);

                $body = str_replace($img, '<img alt="" src="'.$path.$imageList[$filename[0]].'" style="border:none" />', $body);
*/
                $body = str_replace($img, '<img alt="" src="'.$path.$imageList[$arr['path']].'" style="border:none" />', $body);
            }
        }

        return $body;  
    }  



	function deleteMails($mid) // Delete That Mail  
    {  
        if(!$this->marubox)  
            return false;  
          
        imap_delete($this->marubox,$mid);  
    }



    function close_mailbox() //Close Mail Box  
    {  
        if(!$this->marubox)  
            return false;  
  
        imap_close($this->marubox,CL_EXPUNGE);  
    }  



    //移动邮件到指定分组
    function move_mails($msglist,$mailbox)  
    {  
        if(!$this->marubox)  
            return false;  
      
        imap_mail_move($this->marubox, $msglist, $mailbox);  
    }  



	function creat_mailbox($mailbox)  
    {  
        if(!$this->marubox)  
            return false;  

        //imap_renamemailbox($imap_stream, $old_mbox, $new_mbox);  
        imap_create($this->marubox, $mailbox);
    }

    /* 
     * 转换邮件标题的字符编码,处理乱码 
     */  
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



    /** 
     * Set path name of the uploaded file to be saved. 
     * 
     * @param  int    $fileID 
     * @param  string $extension 
     * @access public 
     * @return string 
     */  
    public function setPathName($fileID, $extension)  
    {
        return date('Ym/dHis', time()) . $fileID . mt_rand(0, 10000) . '.' . $extension;
    }
}  
?>  