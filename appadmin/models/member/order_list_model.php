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
	
	
	/**
	 * 对数据表中的单行数据进行修改
	 * @param arr $info 需要修改的键值对
	 * @param int $id 被修改的id编号
	 * @return bool 是否执行成功
	 */
	public function update_info($info, $olid){
		$where="olid={$olid}";
		$update_rule=$this->dbw->update_string($this->table_name, $info, $where);
		if($this->dbw->query($update_rule)){
			return true;
		}else{
			return false;
		}
	}
	
	
	/**
	 * 获取所有的订单id
	 * @param arr $info 需要修改的键值对
	 * @param int $id 被修改的id编号
	 * @return bool 是否执行成功
	 */
	public function get_all_order_list() {
		$this->dbw->select('olid');
		//$this->dbw->where('status', 9);
		$result = $this->dbw->get($this->table_name);
	
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
