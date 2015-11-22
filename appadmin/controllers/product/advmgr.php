<?php

class advmgr extends MY_Controller {

    protected static $common_config;
    protected $table_name;
    public function __construct() {
        parent::__construct();
        $this->load->library('form');
        $this->dbr = $this->load->database("dbr", TRUE);
        $this->load->config("common_config", TRUE);
        self::$common_config = $this->config->item('common_config');
        $this->load->model("member/adv_article_model", "adv_article_model");
        $this->load->model("member/user_model", "user_model");
        $this->table_name = "adv_consult";
    }

    public function index() {
        $this->advmgr_list();
    }
    
    public function advmgr_list() {
    	$this->load->library('form');
        $page = $this->input->get('page');
        $page = max(intval($page),1);
        $dosearch = $this->input->get('dosearch');
        
        $search_arr = array();
        $where_array = array();
        
        if($dosearch == 'ok'){
        	
        	$search_filed=array(
        		'status'=>array(
        			1=>'status=1',
        			2=>'status=2',
        			3=>'status=3',
        		),
        	);
        	
        	if(intval($this->input->get('order_status_id'))!=''){
        		$order_status_id=$this->input->get('order_status_id');
        		if($search_filed['order_status'][$order_status_id]!=''){
        			$where_array[]=$search_filed['order_status'][$order_status_id];
        		}
        	}
        	
            $phone = trim($this->input->get('phone'));
            $search_arr['phone'] = $phone;
            if($phone != ''){
            	// 通过手机号获取用户id
            	$user_info = $this->user_model->get_user_info_by_phone($phone);
            	$uid = $user_info['uid'];
                $where_array[] = "uid = '{$uid}' ";
            }
        	
            $keywords = trim($this->input->get('keywords'));
            $search_arr['keywords'] = $keywords;
            if($keywords != ''){
                $where_array[] = "ad_location like '%{$keywords}%' or remark like '%{$keywords}%' ";
            }
        }
        if(is_array($where_array) and count($where_array) > 0) {
            $where = ' WHERE '.join(' AND ',$where_array);
        }
        $pagesize  = 20;
        $offset    = $pagesize*($page-1);                                                                                     
        $limit     = " LIMIT $offset,$pagesize";
		$order     = " ORDER BY aid DESC";
        $sql_ct    = "SELECT aid FROM $this->table_name $where";
        $query     = $this->dbr->query($sql_ct);
        $log_num   = $query->num_rows();
        $pages     = pages($log_num, $page, $pagesize);
        $sql       = "SELECT aid, show_day, ad_location, remark, category, uid, art_id, ctime, utime, status, show_hours, limit_hours, fans FROM $this->table_name $where $order $limit";
        log_message('debug', '[*************************************]'. __METHOD__ .':'.__LINE__.' order_list sql [' . $sql .']');
        $result    = $this->dbr->query($sql);
        log_message('debug', '[*************************************]'. __METHOD__ .':'.__LINE__.' result [' . json_encode($result) .']');
        $list_data = $result->result_array();
        
        // 获取详情
        $res_content = array();
        foreach($list_data as $item_order) {
        	$uid = $item_order['uid'];
        	$art_id = $item_order['art_id'];
        	log_message('debug', '[******************************]'. __METHOD__ .':'.__LINE__.' curr uid [' . $uid .']');
        	$adv_article_info = $this->adv_article_model->get_adv_article_info_by_art_id($art_id);
        	log_message('debug', '[******************************]'. __METHOD__ .':'.__LINE__.' result [' . json_encode($adv_article_info) .']');
        	$item_order['article_title'] = $adv_article_info['title'];
        	$res_content[] = $item_order;
        }
        
        
        $order_status_list=array(1=>'新增', 2=>'通过', 3=>'拒接');
        $search_arr['order_status_sel']=$this->form->select($order_status_list,$order_status_id,'name="order_status_id"','投放状态');
        
        $this->smarty->assign('search_arr', $search_arr);
        $this->smarty->assign('order_status_list', $order_status_list);
        $this->smarty->assign('list_data', $res_content);
        $this->smarty->assign('pages', $pages);
        $this->smarty->assign('show_dialog', 'true'); 
        $this->smarty->display("member/order_list.html");
    }
	
}
