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

        $this->load->config("order", TRUE);
        $this->orderRequest = $this->config->item('request', 'order');
        $this->load->model("member/user_model", "user_model");
        $this->load->model("member/adv_consult_model", "adv_consult_model");
        $this->load->model("member/adv_article_model", "adv_article_model");
        $this->load->model("member/adv_pay_model", "adv_pay_model");
        $this->load->model("member/official_accounts_model", "official_accounts_model");
        $this->load->model("member/consult_list_model", "consult_list_model");
        $this->load->model("member/order_list_model", "order_list_model");
        $this->load->model("member/procedure_log_model", "procedure_log_model");
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
        	$item_order['article_title'] = $adv_article_info['title']; // 广告名称
        	$item_order['ofc_nick_name'] = $official_accounts_info['nick_name']; // 公众号昵称
        	$item_order['total_price'] = number_format($item_order['total_price']/100, 2, '.', ''); // 实际交易金额(含税)
        	$item_order['ad_price'] = number_format($item_order['ad_price']/100, 2, '.', ''); // 广告费
        	$item_order['original_price'] = number_format($item_order['original_price']/100, 2, '.', ''); // 原价
        	$res_content[] = $item_order;
        }
        
        log_message('debug', '[******************************]'. __METHOD__ .':'.__LINE__.' order_list [' . json_encode($res_content) .']');
        
        
        $pay_status_list=array(1=>'未支付', 2=>'支付');
        $search_arr['pay_status_sel']=$this->form->select($pay_status_list,$pay_status_id,'name="pay_status_id"','付款状态');
        $plat_payed_list=array(1=>'未支付', 2=>'已垫付');
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
    
    
    
    public function order_edit() {
    	$this->load->library('form');
    	$olid = intval($this->input->get('id'));
    	// 订单信息
    	$order_info = $this->order_list_model->get_order_info_by_olid($olid);
    	
    	$pay_status_list=array(1=>'未支付', 2=>'支付');
    	$plat_payed_list=array(1=>'未支付', 2=>'已垫付');
    	$order_status_list=array(1=>'创建', 2=>'划款待执行', 3=>'媒体主执行完成', 9=>'订单完成', 10=>'订单取消');
    	
    	$this->smarty->assign('pay_status_list', $pay_status_list);
    	$this->smarty->assign('plat_payed_list', $plat_payed_list);
    	$this->smarty->assign('order_status_list', $order_status_list);
    	$this->smarty->assign('order_info', $order_info);
    	$this->smarty->assign('input_box',$input_box);
    	$this->smarty->assign('show_dialog','true');
    	$this->smarty->assign('show_validator','true');
    	$this->smarty->display("product/order_edit.html");
    }
	    
    public function order_edit_do() {
    	$cfg = $this->input->post('cfg');
    	if($cfg['olid'] < 1) {
    		show_tips('参数异常，请检测');
    	} else {
    		$olid = $cfg['olid'];
    		// old
    		$old_info = $this->order_list_model->get_order_info_by_olid($olid);
    	}
    	$info = $this->input->post('info');
    	 
    	$cur_time = time();
    	if ($olid > 0) {

            if ($info['ad_price'] <= 0) {
                show_tips('价格不能为0');
            }

            $data = array(
                'olid'  => $olid,
                'ad_price' => $info['ad_price'],
                'user' => $this->session->userdata('mis_user'),

            );
            $url = $this->orderRequest['modify'];
            $this->load->library('curl');
            $this->curl->post($url, $data);
            $res = $this->curl->response;

            $ret = @json_decode($res, true);
            if ($this->curl->http_status_code != 200 || ($ret['errno'] != 0)) {
                log_message('error', 'request api order failed, errno['.$ret['errno'].'], errmsg['.$ret['errmsg'].']');
                show_tips($res['errmsg']);
                echo 0;
            } 
    	}
    	 
    	// 记入流程表
    	$new_info = $this->order_list_model->get_order_info_by_olid($olid);
    	$content_array = array();
    	if ($old_info['ad_price'] != $new_info['ad_price']) {
    		$content_array[] = 'ad_price(' . $old_info['ad_price'] . ' => ' . $new_info['ad_price'] . ')';
    	}
    
    	$procedure_log_info = array(
    			'art_id'		=> 0,
    			'consult_id'	=> 0,
    			'order_id' 		=> $olid,
    			'drawback_id' 	=> 0,
    			'content'      	=> join(';', $content_array),
    			'operator'      => $this->session->userdata('mis_user'), // 获取mis用户
    			'ctime'       	=> $cur_time,
    	);
    	$this->procedure_log_model->create_info($procedure_log_info);
    	 
    	if($order_flag){
    		show_tips('操作成功','','','edit');
    	}else{
    		show_tips('操作异常，请检测');
    	}
    	 
    }
    
    
    
    
    public function order_view() {
    	$this->load->library('form');
    	$olid = intval($this->input->get('id'));
    	// 订单信息
    	$order_info = $this->order_list_model->get_order_info_by_olid($olid);
    	
    	// 询购id
    	$aid = $order_info['aid'];
    	$adv_consult_info = $this->adv_consult_model->get_adv_consult_info_by_aid($aid);
    	$art_id = $adv_consult_info['art_id'];
    	
    	$adv_article_info = $this->adv_article_model->get_adv_article_info_by_art_id($art_id);
    	$adv_consult_info['show_day']=date('Y-m-d h:i:s',$adv_consult_info['show_day']);
    	$input_box['show_day']=$this->form->date('info[show_day]',$adv_consult_info['show_day'],1);
    	
    	// 支付id
    	$pay_id = $order_info['pay_id'];
    	$adv_pay_info = $this->adv_pay_model->get_adv_pay_info_by_pay_id($pay_id);
    	
    	// 公众帐号id
    	$oaid = $order_info['oaid'];
    	// 通过公众号id获取公众号信息
    	$official_accounts_info = $this->official_accounts_model->get_ofc_info_by_oaid($oaid);
    	
    	// 广告主信息
    	$ad_uid = $order_info['ad_uid'];
    	$ad_user_info = $this->user_model->get_user_info_by_uid($ad_uid);
    	
    	// 媒体主信息
    	$news_uid = $order_info['news_uid'];
    	$news_user_info = $this->user_model->get_user_info_by_uid($news_uid);
    	
    	 
    	$consult_status_list=array(1=>'待审核', 2=>'通过', 3=>'不通过');
    	$pay_status_list=array(1=>'未支付', 2=>'支付');
    	$plat_payed_list=array(1=>'未支付', 2=>'已垫付');
    	$order_status_list=array(1=>'创建', 2=>'划款待执行', 3=>'媒体主执行完成', 9=>'订单完成', 10=>'订单取消');
    	$pay_method_list=array(1=>'网银', 2=>'支付宝');
    	$input_box['pay_status_sel']=$this->form->select($pay_status_list,$order_info['pay_status'],'name="info[pay_status]"','付款状态');
    	$input_box['plat_payed_sel']=$this->form->select($plat_payed_list,$order_info['plat_payed'],'name="info[plat_payed]"','垫付状态');
    	$input_box['order_status_sel']=$this->form->select($order_status_list,$order_info['status'],'name="info[order_status]"','订单状态');
    	$input_box['pay_method_sel']=$this->form->select($pay_method_list,$adv_pay_info['pay_method'],'name="info[pay_method]"','付款方式');
    	
    	// 媒体主优惠金额
    	$order_info['discount_price'] = $order_info['original_price'] - $order_info['ad_price']; // 优惠金额
    	
    	$where_array = array();
    	$where_array[] = "order_id = '{$olid}' ";
    	$where = ' WHERE '.join(' AND ',$where_array);
    	$order = " ORDER BY ctime ASC";
    	$sql       = "SELECT action_id, order_id, content, operator, type, ctime, clid FROM order_action_log $where $order";
    	$result    = $this->dbr->query($sql);
    	$list_data = $result->result_array();
    	
    	$this->smarty->assign('adv_consult_info', $adv_consult_info);
    	$this->smarty->assign('adv_article_info', $adv_article_info);
    	$this->smarty->assign('adv_pay_info', $adv_pay_info);
    	$this->smarty->assign('order_info', $order_info);
    	$this->smarty->assign('official_accounts_info', $official_accounts_info);
    	$this->smarty->assign('ad_user_info', $ad_user_info);
    	$this->smarty->assign('news_user_info', $news_user_info);
    	$this->smarty->assign('input_box',$input_box);
    	$this->smarty->assign('list_data', $list_data);
    	$this->smarty->assign('show_dialog','true');
    	$this->smarty->assign('show_validator','true');
    	$this->smarty->display("product/order_view.html");
    }
    
    // 垫付操作
    public function order_advance_one_ajax() {
    	// 订单id
    	$olid = intval($this->input->get('olid'));
    	if($olid>0) {
    		$cur_time = time();
    		
            $data['olid'] = $olid;
            $data['user'] = $this->session->userdata('mis_user');
            $url = $this->orderRequest['advance_pay'];
            $this->load->library('curl');
            $this->curl->post($url, $data);
            $res = $this->curl->response;
            $ret = @json_decode($res, true);
            if ($this->curl->http_status_code != 200 || ($ret['errno'] != 0)) {
                log_message('error', 'request api order failed, errno['.$ret['errno'].'], errmsg['.$ret['errmsg'].']');
                show_tips($ret['errmsg']);
                echo 0;
            } 
            
    		// 记入流程表
    		$procedure_log_info = array(
    				'art_id'		=> 0,
    				'consult_id'	=> 0,
    				'order_id' 		=> $olid,
    				'drawback_id' 	=> 0,
    				'content'      	=> '垫付',
    				'operator'      => $this->session->userdata('mis_user'), // 获取mis用户
    				'ctime'       	=> time(),
    		);
    		$this->procedure_log_model->create_info($procedure_log_info);
    		echo 1;
    	} else {
    		echo 0;
    	}
    }
    
    // 给媒体主打款操作
    public function order_pay_do() {
    	// 订单id
    	$olid = intval($this->input->get('olid'));
    	if($olid>0) {
    		/****************调用PC接口****************/
    		// todo
    		
            $data['olid'] = $olid;
            $data['user'] = $this->session->userdata('mis_user');
            $url = $this->orderRequest['plat_pay'];
            $this->load->library('curl');
            $this->curl->post($url, $data);
            $res = $this->curl->response;
            $ret = @json_decode($res, true);
            if ($this->curl->http_status_code != 200 || ($ret['errno'] != 0)) {
                log_message('error', 'request api order failed, errno['.$ret['errno'].'], errmsg['.$ret['errmsg'].']');
                show_tips($ret['errmsg']);
                echo 0;
            } 
    		
    		echo 1;
    	} else {
    		echo 0;
    	}
    }
    
    // 取消操作
    public function order_cancel_one_ajax() {
    	// 订单id
    	$olid = intval($this->input->get('olid'));
    	if($olid>0) {
    		/****************调用PC接口****************/
    		// todo
            //

            $data['olid'] = $olid;
            $data['user'] = $this->session->userdata('mis_user'); // 获取mis用户
            $url = $this->orderRequest['cancel'];
            $this->load->library('curl');
            $this->curl->post($url, $data);
            $res = $this->curl->response;
            $ret = @json_decode($res, true);
            if ($this->curl->http_status_code != 200 || ($ret['errno'] != 0)) {
                log_message('error', 'request api order failed, errno['.$ret['errno'].'], errmsg['.$ret['errmsg'].']');
                show_tips($ret['errmsg']);
                echo 0;
            } 
    		
    		/****************************************/
    		// 记入流程表
    		$procedure_log_info = array(
    				'art_id'		=> 0,
    				'consult_id'	=> 0,
    				'order_id' 		=> $olid,
    				'drawback_id' 	=> 0,
    				'content'      	=> '取消订单',
    				'operator'      => $this->session->userdata('mis_user'), // 获取mis用户
    				'ctime'       	=> time(),
    		);
    		$this->procedure_log_model->create_info($procedure_log_info);
    		echo 1;
    	} else {
    		echo 0;
    	}
    }
}
