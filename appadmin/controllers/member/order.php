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
        		'status'=>array(
        			1=>'status=1',
        			2=>'status=2',
        		),
        		'pay_status'=>array(
        			1=>'pay_status=1',
        			2=>'pay_status=2',
        		),
        		'plat_payed'=>array(
        			1=>'plat_payed=1',
        			2=>'plat_payed=2',
        		)
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
        	
            $keywords = trim($this->input->get('keywords'));
            $search_arr['keywords'] = $keywords;
            if($keywords != ''){
                $where_array[] = "ad_uid like '%{$keywords}%' or news_uid like '%{$keywords}%' ";
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
        $sql       = "SELECT olid, ad_uid, news_uid, fsid, aid, oaid, ad_location, status, ad_price, total_price, pay_status, plat_payed, ctime, utime FROM $this->table_name $where $order $limit";
        //log_message('debug', '[******]'. __METHOD__ .':'.__LINE__.' user_list sql [' . $sql .']');
        $result    = $this->dbr->query($sql);
        //log_message('debug', '[******]'. __METHOD__ .':'.__LINE__.' result [' . json_encode($result) .']');
        $list_data = $result->result_array();
        
        $order_status_list=array(1=>'已提交待审核', 2=>'已通过未询购', 3=>'已通过询购中', 4=>'已结束');
        $search_arr['order_status_sel']=$this->form->select($order_status_list,$order_status_id,'name="order_status_id"','订单状态');
        
        $pay_status_list=array(1=>'未支付', 2=>'支付');
        $search_arr['pay_status_sel']=$this->form->select($pay_status_list,$pay_status_id,'name="pay_status_id"','广告主付款状态');
        $plat_payed_list=array(1=>'支付', 2=>'未支付');
        $search_arr['plat_payed_sel']=$this->form->select($plat_payed_list,$plat_payed_id,'name="plat_payed_id"','平台付款状态');
        
        $this->smarty->assign('search_arr', $search_arr);
        $this->smarty->assign('order_status_list', $order_status_list);
        $this->smarty->assign('pay_status_list', $pay_status_list);
        $this->smarty->assign('plat_payed_list', $plat_payed_list);
        $this->smarty->assign('list_data', $list_data);
        $this->smarty->assign('pages', $pages);
        $this->smarty->assign('show_dialog', 'true'); 
        $this->smarty->display("member/order_list.html");
    }
	    
    public function order_edit() {
        $this->load->library('form');
        $aid = intval($this->input->get('id'));
        $sql = "SELECT olid, ad_uid, news_uid, fsid, aid, oaid, ad_location, status, ad_price, total_price, pay_status, plat_payed, ctime, utime FROM {$this->table_name} WHERE olid={$aid}";
        $result = $this->db->query($sql);
        $info = $result->row_array();
        
        $order_status_list=array(1=>'已提交待审核', 2=>'已通过未询购', 3=>'已通过询购中', 4=>'已结束');
        $pay_status_list=array(1=>'未支付', 2=>'支付');
        $plat_payed_list=array(1=>'支付', 2=>'未支付');
        $order_status_select = Form::select($order_status_list, 0, 'name="info[status]" id="status"', '状态');
        $pay_status_radio = Form::radio($pay_status_list, $info['pay_status'],'name="info[pay_status]" id="pay_status"');
        $plat_payed_radio = Form::radio($plat_payed_list, $info['plat_payed'],'name="info[plat_payed]" id="plat_payed"');
        $this->smarty->assign('order_status_select', $order_status_select);
        $this->smarty->assign('pay_status_radio', $pay_status_radio);
        $this->smarty->assign('plat_payed_radio', $plat_payed_radio);
        $this->smarty->assign('info', $info);
        $this->smarty->assign('show_dialog','true');
        $this->smarty->assign('show_validator','true');
        $this->smarty->display("member/order_edit.html");
    }

    public function user_edit_do() {
        $cfg = $this->input->post('cfg');
        if($cfg['uid'] < 1) {show_tips('参数异常，请检测');} else {$where = "uid={$cfg['uid']}";}
        $info = $this->input->post('info');
		if ($_FILES['pic']['name'] != "") {
            $this->load->library('oss');
            $pic_ret = $this->oss->upload('pic', array('dir'=>'url'));
            if (isset($pic_ret['error_code']) && intval($pic_ret['error_code'])) {
                show_tips($pic_ret['error_code']. ":" . $pic_ret['error']);
            }
            $info['url'] = $pic_ret;
        }
        $info['utime'] = time();
        //log_message('debug', '[******]'. __METHOD__ .':'.__LINE__.' info sql [' . json_encode($info) .']');
        $query = $this->db->update_string($this->table_name, $info, $where);
        if($this->dbr->query($query)){
			$pre = self::get_user_key();
			$key = $pre.$cfg['uid'];
			$this->hMset($key, $info);
            show_tips('操作成功','','','edit');
        }else{
            show_tips('操作异常，请检测');
        }
    }

}
