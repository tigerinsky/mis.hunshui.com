<?php

class Order_list_model extends MY_Model {
	private $dbr;
	private $dbw;
	public function __construct() {
		parent::__construct();
		$this->dbr = $this->load->database("dbr", TRUE, TRUE);
		$this->dbw = $this->load->database('dbw',TRUE, TRUE);
		$this->table_name = 'order_list';
	}
	
	public function get_order_info_by_olid($olid) {
		$this->dbw->select('olid, ad_uid, news_uid, fsid, aid, oaid, ad_location, status, ctime, utime, ad_price, total_price, original_price, pay_status, pay_id, plat_payed');
		$this->dbw->where('olid', $olid);
		$result = $this->dbw->get($this->table_name);
		
		log_message('debug', '[******************************]'. __METHOD__ .':'.__LINE__.' get_order_info_by_olid sql [' . $this->db->last_query() .']');
		
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
