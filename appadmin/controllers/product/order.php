<?php

class order extends MY_Controller {

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
        $this->table_name = "order_list";
    }

    public function index() {
        $this->order_list();
    }
    
    public function order_list() {
    	$this->load->library('form');
        $page = $this->input->get('page');
        $page = max(intval($page),1);
        $dosearch = $this->input->get('dosearch');
        
        $search_arr = array();
        $where_array = array();
        
        if($dosearch == 'ok'){
        	
        	$search_filed=array(
        		'order_status'=>array(
        			1=>'status=1',
        			2=>'status=2',
        			3=>'status=3',
        			9=>'status=9',
        			10=>'status=10',
        		),
        		'pay_status'=>array(
        			1=>'pay_status=1',
        			2=>'pay_status=2',
        		),
        		'plat_payed'=>array(
        			1=>'plat_payed=1',
        			2=>'plat_payed=2',
        		),
        	);
        	
        	if(intval($this->input->get('order_status_id'))!=''){
        		$order_status_id=$this->input->get('order_status_id');
        		if($search_filed['order_status'][$order_status_id]!=''){
        			$where_array[]=$search_filed['order_status'][$order_status_id];
        		}
        	}
        	
        	if(intval($this->input->get('pay_status_id'))!=''){
        		$pay_status_id=$this->input->get('pay_status_id');
        		if($search_filed['pay_status'][$pay_status_id]!=''){
        			$where_array[]=$search_filed['pay_status'][$pay_status_id];
        		}
        	}
        	
        	if(intval($this->input->get('plat_payed_id'))!=''){
        		$plat_payed_id=$this->input->get('plat_payed_id');
        		if($search_filed['plat_payed'][$plat_payed_id]!=''){
        			$where_array[]=$search_filed['plat_payed'][$plat_payed_id];
        		}
        	}
        	
        	$phone = trim($this->input->get('phone'));
        	$search_arr['phone'] = $phone;
        	if($phone != ''){
        		// 通过手机号获取用户id
        		$user_info = $this->user_model->get_user_info_by_phone($phone);
        		$uid = $user_info['uid'];
        		$where_array[] = "ad_uid = '{$uid}' or news_uid = '{$uid}' ";
        	}
        	
            $keywords = trim($this->input->get('keywords'));
            $search_arr['keywords'] = $keywords;
            if($keywords != ''){
                $where_array[] = "olid like '%{$keywords}%' or ad_location like '%{$keywords}%' ";
            }
        }
        if(is_array($where_array) and count($where_array) > 0) {
            $where = ' WHERE '.join(' AND ',$where_array);
        }
        $pagesize  = 20;
        $offset    = $pagesize*($page-1);
        $limit     = " LIMIT $offset,$pagesize";
		$order     = " ORDER BY olid DESC";
        $sql_ct    = "SELECT olid FROM $this->table_name $where";
        $query     = $this->dbr->query($sql_ct);
        $log_num   = $query->num_rows();
        $pages     = pages($log_num, $page, $pagesize);
        $sql       = "SELECT olid, ad_uid, news_uid, fsid, aid, oaid, ad_location, status, ctime, utime, ad_price, total_price, original_price, pay_status, pay_id, plat_payed FROM $this->table_name $where $order $limit";
        log_message('debug', '[******]'. __METHOD__ .':'.__LINE__.' order_list sql [' . $sql .']');
        $result    = $this->dbr->query($sql);
        $list_data = $result->result_array();
        
        // 获取详情
        $res_content = array();
        foreach($list_data as $item_order) {
        	$oaid = $item_order['oaid']; // 公众帐号id
        	$aid = $item_order['aid']; // 询购id
        	$fsid = $item_order['fsid']; // 限时抢id
        	$pay_id = $item_order['pay_id']; // 此订单广告主支付id
        	// 通过询购id获取询购信息
        	$adv_consult_info = $this->adv_consult_model->get_adv_consult_info_by_aid($aid);
        	// 通过询购id获取文章id
        	$art_id = $adv_consult_info['art_id'];
        	// 通过文章id获取文章信息
        	$adv_article_info = $this->adv_article_model->get_adv_article_info_by_art_id($art_id);
        	log_message('debug', '[******************************]'. __METHOD__ .':'.__LINE__.' result [' . json_encode($adv_article_info) .']');
        	// 通过公众号id获取公众号信息
        	$official_accounts_info = $this->official_accounts_model->get_ofc_info_by_oaid($oaid);
        	log_message('debug', '[******************************]'. __METHOD__ .':'.__LINE__.' result [' . json_encode($official_accounts_info) .']');
        	
        	$item_order['article_id'] = $adv_article_info['art_id']; // 广告id
        	$item_order['article_title'] = $adv_article_info['title']; // 广告标题
        	$item_order['ofc_nick_name'] = $official_accounts_info['nick_name']; // 公众号昵称
        	$res_content[] = $item_order;
        }
        
        log_message('debug', '[******************************]'. __METHOD__ .':'.__LINE__.' order_list [' . json_encode($res_content) .']');
        
        
        $pay_status_list=array(1=>'未支付', 2=>'支付');
        $search_arr['pay_status_sel']=$this->form->select($pay_status_list,$pay_status_id,'name="pay_status_id"','付款状态');
        $plat_payed_list=array(1=>'未支付', 2=>'支付');
        $search_arr['plat_payed_sel']=$this->form->select($plat_payed_list,$plat_payed_id,'name="plat_payed_id"','垫付状态');
        $order_status_list=array(1=>'创建', 2=>'划款待执行', 3=>'媒体主执行完成', 9=>'订单完成', 10=>'订单取消');
        $search_arr['order_status_sel']=$this->form->select($order_status_list,$order_status_id,'name="order_status_id"','订单状态');
        
        
        $this->smarty->assign('search_arr', $search_arr);
        $this->smarty->assign('pay_status_list', $pay_status_list);
        $this->smarty->assign('plat_payed_list', $plat_payed_list);
        $this->smarty->assign('order_status_list', $order_status_list);
        $this->smarty->assign('list_data', $res_content);
        $this->smarty->assign('pages', $pages);
        $this->smarty->assign('show_dialog', 'true'); 
        $this->smarty->display("product/order_list.html");
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
    	 
    	$consult_status_list=array(1=>'待审核', 2=>'通过', 3=>'不通过');
    	$pay_status_list=array(1=>'未支付', 2=>'支付');
    	$plat_payed_list=array(1=>'未支付', 2=>'支付');
    	$order_status_list=array(1=>'创建', 2=>'划款待执行', 3=>'媒体主执行完成', 9=>'计单完成', 10=>'计单取消');
    	$input_box['consult_status_sel']=$this->form->select($consult_status_list,$consult_info['status'],'name="info[consult_status]"','询购状态');
    	$input_box['pay_status_sel']=$this->form->select($pay_status_list,$order_info['pay_status'],'name="info[pay_status]"','付款状态');
    	$input_box['plat_payed_sel']=$this->form->select($plat_payed_list,$order_info['plat_payed'],'name="info[plat_payed]"','垫付状态');
    	$input_box['order_status_sel']=$this->form->select($order_status_list,$order_info['status'],'name="info[order_status]"','订单状态');
    	 
    	 
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
    	
    	$consult_status_list=array(1=>'待审核', 2=>'通过', 3=>'不通过');
    	$pay_status_list=array(1=>'未支付', 2=>'支付');
    	$plat_payed_list=array(1=>'未支付', 2=>'支付');
    	$order_status_list=array(1=>'创建', 2=>'划款待执行', 3=>'媒体主执行完成', 9=>'计单完成', 10=>'计单取消');
    	$input_box['consult_status_sel']=$this->form->select($consult_status_list,$consult_info['status'],'name="info[consult_status]"','询购状态');
    	$input_box['pay_status_sel']=$this->form->select($pay_status_list,$order_info['pay_status'],'name="info[pay_status]"','付款状态');
    	$input_box['plat_payed_sel']=$this->form->select($plat_payed_list,$order_info['plat_payed'],'name="info[plat_payed]"','垫付状态');
    	$input_box['order_status_sel']=$this->form->select($order_status_list,$order_info['status'],'name="info[order_status]"','订单状态');
    	
    	
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
    	
    	// 修改consult_list表
    	$consult_info = array(
    			'status'	  => !empty($info['consult_status']) ? $info['consult_status'] : 1,
    			'utime'       => $cur_time,
    	);
    	$consult_flag = $this->consult_list_model->update_info($consult_info, $clid);
    	
    	if ($olid > 0) {
	    	// 修改order_list表
	    	$order_info = array(
	    			'pay_status'	  => !empty($info['pay_status']) ? $info['pay_status'] : 1,
	    			'plat_payed'	  => !empty($info['plat_payed']) ? $info['plat_payed'] : 1,
	    			'status'	  => !empty($info['order_status']) ? $info['order_status'] : 1,
	    			'utime'       => $cur_time,
	    	);
	    	$order_flag = $this->order_list_model->update_info($order_info, $olid);
    	}
    	
    	if($adv_consult_flag && $consult_flag){
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
