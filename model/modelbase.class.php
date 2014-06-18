<?php

!defined('IN_APP') && exit('Access Denied');

class modelbase {
	
	protected $base;		//当前control类
	protected $db;			//mongo对象
	protected $modelname;	//当前model名字
	protected $collection;	//mongodb当前collection
	protected $convert;		//是否需要调用convert转换item？
	public $idname;
	public $sortfields;	//当前collection排序字段

	function __construct($base) {
		$this->time = time();
		$this->base = $base;
		$this->db = $base->db;
		$this->modelname=substr(get_class($this),0,-5);
		$this->collection = $this->db->selectCollection($this->modelname);
		$this->convert=false;;
		$this->idname = 'id';
		$this->sortfields = array('id'=>1);
	}
	
	/*得到mongodb中自动增长的id*/
	function get_autoincre_id($name= NULL){
		$name = $name ? $name : $this->modelname ;
		$update = array('$inc'=>array("id"=>1));
		$query = array('table_name' => $name);
		$command = array(
			'findandmodify'=>'autoincre_system', 'update'=>$update,
			'query'=>$query, 'new'=>true, 'upsert'=>true
		);
		$id = $this->db->command($command);
		return $id['value']['id'];
	}
	
	/*通用添加方法*/
	function insert($item){	
		$idname=$this->idname;
		$item[$idname] = $this->get_autoincre_id();
		$this->collection->insert($item); 
		return $item[$idname];
	}

	/*通用更新方法
	$criteria 查询条件的数组
	$set	更新字段的数组
	*/
	function update($criteria, $set){	
		$options=array('multiple'=>true);
		$this->collection->update($criteria, array('$set' => $set) ,$options ); 
	}
	
	/*根据id更新*/
	function updateById($id, $set){	
		$criteria=array( $this->idname => $id );
		$this->collection->update($criteria, array('$set' => $set) ); 
	}

	/*字段自动增加1,负数就是减少*/
	function inc($id,$fieldName,$value=1){
		$this->collection->update(array($this->idname=> $id), array('$inc' => array($fieldName=>$value)) ); 
	}
	
	/*通用删除方法*/
	function remove($criteria){	
		$this->collection->remove($criteria); 
	}
	
	/*根据id单条或者批量删除*/
	function removeById($id){
		$criteriaVal= $id;
		if(is_array($id))$criteriaVal =array('$in'=>$id) ;
		$criteria=array($this->idname => $criteriaVal );
		$this->collection->remove($criteria); 
	}
	
	/*如果convert为true，则调用子类方法对时间等类字段进行转换处理。*/
	function findById($id){
		$item = $this->collection->findOne(array ($this->idname => $id) );
		$item && $this->convert && $item = $this->convert($item);
		return $item;
	}
	
	/*其它类型的findBy，例如findByName，findByEmail等..子类不需要定义方法，可以直接调用。*/
	function __call($name, $arguments) {
		$fieldName=strtolower(substr($name,6));
		$fieldValue=$arguments[0];
		$item = $this->collection->findOne(array($fieldName => $fieldValue));
		return $item;
	}

	 function find($criteria=array(), $start=0, $limit=10){
		$itemlist = array();
		$cursor = $this->collection->find($criteria)->skip($start)->limit($limit)->sort($this->sortfields);
		foreach ($cursor as $item) {
			$this->convert && $item = $this->convert($item);
			$itemlist[]= $item;
		}
		return $itemlist;
	 }
	 
	function findAll($criteria=array(), $fields = array()){
		$itemlist = array();
		$cursor = $this->collection->find($criteria, $fields)->sort($this->sortfields);
		foreach ($cursor as $item) {
			$this->convert && $item = $this->convert($item);
			$itemlist[]= $item;
		}
		return $itemlist;
	 }

	function findRow($criteria=array()){
		$item = $this->collection->findOne($criteria);
		$item &&  $this->convert && $item = $this->convert($item);
		return $item;
	 }

	//获取第一列的结果集
	function findColumn($criteria=array(), $fields = array()){
		$columns = array();
		$fields['_id']=0; //取消返回_id
		$cursor = $this->collection->find($criteria, $fields);
		foreach ($cursor as $item) {
			$columns[]=  array_shift($item);
		}
		return $columns;
	 }
	  
	//获取第一行第一列字段值
	function findScalar($criteria=array()){
		$item = $this->collection->findOne($criteria,array('_id'=>0));
		$scalarVal=array_shift($item);
		return $scalarVal;
	 }
	 
	 //获取记录数
	function count($criteria=array()){
		return $this->collection->find($criteria)->count();
	}
	 
	//function exists($condition='',$criteria=array()){}

	 
	/*记录mongodb语句错误*/
	function writeLog(){
		file_put_contents('mongodb.log',var_export($this->db->lastError(),true), FILE_APPEND );
	}



}

