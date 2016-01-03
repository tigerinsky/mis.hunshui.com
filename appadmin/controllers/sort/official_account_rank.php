<?php

class official_account_rank extends MY_Controller {

    protected static $common_config;
    protected $table_name;
    public function __construct() {
        parent::__construct();
        $this->load->library('form');
        $this->dbr = $this->load->database("dbr", TRUE);
        $this->load->config("common_config", TRUE);
        self::$common_config = $this->config->item('common_config');
        $this->load->model("member/official_account_rank_model", "official_account_rank_model");
        $this->table_name = "official_account_rank";
    }

    public function index() {
        $this->official_account_rank_list();
    }
    
    public function official_account_rank_list() {
    	$this->load->library('form');
        $page = $this->input->get('page');
        $page = max(intval($page),1);
        $dosearch = $this->input->get('dosearch');
        
        $search_arr = array();
        $where_array = array();
        
        if($dosearch == 'ok'){
        	
        	$search_filed=array();
        	
            $keywords = trim($this->input->get('keywords'));
            $search_arr['keywords'] = $keywords;
            if($keywords != ''){
                $where_array[] = "rank_id like '%{$keywords}%' ";
            }
        }
        if(is_array($where_array) and count($where_array) > 0) {
            $where = ' WHERE '.join(' AND ',$where_array);
        }
        $pagesize  = 20;
        $offset    = $pagesize*($page-1);
        $limit     = " LIMIT $offset,$pagesize";
		$order     = " ORDER BY rank_id DESC";
        $sql_ct    = "SELECT rank_id FROM $this->table_name $where";
        $query     = $this->dbr->query($sql_ct);
        $log_num   = $query->num_rows();
        $pages     = pages($log_num, $page, $pagesize);
        $sql       = "SELECT rank_id, idx_day, oaid, rank, price, order_num, trend, ctime, worth FROM $this->table_name $where $order $limit";
        log_message('debug', '[******************************]'. __METHOD__ .':'.__LINE__.' banner_list sql [' . $sql .']');
        $result    = $this->dbr->query($sql);
        $list_data = $result->result_array();
        
        
        $this->smarty->assign('search_arr', $search_arr);
        $this->smarty->assign('list_data', $list_data);
        $this->smarty->assign('pages', $pages);
        $this->smarty->assign('show_dialog', 'true');
        $this->smarty->display("sort/official_account_rank_list.html");
    }
    
    
    public function top_one_ajax() {
    	$rank_id = intval($this->input->get('rank_id'));
    	if($rank_id>0) {
    		$item = $this->official_account_rank_model->get_max_rank();
    		$worth = intval($item['worth']) + 1;
    		// 置顶操作
    		$info = array(
    			'worth'	=> $worth,
    		);
    		$this->official_account_rank_model->update_info($info, $rank_id);
    		echo $worth;
    	} else {
    		echo 0;
    	}
    }
    
	
	
}
