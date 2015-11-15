<?php

class official_accounts extends MY_Controller {

    protected static $common_config;
    protected $table_name;
    public function __construct() {
        parent::__construct();
        $this->load->library('form');
        $this->dbr = $this->load->database("dbr", TRUE);
        $this->load->config("common_config", TRUE);
        self::$common_config = $this->config->item('common_config');
        $this->load->model("member/user_model", "user_model");
        $this->table_name = "official_accounts";
    }

    public function index() {
        $this->official_accounts();
    }
    
    public function official_accounts() {
    	$this->load->library('form');
        $page = $this->input->get('page');
        $page = max(intval($page),1);
        $dosearch = $this->input->get('dosearch');
        
        $search_arr['status'] = 0;
        $where_array[] = "status = 0";
        
        if($dosearch == 'ok'){
        	
        	$search_filed=array(
        		'time_regular'=>array(
        			1=>'time_regular=1',
        			2=>'time_regular=2',
        		),
        	);
        	
        	if(intval($this->input->get('time_regular_id'))!=''){
        		$time_regular_id=$this->input->get('time_regular_id');
        		if($search_filed['time_regular'][$time_regular_id]!=''){
        			$where_array[]=$search_filed['time_regular'][$time_regular_id];
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
                $where_array[] = "ofc_account like '%{$keywords}%' or nick_name like '%{$keywords}%' ";
            }
        }
        if(is_array($where_array) and count($where_array) > 0) {
            $where = ' WHERE '.join(' AND ',$where_array);
        }
        $pagesize  = 20;
        $offset    = $pagesize*($page-1);
        $limit     = " LIMIT $offset,$pagesize";
		$order     = " ORDER BY oaid DESC";
        $sql_ct    = "SELECT oaid FROM $this->table_name $where";
        $query     = $this->dbr->query($sql_ct);
        $log_num   = $query->num_rows();
        $pages     = pages($log_num, $page, $pagesize);
        $sql       = "SELECT oaid, uid, ofc_account, nick_name, fan_num, rnum_perweek, male_percent, female_percent, push_time, time_regular, single_price, frt_multi_price, multi_price, head_pic, qr_pic, wx_idty, abstract, category, status FROM $this->table_name $where $order $limit";
        log_message('debug', '[******]'. __METHOD__ .':'.__LINE__.' official_accounts sql [' . $sql .']');
        $result    = $this->dbr->query($sql);
        //log_message('debug', '[******]'. __METHOD__ .':'.__LINE__.' result [' . json_encode($result) .']');
        $list_data = $result->result_array();
        
        $time_regular_list=array(1=>'固定', 2=>'不固定');
        $search_arr['time_regular_sel']=$this->form->select($time_regular_list,$time_regular_id,'name="time_regular_id"','发布时间是否固定');
        
        $this->smarty->assign('search_arr', $search_arr);
        $this->smarty->assign('time_regular_list', $time_regular_list);
        $this->smarty->assign('list_data', $list_data);
        $this->smarty->assign('pages', $pages);
        $this->smarty->assign('show_dialog', 'true'); 
        $this->smarty->display("member/official_accounts.html");
    }
	
	
}
