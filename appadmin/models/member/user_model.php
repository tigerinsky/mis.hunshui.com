<?php

class User_model extends MY_Model {
	private $dbr;
	private $dbw;
	public function __construct() {
		parent::__construct();
		$this->dbr = $this->load->database("dbr", TRUE, TRUE);
		$this->dbw = $this->load->database('dbw',TRUE, TRUE);
		$this->table_name = 'user';
	}
	
	public function get_user_info_by_phone($phone) {
		$this->dbw->select('uid, wx_name, nick_name, cmpy_name, phone, email, url, type, level, zfb_account, status, ctime, utime');
		$this->dbw->where('phone', $phone);
		$this->dbw->where('status', 1);
		$result = $this->dbw->get($this->table_name);
		
		// 获取数据库信息失败
		if (false === $result) {
			return false;
		}
		// 查询无结果
		if (0 === $result->num_rows) {
			return NULL;
		}
		
		log_message('debug', '[******************************]'. __METHOD__ .':'.__LINE__.' get_user_info_by_phone data [' . json_encode($result->result_array()) .']');
		return $result->result_array()[0];
	}
	
	
}
