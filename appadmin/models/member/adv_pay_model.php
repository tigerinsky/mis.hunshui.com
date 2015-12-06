<?php

class Adv_pay_model extends MY_Model {
	private $dbr;
	private $dbw;
	public function __construct() {
		parent::__construct();
		$this->dbr = $this->load->database("dbr", TRUE, TRUE);
		$this->dbw = $this->load->database('dbw',TRUE, TRUE);
		$this->table_name = 'adv_pay';
	}
	
	public function get_adv_pay_info_by_pay_id($pay_id) {
		$this->dbw->select('pay_id, pay_sn, pay_money, order_id, create_time, update_time, pay_method, detail');
		$this->dbw->where('pay_id', $pay_id);
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
	 * 对数据表中的单行数据进行修改
	 * @param arr $info 需要修改的键值对
	 * @param int $id 被修改的id编号
	 * @return bool 是否执行成功
	 */
	public function update_info($info, $pay_id){
		$where="pay_id={$pay_id}";
		$update_rule=$this->dbw->update_string($this->table_name, $info, $where);
		if($this->dbw->query($update_rule)){
			return true;
		}else{
			return false;
		}
	}
	
}
