<?php

class adv_consult extends MY_Controller {

    protected static $common_config;
    protected $table_name;
    public function __construct() {
        parent::__construct();
        $this->load->library('form');
        $this->dbr = $this->load->database("dbr", TRUE);
        $this->load->config("common_config", TRUE);
        self::$common_config = $this->config->item('common_config');
        $this->load->model("member/adv_consult_model", "adv_consult_model");
        $this->load->model("member/adv_article_model", "adv_article_model");
        $this->load->model("member/user_model", "user_model");
        $this->load->model("member/procedure_log_model", "procedure_log_model");
        $this->table_name = "adv_consult";
    }

    public function index() {
        $this->adv_consult_list();
    }
    
    public function adv_consult_list() {
    	$this->load->library('form');
        $page = $this->input->get('page');
        $page = max(intval($page),1);
        $dosearch = $this->input->get('dosearch');
        
        $search_arr = array();
        $where_array = array();
        
        $where_array[] = "c.uid = u.uid ";
        $where_array[] = "c.art_id = a.art_id ";
        
        if($dosearch == 'ok'){
        	
        	$search_filed=array(
        		'status'=>array(
        			1=>'c.status=1',
        			2=>'c.status=2',
        			3=>'c.status=3',
        		),
        	);
        	
        	$time_start=$this->input->get('time_start');
        	$time_end=$this->input->get('time_end');
        	
        	if($time_start !='' && $time_end !=''){
        		$time1=strtotime($time_start);
        		$time2=strtotime($time_end);
        		$where_array[]="c.show_day>{$time1} AND c.show_day<{$time2}";
        	}
        	
        	if(intval($this->input->get('order_status_id'))!=''){
        		$order_status_id=$this->input->get('order_status_id');
        		if($search_filed['status'][$order_status_id]!=''){
        			$where_array[]=$search_filed['status'][$order_status_id];
        		}
        	}
        	
        	
        	$phone = trim($this->input->get('phone'));
        	$search_arr['phone'] = $phone;
        	if($phone != ''){
        		$where_array[] = "u.phone = '{$phone}' ";
        	}
        	
        	$cmpy_name = trim($this->input->get('cmpy_name'));
        	$search_arr['cmpy_name'] = $cmpy_name;
        	if($cmpy_name != ''){
        		$where_array[] = "u.cmpy_name like '%{$cmpy_name}%' ";
        	}
        	
        	$title = trim($this->input->get('title'));
        	$search_arr['title'] = $title;
        	if($title != ''){
        		$where_array[] = "a.title like '%{$title}%' ";
        	}
        	
        }
        if(is_array($where_array) and count($where_array) > 0) {
            $where = ' WHERE '.join(' AND ',$where_array);
        }
        $pagesize  = 20;
        $offset    = $pagesize*($page-1);                                                                                     
        $limit     = " LIMIT $offset,$pagesize";
		$order     = " ORDER BY aid DESC";
        $sql_ct    = "SELECT aid FROM adv_consult as c,adv_article as a,user as u $where";
        $query     = $this->dbr->query($sql_ct);
        $log_num   = $query->num_rows();
        $pages     = pages($log_num, $page, $pagesize);
        $sql       = "SELECT c.aid, c.show_day, c.ad_location, c.remark, c.category, c.uid, c.art_id, c.ctime, c.utime, c.status, c.show_hours, c.limit_hours, c.fans, a.title, u.phone, u.cmpy_name, u.level, c.rank FROM adv_consult as c,adv_article as a,user as u $where $order $limit";
        log_message('debug', '[*************************************]'. __METHOD__ .':'.__LINE__.' advmgr_list sql [' . $sql .']');
        $result    = $this->dbr->query($sql);
        log_message('debug', '[*************************************]'. __METHOD__ .':'.__LINE__.' result [' . json_encode($result) .']');
        $list_data = $result->result_array();
        
        // 获取详情
        $res_content = array();
        foreach($list_data as $item) {
        	$art_id = $item['art_id'];
        	$procedure_log_info = $this->procedure_log_model->get_procedure_log_by_art_id($art_id);
        	$item['operator'] = isset($procedure_log_info['operator']) ? $procedure_log_info['operator'] : '';
        	$res_content[] = $item;
        }
        
        $order_status_list=array(1=>'新增', 2=>'通过', 3=>'拒接');
        $search_arr['order_status_sel']=$this->form->select($order_status_list,$order_status_id,'name="order_status_id"','投放状态');
        
        $search_arr['time_start']=$this->form->date('time_start',$time_start,1);
        $search_arr['time_end']=$this->form->date('time_end',$time_end,1);
        
        $this->smarty->assign('search_arr', $search_arr);
        $this->smarty->assign('order_status_list', $order_status_list);
        $this->smarty->assign('list_data', $res_content);
        //$this->smarty->assign('list_data', $list_data);
        $this->smarty->assign('pages', $pages);
        $this->smarty->assign('show_dialog', 'true'); 
        $this->smarty->display("sort/adv_consult_list.html");
    }
    
    
    public function top_one_ajax() {
    	$aid = intval($this->input->get('aid'));
    	if($aid>0) {
    		$cur_time = time();
    
    		$item = $this->adv_consult_model->get_max_rank();
    		$rank = intval($item['rank']) + 1;
    		// 置顶操作
    		$info = array(
    				'rank'	=> $rank,
    				'utime' => $cur_time,
    		);
    		$this->adv_consult_model->update_info($info, $aid);
    		echo $rank;
    	} else {
    		echo 0;
    	}
    }
    
    
}
