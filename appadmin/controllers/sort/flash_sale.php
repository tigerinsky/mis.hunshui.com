<?php

class flash_sale extends MY_Controller {

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
        $this->load->model("member/drawback_list_model", "drawback_list_model");
        $this->load->model("member/procedure_log_model", "procedure_log_model");
        $this->load->model("member/publish_list_model", "publish_list_model");
        $this->load->model("member/flash_sale_model", "falsh_sale_model");
        $this->table_name = "flash_sale";
    }

    public function index() {
        $this->flash_sale_list();
    }
    
    public function flash_sale_list() {
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
                $where_array[] = "fsid like '%{$keywords}%' ";
            }
        }
        if(is_array($where_array) and count($where_array) > 0) {
            $where = ' WHERE '.join(' AND ',$where_array);
        }
        $pagesize  = 20;
        $offset    = $pagesize*($page-1);
        $limit     = " LIMIT $offset,$pagesize";
		$order     = " ORDER BY fsid DESC";
        $sql_ct    = "SELECT fsid FROM $this->table_name $where";
        $query     = $this->dbr->query($sql_ct);
        $log_num   = $query->num_rows();
        $pages     = pages($log_num, $page, $pagesize);
        $sql       = "SELECT fsid, oaid, uid, ad_location, promo_frt_multi_price, promo_single_price, show_day, show_hours, ctime, utime, first_order_cost, single_price, frt_multi_price, multi_price, promo_multi_price, status, rank FROM $this->table_name $where $order $limit";
        log_message('debug', '[******************************]'. __METHOD__ .':'.__LINE__.' banner_list sql [' . $sql .']');
        $result    = $this->dbr->query($sql);
        $list_data = $result->result_array();
        
        
        $this->smarty->assign('search_arr', $search_arr);
        $this->smarty->assign('list_data', $list_data);
        $this->smarty->assign('pages', $pages);
        $this->smarty->assign('show_dialog', 'true');
        $this->smarty->display("sort/flash_sale_list.html");
    }
    
    
    public function top_one_ajax() {
    	$fsid = intval($this->input->get('fsid'));
    	if($fsid>0) {
    		$cur_time = time();
    		
    		$item = $this->falsh_sale_model->get_max_rank();
    		$rank = intval($item['rank']) + 1;
	        log_message('debug', '[******************************]'. __METHOD__ .':'.__LINE__.' top_one_ajax [' . $rank .']');
    		// 置顶操作
    		$info = array(
    			'rank'	=> $rank,
    			'utime' => $cur_time,
    		);
    		$this->falsh_sale_model->update_info($info, $fsid);
    		echo $rank;
    	} else {
    		echo 0;
    	}
    }
    
	
	
}
