<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * bug管理
 */
class bugtrack extends MY_Controller{
        
    function __construct() {
        parent::__construct();
        $this->dbr=$this->load->database('dbr',TRUE);
        $this->CI =& get_instance();
        $this->CI->config->load('config_rbac',TRUE);
        $this->rbac_config=$this->CI->config->item('config_rbac');
        $this->log_infos = $log_infos = $this->CI->session->userdata($this->rbac_config['rbac_admin_auth_key']);
        //print_r($this->log_infos);
        $this->load->model('bugtrack/bugtrack_model','bugtrack_model');
        $this->load->model('bugtrack/bugtrack_log_model','bugtrack_log_model');
        $this->bug_status = array('1'=>'未修复', '2'=>'已解决');
        $this->bug_type = array('1'=>'Android', '2'=>'IOS', '3'=>'Server', '4'=>'FE');
        $this->bug_priority = array('1'=>'高', '2'=>'正常', '3'=>'低');
        $this->bug_handle_user = array(
        							'1'=>'飞哥',
        							'2'=>'凯峰',
        							'3'=>'王怡',
        							'4'=>'雪冰',
        							'5'=>'浩哥',
        							'6'=>'大闪',
        							'7'=>'刘健',
        							'8'=>'吴桐',
        							'9'=>'仁辉',
        							'10'=>'张鹏',
        							'11'=>'春晖',
        							'12'=>'黄辉',
        							'13'=>'桂哥',
        							'14'=>'xiaojun',
        							'15'=>'郄培',
        							'16'=>'喜东',
        							'17'=>'玥玥',
        						);
        $this->bug_login_user = array(
        							'xiaofei'=>array('id'=>'1', 'name'=>'飞哥'),
        							'kaifeng'=>array('id'=>'2', 'name'=>'凯峰'),
        							'wangyi'=>array('id'=>'3', 'name'=>'王怡'),
        							'xuebing'=>array('id'=>'4', 'name'=>'雪冰'),
        							'chenhao'=>array('id'=>'5', 'name'=>'浩哥'),
        							'dashan'=>array('id'=>'6', 'name'=>'大闪'),
        							'liujian'=>array('id'=>'7', 'name'=>'刘健'),
        							'wutong'=>array('id'=>'8', 'name'=>'吴桐'),
        							'renhui'=>array('id'=>'9', 'name'=>'仁辉'),
        							'zhangpeng'=>array('id'=>'10', 'name'=>'张鹏'),
        							'chunhui'=>array('id'=>'11', 'name'=>'春晖'),
        							'huanghui'=>array('id'=>'12', 'name'=>'黄辉'),
        							'guige'=>array('id'=>'13', 'name'=>'桂哥'),
        							'xiaojun'=>array('id'=>'14', 'name'=>'xiaojun'),
        							'qiepei'=>array('id'=>'15', 'name'=>'郄培'),
        							'xidong'=>array('id'=>'16', 'name'=>'喜东'),
        							'yueyue'=>array('id'=>'17', 'name'=>'玥玥'),
        						);
    }
    
    //默认调用控制器
    function index(){
    	$this->bugtrack_list();
    }
    
