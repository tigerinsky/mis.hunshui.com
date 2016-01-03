<?php

class banner extends MY_Controller {

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
        $this->load->model("member/banner_model", "banner_model");
        $this->table_name = "banner";
    }

    public function index() {
        $this->banner_list();
    }
    
    public function banner_list() {
    	$this->load->library('form');
        $page = $this->input->get('page');
        $page = max(intval($page),1);
        $dosearch = $this->input->get('dosearch');
        
        $search_arr['is_deleted']=1;
        $where_array[]="is_deleted=1";
        
        if($dosearch == 'ok'){
        	
        	$search_filed=array();
        	
            $keywords = trim($this->input->get('keywords'));
            $search_arr['keywords'] = $keywords;
            if($keywords != ''){
                $where_array[] = "description like '%{$keywords}%' ";
            }
        }
        if(is_array($where_array) and count($where_array) > 0) {
            $where = ' WHERE '.join(' AND ',$where_array);
        }
        $pagesize  = 20;
        $offset    = $pagesize*($page-1);
        $limit     = " LIMIT $offset,$pagesize";
		$order     = " ORDER BY bid DESC";
        $sql_ct    = "SELECT bid FROM $this->table_name $where";
        $query     = $this->dbr->query($sql_ct);
        $log_num   = $query->num_rows();
        $pages     = pages($log_num, $page, $pagesize);
        $sql       = "SELECT bid, url, description, ctime, utime, rank, is_deleted FROM $this->table_name $where $order $limit";
        log_message('debug', '[******************************]'. __METHOD__ .':'.__LINE__.' banner_list sql [' . $sql .']');
        $result    = $this->dbr->query($sql);
        $list_data = $result->result_array();
        
        log_message('debug', '[******************************]'. __METHOD__ .':'.__LINE__.' banner_list [' . json_encode($list_data) .']');
        
        $this->smarty->assign('search_arr', $search_arr);
        $this->smarty->assign('list_data', $list_data);
        $this->smarty->assign('pages', $pages);
        $this->smarty->assign('show_dialog', 'true'); 
        $this->smarty->display("product/banner_list.html");
    }
    
    
    // 新建banner
    function banner_add(){
    	$this->load->library('form');
    	
    	$this->smarty->assign('random_version', rand(100,999));
    	$this->smarty->assign('show_dialog','true');
    	$this->smarty->assign('show_validator','true');
    	$this->smarty->display('product/banner_add.html');
    }
    
    
    //执行新建banner操作
    function banner_add_do(){
    	$info = $this->input->post('info');
    	$pic  = $this->input->post('pic');
    	
    	$url = $pic[0];
    	
    	$cur_time = time();
    	$banner_info = array(
    			'url'	  		=> $url,
    			'description'	=> $info['description'],
    			'ctime'     	=> $cur_time,
    			'utime'     	=> $cur_time,
    	);
    
    	if( $url != ''){
    		if($this->banner_model->create_info($banner_info)){
    			show_tips('操作成功','','','add');
    		}else{
    			show_tips('操作异常');
    		}
    	}else{
    		show_tips('数据不完整，请检测');
    	}
    }
    
    
    
    public function banner_edit() {
    	$this->load->library('form');
    	$bid = intval($this->input->get('id'));
    	
    	// banner信息
    	$banner_info = $this->banner_model->get_banner_info_by_bid($bid);
    	
    	$this->smarty->assign('banner_info', $banner_info);
    	$this->smarty->assign('random_version', rand(100,999));
    	$this->smarty->assign('show_dialog','true');
    	$this->smarty->assign('show_validator','true');
    	$this->smarty->display("product/banner_edit.html");
    }
    
    
    
    public function banner_edit_do() {
    	$cfg = $this->input->post('cfg');
    	if($cfg['bid'] < 1 || $cfg['bid'] < 1) {
    		show_tips('参数异常，请检测');
    	} else {
    		$bid = $cfg['bid'];
    	}
    	$info = $this->input->post('info');
    	$pic = $this->input->post('pic');
    	
    	$url = $pic[0];
    	
    	$cur_time = time();
    	// 修改banner表
    	$banner_info = array(
    		'url' 			=> $url,
    		'description' 	=> $info['description'],
    		'utime'     	=> $cur_time,
    	);
    	$banner_flag = $this->banner_model->update_info($banner_info, $bid);
    	
    	if($banner_flag){
    		show_tips('操作成功','','','edit');
    	}else{
    		show_tips('操作异常，请检测');
    	}
    	
    }
    
    
    
    public function banner_del_one_ajax() {
    	$bid = intval($this->input->get('bid'));
    	if($bid>0) {
    		$cur_time = time();
    		
    		// 修改banner表, 是否删除：1、未删除，2、已删除
    		$banner_info = array(
    				'is_deleted' => 2,
    				'utime'  => $cur_time,
    		);
    		$publish_flag = $this->banner_model->update_info($banner_info, $bid);
    		echo 1;
    	} else {
    		echo 0;
    	}
    }
    
    
    public function banner_top_one_ajax() {
    	$bid = intval($this->input->get('bid'));
    	if($bid>0) {
    		$cur_time = time();
    		
    		$item = $this->banner_model->get_max_rank();
    		$rank = intval($item['rank']) + 1;
    		// 修改banner表, 置顶操作
    		$banner_info = array(
    				'rank'	=> $rank,
    				'utime' => $cur_time,
    		);
    		$publish_flag = $this->banner_model->update_info($banner_info, $bid);
    		echo $rank;
    	} else {
    		echo 0;
    	}
    }
    
	
	
}
