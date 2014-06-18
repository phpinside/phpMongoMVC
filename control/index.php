<?php
!defined('IN_APP') && exit('Access Denied');
define('DAY', 86400);

class indexcontrol extends controlbase {

	function ondefault() {
/*		$query=array('endtime'=>array('$gte'=>$this->time) );
		$myMemList = $this('mem')->findIdNames($this->user['uid'],2);

		$newsList=$this('news')->find(array(),0,8);


		$onlineMemList = $this('mem')->findAll(array('isonline'=>1));
		$msgList = $this('message')->find(array(),0,13);


		$friendLinks=$this('friendlink')->findAll();*/
		$title='';
		$problems = $this('blog')->findProblems();
		include template('index');
	}

	function onindex() {
		$this->ondefault();
	}

	function oncourse() {
		$title='培训课程_';
		include template('course');
	} 

	function onteacher() {
		$title='专家讲师_';
		include template('teacher');
	}

	function ongoal() {
		$title='就业目标_';
		include template('goal');
	}


	function onapply() {
		$title='报名须知_';
		include template('apply');
	}

	function oncontact() {
		$title='联系我们_';
		include template('contact');
	}

	/*function oneveryday() {
		$title='每日一题';
		include template('everyday');
	}*/

 

 
 

}