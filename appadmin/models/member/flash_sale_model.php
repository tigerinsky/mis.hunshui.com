<?php

class Flash_sale_model extends MY_Model {
	private $dbr;
	private $dbw;
	public function __construct() {
		parent::__construct();
		$this->dbr = $this->load->database("dbr", TRUE, TRUE);
		$this->dbw = $this->load->database('dbw',TRUE, TRUE);
		$this->table_name = 'flash_sale';
	}
	
	
	public function get_max_rank() {
		$this->dbw->select_max('rank');
		$result = $this->dbw->get($this->table_name);
		
		// 获取数据库信息失败
		if (false === $result) {
			return false;
		}
		// 查询无结果
		if (0 === $result->num_rows) {
			return NULL;
		}
		return $result->result_array()[0];
	}
	
	
	/**
	 * 向数据表中写入一行数据
	 * @param arr $info 需要插入的数据
	 * @param bool 是否成功执行
	 */
	public function create_info($info){
		$insert_query=$this->db->insert_string($this->table_name,$info);
		if($this->db->query($insert_query)){
			return true;
		} else {
			return false;
		}
	}
	
	
	/**
	 * 对数据表中的单行数据进行修改
	 * @param arr $info 需要修改的键值对
	 * @param int $id 被修改的id编号
	 * @return bool 是否执行成功
	 */
	public function update_info($info, $fsid){
		$where="fsid={$fsid}";
		$update_rule=$this->dbw->update_string($this->table_name, $info, $where);
		if($this->dbw->query($update_rule)){
			return true;
		}else{
			return false;
		}
	}
	
	
}
