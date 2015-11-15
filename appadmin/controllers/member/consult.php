<?php

class consult extends MY_Controller {

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
        $this->load->model("member/official_accounts_model", "official_accounts_model");
        $this->table_name = "consult_list";
    }

    public function index() {
        $this->consult_list();
    }
    
    public function consult_list() {
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
        	
        	if(intval($this->input->get('consult_status_id'))!=''){
        		$consult_status_id=$this->input->get('consult_status_id');
        		if($search_filed['consult_status'][$consult_status_id]!=''){
        			$where_array[]=$search_filed['consult_status'][$consult_status_id];
        		}
        	}
        	
        	$phone = trim($this->input->get('phone'));
        	$search_arr['phone'] = $phone;
        	if($phone != ''){
        		// 通过手机号获取用户id
        		$user_info = $this->user_model->get_user_info_by_phone($phone);
        		$uid = $user_info['uid'];
        		$where_array[] = "media_uid = '{$uid}' ";
        	}
        	
            $keywords = trim($this->input->get('keywords'));
            $search_arr['keywords'] = $keywords;
            if($keywords != ''){
                $where_array[] = "oaid like '%{$keywords}%' or media_uid like '%{$keywords}%' ";
            }
        }
        if(is_array($where_array) and count($where_array) > 0) {
            $where = ' WHERE '.join(' AND ',$where_array);
        }
        $pagesize  = 20;
        $offset    = $pagesize*($page-1);
        $limit     = " LIMIT $offset,$pagesize";
		$order     = " ORDER BY clid DESC";
        $sql_ct    = "SELECT clid FROM $this->table_name $where";
        $query     = $this->dbr->query($sql_ct);
        $log_num   = $query->num_rows();
        $pages     = pages($log_num, $page, $pagesize);
        $sql       = "SELECT clid, oaid, media_uid, consult_id, flash_sale_id, order_id, status, ctime, utime FROM $this->table_name $where $order $limit";
        log_message('debug', '[******]'. __METHOD__ .':'.__LINE__.' consult_list sql [' . $sql .']');
        $result    = $this->dbr->query($sql);
        //log_message('debug', '[******]'. __METHOD__ .':'.__LINE__.' result [' . json_encode($result) .']');
        $list_data = $result->result_array();
        
        // 获取详情
        $res_content = array();
        foreach($list_data as $item_consult) {
        	$oaid = $item_consult['oaid']; // 公众帐号id
        	$consult_id = $item_consult['consult_id']; // 询购id
        	//log_message('debug', '[******************************]'. __METHOD__ .':'.__LINE__.' curr uid [' . $uid .']');
        	// 通过询购id获取文章id
        	$adv_consult_info = $this->adv_consult_model->get_adv_consult_info_by_aid($consult_id);
        	$art_id = $adv_consult_info['art_id'];
        	$adv_article_info = $this->adv_article_model->get_adv_article_info_by_art_id($art_id);
        	log_message('debug', '[******************************]'. __METHOD__ .':'.__LINE__.' result [' . json_encode($adv_article_info) .']');
        	// 通过公众号id获取公众号信息
        	$official_accounts_info = $this->official_accounts_model->get_ofc_info_by_oaid($oaid);
        	log_message('debug', '[******************************]'. __METHOD__ .':'.__LINE__.' result [' . json_encode($official_accounts_info) .']');
        	$item_consult['article_title'] = $adv_article_info['title'];
        	$item_consult['ofc_nick_name'] = $official_accounts_info['nick_name'];
        	$res_content[] = $item_consult;
        }
        
        $consult_status_list=array(1=>'待审核', 2=>'通过', 3=>'不通过');
        $search_arr['consult_status_sel']=$this->form->select($consult_status_list,$consult_status_id,'name="consult_status_id"','接单状态');
        
        $this->smarty->assign('search_arr', $search_arr);
        $this->smarty->assign('consult_status_list', $consult_status_list);
        $this->smarty->assign('list_data', $res_content);
        $this->smarty->assign('pages', $pages);
        $this->smarty->assign('show_dialog', 'true'); 
        $this->smarty->display("member/consult_list.html");
    }
	
	
}
