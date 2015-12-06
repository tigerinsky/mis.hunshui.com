<?php

class Consult_list_model extends MY_Model {
	private $dbr;
	private $dbw;
	public function __construct() {
		parent::__construct();
		$this->dbr = $this->load->database("dbr", TRUE, TRUE);
		$this->dbw = $this->load->database('dbw',TRUE, TRUE);
		$this->table_name = 'consult_list';
	}
	
	public function get_consult_info_by_clid($clid) {
		$this->dbw->select('clid, oaid, media_uid, consult_id, flash_sale_id, order_id, status, ctime, utime');
		$this->dbw->where('clid', $clid);
		$result = $this->dbw->get($this->table_name);
		
		log_message('debug', '[******************************]'. __METHOD__ .':'.__LINE__.' get_consult_info_by_clid sql [' . $this->db->last_query() .']');
		
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
	public function update_info($info, $clid){
		$where="clid={$clid}";
		$update_rule=$this->dbw->update_string($this->table_name, $info, $where);
		if($this->dbw->query($update_rule)){
			return true;
		}else{
			return false;
		}
	}
	
}
