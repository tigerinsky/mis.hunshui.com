<?php

class procedure extends MY_Controller {

    protected static $common_config;
    protected $table_name;
    public function __construct() {
        parent::__construct();
        $this->load->library('form');
        $this->dbr = $this->load->database("dbr", TRUE);
        $this->load->config("common_config", TRUE);
        self::$common_config = $this->config->item('common_config');
        $this->load->model("member/user_model", "user_model");
        $this->load->model("member/adv_consult_model", "adv_consult_model");
        $this->load->model("member/adv_article_model", "adv_article_model");
        $this->load->model("member/adv_pay_model", "adv_pay_model");
        $this->load->model("member/official_accounts_model", "official_accounts_model");
        $this->load->model("member/consult_list_model", "consult_list_model");
        $this->load->model("member/order_list_model", "order_list_model");
        $this->table_name = "procedure_log";
    }

    public function index() {
        $this->procedure_list();
    }
    
    public function procedure_list() {
    	$this->load->library('form');
        $page = $this->input->get('page');
        $page = max(intval($page),1);
        $dosearch = $this->input->get('dosearch');
        
        $search_arr = array();
        $where_array = array();
        
        if($dosearch == 'ok'){
        	// 广告id
        	$art_id = trim($this->input->get('art_id'));
        	$search_arr['art_id'] = $art_id;
        	if($art_id != ''){
        		$where_array[] = "art_id = '{$art_id}' ";
        	}
        	
        	// 询购id
        	$consult_id = trim($this->input->get('consult_id'));
        	$search_arr['consult_id'] = $consult_id;
        	if($consult_id != ''){
        		$where_array[] = "consult_id = '{$consult_id}' ";
        	}
        	 
        	// 订单id
        	$order_id = trim($this->input->get('order_id'));
        	$search_arr['order_id'] = $order_id;
        	if($order_id != ''){
        		$where_array[] = "order_id = '{$order_id}' ";
        	}
        	
        	// 退款id
        	$drawback_id = trim($this->input->get('drawback_id'));
        	$search_arr['drawback_id'] = $drawback_id;
        	if($drawback_id != ''){
        		$where_array[] = "drawback_id = '{$drawback_id}' ";
        	}
        	
        	// mis用户名
        	$operator = trim($this->input->get('operator'));
        	$search_arr['operator'] = $operator;
        	if($operator != ''){
        		$where_array[] = "operator = '{$operator}' ";
        	}
        	
        	$keywords = trim($this->input->get('keywords'));
        	$search_arr['keywords'] = $keywords;
        	if($keywords != ''){
        		$where_array[] = "content like '%{$keywords}%' ";
        	}
        	
        }
        if(is_array($where_array) and count($where_array) > 0) {
            $where = ' WHERE '.join(' AND ',$where_array);
        }
        $pagesize  = 20;
        $offset    = $pagesize*($page-1);
        $limit     = " LIMIT $offset,$pagesize";
		$order     = " ORDER BY procedure_id DESC";
        $sql_ct    = "SELECT procedure_id FROM $this->table_name $where";
        $query     = $this->dbr->query($sql_ct);
        $log_num   = $query->num_rows();
        $pages     = pages($log_num, $page, $pagesize);
        $sql       = "SELECT procedure_id, art_id, consult_id, order_id, drawback_id, content, operator, ctime FROM $this->table_name $where $order $limit";
        log_message('debug', '[******]'. __METHOD__ .':'.__LINE__.' order_list sql [' . $sql .']');
        $result    = $this->dbr->query($sql);
        $list_data = $result->result_array();
        
        
        $this->smarty->assign('search_arr', $search_arr);
        $this->smarty->assign('list_data', $list_data);
        $this->smarty->assign('pages', $pages);
        $this->smarty->assign('show_dialog', 'true'); 
        $this->smarty->display("product/procedure_list.html");
    }
    
    
}