    //显示bug列表，同时有检索功能
    private function bugtrack_list(){
        $this->load->library('form');
        $page=$this->input->get('page');
        $page = max(intval($page),1);
        $dosearch=$this->input->get('dosearch');
        
        $search_arr['is_deleted']=1;
        $where_array[]="is_deleted=1";
        
        if($dosearch=='ok'){
            
            $search_filed=array(
                'handle_user'=>array(
                    '1'=>'handle_user=1 OR create_user=1',
                    '2'=>'handle_user=2 OR create_user=2',
                    '3'=>'handle_user=3 OR create_user=3',
                    '4'=>'handle_user=4 OR create_user=4',
                    '5'=>'handle_user=5 OR create_user=5',
                    '6'=>'handle_user=6 OR create_user=6',
                    '7'=>'handle_user=7 OR create_user=7',
                    '8'=>'handle_user=8 OR create_user=8',
                    '9'=>'handle_user=9 OR create_user=9',
                    '10'=>'handle_user=10 OR create_user=10',
                    '11'=>'handle_user=11 OR create_user=11',
                    '12'=>'handle_user=12 OR create_user=12',
                    '13'=>'handle_user=13 OR create_user=13',
                    '14'=>'handle_user=14 OR create_user=14',
                    '15'=>'handle_user=15 OR create_user=15',
                    '16'=>'handle_user=16 OR create_user=16',
                    '17'=>'handle_user=17 OR create_user=17',
                    '18'=>'handle_user=18 OR create_user=18',
                    '19'=>'handle_user=19 OR create_user=19',
                ),
                'priority'=>array(
                    '1'=>'priority=1',
                    '2'=>'priority=2',
                    '3'=>'priority=3',
                ),
                'status'=>array(
                    '1'=>'status=1',
                    '2'=>'status=2',
                ),
                'type'=>array(
                    '1'=>'type=1',
                    '2'=>'type=2',
                    '3'=>'type=3',
                    '4'=>'type=4',
                ),
            );
            
            if(intval($this->input->get('handle_user_id'))!=''){
                $handle_user_id=$this->input->get('handle_user_id');
                if($search_filed['handle_user'][$handle_user_id]!=''){
                    $where_array[]=$search_filed['handle_user'][$handle_user_id];
                }
            }
            if(intval($this->input->get('priority_id'))!=''){
                $priority_id=$this->input->get('priority_id');
                if($search_filed['priority'][$priority_id]!=''){
                    $where_array[]=$search_filed['priority'][$priority_id];
                }
            }
            if(intval($this->input->get('status_id'))!=''){
                $status_id=$this->input->get('status_id');
                if($search_filed['status'][$status_id]!=''){
                    $where_array[]=$search_filed['status'][$status_id];
                }
            }
            if(intval($this->input->get('type_id'))!=''){
                $type_id=$this->input->get('type_id');
                if($search_filed['type'][$type_id]!=''){
                    $where_array[]=$search_filed['type'][$type_id];
                }
            }

            $keywords=trim($this->input->get('keywords'));

            if($keywords!=''){
                $search_arr['keywords']=$keywords;
                $where_array[]="title like '%{$keywords}%'";
            }

        }

        if(is_array($where_array) and count($where_array)>0){
            $where=' WHERE '.join(' AND ',$where_array);
        }

        $pagesize = 20;
        $offset = $pagesize*($page-1);
        $limit = "LIMIT $offset,$pagesize";
        
        $user_num = $this->bugtrack_model->get_count_by_parm($where);
        $pages = pages($user_num, $page, $pagesize);
        $list_data = $this->bugtrack_model->get_data_by_parm($where, $limit);

        $this->load->library('form');
        //$img_type_list=array('1'=>'素描','2'=>'色彩','3'=>'速写','4'=>'设计','5'=>'创作','6'=>'照片');
        $handle_user_list = $this->bug_handle_user;
        $priority_list = $this->bug_priority;
        $status_list = $this->bug_status;
        $type_list = $this->bug_type;
        $search_arr['status_sel']=$this->form->select($status_list, $status_id,'name="status_id"','状态');
        $search_arr['handle_user_sel']=$this->form->select($handle_user_list, $handle_user_id,'name="handle_user_id"','负责人');
        $search_arr['priority_sel']=$this->form->select($priority_list, $priority_id,'name="priority_id"','优先级');
        $search_arr['type_sel']=$this->form->select($type_list, $type_id,'name="type_id"','类型');
        $this->smarty->assign('search_arr', $search_arr);
        $this->smarty->assign('handle_user_list', $handle_user_list);
        $this->smarty->assign('priority_list', $priority_list);
        $this->smarty->assign('status_list', $status_list);
        $this->smarty->assign('list_data', $list_data);
        $this->smarty->assign('pages', $pages);
        $this->smarty->assign('show_dialog', 'true');
        $this->smarty->display('bugtrack/bugtrack_list.html');
    }

