<?php

class Official_accounts_model extends MY_Model {
	private $dbr;
	private $dbw;
	public function __construct() {
		parent::__construct();
		$this->dbr = $this->load->database("dbr", TRUE, TRUE);
		$this->dbw = $this->load->database('dbw',TRUE, TRUE);
		$this->table_name = 'official_accounts';
		//$this->load->model("phone/phonebook_model", "_phonebook");
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
	public function update_info($info, $oaid){
		$where="oaid={$oaid}";
		$update_rule=$this->dbw->update_string($this->table_name, $info, $where);
		if($this->dbw->query($update_rule)){
			return true;
		}else{
			return false;
		}
	}
	
	public function get_ofc_list_by_uid($uid) {
		$this->dbw->select('oaid, uid, ofc_account, nick_name, head_pic, qr_pic, wx_idty, abstract');
		$this->dbw->where('uid', $uid);
		$this->dbw->where('status', 0);
		$result = $this->dbw->get($this->table_name);
		
		log_message('debug', '[******************************]'. __METHOD__ .':'.__LINE__.' get_ofc_list sql [' . $this->db->last_query() .']');
		
		// 获取数据库信息失败
		if (false === $result) {
			return false;
		}
		// 查询无结果
		if (0 === $result->num_rows) {
			return NULL;
		}
		return $result->result_array();
	}
	
	
	public function get_ofc_info_by_oaid($oaid) {
		$this->dbw->select('oaid, uid, ofc_account, nick_name, head_pic, qr_pic, wx_idty, abstract');
		$this->dbw->where('oaid', $oaid);
		$result = $this->dbw->get($this->table_name);
	
		log_message('debug', '[******************************]'. __METHOD__ .':'.__LINE__.' get_ofc_info_by_oaid sql [' . $this->db->last_query() .']');
	
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
	
	
}
