<?php

class Procedure_log_model extends MY_Model {
	private $dbr;
	private $dbw;
	public function __construct() {
		parent::__construct();
		$this->dbr = $this->load->database("dbr", TRUE, TRUE);
		$this->dbw = $this->load->database('dbw',TRUE, TRUE);
		$this->table_name = 'procedure_log';
	}
	
	public function get_procedure_log_by_procedure_id($procedure_id) {
		$this->dbw->select('procedure_id, art_id, consult_id, order_id, content, operator, ctime');
		$this->dbw->where('procedure_id', $procedure_id);
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
	 * 根据广告id获取最新的一条修改信息
	 * @param unknown_type $art_id
	 */
	public function get_procedure_log_by_art_id($art_id) {
		$this->dbw->select('procedure_id, art_id, consult_id, order_id, content, operator, ctime');
		$this->dbw->where('art_id', $art_id);
		$this->dbw->order_by("ctime", "desc");
		$this->dbw->limit(1);
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
	 * 根据询购id获取最新的一条修改信息
	 * @param unknown_type $consult_id
	 */
	public function get_procedure_log_by_consult_id($consult_id) {
		$this->dbw->select('procedure_id, art_id, consult_id, order_id, content, operator, ctime');
		$this->dbw->where('consult_id', $consult_id);
		$this->dbw->order_by("ctime", "desc");
		$this->dbw->limit(1);
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
	 * 根据订单id获取最新的一条修改信息
	 * @param unknown_type $order_id
	 */
	public function get_procedure_log_by_order_id($order_id) {
		$this->dbw->select('procedure_id, art_id, consult_id, order_id, content, operator, ctime');
		$this->dbw->where('order_id', $order_id);
		$this->dbw->order_by("ctime", "desc");
		$this->dbw->limit(1);
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
	public function update_info($info, $dbid){
		$where="dbid={$dbid}";
		$update_rule=$this->dbw->update_string($this->table_name, $info, $where);
		if($this->dbw->query($update_rule)){
			return true;
		}else{
			return false;
		}
	}
	
	
}