    /**
     * 对外提供的接口
     * 
     */ 
    function get_bug_list(){
        $request = $this->request_array;
        $response = $this->response_array;
        
        $result = array();
        $result['uname'] = $admin_session['uname'];
        $response['errno'] = 0;
        $response['data']['content'] = $result;
        $this->renderJson($response['errno'], $response['data']);

    }
    
    
    /**
     * ajax调用的函数
     *
     */
    function get_img_title_list_ajax(){
    	$request = $this->request_array;
    	$response = $this->response_array;
    
    	$img_type = $request['img_type'];
    
    	$result = array();
    	if (isset($this->mis_imgmgr['imgmgr_level_2'][$img_type])) {
    		$result = $this->mis_imgmgr['imgmgr_level_2'][$img_type];
    	}
    	
    	$response['errno'] = 0;
    	$response['data']['content'] = $result;
    	
    	$this->renderJson($response['errno'], $response['data']);
    
    }


    //对要闻进行单条推荐
    function sug_one_ajax(){
        if(intval($_GET['id'])>0) {
            $id=$this->input->get('id');
            if($this->tweet_model->one_sug($id, 1)){
				echo 1;
            }else{
				echo 0;
            }
        } else {
			echo 0;
        }
    }
    
    //对要闻闻进行批量推荐属性设置
    function tweet_sug(){
        if(intval($_POST['dosubmit'])==1) {
            $ids=$this->input->post('ids');
            if(is_array($ids) and count($ids)>0){
                $ids_str=join("','",$ids);
                if($this->tweet_model->tweet_sug($ids_str)){
                    show_tips('操作成功',HTTP_REFERER);
                }else{
                    show_tips('操作异常');
                }
            }else{
                show_tips('参数有误，请重新提交');
            }
        } else {
            show_tips('操作异常');
        }
    }
    
    //对要闻进行单条取消推荐
    function sug_one_cancel_ajax() {
        if(intval($_GET['id'])>0) {
            $id=$this->input->get('id');
            if($this->tweet_model->one_sug($id, 0)){
				echo 1;
            }else{
				echo 0;
            }
        } else {
			echo 0;
        }
    }
    //对要闻闻进行批量推荐属性取消
    function tweet_clear_sug(){
        if(intval($_POST['dosubmit'])==1) {
            $ids=$this->input->post('ids');
            if(is_array($ids) and count($ids)>0){
                $ids_str=join("','",$ids);
                if($this->tweet_model->tweet_clear_sug($ids_str)){
                    show_tips('操作成功',HTTP_REFERER);
                }else{
                    show_tips('操作异常');
                }
            }else{
                show_tips('参数有误，请重新提交');
            }
        } else {
            show_tips('操作异常');
        }
    }

    //对BUG进行单条删除属性变更
    function del_one_ajax(){
        if(intval($_GET['id'])>0) {
            $id=$this->input->get('id');
            if($this->bugtrack_model->del_info($id)){
				echo 1;
            }else{
				echo 0;
            }
        } else {
			echo 0;
        }
    }
    
	//对要闻进行单条取消删除
    function del_one_cancel_ajax(){
        if(intval($_GET['id'])>0) {
            $id=$this->input->get('id');
            if($this->tweet_model->one_del($id, 0)){
				echo 1;
            }else{
				echo 0;
            }
        } else {
			echo 0;
        }
    }
    //对要闻闻进行批量删除属性设置
    function tweet_del(){
        if(intval($_POST['dosubmit'])==1) {
            $ids=$this->input->post('ids');
            if(is_array($ids) and count($ids)>0){
                $ids_str=join("','",$ids);
                if($this->tweet_model->tweet_del($ids_str)){
                    show_tips('操作成功',HTTP_REFERER);
                }else{
                    show_tips('操作异常');
                }
            }else{
                show_tips('参数有误，请重新提交');
            }
        } else {
            show_tips('操作异常');
        }
    }

