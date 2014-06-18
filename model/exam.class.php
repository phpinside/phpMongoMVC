<?php

!defined('IN_APP') && exit('Access Denied');

class exammodel extends modelbase {


	private $rightAnswers = array(
		"B","C","A","B","A",
		"C","D","D","B","C",
		"B","D","A","D","C",
		"D","B","C","D","B"
	);

	function __construct($base) {
		parent::__construct($base);
		$this->convert=true;
		$this->sortfields = array('id'=>1);
	}
	
	function add($exam){
        	$exam['score'] = 100-count(array_diff_assoc($exam['answers'],$this->rightAnswers))*5;
		$findexam=$this->findRow( array('phone'=>$exam['phone']) );
		$exam['sendsms'] = 0;
		$exam['sendmail'] = 0;
		return empty($findexam) ? $this->insert($exam) : 0 ;
	}

 	function convert($item){
		//$item['format_dateline'] = tdate($item['dateline']);
		return $item;
	}
	
	//获取到发送标志后同时标志为已经发送
	function findAndUpSms(){
		//$criteria=array('sendsms'=>array('$exists'=>false) );
		$criteria=array( 'sendsms'=> 0 );//默认插入的为0
		$fields=array('truename'=>1,'phone'=>1,'score'=>1 );
		$items = $this->findAll($criteria,$fields);
		//更新标识，设置为已经发送短信
		$this->update($criteria, array('sendsms'=>1 ));
		return $items;
	}
	
	 
}

