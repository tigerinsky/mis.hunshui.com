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
	
	
}
