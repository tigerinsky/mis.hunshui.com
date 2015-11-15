<?php

class Adv_consult_model extends MY_Model {
	private $dbr;
	private $dbw;
	public function __construct() {
		parent::__construct();
		$this->dbr = $this->load->database("dbr", TRUE, TRUE);
		$this->dbw = $this->load->database('dbw',TRUE, TRUE);
		$this->table_name = 'adv_consult';
	}
	
	public function get_adv_consult_info_by_aid($aid) {
		$this->dbw->select('aid, show_day, ad_location, remark, category, uid, art_id, ctime, utime, status, show_hours, limit_hours, fans');
		$this->db->where('aid', $aid);
		$result = $this->dbw->get($this->table_name);
		
		log_message('debug', '[******************************]'. __METHOD__ .':'.__LINE__.' get_adv_consult_info_by_aid sql [' . $this->db->last_query() .']');
		
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
