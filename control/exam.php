<?php

!defined('IN_APP') && exit('Access Denied');

class examcontrol extends controlbase {

	/*显示测试题*/
	function ondefault() {
		$title='史上最难PHPer笔试题';
		include template('exam');
	}
	
	//提交考卷
	function oncommit() {

		if(!isset($this->post['truename'])){
			exit('<h1>哥们，等不及可以先加入QQ群：124421692 和PHP高手一起讨论。</h1>');
		}

		//$this->post['truename']=htmlentities($this->post['truename']);
		$this->post['phone']=trim($this->post['phone']);
		$this->post['ip']=$this->ip;
		$this->post['dateline']=date("Y-m-d H:m:s");

		$examid=$this('exam')->add($this->post);
		echo $examid;
		//$jsonstr=json_encode($this->post)."\n";

		//file_put_contents('usersdata.php', $jsonstr, FILE_APPEND);
	}


	//查看考卷列表
	function onlist() {
		if(!isset($this->get['pass'])){
			exit('<h1>Error!</h1>');
		}
		$examlist=$this('exam')->findAll();
		//$isAndroid = (false !== strpos($_SERVER['HTTP_USER_AGENT'], 'Android') );

		include template('examlist');
	}

	//手机端获取用户列表，然后发送短信。
	function ongetscore(){
		$items = $this('exam')->findAndUpSms();
		echo json_encode($items);
	}

	//设置短信发送状态
	function onupsms(){
		$id= intval($this->get['id']);
		$sendsms= intval($this->get['sendsms']);
		$findexam=$this('exam')->updateById($id,array( 'sendsms'=> $sendsms ));
		header('location:?c=exam&a=list&pass');
	}

	//设置邮件发送状态
	function onupmail(){
		$id= intval($this->get['id']);
		$sendmail= intval($this->get['sendmail']);
		$findexam=$this('exam')->updateById($id,array( 'sendmail'=> $sendmail ));
		header('location:?c=exam&a=list&pass');
	}

	//删除指定记录
	function onremove(){
		$id= intval($this->get['id']);
		$findexam=$this('exam')->removeById($id);
		header('location:?c=exam&a=list&pass');
	}	

}