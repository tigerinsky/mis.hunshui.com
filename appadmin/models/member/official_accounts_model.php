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
	
	public function get_ofc_list_by_uid($uid) {
		$this->dbr->select('oaid, uid, ofc_account, nick_name, head_pic, qr_pic, wx_idty, abstract');
		$this->db->where('uid', $uid);
		$this->db->where('status', 1);
		$result = $this->dbr->get($this->table_name);
		
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
	
	
}
