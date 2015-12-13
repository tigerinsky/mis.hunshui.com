<?php

class drawback extends MY_Controller {

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
        $this->table_name = "drawback_list";
    }

    public function index() {
        $this->drawback_list();
    }
    
    public function drawback_list() {
    	$this->load->library('form');
        $page = $this->input->get('page');
        $page = max(intval($page),1);
        $dosearch = $this->input->get('dosearch');
        
        $search_arr['is_deleted']=1;
        $where_array[]="is_deleted=1";
        
        if($dosearch == 'ok'){
        	
        	$search_filed=array(
        			'status'=>array(
        					1=>'status=1',
        					2=>'status=2',
        			),
        	);
        	 
        	if(intval($this->input->get('status_id'))!=''){
        		$status_id=$this->input->get('status_id');
        		if($search_filed['status'][$status_id]!=''){
        			$where_array[]=$search_filed['status'][$status_id];
        		}
        	}
        	
        	$time_start=$this->input->get('time_start');
        	$time_end=$this->input->get('time_end');
        	
        	if($time_start !='' && $time_end !=''){
        		$time1=strtotime($time_start);
        		$time2=strtotime($time_end);
        		$where_array[]="drawback_time>{$time1} AND drawback_time<{$time2} ";
        	}
        	
        	// 广告id
        	$art_id = trim($this->input->get('art_id'));
        	$search_arr['art_id'] = $art_id;
        	if($art_id != ''){
        		$where_array[] = "art_id = '{$art_id}' ";
        	}
        	
        	// 广告名称（支持模糊匹配）
        	$title = trim($this->input->get('title'));
        	$search_arr['title'] = $title;
        	if($title != ''){
        		// 通过广告标题获取匹配到的广告id列表
        		$art_id_list = $this->adv_article_model->get_art_id_list_by_title($title);
        		$where_array[] = "art_id in '{$uid}' ";
        	}
        	
        	// 订单id
        	$order_id = trim($this->input->get('order_id'));
        	$search_arr['order_id'] = $order_id;
        	if($order_id != ''){
        		$where_array[] = "order_id = '{$order_id}' ";
        	}
        	
        	// 广告主或媒体主手机号
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
                $where_array[] = "mis_name like '%{$keywords}%' ";
            }
        }
        if(is_array($where_array) and count($where_array) > 0) {
            $where = ' WHERE '.join(' AND ',$where_array);
        }
        $pagesize  = 20;
        $offset    = $pagesize*($page-1);
        $limit     = " LIMIT $offset,$pagesize";
		$order     = " ORDER BY dbid DESC";
        $sql_ct    = "SELECT dbid FROM $this->table_name $where";
        $query     = $this->dbr->query($sql_ct);
        $log_num   = $query->num_rows();
        $pages     = pages($log_num, $page, $pagesize);
        $sql       = "SELECT dbid, art_id, ad_uid, news_uid, order_id, status, reason, drawback_price, drawback_time, mis_name, ctime, utime FROM $this->table_name $where $order $limit";
        log_message('debug', '[******]'. __METHOD__ .':'.__LINE__.' drawback_list sql [' . $sql .']');
        $result    = $this->dbr->query($sql);
        $list_data = $result->result_array();
        
        // 获取详情
        $res_content = array();
        foreach($list_data as $item_drawback) {
        	$art_id = $item_drawback['art_id']; // 广告id
        	$ad_uid = $item_drawback['ad_uid']; // 广告主id
        	$news_uid = $item_drawback['news_uid']; // 媒体主id
        	$order_id = $item_drawback['order_id']; // 订单id
        	// 通过文章id获取文章信息
        	$adv_article_info = $this->adv_article_model->get_adv_article_info_by_art_id($art_id);
        	log_message('debug', '[******************************]'. __METHOD__ .':'.__LINE__.' result [' . json_encode($adv_article_info) .']');
        	// 通过广告主id获取用户信息
        	$ad_user_info = $this->user_model->get_user_info_by_uid($ad_uid);
        	// 通过媒体主id获取用户信息
        	$news_user_info = $this->user_model->get_user_info_by_uid($news_uid);
        	// 通过订单id获取订单信息
        	$order_info = $this->order_list_model->get_order_info_by_olid($order_id);
        	log_message('debug', '[******************************]'. __METHOD__ .':'.__LINE__.' result [' . json_encode($order_info) .']');
        	
        	$item_drawback['article_title'] = $adv_article_info['title']; // 广告名称
        	$item_drawback['ad_cmpy_name'] = $ad_user_info['cmpy_name']; // 广告主公司
        	$item_drawback['ad_phone'] = $ad_user_info['phone']; // 广告主联系电话
        	$item_drawback['news_nick_name'] = $news_user_info['nick_name']; // 媒体主昵称
        	$item_drawback['news_phone'] = $news_user_info['phone']; // 媒体主联系电话
        	$res_content[] = $item_drawback;
        }
        
        log_message('debug', '[******************************]'. __METHOD__ .':'.__LINE__.' drawback_list [' . json_encode($res_content) .']');
        
        $status_list=array(1=>'已退款', 2=>'待退款');
        $search_arr['status_sel']=$this->form->select($status_list,$status_id,'name="status_id"','退款状态');
        $search_arr['time_start']=$this->form->date('time_start',$time_start,1);
        $search_arr['time_end']=$this->form->date('time_end',$time_end,1);
        
        $this->smarty->assign('search_arr', $search_arr);
        $this->smarty->assign('status_list', $status_list);
        $this->smarty->assign('list_data', $res_content);
        $this->smarty->assign('pages', $pages);
        $this->smarty->assign('show_dialog', 'true'); 
        $this->smarty->display("product/drawback_list.html");
    }
    
    
    // 新建退款
    function drawback_add(){
    	$this->load->library('form');
    	
    	// 所有的订单id
    	$all_order_list = $this->order_list_model->get_all_order_list();
    	
    	foreach($all_order_list as $order) {
    		$order_id_list[$order['olid']] = $order['olid'];
    	}
    	$order_id_sel=Form::select($order_id_list,$info['order_id'],'id="order_id" name="info[order_id]"','请选择');
    	
    	$drawback_time = date("Y-m-d H:i:s", time());
    	$input_box['drawback_time'] = $this->form->date('info[drawback_time]', $drawback_time, 1);
    	
    	$drawback_status_list = array(1=>'已退款', 2=>'待退款');
    	$input_box['status_sel']=$this->form->select($drawback_status_list,1,'name="info[status]"','请选择');
    	
    	$this->smarty->assign('input_box',$input_box);
    	$this->smarty->assign('order_id_sel',$order_id_sel);
    	$this->smarty->assign('random_version', rand(100,999));
    	$this->smarty->assign('show_dialog','true');
    	$this->smarty->assign('show_validator','true');
    	$this->smarty->display('product/drawback_add.html');
    }
    
    
    //执行新建退款操作
    function drawback_add_do(){
    	$info = $this->input->post('info');
    	// 订单id
    	$order_id = $info['order_id'];
    	// 通过订单id获取订单信息
    	$order_info = $this->order_list_model->get_order_info_by_olid($order_id);
    	// 询购id
    	$aid = $order_info['aid'];
    	// 通过询购id获取询购信息
    	$adv_consult_info = $this->adv_consult_model->get_adv_consult_info_by_aid($aid);
    	
    	$cur_time = time();
    	$drawback_info = array(
    			'art_id'	  		=> $adv_consult_info['art_id'],
    			'ad_uid'	  		=> $order_info['ad_uid'],
    			'news_uid'	  		=> $order_info['news_uid'],
    			'order_id'	  		=> $info['order_id'],
    			'status'	  		=> $info['status'],
    			'reason'	  		=> $info['reason'],
    			'drawback_price'	=> $info['drawback_price'],
    			'drawback_time'	  	=> strtotime($info['drawback_time']),
    			'mis_name' 			=> 'test',
    			'ctime'      		=> $cur_time,
    			'utime'       		=> $cur_time,
    			'is_deleted'       	=> 1,
    	);
    
    	if( $info['order_id']!='' && $adv_consult_info['art_id'] != ''){
    		if($this->drawback_list_model->create_info($drawback_info)){
    			show_tips('操作成功','','','add');
    		}else{
    			show_tips('操作异常');
    		}
    	}else{
    		show_tips('数据不完整，请检测');
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
    	$plat_payed_list=array(1=>'未支付', 2=>'支付');
    	$order_status_list=array(1=>'创建', 2=>'划款待执行', 3=>'媒体主执行完成', 9=>'订单完成', 10=>'订单取消');
    	$pay_method_list=array(1=>'网银', 2=>'支付宝');
    	$input_box['pay_status_sel']=$this->form->select($pay_status_list,$order_info['pay_status'],'name="info[pay_status]"','付款状态');
    	$input_box['plat_payed_sel']=$this->form->select($plat_payed_list,$order_info['plat_payed'],'name="info[plat_payed]"','垫付状态');
    	$input_box['order_status_sel']=$this->form->select($order_status_list,$order_info['status'],'name="info[order_status]"','订单状态');
    	$input_box['pay_method_sel']=$this->form->select($pay_method_list,$adv_pay_info['pay_method'],'name="info[pay_method]"','付款方式');
    	
    	// 媒体主优惠金额
    	$order_info['discount_price'] = $order_info['original_price'] - $order_info['ad_price']; // 优惠金额
    	
    	$this->smarty->assign('adv_consult_info', $adv_consult_info);
    	$this->smarty->assign('adv_article_info', $adv_article_info);
    	$this->smarty->assign('adv_pay_info', $adv_pay_info);
    	$this->smarty->assign('order_info', $order_info);
    	$this->smarty->assign('official_accounts_info', $official_accounts_info);
    	$this->smarty->assign('ad_user_info', $ad_user_info);
    	$this->smarty->assign('news_user_info', $news_user_info);
    	$this->smarty->assign('input_box',$input_box);
    	$this->smarty->assign('show_dialog','true');
    	$this->smarty->assign('show_validator','true');
    	$this->smarty->display("product/order_view.html");
    }
    
    
    public function order_advance_one_ajax() {
    	$olid = intval($this->input->get('olid'));
    	if($olid>0) {
    		$cur_time = time();
    		
    		// 修改order_list表, 平台付款，1未支付，2支付
    		$order_info = array(
    				'plat_payed' => 2,
    				'utime'  => $cur_time,
    		);
    		$order_flag = $this->order_list_model->update_info($order_info, $olid);
    		echo 1;
    	} else {
    		echo 0;
    	}
    }
    
	
	
}
