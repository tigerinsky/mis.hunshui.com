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
        $this->load->model("member/adv_consult_model", "adv_consult_model");
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
        	
//             $phone = trim($this->input->get('phone'));
//             $search_arr['phone'] = $phone;
//             if($phone != ''){
//             	// 通过手机号获取用户id
//             	$user_info = $this->user_model->get_user_info_by_phone($phone);
//             	$uid = $user_info['uid'];
//                 $where_array[] = "uid = '{$uid}' ";
//             }
        	
//             $keywords = trim($this->input->get('keywords'));
//             $search_arr['keywords'] = $keywords;
//             if($keywords != ''){
//                 $where_array[] = "ad_location like '%{$keywords}%' or remark like '%{$keywords}%' ";
//             }
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
        $sql       = "SELECT c.aid, c.show_day, c.ad_location, c.remark, c.category, c.uid, c.art_id, c.ctime, c.utime, c.status, c.show_hours, c.limit_hours, c.fans, a.title, u.phone, u.cmpy_name, u.level FROM adv_consult as c,adv_article as a,user as u $where $order $limit";
        log_message('debug', '[*************************************]'. __METHOD__ .':'.__LINE__.' advmgr_list sql [' . $sql .']');
        $result    = $this->dbr->query($sql);
        log_message('debug', '[*************************************]'. __METHOD__ .':'.__LINE__.' result [' . json_encode($result) .']');
        $list_data = $result->result_array();
        
        $order_status_list=array(1=>'新增', 2=>'通过', 3=>'拒接');
        $search_arr['order_status_sel']=$this->form->select($order_status_list,$order_status_id,'name="order_status_id"','投放状态');
        
        $search_arr['time_start']=$this->form->date('time_start',$time_start,1);
        $search_arr['time_end']=$this->form->date('time_end',$time_end,1);
        
        $this->smarty->assign('search_arr', $search_arr);
        $this->smarty->assign('order_status_list', $order_status_list);
        //$this->smarty->assign('list_data', $res_content);
        $this->smarty->assign('list_data', $list_data);
        $this->smarty->assign('pages', $pages);
        $this->smarty->assign('show_dialog', 'true'); 
        $this->smarty->display("product/advmgr_list.html");
    }
    
    
    public function advmgr_view() {
    	$this->load->library('form');
    	$aid = intval($this->input->get('id'));
    	
    	$where_array = array();
    	$where_array[] = "c.uid = u.uid ";
    	$where_array[] = "c.art_id = a.art_id ";
    	$where_array[] = "c.aid = '{$aid}' ";
    	 
    	if(is_array($where_array) and count($where_array) > 0) {
    		$where = ' WHERE '.join(' AND ',$where_array);
    	}
    	
    	$sql = "SELECT u.cmpy_name, u.phone, u.wx_name, u.nick_name, u.level, a.title, a.author, a.content, a.url, a.abstract, a.original_link, c.aid, c.show_day, c.ad_location, c.remark, c.category, c.uid, c.art_id FROM adv_consult as c,adv_article as a,user as u $where";
    	$result = $this->db->query($sql);
    	$info = $result->row_array();
    	$info['show_day']=date('Y-m-d h:i:s',$info['show_day']);
    	$input_box['show_day']=$this->form->date('info[show_day]',$info['show_day'],1);
    	
    	$this->smarty->assign('info', $info);
    	$this->smarty->assign('input_box',$input_box);
    	$this->smarty->assign('show_dialog','true');
    	$this->smarty->assign('show_validator','true');
    	$this->smarty->display("product/advmgr_view.html");
    }
    
    
    public function advmgr_edit() {
    	$this->load->library('form');
    	$aid = intval($this->input->get('id'));
    	
    	$where_array = array();
    	$where_array[] = "c.uid = u.uid ";
    	$where_array[] = "c.art_id = a.art_id ";
    	$where_array[] = "c.aid = '{$aid}' ";
    	
    	if(is_array($where_array) and count($where_array) > 0) {
    		$where = ' WHERE '.join(' AND ',$where_array);
    	}
    	
    	$sql = "SELECT u.cmpy_name, u.phone, u.wx_name, u.nick_name, u.level, a.title, a.author, a.content, a.url, a.abstract, a.original_link, c.aid, c.show_day, c.ad_location, c.remark, c.category, c.uid, c.art_id FROM adv_consult as c,adv_article as a,user as u $where";
    	$result = $this->db->query($sql);
    	$info = $result->row_array();
    	$info['show_day']=date('Y-m-d h:i:s',$info['show_day']);
    	$input_box['show_day']=$this->form->date('info[show_day]',$info['show_day'],1);
    	
    	$this->smarty->assign('info', $info);
    	$this->smarty->assign('input_box',$input_box);
    	$this->smarty->assign('show_dialog','true');
    	$this->smarty->assign('show_validator','true');
    	$this->smarty->display("product/advmgr_edit.html");
    }
    
    
    public function advmgr_edit_do() {
    	$cfg = $this->input->post('cfg');
    	if($cfg['aid'] < 1 || $cfg['art_id'] < 1) {
    		show_tips('参数异常，请检测');
    	} else {
    		$aid = $cfg['aid'];
    		$art_id = $cfg['art_id'];
    	}
    	$info = $this->input->post('info');
    	/*
    	if ($_FILES['pic']['name'] != "") {
    		$this->load->library('oss');
    		$pic_ret = $this->oss->upload('pic', array('dir'=>'url'));
    		if (isset($pic_ret['error_code']) && intval($pic_ret['error_code'])) {
    			show_tips($pic_ret['error_code']. ":" . $pic_ret['error']);
    		}
    		$info['url'] = $pic_ret;
    	}
    	*/
    	
    	$cur_time = time();
    	
    	// 修改adv_consult表
    	$consult_info = array(
    		'category'	  => $info['category'],
    		'show_day'	  => strtotime($info['show_day']),
    		'ad_location' => $info['ad_location'],
    		'remark'      => $info['remark'],
    		'utime'       => $cur_time,
    	);
    	$consult_flag = $this->adv_consult_model->update_info($consult_info,$aid);
    	
    	// 修改adv_article表
    	$article_info = array(
    		'title'	  		=> $info['title'],
    		'author'	  	=> $info['author'],
    		'content' 		=> $info['content'],
    		'url'      		=> $info['url'],
    		'abstract'      => $info['abstract'],
    		'original_link'	=> $info['original_link'],
    		'utime'       	=> $cur_time,
    	);
    	$article_flag = $this->adv_article_model->update_info($article_info, $art_id);
    	
    	if($consult_flag && $article_flag){
    		show_tips('操作成功','','','edit');
    	}else{
    		show_tips('操作异常，请检测');
    	}
    	
    }
    
    
    public function advmgr_del_one_ajax() {
    	$aid = intval($this->input->get('aid'));
    	if($aid>0) {
    		// 状态：1、新增，2、通过，3、拒接
    		$del_query = "UPDATE {$this->table_name}  SET `status`= 3 WHERE aid={$aid}";
    		$this->db->query($del_query);
    		echo 1;
    	} else {
    		echo 0;
    	}
    
    }
    
    
}
