<?php

!defined('IN_APP') && exit('Access Denied');

class blogmodel extends modelbase {

	function __construct($base) {
		parent::__construct($base);
		$this->convert=false;
		$this->sortfields = array('id'=>1);
	}
	
	/*从文件中加载每日一题的内容*/
 	function findProblems() {
 		$needUpDate = true;
		$filepath = APP_ROOT.'/data/cache/problems.php';
 		if( file_exists($filepath) ){
 			$timediff = $this->time - filemtime($filepath) ;
			( $timediff < DAY ) && $needUpDate = false;
 		}
 		if( $needUpDate ){
			$bloghtml = file_get_contents('http://blog.sijiaomao.com/?cat=6');
			preg_match_all ('#<h3.+><a.+href="(.+)".+>(.+)</a></h3>#isU' ,$bloghtml, $matches);
			$problems = array_combine($matches[1] ,$matches[2]);
			file_put_contents($filepath, serialize($problems ) );
		}else{
			$problems = (array)unserialize( file_get_contents($filepath) );
		}
 		return $problems;
 	}
 
}