    //对要闻闻进行批量删除属性取消
    function tweet_clear_del(){
        if(intval($_POST['dosubmit'])==1) {
            $ids=$this->input->post('ids');
            if(is_array($ids) and count($ids)>0){
                $ids_str=join("','",$ids);
                if($this->tweet_model->tweet_clear_del($ids_str)){
                    show_tips('操作成功',HTTP_REFERER);
                }else{
                    show_tips('操作异常');
                }
            }else{
                show_tips('参数有误，请重新提交');
            }
        } else {
            show_tips('操作异常');
        }
    }
    
    
    //添加BUG
    function bugtrack_add(){
        $this->load->library('form');
        
        $handle_user_list = $this->bug_handle_user;
        $handle_user_sel = Form::select($handle_user_list, $info['handle_user'], 'id="handle_user" name="info[handle_user]"', '请选择');
        $priority_list = $this->bug_priority;
        //$priority_sel = Form::select($priority_list, $info['priority'], 'id="priority" name="info[priority]"', '优先级');
        $priority_sel = Form::select($priority_list, 2, 'id="priority" name="info[priority]"', '优先级');
        $status_list = $this->bug_status;
        //$status_sel = Form::select($status_list, $info['status'], 'id="status" name="info[status]"', '状态');
        $status_sel = Form::select($status_list, 1, 'id="status" name="info[status]"', '状态');

        $this->smarty->assign('handle_user_sel', $handle_user_sel);
        $this->smarty->assign('priority_sel', $priority_sel);
        $this->smarty->assign('status_sel', $status_sel);
        $this->smarty->assign('random_version', rand(100,999));
        $this->smarty->assign('show_dialog','true');
        $this->smarty->assign('show_validator','true');
        $this->smarty->display('bugtrack/bugtrack_add.html');
    }
    
    //执行添加BUG操作
    function bugtrack_add_do(){
        $info = $this->input->post('info');
        log_message('debug', '*****************[test]******************bugtrack_add_do');
        log_message('debug', $info['handle_user']);
        log_message('debug', $info['status']);
        log_message('debug', $info['priority']);
        log_message('debug', $info['content']);
		
        // 转义
        //$info['content'] = addslashes($info['content']);
        // 反转义
        //$info['content'] = stripslashes($info['content']);
        
        // 当前登录用户
        $uname = $this->log_infos['uname'];
        if (isset($this->bug_login_user[$uname])) {
        	$uid = $this->bug_login_user[$uname]['id'];
        } else {
        	$uid = '1';
        }
        $info['create_user'] = $uid;
        
        $now_time = time();
        $info['publish_time'] = $now_time;
        $info['resolve_time'] = $now_time;
        if( $info['title']!='' && $info['content'] != '') {
            if($this->bugtrack_model->create_info($info)){
                show_tips('操作成功','','','add');
            }else{
                show_tips('操作异常');
            }
        }else{
            show_tips('数据不完整，请检测');
        }
    }
    
    //修改BUG
    function bugtrack_edit(){
        $this->load->library('form');
        $bugtrack_id = $this->input->get('id');
        $info = $this->bugtrack_model->get_info_by_id($bugtrack_id);
		
        $handle_user_list = $this->bug_handle_user;
        $handle_user_sel = Form::select($handle_user_list, $info['handle_user'], 'id="handle_user" name="info[handle_user]"', '请选择');
        $priority_list = $this->bug_priority;
        $priority_sel = Form::select($priority_list, $info['priority'], 'id="priority" name="info[priority]"', '请选择');
        $status_list = $this->bug_status;
        $status_sel = Form::select($status_list, $info['status'], 'id="status" name="info[status]"', '请选择');
        
        $this->smarty->assign('info',$info);
        $this->smarty->assign('handle_user_sel', $handle_user_sel);
        $this->smarty->assign('priority_sel', $priority_sel);
        $this->smarty->assign('status_sel', $status_sel);
        $this->smarty->assign('random_version', rand(100,999));
        $this->smarty->assign('show_dialog','true');
        $this->smarty->assign('show_validator','true');
        $this->smarty->display('bugtrack/bugtrack_edit.html');
    }
    
