<?php

class Adv_article_model extends MY_Model {
	private $dbr;
	private $dbw;
	public function __construct() {
		parent::__construct();
		$this->dbr = $this->load->database("dbr", TRUE, TRUE);
		$this->dbw = $this->load->database('dbw',TRUE, TRUE);
		$this->table_name = 'adv_article';
	}
	
	public function get_adv_article_info_by_art_id($art_id) {
		$this->dbw->select('art_id, title, author, url, is_show, abstract, content, original_link, uid');
		$this->dbw->where('art_id', $art_id);
		$result = $this->dbw->get($this->table_name);
		
		log_message('debug', '[******************************]'. __METHOD__ .':'.__LINE__.' get_adv_article_info_by_art_id sql [' . $this->db->last_query() .']');
		
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
	 * 根据广告标题返回匹配到的广告id列表
	 * @param arr
	 */
	public function get_art_id_list_by_title($title) {
		$this->dbw->select('art_id');
		$this->dbw->like('title', $title, 'both');
		$result = $this->dbw->get($this->table_name);
	
		// 获取数据库信息失败
		if (false === $result) {
			return false;
		}
		// 查询无结果
		if (0 === $result->num_rows) {
			return NULL;
		}
	
		log_message('debug', '[******************************]'. __METHOD__ .':'.__LINE__.' get_art_id_list_by_title data [' . json_encode($result->result_array()) .']');
		return $result->result_array();
	}
	
	
	/**
	 * 对数据表中的单行数据进行修改
	 * @param arr $info 需要修改的键值对
	 * @param int $id 被修改的id编号
	 * @return bool 是否执行成功
	 */
	public function update_info($info, $art_id){
		$where="art_id={$art_id}";
		$update_rule=$this->dbw->update_string($this->table_name, $info, $where);
		if($this->dbw->query($update_rule)){
			return true;
		}else{
			return false;
		}
	}
	
}
