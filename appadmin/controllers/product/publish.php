<?php

class publish extends MY_Controller {

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
        $this->table_name = "publish_list";
    }

    public function index() {
        $this->publish_list();
    }
    
    public function publish_list() {
    	$this->load->library('form');
        $page = $this->input->get('page');
        $page = max(intval($page),1);
        $dosearch = $this->input->get('dosearch');
        
        $search_arr['is_deleted']=1;
        $where_array[]="is_deleted=1";
        
        if($dosearch == 'ok'){
        	
        	$search_filed=array(
        			'type'=>array(
        					1=>'type=1',
        					2=>'type=2',
        			),
        	);
        	 
        	if(intval($this->input->get('type_id'))!=''){
        		$type_id=$this->input->get('type_id');
        		if($search_filed['type'][$type_id]!=''){
        			$where_array[]=$search_filed['type'][$type_id];
        		}
        	}
        	
        	$time_start=$this->input->get('time_start');
        	$time_end=$this->input->get('time_end');
        	
        	if($time_start !='' && $time_end !=''){
        		$time1=strtotime($time_start);
        		$time2=strtotime($time_end);
        		$where_array[]="drawback_time>{$time1} AND drawback_time<{$time2} ";
        	}
        	
            $keywords = trim($this->input->get('keywords'));
            $search_arr['keywords'] = $keywords;
            if($keywords != ''){
                $where_array[] = "title like '%{$keywords}%' ";
            }
        }
        if(is_array($where_array) and count($where_array) > 0) {
            $where = ' WHERE '.join(' AND ',$where_array);
        }
        $pagesize  = 20;
        $offset    = $pagesize*($page-1);
        $limit     = " LIMIT $offset,$pagesize";
		$order     = " ORDER BY plid DESC";
        $sql_ct    = "SELECT plid FROM $this->table_name $where";
        $query     = $this->dbr->query($sql_ct);
        $log_num   = $query->num_rows();
        $pages     = pages($log_num, $page, $pagesize);
        $sql       = "SELECT plid, title, content, operator, ctime, utime, type, is_deleted FROM $this->table_name $where $order $limit";
        log_message('debug', '[******************************]'. __METHOD__ .':'.__LINE__.' publish_list sql [' . $sql .']');
        $result    = $this->dbr->query($sql);
        $list_data = $result->result_array();
        
        log_message('debug', '[******************************]'. __METHOD__ .':'.__LINE__.' publish_list [' . json_encode($list_data) .']');
        
        $type_list=array(1=>'公告', 2=>'说明');
        $search_arr['type_sel']=$this->form->select($type_list,$type_id,'name="type_id"','信息类别');
        $search_arr['time_start']=$this->form->date('time_start',$time_start,1);
        $search_arr['time_end']=$this->form->date('time_end',$time_end,1);
        
        $this->smarty->assign('search_arr', $search_arr);
        $this->smarty->assign('type_list', $type_list);
        $this->smarty->assign('list_data', $list_data);
        $this->smarty->assign('pages', $pages);
        $this->smarty->assign('show_dialog', 'true'); 
        $this->smarty->display("product/publish_list.html");
    }
    
    
    // 新建发布
    function publish_add(){
    	$this->load->library('form');
    	
    	$publish_type_list = array(1=>'公告', 2=>'说明');
    	$input_box['type_sel']=$this->form->select($publish_type_list,1,'name="info[type]"','请选择');
    	
    	$this->smarty->assign('input_box',$input_box);
    	$this->smarty->assign('random_version', rand(100,999));
    	$this->smarty->assign('show_dialog','true');
    	$this->smarty->assign('show_validator','true');
    	$this->smarty->display('product/publish_add.html');
    }
    
    
    //执行新建发布操作
    function publish_add_do(){
    	$info = $this->input->post('info');
    	
    	$cur_time = time();
    	$publish_info = array(
    			'type'	  		=> $info['type'],
    			'title'	  		=> $info['title'],
    			'operator'		=> 'admin',
    			'content'		=> $info['content'],
    			'ctime'     	=> $cur_time,
    			'utime'     	=> $cur_time,
    			'is_deleted'    => 1,
    	);
    
    	if( $info['title']!=''){
    		if($this->publish_list_model->create_info($publish_info)){
    			show_tips('操作成功','','','add');
    		}else{
    			show_tips('操作异常');
    		}
    	}else{
    		show_tips('数据不完整，请检测');
    	}
    }
    
    
    
    public function publish_view() {
    	$this->load->library('form');
    	$plid = intval($this->input->get('id'));
    	// 发布信息
    	$publish_info = $this->publish_list_model->get_publish_info_by_plid($plid);
    	
    	$publish_info['content'] = str_replace('&','&amp;',$publish_info['content']);
    	$publish_info['content'] = str_replace('<','&lt;',$publish_info['content']);
    	$publish_info['content'] = str_replace('>','&gt;',$publish_info['content']);
    	$publish_info['content'] = str_replace('"','&quot;',$publish_info['content']);
    	$publish_info['content'] = str_replace("'",'&#39;',$publish_info['content']);
    	
    	$publish_info['ctime']=date('Y-m-d h:i:s',$publish_info['ctime']);
    	$input_box['ctime']=$this->form->date('info[ctime]',$publish_info['ctime'],1);
    	
    	$type_list=array(1=>'公告', 2=>'说明');
    	$input_box['type_sel']=$this->form->select($type_list,$publish_info['type'],'name="info[type]"','发布类型');
    	
    	$this->smarty->assign('publish_info', $publish_info);
    	$this->smarty->assign('input_box',$input_box);
    	$this->smarty->assign('show_dialog','true');
    	$this->smarty->assign('show_validator','true');
    	$this->smarty->display("product/publish_view.html");
    }
    
    
    
    public function publish_edit() {
    	$this->load->library('form');
    	$plid = intval($this->input->get('id'));
    	
    	// 发布信息
    	$publish_info = $this->publish_list_model->get_publish_info_by_plid($plid);
    	
    	$publish_info['content'] = str_replace('&','&amp;',$publish_info['content']);
    	$publish_info['content'] = str_replace('<','&lt;',$publish_info['content']);
    	$publish_info['content'] = str_replace('>','&gt;',$publish_info['content']);
    	$publish_info['content'] = str_replace('"','&quot;',$publish_info['content']);
    	$publish_info['content'] = str_replace("'",'&#39;',$publish_info['content']);
    	
    	log_message('debug', '[******************************]'. __METHOD__ .':'.__LINE__.' info [' . json_encode($publish_info) .']');
    	
    	$publish_info['ctime']=date('Y-m-d h:i:s',$publish_info['ctime']);
    	$input_box['ctime']=$this->form->date('info[ctime]',$publish_info['ctime'],1);
    	
    	$type_list=array(1=>'公告', 2=>'说明');
    	$input_box['type_sel']=$this->form->select($type_list,$publish_info['type'],'name="info[type]"','发布类型');
		
    	$this->smarty->assign('publish_info', $publish_info);
    	$this->smarty->assign('input_box',$input_box);
    	$this->smarty->assign('show_dialog','true');
    	$this->smarty->assign('show_validator','true');
    	$this->smarty->display("product/publish_edit.html");
    }
    
    
    
    public function publish_edit_do() {
    	$cfg = $this->input->post('cfg');
    	if($cfg['plid'] < 1 || $cfg['plid'] < 1) {
    		show_tips('参数异常，请检测');
    	} else {
    		$plid = $cfg['plid'];
    	}
    	$info = $this->input->post('info');
    	
    	log_message('debug', '[******************************]'. __METHOD__ .':'.__LINE__.' info [' . json_encode($info) .']');
    	
    	$cur_time = time();
    	// 修改publish_list表
    	$publish_info = array(
    			'title' 	=> $info['title'],
    			'content' 	=> $info['content'],
    			'type' 		=> !empty($info['type']) ? $info['type'] : 1,
    			'utime'     => $cur_time,
    	);
    	$publish_flag = $this->publish_list_model->update_info($publish_info, $plid);
    	
    	if($publish_flag){
    		show_tips('操作成功','','','edit');
    	}else{
    		show_tips('操作异常，请检测');
    	}
    	
    }
    
    
    
    public function publish_del_one_ajax() {
    	$plid = intval($this->input->get('plid'));
    	if($plid>0) {
    		$cur_time = time();
    		
    		// 修改publish_list表, 是否删除：1、未删除，2、已删除
    		$publish_info = array(
    				'is_deleted' => 2,
    				'utime'  => $cur_time,
    		);
    		$publish_flag = $this->publish_list_model->update_info($publish_info, $plid);
    		echo 1;
    	} else {
    		echo 0;
    	}
    }
    
	
	
}
