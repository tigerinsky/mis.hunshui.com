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
        $this->load->model("member/consult_list_model", "consult_list_model");
        $this->load->model("member/order_list_model", "order_list_model");
        $this->load->model("member/procedure_log_model", "procedure_log_model");
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
        		'consult_status'=>array(
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
        	$media_uid = $item_consult['media_uid']; // 媒体主id
        	$order_id = $item_consult['order_id']; // 订单id
        	//log_message('debug', '[******************************]'. __METHOD__ .':'.__LINE__.' curr uid [' . $uid .']');
        	// 通过询购id获取询购信息
        	$adv_consult_info = $this->adv_consult_model->get_adv_consult_info_by_aid($consult_id);
        	// 通过询购id获取文章id
        	$art_id = $adv_consult_info['art_id'];
        	// 通过文章id获取文章信息
        	$adv_article_info = $this->adv_article_model->get_adv_article_info_by_art_id($art_id);
        	log_message('debug', '[******************************]'. __METHOD__ .':'.__LINE__.' result [' . json_encode($adv_article_info) .']');
        	// 通过公众号id获取公众号信息
        	$official_accounts_info = $this->official_accounts_model->get_ofc_info_by_oaid($oaid);
        	log_message('debug', '[******************************]'. __METHOD__ .':'.__LINE__.' result [' . json_encode($official_accounts_info) .']');
        	// 通过媒体主id获取用户信息
        	$user_info = $this->user_model->get_user_info_by_uid($media_uid);
        	log_message('debug', '[******************************]'. __METHOD__ .':'.__LINE__.' result [' . json_encode($user_info) .']');
        	// 通过订单id获取订单信息
        	$order_info = $this->order_list_model->get_order_info_by_olid($order_id);
        	log_message('debug', '[******************************]'. __METHOD__ .':'.__LINE__.' result [' . json_encode($order_info) .']');
        	
        	$item_consult['article_title'] = $adv_article_info['title']; // 文章标题
        	$item_consult['ofc_nick_name'] = $official_accounts_info['nick_name']; // 公众号昵称
        	$item_consult['user_wx_name'] = $user_info['wx_name']; // 媒体主微信号
        	$item_consult['user_nick_name'] = $user_info['nick_name']; // 媒体主昵称
        	$item_consult['user_phone'] = $user_info['phone']; // 媒体主手机号
        	$item_consult['show_day'] = $adv_consult_info['show_day']; // 投放时间
        	$item_consult['ad_location'] = $adv_consult_info['ad_location']; // 投放位置
        	$item_consult['feedback_time'] = $order_info['ctime']; // 反馈时间
        	$item_consult['discount_price'] = number_format($order_info['ad_price']/100, 2, '.', ''); // 优惠金额
        	$item_consult['total_price'] = number_format($order_info['total_price']/100, 2, '.', ''); // 实际交易金额(含税)
        	$res_content[] = $item_consult;
        }
        
        $consult_status_list=array(1=>'待审核', 2=>'通过', 3=>'不通过');
        $search_arr['consult_status_sel']=$this->form->select($consult_status_list,$consult_status_id,'name="consult_status_id"','询购状态');
        
        $ad_location_list=array(1=>'单图文', 2=>'多图文头条', 4=>'多图文2~N条');
        
        
        $this->smarty->assign('search_arr', $search_arr);
        $this->smarty->assign('consult_status_list', $consult_status_list);
        $this->smarty->assign('ad_location_list', $ad_location_list);
        $this->smarty->assign('list_data', $res_content);
        $this->smarty->assign('pages', $pages);
        $this->smarty->assign('show_dialog', 'true'); 
        $this->smarty->display("product/consult_list.html");
    }
    
    
    public function consult_view() {
    	$this->load->library('form');
    	$clid = intval($this->input->get('id'));
    	 
    	$consult_info = $this->consult_list_model->get_consult_info_by_clid($clid);
    	 
    	$consult_id = $consult_info['consult_id'];
    	$adv_consult_info = $this->adv_consult_model->get_adv_consult_info_by_aid($consult_id);
    	$art_id = $adv_consult_info['art_id'];
    	$adv_article_info = $this->adv_article_model->get_adv_article_info_by_art_id($art_id);
    	$adv_consult_info['show_day']=date('Y-m-d h:i:s',$adv_consult_info['show_day']);
    	$input_box['show_day']=$this->form->date('info[show_day]',$adv_consult_info['show_day'],1);
    	 
    	$order_id = $consult_info['order_id'];
    	$order_info = $this->order_list_model->get_order_info_by_olid($order_id);
    	
    	$order_info['original_price'] = number_format($order_info['original_price']/100, 2, '.', ''); // 原价
    	$order_info['total_price'] = number_format($order_info['total_price']/100, 2, '.', ''); // 实际交易金额(含税)
    	 
    	//$consult_status_list=array(1=>'待审核', 2=>'通过', 3=>'不通过');
    	$ad_location_list=array(1=>'单图文', 2=>'多图文头条', 4=>'多图文2~N条');
    	//$input_box['consult_status_sel']=$this->form->select($consult_status_list,$consult_info['status'],'name="info[consult_status]"','询购状态');
    	$input_box['ad_location_sel']=$this->form->select($ad_location_list,$adv_consult_info['ad_location'],'name="info[ad_location]"','投放位置');
    	 
    	 
    	$this->smarty->assign('consult_info', $consult_info);
    	$this->smarty->assign('adv_consult_info', $adv_consult_info);
    	$this->smarty->assign('adv_article_info', $adv_article_info);
    	$this->smarty->assign('order_info', $order_info);
    	$this->smarty->assign('input_box',$input_box);
    	$this->smarty->assign('show_dialog','true');
    	$this->smarty->assign('show_validator','true');
    	$this->smarty->display("product/consult_view.html");
    }
    
    
    public function consult_edit() {
    	$this->load->library('form');
    	$clid = intval($this->input->get('id'));
    	
    	$consult_info = $this->consult_list_model->get_consult_info_by_clid($clid);
    	
    	$consult_id = $consult_info['consult_id'];
    	$adv_consult_info = $this->adv_consult_model->get_adv_consult_info_by_aid($consult_id);
    	
    	$art_id = $adv_consult_info['art_id'];
    	$adv_article_info = $this->adv_article_model->get_adv_article_info_by_art_id($art_id);
    	$adv_consult_info['show_day']=date('Y-m-d h:i:s',$adv_consult_info['show_day']);
    	$input_box['show_day']=$this->form->date('info[show_day]',$adv_consult_info['show_day'],1);
    	
    	$order_id = $consult_info['order_id'];
    	$order_info = $this->order_list_model->get_order_info_by_olid($order_id);
    	
    	$order_info['original_price'] = number_format($order_info['original_price']/100, 2, '.', ''); // 原价
    	$order_info['total_price'] = number_format($order_info['total_price']/100, 2, '.', ''); // 实际交易金额(含税)
    	
    	//$consult_status_list=array(1=>'待审核', 2=>'通过', 3=>'不通过');
    	$ad_location_list=array(1=>'单图文', 2=>'多图文头条', 4=>'多图文2~N条');
    	//$input_box['consult_status_sel']=$this->form->select($consult_status_list,$consult_info['status'],'name="info[consult_status]"','询购状态');
    	$input_box['ad_location_sel']=$this->form->select($ad_location_list,$adv_consult_info['ad_location'],'name="info[ad_location]"','投放位置');
    	
    	
    	$this->smarty->assign('consult_info', $consult_info);
    	$this->smarty->assign('adv_consult_info', $adv_consult_info);
    	$this->smarty->assign('adv_article_info', $adv_article_info);
    	$this->smarty->assign('order_info', $order_info);
    	$this->smarty->assign('input_box',$input_box);
    	$this->smarty->assign('show_dialog','true');
    	$this->smarty->assign('show_validator','true');
    	$this->smarty->display("product/consult_edit.html");
    }
    
    
    
    
    public function consult_edit_do() {
    	$cfg = $this->input->post('cfg');
    	if($cfg['clid'] < 1 || $cfg['aid'] < 1) {
    		show_tips('参数异常，请检测');
    	} else {
    		$clid = $cfg['clid'];
    		$aid = $cfg['aid'];
    		$olid = $cfg['olid'];
    		// old
    		$old_info = $this->consult_list_model->get_consult_info_by_clid($clid);
    	}
    	$info = $this->input->post('info');
    	
    	log_message('debug', '[******************************]'. __METHOD__ .':'.__LINE__.' info [' . json_encode($info) .']');
    	
    	$cur_time = time();
    	
    	// 修改adv_consult表
    	$adv_consult_info = array(
    			'show_day'	  => strtotime($info['show_day']),
    			'ad_location' => $info['ad_location'],
    			'utime'       => $cur_time,
    	);
    	$adv_consult_flag = $this->adv_consult_model->update_info($adv_consult_info, $aid);
    	
    	/*
    	// 修改consult_list表
    	$consult_info = array(
    			'status'	  => !empty($info['consult_status']) ? $info['consult_status'] : 1,
    			'utime'       => $cur_time,
    	);
    	$consult_flag = $this->consult_list_model->update_info($consult_info, $clid);
    	*/
    	
    	// 记入流程表
    	// new
    	$new_info = $this->consult_list_model->get_consult_info_by_clid($clid);
    	$content_array = array();
    	if ($old_info['status'] != $new_info['status']) {
    		$content_array[] = 'status(' . $old_info['status'] . ' => ' . $new_info['status'] . ')';
    	}
    	 
    	$procedure_log_info = array(
    			'art_id'		=> 0,
    			'consult_id'	=> $clid,
    			'order_id' 		=> 0,
    			'drawback_id' 	=> 0,
    			'content'      	=> join(';', $content_array),
    			'operator'      => $this->session->userdata('mis_user'), // 获取mis用户
    			'ctime'       	=> $cur_time,
    	);
    	$this->procedure_log_model->create_info($procedure_log_info);
    	
    	if($adv_consult_flag){
    		show_tips('操作成功','','','edit');
    	}else{
    		show_tips('操作异常，请检测');
    	}
    	
    }
    
    
    public function consult_cancel_one_ajax() {
    	$clid = intval($this->input->get('clid'));
    	if($clid>0) {
    		$cur_time = time();
    		
    		$consult_info = $this->consult_list_model->get_consult_info_by_clid($clid);
    		// 修改consult_list表,状态：1、待审核，2、通过， 3、不通过
    		$consult_info = array(
    				'status' => 3,
    				'utime'  => $cur_time,
    		);
    		$consult_flag = $this->consult_list_model->update_info($consult_info, $clid);
    		
    		$consult_id = $consult_info['consult_id'];
    		// 修改adv_consult表,状态：1、新增，2、通过，3、拒接
    		$adv_consult_info = array(
    				'status' => 3,
    				'utime'  => $cur_time,
    		);
    		$adv_consult_flag = $this->adv_consult_model->update_info($adv_consult_info, $consult_id);
    		
    		$order_id = $consult_info['order_id'];
    		if ($order_id > 0) {
    			// 修改order_list表,状态1、创建，2、划款待执行，3媒体主执行完成、9订单完成、10订单取消
    			$order_info = array(
    					'status' => 10,
    					'utime'  => $cur_time,
    			);
    			$order_flag = $this->order_list_model->update_info($order_info, $order_id);
    		}
    		//echo 1;
    		show_tips('操作成功','','','cancel');
    	} else {
    		//echo 0;
    		show_tips('操作异常，请检测');
    	}
    }
    
	
	
}
