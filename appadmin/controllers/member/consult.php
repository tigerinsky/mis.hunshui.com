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
        
        $consult_status_list=array(1=>'待审核', 2=>'通过', 3=>'不通过');
        $search_arr['consult_status_sel']=$this->form->select($consult_status_list,$consult_status_id,'name="consult_status_id"','接单状态');
        
        $this->smarty->assign('search_arr', $search_arr);
        $this->smarty->assign('consult_status_list', $consult_status_list);
        $this->smarty->assign('list_data', $list_data);
        $this->smarty->assign('pages', $pages);
        $this->smarty->assign('show_dialog', 'true'); 
        $this->smarty->display("member/consult_list.html");
    }
	
	
}