    //执行修改BUG操作
    function bugtrack_edit_do(){
        $id = $this->input->post('id');
        // 新提交的数据
        $info = $this->input->post('info');
        // 老的数据
        $old_info = $this->bugtrack_model->get_info_by_id($id);
        
        if ($info['status'] == '2') { // 已修复
        	$item = array(
        			'bugtrack_id' => $id,
        			'old_handle_user' => $old_info['handle_user'],
        			'new_handle_user' => $old_info['handle_user'],
        			'type' => 3,
        			'ctime' => time(),
        	);
        	$this->bugtrack_log_model->create_info($item);
        } elseif ($info['handle_user'] != $old_info['handle_user']) { // 转发
        	$is_info = $this->bugtrack_log_model->get_info_by_bugid($id);
        	if ($is_info == '') { // 新建
        		$item = array(
        				'bugtrack_id' => $id,
        				'old_handle_user' => $old_info['create_user'],
        				'new_handle_user' => $old_info['handle_user'],
        				'type' => 1,
        				'ctime' => time(),
        		);
        		$this->bugtrack_log_model->create_info($item);
        	}
        	// 转发
        	$item = array(
        			'bugtrack_id' => $id,
        			'old_handle_user' => $old_info['handle_user'],
        			'new_handle_user' => $info['handle_user'],
        			'type' => 2,
        			'ctime' => time(),
        	);
        	$this->bugtrack_log_model->create_info($item);
        } elseif ($info['status'] != $old_info['status']) {
        	// 转发
        	$item = array(
        			'bugtrack_id' => $id,
        			'old_handle_user' => $old_info['handle_user'],
        			'new_handle_user' => $info['handle_user'],
        			'type' => 2,
        			'ctime' => time(),
        	);
        	$this->bugtrack_log_model->create_info($item);
        }
        
        $now_time = time();
        $info['resolve_time'] = $now_time;
        
        if($info['title'] != '' && $info['content'] != '') {
            if($this->bugtrack_model->update_info($info, $id)){
                show_tips('操作成功','','','edit');
            }else{
                show_tips('操作异常，请检测');
            }
        }else{
            show_tips('数据不完整，请检测');
        }
    }
    
    //查看BUG详情
    function bugtrack_detail(){
    	$this->load->library('form');
    	$bugtrack_id = $this->input->get('id');
    	$info = $this->bugtrack_model->get_info_by_id($bugtrack_id);
    	
    	$log_list = $this->bugtrack_log_model->get_info_by_bugid($bugtrack_id);
    	
    	$handle_user_list = $this->bug_handle_user;
    	$handle_user_sel = Form::select($handle_user_list, $info['handle_user'], 'id="handle_user" name="info[handle_user]"', '请选择');
    	$priority_list = $this->bug_priority;
    	$priority_sel = Form::select($priority_list, $info['priority'], 'id="priority" name="info[priority]"', '请选择');
    	$status_list = $this->bug_status;
    	$status_sel = Form::select($status_list, $info['status'], 'id="status" name="info[status]"', '请选择');
    	
    	$log_type_list = array('1'=>'新建', '2'=>'转发', '3'=>'已修复');
    	
    	$this->smarty->assign('info',$info);
    	$this->smarty->assign('handle_user_sel', $handle_user_sel);
    	$this->smarty->assign('priority_sel', $priority_sel);
    	$this->smarty->assign('status_sel', $status_sel);
    	$this->smarty->assign('handle_user_list', $handle_user_list);
    	$this->smarty->assign('log_type_list', $log_type_list);
    	$this->smarty->assign('log_list', $log_list);
    	$this->smarty->assign('random_version', rand(100,999));
    	$this->smarty->assign('show_dialog','true');
    	$this->smarty->assign('show_validator','true');
    	$this->smarty->display('bugtrack/bugtrack_detail.html');
    }
    
    //单条删除要闻
    function tweet_del_one_ajax(){
        $tweet_id=intval($this->input->get('id'));
        $ret=0;
        if($tweet_id>0){
            if($this->tweet_model->del_info($tweet_id)){
                $ret=1;
            }
        }
        echo $ret;
    }

}
