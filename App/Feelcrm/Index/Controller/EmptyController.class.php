<?php
namespace Index\Controller;

use Index\Common\BasicController;

class EmptyController extends  BasicController
{
    public function _empty()
	{
        header("HTTP/1.0 404 Not Found");//使HTTP返回404状态码 

        $this->display("Public:404"); 
    }
}